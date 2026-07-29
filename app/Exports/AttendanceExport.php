<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\StudentNew;
use App\Services\AttendanceStatusResolver;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Ma trận điểm danh tổng kết theo lớp / loại buổi.
 *
 * Cột cố định: STT | Tên thánh | Họ tên đệm | Tên | Giáo họ
 * Hàng ngày: từng buổi — ô: trống = có mặt, CP / KP / ?
 * Cột tổng kết theo HS: Có mặt | Vắng CP | Vắng KP | Tỷ lệ (%)
 * Cuối sheet: thống kê theo từng buổi
 */
class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    private int $rowIndex = 0;

    private const FIXED_COLUMNS = 5;

    private const SUMMARY_COLUMNS = 4;

    /** @var Collection<int, AttendanceSession>|null */
    private ?Collection $sessions = null;

    /** @var array<int, array<int, array{status: ?int, note: ?string}>> */
    private array $recordsMap = [];

    /** @var list<int> */
    private array $studentIds = [];

    private bool $recordsLoaded = false;

    private string $cutoffLabel = '20:00';

    /**
     * @param  int|null  $semester  1|2 = học kỳ, 3 = hè, null = cả năm
     * @param  int|null  $attendanceType  1=đi học, 2=đi lễ, null=cả hai
     */
    public function __construct(
        private ?int $classId,
        private ?int $semester = null,
        private ?int $attendanceType = 1,
        private string $sheetTitle = '',
    ) {}

    public function collection(): Collection
    {
        $this->loadSessions();
        $this->loadRecordsMap();

        $students = StudentNew::query()
            ->whereHas('classes', fn ($q) => $q->where('classes.id', $this->classId))
            ->join('students_class', 'students.id', '=', 'students_class.student_id')
            ->where('students_class.class_id', $this->classId)
            ->where('students_class.status', 1)
            ->with(['saint:id,name', 'parishGroup:id,name'])
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->select([
                'students.id',
                'students.saint_id',
                'students.last_name',
                'students.first_name',
                'students.parish_group_id',
            ])
            ->get();

        $this->studentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->all();

        return $students;
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
            $student->saint?->name ?? '',
            $student->last_name ?? '',
            $student->first_name ?? '',
            $student->parishGroup?->name ?? '',
        ];

        $present = 0;
        $absentExcused = 0;
        $absentUnexcused = 0;
        $sessionCount = $this->sessions?->count() ?? 0;

        foreach ($this->sessions ?? [] as $session) {
            $cell = $this->recordsMap[$student->id][$session->id] ?? null;
            $status = is_array($cell) && $cell['status'] !== null
                ? (int) $cell['status']
                : null;
            $row[] = $this->statusCell($status);

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
        if ($this->sheetTitle !== '') {
            return mb_substr($this->sheetTitle, 0, 31);
        }

        return match ($this->attendanceType) {
            AttendanceSession::TYPE_CEREMONY => 'Đi lễ',
            AttendanceSession::TYPE_CLASS => 'Đi học',
            default => 'Điểm danh',
        };
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->loadSessions();

                $class = CatechismClass::find($this->classId);
                $className = $class->name ?? '-';
                $typeLabel = match ($this->attendanceType) {
                    AttendanceSession::TYPE_CEREMONY => 'Đi lễ',
                    AttendanceSession::TYPE_CLASS => 'Đi học',
                    default => 'Đi học + Đi lễ',
                };
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
                    'Ngày xuất: ' . now()->format('d/m/Y H:i:s')
                        . " · {$this->rowIndex} học sinh · {$sessionCount} buổi"
                        . " · Giờ chốt: {$this->cutoffLabel}"
                        . ' · Ký hiệu: trống = có mặt, CP = có phép, KP = không phép, ? = chưa điểm danh'
                );
                $sheet->mergeCells("A2:{$lastCol}2");

                $this->appendSemesterHeader($sheet, $summaryStartCol, $lastCol);

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

                for ($col = 1; $col <= self::FIXED_COLUMNS; $col++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
                }

                if ($sessionCount > 0) {
                    $lastSessionCol = self::FIXED_COLUMNS + $sessionCount;
                    for ($col = self::FIXED_COLUMNS + 1; $col <= $lastSessionCol; $col++) {
                        $dim = $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col));
                        $dim->setAutoSize(false);
                        $dim->setWidth(7);
                    }

                    $sessionStart = Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS + 1);
                    $sessionEnd = Coordinate::stringFromColumnIndex($lastSessionCol);
                    $sheet->getStyle("{$sessionStart}" . ($headerRow + 1) . ":{$sessionEnd}{$dataLastRow}")
                        ->getAlignment()
                        ->setHorizontal('center')
                        ->setVertical('center')
                        ->setWrapText(true);
                }

                for ($col = self::FIXED_COLUMNS + $sessionCount + 1; $col <= $lastColIndex; $col++) {
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

                $statsLastRow = $this->appendSessionStatsRows($sheet, $headerRow, $dataLastRow);

                // Đóng băng đến hết cột "Tên" (D); cuộn ngang từ "Giáo họ"
                $sheet->freezePane('E' . ($headerRow + 1));

                if ($statsLastRow > $dataLastRow) {
                    $sheet->getStyle('A' . ($dataLastRow + 1) . ":{$lastCol}{$statsLastRow}")
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
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'F1F5F9'],
                            ],
                        ]);
                }
            },
        ];
    }

    /**
     * Hàng 3: Thông tin HS + nhóm cột buổi theo học kỳ + Tổng kết.
     */
    private function appendSemesterHeader(Worksheet $sheet, string $summaryStartCol, string $lastCol): void
    {
        $sheet->setCellValue('A3', 'Thông tin học sinh');
        $sheet->mergeCells('A3:' . Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS) . '3');

        $groups = [];
        foreach ($this->sessions ?? [] as $index => $session) {
            $label = match ((int) $session->semester) {
                1 => 'Học kỳ 1',
                2 => 'Học kỳ 2',
                default => 'Kỳ hè / ngoài học kỳ',
            };

            $columnIndex = self::FIXED_COLUMNS + $index + 1;
            if ($groups === [] || $groups[array_key_last($groups)]['label'] !== $label) {
                $groups[] = [
                    'label' => $label,
                    'start' => $columnIndex,
                    'end'   => $columnIndex,
                ];
            } else {
                $groups[array_key_last($groups)]['end'] = $columnIndex;
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

        $sheet->setCellValue("{$summaryStartCol}3", 'Tổng kết');
        $sheet->mergeCells("{$summaryStartCol}3:{$lastCol}3");

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
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    /**
     * 3 dòng thống kê theo buổi — giống footer trên giao diện điểm danh.
     */
    private function appendSessionStatsRows(Worksheet $sheet, int $headerRow, int $dataLastRow): int
    {
        if ($this->sessions === null || $this->sessions->isEmpty()) {
            return $dataLastRow;
        }

        $studentIds = $this->studentIds;
        if ($studentIds === [] && $this->rowIndex > 0) {
            $studentIds = array_keys($this->recordsMap);
        }

        $stats = [];
        foreach ($this->sessions as $session) {
            $present = 0;
            $excused = 0;
            $unexcused = 0;

            foreach ($studentIds as $studentId) {
                $status = $this->recordsMap[$studentId][$session->id]['status'] ?? null;
                $status = $status !== null ? (int) $status : null;
                match ($status) {
                    AttendanceRecord::STATUS_PRESENT => $present++,
                    AttendanceRecord::STATUS_ABSENT_EXCUSED => $excused++,
                    AttendanceRecord::STATUS_ABSENT_UNEXCUSED => $unexcused++,
                    default => null,
                };
            }

            $stats[(int) $session->id] = compact('present', 'excused', 'unexcused');
        }

        $rows = [
            ['label' => 'Thống kê — Có mặt', 'key' => 'present'],
            ['label' => 'Thống kê — Vắng có phép', 'key' => 'excused'],
            ['label' => 'Thống kê — Vắng không phép', 'key' => 'unexcused'],
        ];

        $rowNum = $dataLastRow;
        foreach ($rows as $meta) {
            $rowNum++;
            $sheet->setCellValue("A{$rowNum}", $meta['label']);
            $sheet->mergeCells(
                'A' . $rowNum . ':' . Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS) . $rowNum
            );

            $col = self::FIXED_COLUMNS;
            foreach ($this->sessions as $session) {
                $col++;
                $value = $stats[(int) $session->id][$meta['key']] ?? 0;
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($col) . $rowNum,
                    $value
                );
            }

            for ($i = 1; $i <= self::SUMMARY_COLUMNS; $i++) {
                $col++;
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . $rowNum, '');
            }
        }

        $firstStatsCol = Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS + 1);
        $lastSessionCol = Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS + $this->sessions->count());
        $sheet->getStyle("{$firstStatsCol}" . ($dataLastRow + 1) . ":{$lastSessionCol}{$rowNum}")
            ->getAlignment()
            ->setHorizontal('center');

        return $rowNum;
    }

    private function loadSessions(): void
    {
        if ($this->sessions !== null) {
            return;
        }

        $class = CatechismClass::query()->find($this->classId);
        $namHoc = $class?->school_year_id
            ? \App\Models\NamHoc::find((int) $class->school_year_id)
            : null;

        $query = AttendanceSession::query()
            ->where('class_id', $this->classId)
            ->when(
                $this->attendanceType !== null,
                fn ($q) => $q->where('type', $this->attendanceType)
            );

        if (in_array($this->semester, [1, 2, 3], true)) {
            app(\App\Services\SchoolYearResolver::class)
                ->applyAttendanceKyFilter($query, $namHoc, $this->semester);
        }

        $this->sessions = $query
            ->where('status', '!=', AttendanceSession::STATUS_CANCELLED)
            ->with('catechismClass.parish')
            ->orderBy('date')
            ->orderBy('type')
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

            return;
        }

        $records = AttendanceRecord::query()
            ->whereIn('session_id', $this->sessions->pluck('id'))
            ->get(['session_id', 'student_id', 'status', 'note']);

        foreach ($records as $record) {
            $status = $record->status !== null ? (int) $record->status : null;
            $this->recordsMap[(int) $record->student_id][(int) $record->session_id] = [
                'status' => $status,
                'note'   => $record->note,
            ];
        }

        $effectiveMatrix = app(AttendanceStatusResolver::class)->matrix($this->sessions);
        foreach ($effectiveMatrix as $sessionId => $studentStatuses) {
            foreach ($studentStatuses as $studentId => $status) {
                $this->recordsMap[$studentId][$sessionId] = [
                    'status' => $status,
                    'note'   => $this->recordsMap[$studentId][$sessionId]['note'] ?? null,
                ];
            }
        }
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

        // Có mặt — để trống, tránh rối mắt
        return '';
    }
}
