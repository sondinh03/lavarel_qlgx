<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\NamHoc;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Sheet tổng hợp học sinh vắng toàn giáo xứ — thống kê theo từng lớp.
 *
 * STT | Lớp | Số HS lớp | Số HS vắng | Lượt CP | Lượt KP | Tổng lượt vắng | Số buổi
 */
class AbsentStudentsParishSummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    private int $rowIndex = 0;

    /** @var array{students: int, absentees: int, cp: int, kp: int, total: int, sessions: int} */
    private array $totals = [
        'students'  => 0,
        'absentees' => 0,
        'cp'        => 0,
        'kp'        => 0,
        'total'     => 0,
        'sessions'  => 0,
    ];

    /**
     * @param  list<int>  $statuses
     */
    public function __construct(
        private int $parishId,
        private int $namHocId,
        private string $fromDate,
        private string $toDate,
        private ?int $attendanceType = null,
        private array $statuses = [
            AttendanceRecord::STATUS_ABSENT_EXCUSED,
            AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
        ],
    ) {}

    public function collection(): Collection
    {
        $classes = CatechismClass::query()
            ->where('classes.parish_id', $this->parishId)
            ->where('classes.school_year_id', $this->namHocId)
            ->active()
            ->ordered()
            ->select('classes.id', 'classes.name')
            ->get();

        if ($classes->isEmpty()) {
            return collect();
        }

        $classIds = $classes->pluck('id')->map(fn ($id) => (int) $id)->all();

        $studentCounts = DB::table('students_class')
            ->whereIn('class_id', $classIds)
            ->where('status', 1)
            ->groupBy('class_id')
            ->selectRaw('class_id, COUNT(DISTINCT student_id) as cnt')
            ->pluck('cnt', 'class_id');

        $sessionCounts = AttendanceSession::query()
            ->whereIn('class_id', $classIds)
            ->whereDate('date', '>=', $this->fromDate)
            ->whereDate('date', '<=', $this->toDate)
            ->when(
                $this->attendanceType !== null,
                fn ($q) => $q->where('type', $this->attendanceType)
            )
            ->groupBy('class_id')
            ->selectRaw('class_id, COUNT(*) as cnt')
            ->pluck('cnt', 'class_id');

        $absentStats = AttendanceRecord::query()
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendance_records.session_id')
            ->join('students_class', function ($join) {
                $join->on('students_class.student_id', '=', 'attendance_records.student_id')
                    ->on('students_class.class_id', '=', 'attendance_sessions.class_id')
                    ->where('students_class.status', 1);
            })
            ->whereIn('attendance_sessions.class_id', $classIds)
            ->whereDate('attendance_sessions.date', '>=', $this->fromDate)
            ->whereDate('attendance_sessions.date', '<=', $this->toDate)
            ->when(
                $this->attendanceType !== null,
                fn ($q) => $q->where('attendance_sessions.type', $this->attendanceType)
            )
            ->whereIn('attendance_records.status', $this->statuses)
            ->groupBy('attendance_sessions.class_id')
            ->selectRaw('
                attendance_sessions.class_id,
                COUNT(DISTINCT attendance_records.student_id) as absentees,
                SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as cp,
                SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as kp,
                COUNT(*) as total
            ', [
                AttendanceRecord::STATUS_ABSENT_EXCUSED,
                AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
            ])
            ->get()
            ->keyBy('class_id');

        $rows = $classes->map(function ($class) use ($studentCounts, $sessionCounts, $absentStats) {
            $classId = (int) $class->id;
            $stats = $absentStats->get($classId);
            $students = (int) ($studentCounts[$classId] ?? 0);
            $sessions = (int) ($sessionCounts[$classId] ?? 0);
            $absentees = (int) ($stats->absentees ?? 0);
            $cp = (int) ($stats->cp ?? 0);
            $kp = (int) ($stats->kp ?? 0);
            $total = (int) ($stats->total ?? 0);

            $this->totals['students'] += $students;
            $this->totals['absentees'] += $absentees;
            $this->totals['cp'] += $cp;
            $this->totals['kp'] += $kp;
            $this->totals['total'] += $total;
            $this->totals['sessions'] += $sessions;

            return (object) [
                'name'      => $class->name,
                'students'  => $students,
                'absentees' => $absentees,
                'cp'        => $cp,
                'kp'        => $kp,
                'total'     => $total,
                'sessions'  => $sessions,
            ];
        });

        return $rows->values();
    }

    public function headings(): array
    {
        return [
            'STT',
            'Lớp',
            'Số HS lớp',
            'Số HS vắng',
            'Lượt CP',
            'Lượt KP',
            'Tổng lượt vắng',
            'Số buổi',
        ];
    }

    public function map($row): array
    {
        return [
            ++$this->rowIndex,
            $row->name,
            $row->students,
            $row->absentees,
            $row->cp,
            $row->kp,
            $row->total,
            $row->sessions,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 13,
                    'name' => 'Times New Roman',
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Tổng hợp';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $lastCol = 'H';
                $headerRow = 4;
                $dataLastRow = $this->rowIndex > 0
                    ? $this->rowIndex + $headerRow
                    : $headerRow;

                $yearName = NamHoc::where('id', $this->namHocId)->value('name') ?? '-';
                $fromLabel = Carbon::parse($this->fromDate)->format('d/m/Y');
                $toLabel = Carbon::parse($this->toDate)->format('d/m/Y');
                $typeLabel = match ($this->attendanceType) {
                    AttendanceSession::TYPE_CLASS => 'Đi học',
                    AttendanceSession::TYPE_CEREMONY => 'Đi lễ',
                    default => 'Đi học + Đi lễ',
                };

                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue(
                    'A1',
                    "Tổng hợp học sinh vắng toàn giáo xứ - {$yearName} - {$fromLabel} → {$toLabel}"
                );
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'name' => 'Times New Roman',
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                $sheet->setCellValue(
                    'A2',
                    'Ngày xuất: ' . now()->format('d/m/Y H:i:s')
                        . " · {$typeLabel}"
                        . " · {$this->rowIndex} lớp"
                        . ' · Thống kê theo từng lớp (CP = có phép, KP = không phép)'
                );
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->setCellValue('A3', 'Thống kê theo lớp');
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'DCE6F1'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")
                    ->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => [
                            'horizontal' => 'center',
                            'wrapText' => true,
                        ],
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => ['rgb' => 'EAF7EF'],
                        ],
                    ]);

                $totalRow = $dataLastRow + 1;
                if ($this->rowIndex > 0) {
                    $sheet->setCellValue("A{$totalRow}", '');
                    $sheet->setCellValue("B{$totalRow}", 'Tổng cộng');
                    $sheet->setCellValue("C{$totalRow}", $this->totals['students']);
                    $sheet->setCellValue("D{$totalRow}", $this->totals['absentees']);
                    $sheet->setCellValue("E{$totalRow}", $this->totals['cp']);
                    $sheet->setCellValue("F{$totalRow}", $this->totals['kp']);
                    $sheet->setCellValue("G{$totalRow}", $this->totals['total']);
                    $sheet->setCellValue("H{$totalRow}", $this->totals['sessions']);

                    $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => ['rgb' => 'FFF7ED'],
                        ],
                    ]);
                }

                $borderLast = max($dataLastRow, $totalRow);
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$borderLast}")
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => 'thick',
                                'color' => ['rgb' => '000000'],
                            ],
                            'inside' => [
                                'borderStyle' => 'thin',
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                $sheet->getStyle("A1:{$lastCol}{$borderLast}")
                    ->getFont()
                    ->setName('Times New Roman')
                    ->setSize(12);

                foreach (range(1, 8) as $col) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
                }

                $sheet->getStyle("A" . ($headerRow + 1) . ":A{$borderLast}")
                    ->getAlignment()
                    ->setHorizontal('center');
                $sheet->getStyle("C" . ($headerRow + 1) . ":H{$borderLast}")
                    ->getAlignment()
                    ->setHorizontal('center');

                $sheet->freezePane('A' . ($headerRow + 1));
            },
        ];
    }
}
