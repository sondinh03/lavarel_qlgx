<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/** Sheet "Phân phối" cho HK1, HK2 và cả năm. */
class ScoreDistributionRangesSheetExport implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    private const HEADER_ROW = 3;

    /** Màu đồng bộ với sheet Chi tiết và file bảng điểm toàn xứ. */
    private const PERIOD_STYLES = [
        ['start' => 'C', 'end' => 'D', 'header' => 'C6DBEF', 'data' => 'F2F7FB'],
        ['start' => 'E', 'end' => 'F', 'header' => 'FBE0C4', 'data' => 'FFF7ED'],
        ['start' => 'G', 'end' => 'H', 'header' => 'CFEBD9', 'data' => 'EAF7EF'],
    ];

    /**
     * @param  array<string, array<string, mixed>>  $periods
     */
    public function __construct(
        private array $periods,
        private string $schoolYearName,
        private string $scopeName,
    ) {}

    public function array(): array
    {
        $semesterOne = $this->periods['semester_1']['distribution'];
        $semesterTwo = $this->periods['semester_2']['distribution'];
        $year        = $this->periods['year']['distribution'];
        $totals = [
            array_sum(array_column($semesterOne, 'count')),
            array_sum(array_column($semesterTwo, 'count')),
            array_sum(array_column($year, 'count')),
        ];

        $rows = [
            ['Phân phối điểm trung bình', '', '', '', '', '', '', ''],
            [
                $this->schoolYearName . ' · ' . $this->scopeName
                . ' · Xuất lúc ' . now()->format('H:i d/m/Y'),
                '', '', '', '', '', '', '',
            ],
            [
                'Khoảng điểm', 'Diễn giải',
                'HK1 - Số HS', 'HK1 - Tỉ lệ',
                'HK2 - Số HS', 'HK2 - Tỉ lệ',
                'Cả năm - Số HS', 'Cả năm - Tỉ lệ',
            ],
        ];

        foreach ($semesterOne as $index => $range) {
            $rows[] = [
                $range['label'],
                $range['description'],
                $range['count'],
                $range['percentage'] / 100,
                $semesterTwo[$index]['count'],
                $semesterTwo[$index]['percentage'] / 100,
                $year[$index]['count'],
                $year[$index]['percentage'] / 100,
            ];
        }

        $rows[] = [
            'Tổng cộng', '',
            $totals[0], $totals[0] > 0 ? 1 : 0,
            $totals[1], $totals[1] > 0 ? 1 : 0,
            $totals[2], $totals[2] > 0 ? 1 : 0,
        ];

        return $rows;
    }

    public function title(): string
    {
        return 'Phân phối';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $firstRow = self::HEADER_ROW + 1;
                $distribution = $this->periods['semester_1']['distribution'];
                $lastRow  = self::HEADER_ROW + count($distribution);
                $totalRow = $lastRow + 1;

                $sheet->getStyle('A1:H' . $totalRow)->getFont()->setName('Times New Roman');

                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 16],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(26);

                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A' . self::HEADER_ROW . ':H' . self::HEADER_ROW)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '334155']],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DCE6F1'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(self::HEADER_ROW)->setRowHeight(22);

                $sheet->getStyle("A{$firstRow}:H{$totalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'E2E8F0'],
                        ],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("A{$firstRow}:A{$totalRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$firstRow}:H{$totalRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                foreach (['D', 'F', 'H'] as $column) {
                    $sheet->getStyle("{$column}{$firstRow}:{$column}{$totalRow}")
                        ->getNumberFormat()->setFormatCode('0.0%');
                }

                // Mỗi cặp số lượng / tỉ lệ mang màu của đúng học kỳ.
                foreach (self::PERIOD_STYLES as $style) {
                    $start = $style['start'];
                    $end   = $style['end'];
                    $sheet->getStyle("{$start}" . self::HEADER_ROW . ":{$end}" . self::HEADER_ROW)
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($style['header']);
                    $sheet->getStyle("{$start}{$firstRow}:{$end}{$lastRow}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($style['data']);
                    $sheet->getStyle("{$start}{$totalRow}:{$end}{$totalRow}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($style['header']);
                    $sheet->getStyle("{$start}" . self::HEADER_ROW . ":{$start}{$totalRow}")
                        ->applyFromArray([
                            'borders' => [
                                'left' => [
                                    'borderStyle' => Border::BORDER_MEDIUM,
                                    'color'       => ['rgb' => '8A9BAC'],
                                ],
                            ],
                        ]);
                }

                // Mỗi khoảng dùng đúng màu của biểu đồ cột để đọc nhanh.
                foreach ($distribution as $index => $range) {
                    $row = $firstRow + $index;
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => strtoupper(ltrim((string) $range['color'], '#'))],
                        ],
                    ]);
                }

                $sheet->getStyle("A{$totalRow}:H{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                $sheet->getStyle("A{$totalRow}:B{$totalRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('DCE6F1');

                $sheet->setSelectedCell('A1');
            },
        ];
    }
}
