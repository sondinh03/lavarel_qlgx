<?php

namespace App\Exports;

use App\Models\CatechismClass;
use App\Models\StudentNew;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    private int $rowIndex = 0;

    public function __construct(
        private ?int $classId,
        private ?int $parishId = null,
    ) {}

    public function collection()
    {
        return StudentNew::query()
            ->whereHas('classes', fn($q) => $q->where('classes.id', $this->classId))
            ->with(['saint', 'parishGroup'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'STT',
            'Tên thánh',
            'Họ tên đệm',
            'Tên',
            'Ngày sinh',
            'Giới tính',
            'Giao họ',
            'Họ tên bố',
            'Họ tên mẹ',
            'Số điện thoại',
            'Email',
            'Ghi chú',
        ];
    }

    public function map($student): array
    {
        return [
            ++$this->rowIndex,
            $student->saint?->name ?? '',
            $student->last_name,
            $student->first_name,
            $student->birthday?->format('d/m/Y') ?? '',
            match ($student->gender) {
                'male' => 'Nam',
                'female' => 'Nữ',
                default => '',
            },
            $student->parishGroup?->name ?? '',
            $student->father_name ?? '',
            $student->mother_name ?? '',
            $student->phone ?? '',
            $student->email ?? '',
            $student->note ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // 🔹 Toàn bộ sheet: Times New Roman
            'A1:L1000' => [
                'font' => [
                    'name' => 'Times New Roman',
                    'size' => 12,
                ],
            ],

            // 🔹 Header
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

                $class = CatechismClass::find($this->classId);
                $className = $class->name ?? '-';

                $headerRow = 4;
                $dataLastRow = $this->rowIndex > 0
                    ? $this->rowIndex + $headerRow
                    : $headerRow;

                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', "Danh sách học sinh - Lớp $className");
                $sheet->mergeCells("A1:L1");

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

                // ========================
                // 🔥 SUB HEADER
                // ========================
                $sheet->setCellValue('H2', 'Ngày xuất: ' . now()->format('d/m/Y H:i:s'));

                $sheet->mergeCells('H2:L2');

                $sheet->getStyle('A2:L2')->applyFromArray([
                    'font' => [
                        'size' => 12,
                        'name' => 'Times New Roman',
                    ],
                ]);

                // ========================
                // 🔹 HEADER TABLE (dòng 4)
                // ========================

                // Căn giữa header
                $sheet->getStyle("A{$headerRow}:L{$headerRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

                // Background header
                $sheet->getStyle("A{$headerRow}:L{$headerRow}")
                    ->getFill()
                    ->applyFromArray([
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'EAF7EF'],
                    ]);

                $sheet->getStyle("D" . ($headerRow + 1) . ":D{$dataLastRow}")
                    ->getFont()
                    ->setBold(true);

                // ========================
                // 🔥 BORDER (CHUẨN)
                // ========================
                $sheet->getStyle("A{$headerRow}:L{$dataLastRow}")
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

                // ========================
                // 🔹 AUTO WIDTH
                // ========================
                foreach (range('A', 'L') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // ========================
                // 🔹 STT CENTER
                // ========================
                $sheet->getStyle("A" . ($headerRow + 1) . ":A{$dataLastRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

                // ========================
                // 🔹 FREEZE
                // ========================
                $sheet->freezePane("A" . ($headerRow + 1));
            },
        ];
    }
}
