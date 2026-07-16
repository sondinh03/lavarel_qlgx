<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\StudentNew;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    private int $rowIndex = 0;

    /** @var Collection<int, AttendanceSession> */
    private ?Collection $sessions = null;

    /** @var array<int, array<int, array{status: ?int, note: ?string}>> */
    private array $recordsMap = [];

    private const FIXED_COLUMNS = 7;

    private const SUMMARY_COLUMNS = 4;

    public function __construct(
        private ?int $classId,
        private ?int $semester = 1,
        private int $attendanceType = 1,
    ) {}

    public function collection()
    {
        $this->loadSessions();
        $this->loadRecordsMap();

        return StudentNew::query()
            ->whereHas('classes', fn ($q) => $q->where('classes.id', $this->classId))
            ->join('students_class', 'students.id', '=', 'students_class.student_id')
            ->where('students_class.class_id', $this->classId)
            ->where('students_class.status', 1)
            ->with(['saint', 'parishGroup'])
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->select('students.*')
            ->get();
    }

    public function headings(): array
    {
        $this->loadSessions();

        $headings = [
            'STT',
            'Mã học sinh',
            'Tên thánh',
            'Họ tên đệm',
            'Tên',
            'Ngày sinh',
            'Giáo họ',
        ];

        foreach ($this->sessions ?? [] as $session) {
            $date = $session->date;
            $dayName = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$date->dayOfWeek];
            $headings[] = "{$dayName} {$date->format('d/m')}";
        }

        $headings[] = 'Có mặt';
        $headings[] = 'Vắng CP';
        $headings[] = 'Vắng KP';
        $headings[] = 'Tỷ lệ có mặt (%)';

        return $headings;
    }

    public function map($student): array
    {
        $this->loadSessions();
        $this->loadRecordsMap();

        $row = [
            ++$this->rowIndex,
            $student->student_code ?? '',
            $student->saint?->name ?? '',
            $student->last_name,
            $student->first_name,
            $student->birthday?->format('d/m/Y') ?? '',
            $student->parishGroup?->name ?? '',
        ];

        $present = 0;
        $absentExcused = 0;
        $absentUnexcused = 0;
        $sessionCount = $this->sessions?->count() ?? 0;

        foreach ($this->sessions ?? [] as $session) {
            $status = $this->recordsMap[$student->id][$session->id]['status'] ?? null;
            $row[] = $this->statusLabel($status);

            match ($status) {
                AttendanceRecord::STATUS_PRESENT => $present++,
                AttendanceRecord::STATUS_ABSENT_EXCUSED => $absentExcused++,
                AttendanceRecord::STATUS_ABSENT_UNEXCUSED => $absentUnexcused++,
                default => null,
            };
        }

        $rate = $sessionCount > 0
            ? round(($present / $sessionCount) * 100, 1)
            : '';

        $row[] = $present;
        $row[] = $absentExcused;
        $row[] = $absentUnexcused;
        $row[] = $rate;

        return $row;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            'A1:ZZ1000' => [
                'font' => [
                    'name' => 'Times New Roman',
                    'size' => 12,
                ],
            ],
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 13,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->loadSessions();

                $class = CatechismClass::find($this->classId);
                $className = $class->name ?? '-';
                $typeLabel = $this->attendanceType === AttendanceSession::TYPE_CEREMONY ? 'Đi lễ' : 'Đi học';
                $sessionCount = $this->sessions?->count() ?? 0;
                $lastColIndex = self::FIXED_COLUMNS + $sessionCount + self::SUMMARY_COLUMNS;
                $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
                $summaryStartCol = Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS + $sessionCount + 1);

                $headerRow = 4;
                $dataLastRow = $this->rowIndex > 0
                    ? $this->rowIndex + $headerRow
                    : $headerRow;

                $semesterLabel = match ($this->semester) {
                    1, 2 => "Học kỳ {$this->semester}",
                    3 => 'Kỳ hè',
                    default => 'Cả năm',
                };

                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue(
                    'A1',
                    "Bảng điểm danh - Lớp {$className} - {$semesterLabel} - {$typeLabel}"
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
                    'Ngày xuất: ' . now()->format('d/m/Y H:i:s') . " · {$sessionCount} buổi"
                );
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")
                    ->getAlignment()
                    ->setHorizontal('center')
                    ->setWrapText(true);

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")
                    ->getFill()
                    ->applyFromArray([
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'EAF7EF'],
                    ]);

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$dataLastRow}")
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

                for ($col = 1; $col <= $lastColIndex; $col++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
                }

                $sheet->getStyle('A' . ($headerRow + 1) . ":A{$dataLastRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

                $sheet->getStyle("{$summaryStartCol}{$headerRow}:{$lastCol}{$dataLastRow}")
                    ->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => 'center'],
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => ['rgb' => 'FFF7ED'],
                        ],
                        'borders' => [
                            'left' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                $sheet->freezePane('A' . ($headerRow + 1));
            },
        ];
    }

    private function loadSessions(): void
    {
        if ($this->sessions !== null) {
            return;
        }

        $this->sessions = AttendanceSession::query()
            ->where('class_id', $this->classId)
            ->where('type', $this->attendanceType)
            ->when(
                in_array($this->semester, [1, 2], true),
                fn ($q) => $q->where('semester', $this->semester)
            )
            ->when(
                $this->semester === 3,
                fn ($q) => $q->whereNull('semester')
            )
            ->orderBy('date')
            ->get();
    }

    private function loadRecordsMap(): void
    {
        if (!empty($this->recordsMap)) {
            return;
        }

        $this->loadSessions();

        if ($this->sessions === null || $this->sessions->isEmpty()) {
            return;
        }

        $records = AttendanceRecord::query()
            ->whereIn('session_id', $this->sessions->pluck('id'))
            ->get(['session_id', 'student_id', 'status', 'note']);

        foreach ($records as $record) {
            $this->recordsMap[$record->student_id][$record->session_id] = [
                'status' => $record->status,
                'note'   => $record->note,
            ];
        }
    }

    private function statusLabel(?int $status): string
    {
        return match ($status) {
            AttendanceRecord::STATUS_PRESENT => 'Có mặt',
            AttendanceRecord::STATUS_ABSENT_EXCUSED => 'Vắng CP',
            AttendanceRecord::STATUS_ABSENT_UNEXCUSED => 'Vắng KP',
            default => '',
        };
    }
}
