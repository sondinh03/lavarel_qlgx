<?php

namespace App\Exports;

use App\Models\NamHoc;
use App\Models\Teacher;
use App\Models\TeacherAttendanceRecord;
use App\Models\TeacherAttendanceSession;
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
 * Ma trận điểm danh tổng kết GLV theo loại buổi.
 *
 * Cột cố định: STT | Tên thánh | Họ tên đệm | Tên | Giáo họ
 * Ô: trống = có mặt, CP / KP / ?
 * Cột tổng kết theo GLV + thống kê theo buổi (cuối sheet)
 */
class TeacherAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    private int $rowIndex = 0;

    private const FIXED_COLUMNS = 5;

    private const SUMMARY_COLUMNS = 4;

    /** @var Collection<int, TeacherAttendanceSession>|null */
    private ?Collection $sessions = null;

    /** @var array<int, array<int, array{status: ?int, note: ?string}>> */
    private array $recordsMap = [];

    /** @var list<int> */
    private array $teacherIds = [];

    private bool $recordsLoaded = false;

    public function __construct(
        private int $parishId,
        private int $namHocId,
        private int $attendanceType = TeacherAttendanceSession::TYPE_TEACH,
        private string $sheetTitle = '',
    ) {}

    public function collection(): Collection
    {
        $this->loadSessions();
        $this->loadRecordsMap();

        $teachers = Teacher::query()
            ->where('parish_id', $this->parishId)
            ->active()
            ->with(['saint:id,name', 'parishGroup:id,name'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get([
                'id',
                'saint_id',
                'last_name',
                'first_name',
                'parish_group_id',
            ]);

        $this->teacherIds = $teachers->pluck('id')->map(fn ($id) => (int) $id)->all();

        return $teachers;
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

    public function map($teacher): array
    {
        $this->loadSessions();
        $this->loadRecordsMap();

        $row = [
            ++$this->rowIndex,
            $teacher->saint?->name ?? '',
            $teacher->last_name ?? '',
            $teacher->first_name ?? '',
            $teacher->parishGroup?->name ?? '',
        ];

        $present = 0;
        $absentExcused = 0;
        $absentUnexcused = 0;
        $sessionCount = $this->sessions?->count() ?? 0;

        foreach ($this->sessions ?? [] as $session) {
            $cell = $this->recordsMap[$teacher->id][$session->id] ?? null;
            $status = is_array($cell) && $cell['status'] !== null
                ? (int) $cell['status']
                : null;
            $row[] = $this->statusCell($status);

            match ($status) {
                TeacherAttendanceRecord::STATUS_PRESENT => $present++,
                TeacherAttendanceRecord::STATUS_ABSENT_EXCUSED => $absentExcused++,
                TeacherAttendanceRecord::STATUS_ABSENT_UNEXCUSED => $absentUnexcused++,
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

        return mb_substr(TeacherAttendanceSession::typeLabel($this->attendanceType), 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->loadSessions();

                $yearName = NamHoc::where('id', $this->namHocId)->value('name') ?? '-';
                $typeLabel = TeacherAttendanceSession::typeLabel($this->attendanceType);
                $sessionCount = $this->sessions?->count() ?? 0;
                $lastColIndex = self::FIXED_COLUMNS + $sessionCount + self::SUMMARY_COLUMNS;
                $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
                $summaryStartCol = Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS + $sessionCount + 1);

                $headerRow = 4;
                $dataLastRow = $this->rowIndex > 0
                    ? $this->rowIndex + $headerRow
                    : $headerRow;

                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue(
                    'A1',
                    "Bảng điểm danh GLV - {$yearName} - Cả năm - {$typeLabel}"
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
                        . " · {$this->rowIndex} GLV · {$sessionCount} buổi"
                        . ' · Ký hiệu: trống = có mặt, CP = có phép, KP = không phép, ? = chưa điểm danh'
                );
                $sheet->mergeCells("A2:{$lastCol}2");

                $this->appendGroupHeader($sheet, $summaryStartCol, $lastCol, $typeLabel);

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

                $statsLastRow = $this->appendSessionStatsRows($sheet, $dataLastRow);

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

    private function appendGroupHeader(
        Worksheet $sheet,
        string $summaryStartCol,
        string $lastCol,
        string $typeLabel
    ): void {
        $sheet->setCellValue('A3', 'Thông tin GLV');
        $sheet->mergeCells('A3:' . Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS) . '3');

        $sessionCount = $this->sessions?->count() ?? 0;
        if ($sessionCount > 0) {
            $start = Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS + 1);
            $end = Coordinate::stringFromColumnIndex(self::FIXED_COLUMNS + $sessionCount);
            $sheet->setCellValue("{$start}3", $typeLabel);
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

    private function appendSessionStatsRows(Worksheet $sheet, int $dataLastRow): int
    {
        if ($this->sessions === null || $this->sessions->isEmpty()) {
            return $dataLastRow;
        }

        $teacherIds = $this->teacherIds !== []
            ? $this->teacherIds
            : array_keys($this->recordsMap);

        $stats = [];
        foreach ($this->sessions as $session) {
            $present = 0;
            $excused = 0;
            $unexcused = 0;

            foreach ($teacherIds as $teacherId) {
                $status = $this->recordsMap[$teacherId][$session->id]['status'] ?? null;
                $status = $status !== null ? (int) $status : null;
                match ($status) {
                    TeacherAttendanceRecord::STATUS_PRESENT => $present++,
                    TeacherAttendanceRecord::STATUS_ABSENT_EXCUSED => $excused++,
                    TeacherAttendanceRecord::STATUS_ABSENT_UNEXCUSED => $unexcused++,
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
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($col) . $rowNum,
                    $stats[(int) $session->id][$meta['key']] ?? 0
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

        $this->sessions = TeacherAttendanceSession::query()
            ->where('parish_id', $this->parishId)
            ->where('namhoc_id', $this->namHocId)
            ->where('type', $this->attendanceType)
            ->where('status', '!=', TeacherAttendanceSession::STATUS_CANCELLED)
            ->orderBy('date')
            ->get(['id', 'parish_id', 'namhoc_id', 'date', 'type', 'status']);
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

        $records = TeacherAttendanceRecord::query()
            ->whereIn('session_id', $this->sessions->pluck('id'))
            ->get(['session_id', 'teacher_id', 'status', 'note']);

        foreach ($records as $record) {
            $status = $record->status !== null ? (int) $record->status : null;
            $this->recordsMap[(int) $record->teacher_id][(int) $record->session_id] = [
                'status' => $status,
                'note'   => $record->note,
            ];
        }
    }

    private function statusCell(?int $status): string
    {
        if ($status === null) {
            return '?';
        }

        if ($status === TeacherAttendanceRecord::STATUS_ABSENT_EXCUSED) {
            return 'CP';
        }

        if ($status === TeacherAttendanceRecord::STATUS_ABSENT_UNEXCUSED) {
            return 'KP';
        }

        return '';
    }
}
