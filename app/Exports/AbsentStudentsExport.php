<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\StudentNew;
use App\Services\AttendanceStatusResolver;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
 * Ma trận học sinh vắng theo lớp.
 *
 * Cột cố định: STT | Tên thánh | Họ tên đệm | Tên | Giáo họ
 * Hàng loại buổi: nhóm theo type (Đi học rồi Đi lễ), merge một nhãn / nhóm
 * Hàng ngày: từng buổi
 * Ô: CP / KP / ? (chưa điểm danh); có mặt để trống
 */
class AbsentStudentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    private int $rowIndex = 0;

    private const FIXED_COLUMNS = 5;

    /** @var Collection<int, AttendanceSession>|null */
    private ?Collection $sessions = null;

    /** @var array<int, array<int, array{status: ?int, note: ?string}>> */
    private array $recordsMap = [];

    /** @var list<int> */
    private array $absentStudentIds = [];

    private bool $recordsLoaded = false;

    private string $cutoffLabel = '20:00';

    /**
     * @param  int|null  $attendanceType  1=đi học, 2=đi lễ, null=cả hai
     * @param  list<int>  $statuses
     */
    public function __construct(
        private int $classId,
        private string $fromDate,
        private string $toDate,
        private ?int $attendanceType = null,
        private array $statuses = [
            AttendanceRecord::STATUS_ABSENT_EXCUSED,
            AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
        ],
        private string $sheetTitle = 'Vắng',
    ) {}

    public function collection(): Collection
    {
        $this->loadSessions();
        $this->loadRecordsMap();

        if ($this->absentStudentIds === []) {
            return collect();
        }

        return StudentNew::query()
            ->whereIn('students.id', $this->absentStudentIds)
            ->whereHas('classes', function ($q) {
                $q->where('classes.id', $this->classId)
                    ->where('students_class.status', 1);
            })
            ->with(['saint:id,name', 'parishGroup:id,name'])
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->get([
                'students.id',
                'students.saint_id',
                'students.last_name',
                'students.first_name',
                'students.parish_group_id',
            ]);
    }

    public function headings(): array
    {
        $this->loadSessions();

        $headings = [
            'STT',
            'Tên thánh',
            'Họ tên đệm',
            'Tên',
            'Giáo họ',
        ];

        foreach ($this->sessions ?? [] as $session) {
            $date = $session->date;
            $dayName = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$date->dayOfWeek];
            $headings[] = "{$dayName} {$date->format('d/m')}";
        }

        return $headings;
    }

    public function map($student): array
    {
        $this->loadSessions();
        $this->loadRecordsMap();

        $row = [
            ++$this->rowIndex,
            $student->saint?->name ?? '',
            $student->last_name ?? '',
            $student->first_name ?? '',
            $student->parishGroup?->name ?? '',
        ];

        foreach ($this->sessions ?? [] as $session) {
            $cell = $this->recordsMap[$student->id][$session->id] ?? null;
            $row[] = $cell === null
                ? $this->statusCell(null)
                : $this->statusCell($cell['status'] ?? null);
        }

        return $row;
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
        return mb_substr($this->sheetTitle, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->loadSessions();

                $sessionCount = $this->sessions?->count() ?? 0;
                $lastColIndex = max(self::FIXED_COLUMNS, self::FIXED_COLUMNS + $sessionCount);
                $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);

                $headerRow = 4;
                $dataLastRow = $this->rowIndex > 0
                    ? $this->rowIndex + $headerRow
                    : $headerRow;

                $class = CatechismClass::find($this->classId);
                $className = $class->name ?? '-';
                $fromLabel = Carbon::parse($this->fromDate)->format('d/m/Y');
                $toLabel = Carbon::parse($this->toDate)->format('d/m/Y');
                $typeLabel = match ($this->attendanceType) {
                    AttendanceSession::TYPE_CLASS => 'Đi học',
                    AttendanceSession::TYPE_CEREMONY => 'Đi lễ',
                    default => 'Đi học + Đi lễ',
                };
                $statusLabel = $this->statusesLabel();

                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue(
                    'A1',
                    "Danh sách học sinh vắng - Lớp {$className} - {$fromLabel} → {$toLabel}"
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
                        . " · {$typeLabel} · {$statusLabel}"
                        . " · {$this->rowIndex} học sinh · {$sessionCount} buổi"
                        . " · Giờ chốt: {$this->cutoffLabel}"
                        . ' · Ký hiệu: CP = có phép, KP = không phép, ? = chưa điểm danh'
                );
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->setCellValue('A3', 'Thông tin học sinh');
                $sheet->mergeCells('A3:' . Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS) . '3');

                $this->appendTypeGroupHeader($sheet);

                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                        'wrapText' => true,
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

                $sheet->getStyle("A1:{$lastCol}" . max($dataLastRow, $headerRow))
                    ->getFont()
                    ->setName('Times New Roman')
                    ->setSize(12);

                // Cột thông tin HS: autosize
                for ($col = 1; $col <= self::FIXED_COLUMNS; $col++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
                }

                $sheet->getStyle('A' . ($headerRow + 1) . ":A{$dataLastRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

                if ($sessionCount > 0) {
                    for ($col = self::FIXED_COLUMNS + 1; $col <= $lastColIndex; $col++) {
                        $dim = $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col));
                        $dim->setAutoSize(false);
                        $dim->setWidth(7);
                    }

                    $sessionStart = Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS + 1);
                    $sheet->getStyle("{$sessionStart}" . ($headerRow + 1) . ":{$lastCol}{$dataLastRow}")
                        ->getAlignment()
                        ->setHorizontal('center')
                        ->setVertical('center')
                        ->setWrapText(true);
                }

                // Đóng băng hàng tiêu đề + các cột đến hết "Tên" (cột D); cuộn ngang từ "Giáo họ"
                $sheet->freezePane('E' . ($headerRow + 1));
            },
        ];
    }

    /**
     * Hàng 3: merge nhãn loại buổi theo nhóm (Đi học | Đi lễ).
     */
    private function appendTypeGroupHeader(Worksheet $sheet): void
    {
        if ($this->sessions === null || $this->sessions->isEmpty()) {
            return;
        }

        $groups = [];
        foreach ($this->sessions as $index => $session) {
            $type = (int) $session->type;
            $label = $this->typeLabel($type);
            $colIndex = self::FIXED_COLUMNS + $index + 1;

            if ($groups === [] || $groups[array_key_last($groups)]['type'] !== $type) {
                $groups[] = [
                    'type'  => $type,
                    'label' => $label,
                    'start' => $colIndex,
                    'end'   => $colIndex,
                ];
            } else {
                $groups[array_key_last($groups)]['end'] = $colIndex;
            }
        }

        foreach ($groups as $group) {
            $start = Coordinate::stringFromColumnIndex($group['start']);
            $end = Coordinate::stringFromColumnIndex($group['end']);
            $sheet->setCellValue("{$start}3", $group['label']);
            if ($start !== $end) {
                $sheet->mergeCells("{$start}3:{$end}3");
            }
        }
    }

    private function loadSessions(): void
    {
        if ($this->sessions !== null) {
            return;
        }

        // Gom cùng loại về một bên: Đi học trước, Đi lễ sau; trong nhóm sort theo ngày
        $this->sessions = AttendanceSession::query()
            ->where('class_id', $this->classId)
            ->whereDate('date', '>=', $this->fromDate)
            ->whereDate('date', '<=', $this->toDate)
            ->when(
                $this->attendanceType !== null,
                fn ($q) => $q->where('type', $this->attendanceType)
            )
            ->where('status', '!=', AttendanceSession::STATUS_CANCELLED)
            ->whereHas('records', function ($q) {
                $q->whereIn('status', [
                    AttendanceRecord::STATUS_PRESENT,
                    AttendanceRecord::STATUS_ABSENT_EXCUSED,
                    AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
                ])
                    ->whereHas('student.classes', function ($qq) {
                        $qq->where('classes.id', $this->classId)
                            ->where('students_class.status', 1);
                    });
            })
            ->with('catechismClass.parish')
            ->orderBy('type')
            ->orderBy('date')
            ->get(['id', 'class_id', 'date', 'type', 'semester', 'status']);

        $parish = $this->sessions->first()?->catechismClass?->parish;
        $this->cutoffLabel = app(AttendanceStatusResolver::class)->cutoffLabel($parish);
    }

    private function loadRecordsMap(): void
    {
        if ($this->recordsLoaded) {
            return;
        }

        $this->recordsLoaded = true;
        $this->loadSessions();

        if ($this->sessions === null || $this->sessions->isEmpty()) {
            $this->recordsMap = [];
            $this->absentStudentIds = [];

            return;
        }

        // Ghi chú vẫn lấy từ record thật; trạng thái hiệu lực lấy từ resolver chung.
        $records = AttendanceRecord::query()
            ->whereIn('session_id', $this->sessions->pluck('id'))
            ->whereHas('student.classes', function ($q) {
                $q->where('classes.id', $this->classId)
                    ->where('students_class.status', 1);
            })
            ->get(['session_id', 'student_id', 'status', 'note']);

        foreach ($records as $record) {
            $sid = (int) $record->student_id;

            $this->recordsMap[$sid][(int) $record->session_id] = [
                'status' => $record->status !== null ? (int) $record->status : null,
                'note'   => $record->note,
            ];
        }

        $effectiveMatrix = app(AttendanceStatusResolver::class)->matrix($this->sessions);
        $absentIds = [];
        $qualifyingSessionIds = [];

        foreach ($effectiveMatrix as $sessionId => $studentStatuses) {
            foreach ($studentStatuses as $studentId => $status) {
                $this->recordsMap[$studentId][$sessionId] = [
                    'status' => $status,
                    'note'   => $this->recordsMap[$studentId][$sessionId]['note'] ?? null,
                ];

                if ($status !== null && in_array($status, $this->statuses, true)) {
                    $absentIds[$studentId] = true;
                    $qualifyingSessionIds[$sessionId] = true;
                }
            }
        }

        $this->sessions = $this->sessions
            ->filter(fn ($session) => isset($qualifyingSessionIds[(int) $session->id]))
            ->values();
        $this->absentStudentIds = array_map('intval', array_keys($absentIds));
    }

    private function statusCell(?int $status): string
    {
        if ($status === null) {
            return '?';
        }

        if ($status === AttendanceRecord::STATUS_ABSENT_EXCUSED) {
            return 'CP';
        }

        if ($status === AttendanceRecord::STATUS_ABSENT_UNEXCUSED) {
            return 'KP';
        }

        // Có mặt — để trống trên sheet vắng
        return '';
    }

    private function statusesLabel(): string
    {
        $hasCp = in_array(AttendanceRecord::STATUS_ABSENT_EXCUSED, $this->statuses, true);
        $hasKp = in_array(AttendanceRecord::STATUS_ABSENT_UNEXCUSED, $this->statuses, true);

        if ($hasCp && $hasKp) {
            return 'Vắng có phép và không phép';
        }
        if ($hasCp) {
            return 'Vắng có phép';
        }
        if ($hasKp) {
            return 'Vắng không phép';
        }

        return 'Vắng';
    }

    private function typeLabel(int $type): string
    {
        return match ($type) {
            AttendanceSession::TYPE_CLASS => 'Đi học',
            AttendanceSession::TYPE_CEREMONY => 'Đi lễ',
            default => 'Khác',
        };
    }
}
