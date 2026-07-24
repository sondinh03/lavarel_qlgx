<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
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
 * Một sheet danh sách học sinh vắng của một lớp (format gần giống AttendanceExport).
 */
class AbsentStudentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    private int $rowIndex = 0;

    private const LAST_COL_INDEX = 12;

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
        return AttendanceRecord::query()
            ->with([
                'student:id,student_code,saint_id,last_name,first_name,birthday,parish_group_id',
                'student.saint:id,name',
                'student.parishGroup:id,name',
                'session:id,class_id,date,type,semester',
            ])
            ->whereIn('status', $this->statuses)
            ->whereHas('session', function ($q) {
                $q->where('class_id', $this->classId)
                    ->whereDate('date', '>=', $this->fromDate)
                    ->whereDate('date', '<=', $this->toDate)
                    ->when(
                        $this->attendanceType !== null,
                        fn ($qq) => $qq->where('type', $this->attendanceType)
                    );
            })
            ->whereHas('student.classes', function ($q) {
                $q->where('classes.id', $this->classId)
                    ->where('students_class.status', 1);
            })
            ->get()
            ->sortBy([
                fn ($r) => optional($r->session?->date)->format('Y-m-d') ?? '',
                fn ($r) => (int) ($r->session?->type ?? 0),
                fn ($r) => $r->student?->first_name ?? '',
                fn ($r) => $r->student?->last_name ?? '',
            ])
            ->values();
    }

    public function headings(): array
    {
        return [
            'STT',
            'Mã học sinh',
            'Tên thánh',
            'Họ tên đệm',
            'Tên',
            'Ngày sinh',
            'Giáo họ',
            'Ngày',
            'Thứ',
            'Loại buổi',
            'Trạng thái',
            'Ghi chú',
        ];
    }

    public function map($record): array
    {
        $student = $record->student;
        $session = $record->session;
        $date = $session?->date;

        return [
            ++$this->rowIndex,
            $student?->student_code ?? '',
            $student?->saint?->name ?? '',
            $student?->last_name ?? '',
            $student?->first_name ?? '',
            $student?->birthday?->format('d/m/Y') ?? '',
            $student?->parishGroup?->name ?? '',
            $date ? $date->format('d/m/Y') : '',
            $date ? $this->vietnameseDayShort($date) : '',
            $this->typeLabel((int) ($session?->type ?? 0)),
            $this->statusLabel((int) $record->status),
            $record->note ?? '',
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
        return mb_substr($this->sheetTitle, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(self::LAST_COL_INDEX);
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
                        . " · {$this->rowIndex} lượt vắng"
                );
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->setCellValue('A3', 'Thông tin học sinh');
                $sheet->mergeCells('A3:G3');
                $sheet->setCellValue('H3', 'Buổi vắng');
                $sheet->mergeCells('H3:L3');

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

                for ($col = 1; $col <= self::LAST_COL_INDEX; $col++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
                }

                $sheet->getStyle('A' . ($headerRow + 1) . ":A{$dataLastRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

                $sheet->getStyle('H' . ($headerRow + 1) . ":K{$dataLastRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

                $sheet->freezePane('A' . ($headerRow + 1));
            },
        ];
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

    private function statusLabel(int $status): string
    {
        return match ($status) {
            AttendanceRecord::STATUS_ABSENT_EXCUSED => 'Vắng có phép',
            AttendanceRecord::STATUS_ABSENT_UNEXCUSED => 'Vắng không phép',
            default => 'Khác',
        };
    }

    private function vietnameseDayShort(Carbon $date): string
    {
        return ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$date->dayOfWeek];
    }
}
