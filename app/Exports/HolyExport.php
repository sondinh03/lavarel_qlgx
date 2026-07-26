<?php

namespace App\Exports;

use App\Models\Holymanagement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Xuất danh sách tên thánh (danh mục dùng chung).
 */
class HolyExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    private int $rowIndex = 0;

    public function __construct(
        private ?string $search = null,
    ) {}

    public function collection(): Collection
    {
        return Holymanagement::query()
            ->withCount(['students', 'parishioners', 'teachers'])
            ->when($this->search !== null && trim($this->search) !== '', function ($q) {
                $q->where('name', 'like', '%' . trim($this->search) . '%');
            })
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'STT',
            'Tên thánh',
            'Số học sinh',
            'Số giáo dân',
            'Số GLV',
        ];
    }

    public function map($holy): array
    {
        ++$this->rowIndex;

        return [
            $this->rowIndex,
            $holy->name ?? '',
            (int) ($holy->students_count ?? 0),
            (int) ($holy->parishioners_count ?? 0),
            (int) ($holy->teachers_count ?? 0),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            'A1:E1000' => [
                'font' => [
                    'name' => 'Times New Roman',
                    'size' => 12,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'HỆ THỐNG QUẢN LÝ GIÁO XỨ');
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'name' => 'Times New Roman',
                    ],
                    'alignment' => [
                        'horizontal' => 'left',
                        'vertical'   => 'center',
                    ],
                ]);

                $sheet->setCellValue('A2', 'DANH SÁCH TÊN THÁNH');
                $sheet->mergeCells('A2:E2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'name' => 'Times New Roman',
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical'   => 'center',
                    ],
                ]);

                $sheet->setCellValue('A3', 'Ngày xuất: ' . now()->format('d/m/Y H:i:s'));
                $sheet->mergeCells('A3:E3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'size' => 12,
                        'name' => 'Times New Roman',
                    ],
                    'alignment' => [
                        'horizontal' => 'right',
                        'vertical'   => 'center',
                    ],
                ]);

                $headerRow = 4;
                $dataLastRow = $this->rowIndex > 0
                    ? $this->rowIndex + $headerRow
                    : $headerRow;

                $sheet->getStyle("A{$headerRow}:E{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'name' => 'Times New Roman',
                    ],
                    'fill' => [
                        'fillType'   => 'solid',
                        'startColor' => ['rgb' => 'EAF7EF'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical'   => 'center',
                    ],
                ]);

                if ($this->rowIndex > 0) {
                    $sheet->getStyle("A{$headerRow}:E{$dataLastRow}")->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => 'thick',
                                'color'       => ['rgb' => '000000'],
                            ],
                            'inside' => [
                                'borderStyle' => 'thin',
                                'color'       => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                    $sheet->getStyle('A' . ($headerRow + 1) . ":A{$dataLastRow}")
                        ->getAlignment()
                        ->setHorizontal('center');

                    $sheet->getStyle('C' . ($headerRow + 1) . ":E{$dataLastRow}")
                        ->getAlignment()
                        ->setHorizontal('center');

                    $sheet->getStyle('B' . ($headerRow + 1) . ":B{$dataLastRow}")
                        ->getFont()
                        ->setBold(true);
                }

                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->freezePane('A5');
            },
        ];
    }
}
