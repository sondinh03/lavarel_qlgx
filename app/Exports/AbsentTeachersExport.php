<?php

namespace App\Exports;

use App\Models\NamHoc;
use App\Models\Teacher;
use App\Models\TeacherAttendanceRecord;
use App\Models\TeacherAttendanceSession;
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
 * Ma trận GLV vắng theo loại buổi / khoảng ngày.
 *
 * Cột cố định: STT | Tên thánh | Họ tên đệm | Tên | Giáo họ
 * Ô: CP / KP / ? ; có mặt để trống
 */
class AbsentTeachersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    private int $rowIndex = 0;

    private const FIXED_COLUMNS = 5;

    /** @var Collection<int, TeacherAttendanceSession>|null */
    private ?Collection $sessions = null;

    /** @var array<int, array<int, array{status: ?int, note: ?string}>> */
    private array $recordsMap = [];

    /** @var list<int> */
    private array $absentTeacherIds = [];

    private bool $recordsLoaded = false;

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
            TeacherAttendanceRecord::STATUS_ABSENT_EXCUSED,
            TeacherAttendanceRecord::STATUS_ABSENT_UNEXCUSED,
        ],
        private string $sheetTitle = 'Vắng',
    ) {}

    public function collection(): Collection
    {
        $this->loadSessions();
        $this->loadRecordsMap();

        if ($this->absentTeacherIds === []) {
            return collect();
        }

        return Teacher::query()
            ->where('parish_id', $this->parishId)
            ->whereIn('id', $this->absentTeacherIds)
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

        foreach ($this->sessions ?? [] as $session) {
            $cell = $this->recordsMap[$teacher->id][$session->id] ?? null;
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

                $yearName = NamHoc::where('id', $this->namHocId)->value('name') ?? '-';
                $fromLabel = Carbon::parse($this->fromDate)->format('d/m/Y');
                $toLabel = Carbon::parse($this->toDate)->format('d/m/Y');
                $typeLabel = $this->attendanceType !== null
                    ? TeacherAttendanceSession::typeLabel($this->attendanceType)
                    : 'Tất cả loại buổi';
                $statusLabel = $this->statusesLabel();

                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue(
                    'A1',
                    "Danh sách GLV vắng - {$yearName} - {$fromLabel} → {$toLabel}"
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
                        . " · {$this->rowIndex} GLV · {$sessionCount} buổi"
                        . ' · Ký hiệu: CP = có phép, KP = không phép, ? = chưa điểm danh'
                );
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->setCellValue('A3', 'Thông tin GLV');
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

                $sheet->freezePane('E' . ($headerRow + 1));
            },
        ];
    }

    private function appendTypeGroupHeader(Worksheet $sheet): void
    {
        if ($this->sessions === null || $this->sessions->isEmpty()) {
            return;
        }

        $groups = [];
        foreach ($this->sessions as $index => $session) {
            $type = (int) $session->type;
            $label = TeacherAttendanceSession::typeLabel($type);
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

        $this->sessions = TeacherAttendanceSession::query()
            ->where('parish_id', $this->parishId)
            ->where('namhoc_id', $this->namHocId)
            ->whereDate('date', '>=', $this->fromDate)
            ->whereDate('date', '<=', $this->toDate)
            ->where('status', '!=', TeacherAttendanceSession::STATUS_CANCELLED)
            ->when(
                $this->attendanceType !== null,
                fn ($q) => $q->where('type', $this->attendanceType)
            )
            ->whereHas('records', function ($q) {
                $q->whereIn('status', $this->statuses)
                    ->whereHas('teacher', function ($qq) {
                        $qq->where('parish_id', $this->parishId)->active();
                    });
            })
            ->orderBy('type')
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
            $this->absentTeacherIds = [];

            return;
        }

        $records = TeacherAttendanceRecord::query()
            ->whereIn('session_id', $this->sessions->pluck('id'))
            ->whereHas('teacher', function ($q) {
                $q->where('parish_id', $this->parishId)->active();
            })
            ->get(['session_id', 'teacher_id', 'status', 'note']);

        $absentIds = [];
        foreach ($records as $record) {
            $tid = (int) $record->teacher_id;
            $status = $record->status !== null ? (int) $record->status : null;

            $this->recordsMap[$tid][(int) $record->session_id] = [
                'status' => $status,
                'note'   => $record->note,
            ];

            if ($status !== null && in_array($status, $this->statuses, true)) {
                $absentIds[$tid] = true;
            }
        }

        $this->absentTeacherIds = array_map('intval', array_keys($absentIds));
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

    private function statusesLabel(): string
    {
        $hasCp = in_array(TeacherAttendanceRecord::STATUS_ABSENT_EXCUSED, $this->statuses, true);
        $hasKp = in_array(TeacherAttendanceRecord::STATUS_ABSENT_UNEXCUSED, $this->statuses, true);

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
}
