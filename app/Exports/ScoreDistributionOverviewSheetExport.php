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

/** Sheet "Tổng quan" cho HK1, HK2 và cả năm. */
class ScoreDistributionOverviewSheetExport implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    /** Màu đồng bộ với sheet Chi tiết và file bảng điểm toàn xứ. */
    private const PERIOD_STYLES = [
        ['columns' => 'B:C', 'start' => 'B', 'header' => 'C6DBEF', 'data' => 'F2F7FB'],
        ['columns' => 'D:E', 'start' => 'D', 'header' => 'FBE0C4', 'data' => 'FFF7ED'],
        ['columns' => 'F:G', 'start' => 'F', 'header' => 'CFEBD9', 'data' => 'EAF7EF'],
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
        $semesterOne = $this->periods['semester_1']['overview'];
        $semesterTwo = $this->periods['semester_2']['overview'];
        $year        = $this->periods['year']['overview'];
        $rows = [
            ['Tổng quan kết quả học tập', '', '', '', '', '', ''],
            [
                $this->schoolYearName . ' · ' . $this->scopeName
                . ' · Xuất lúc ' . now()->format('H:i d/m/Y'),
                '', '', '', '', '', '',
            ],
            ['Chỉ số', 'Học kỳ 1', 'Học kỳ 2', 'Cả năm', '', '', ''],
            ['Điểm trung bình chung', $semesterOne['avg'], $semesterTwo['avg'], $year['avg'], '', '', ''],
            ['Điểm cao nhất', $semesterOne['max'], $semesterTwo['max'], $year['max'], '', '', ''],
            ['Điểm thấp nhất', $semesterOne['min'], $semesterTwo['min'], $year['min'], '', '', ''],
            ['Số học sinh đã có điểm', $semesterOne['count'], $semesterTwo['count'], $year['count'], '', '', ''],
            ['Học sinh chưa có điểm', $semesterOne['missing'], $semesterTwo['missing'], $year['missing'], '', '', ''],
            ['Tổng số học sinh', $semesterOne['total_students'], $semesterTwo['total_students'], $year['total_students'], '', '', ''],
            [
                'Tỉ lệ đạt (từ 5.0)',
                $semesterOne['pass_rate'] / 100,
                $semesterTwo['pass_rate'] / 100,
                $year['pass_rate'] / 100,
                '', '', '',
            ],
            ['', '', '', '', '', '', ''],
            ['Xếp loại học lực', 'HK1 - Số HS', 'HK1 - Tỉ lệ', 'HK2 - Số HS', 'HK2 - Tỉ lệ', 'Cả năm - Số HS', 'Cả năm - Tỉ lệ'],
        ];

        foreach ($this->periods['semester_1']['ratings'] as $index => $rating) {
            $semesterTwoRating = $this->periods['semester_2']['ratings'][$index];
            $yearRating        = $this->periods['year']['ratings'][$index];
            $rows[] = [
                $rating['label'],
                $rating['count'],
                $rating['percentage'] / 100,
                $semesterTwoRating['count'],
                $semesterTwoRating['percentage'] / 100,
                $yearRating['count'],
                $yearRating['percentage'] / 100,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Tổng quan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $metricFirst  = 4;
                $metricLast   = 10;
                $ratingHeader = 12;
                $ratingFirst  = 13;
                $ratingLast   = 12 + count($this->periods['semester_1']['ratings']);

                $sheet->getStyle('A1:G' . $ratingLast)->getFont()->setName('Times New Roman');

                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 16],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(26);

                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                foreach ([3, $ratingHeader] as $headerRow) {
                    $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
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
                    $sheet->getRowDimension($headerRow)->setRowHeight(22);
                }

                $sheet->getStyle("A{$metricFirst}:D{$metricLast}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'E2E8F0'],
                            ],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("A{$ratingFirst}:G{$ratingLast}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'E2E8F0'],
                        ],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("B{$metricFirst}:D{$metricLast}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$ratingFirst}:G{$ratingLast}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$metricFirst}:D{$metricLast}")->getFont()->setBold(true);

                // Bảng chỉ số: mỗi học kỳ một cột. Bảng xếp loại: mỗi học kỳ một cặp số lượng / tỉ lệ.
                $metricStyles = [
                    ['column' => 'B', 'header' => 'C6DBEF', 'data' => 'F2F7FB'],
                    ['column' => 'C', 'header' => 'FBE0C4', 'data' => 'FFF7ED'],
                    ['column' => 'D', 'header' => 'CFEBD9', 'data' => 'EAF7EF'],
                ];
                foreach ($metricStyles as $style) {
                    $column = $style['column'];
                    $sheet->getStyle("{$column}3")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($style['header']);
                    $sheet->getStyle("{$column}{$metricFirst}:{$column}{$metricLast}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($style['data']);
                    $this->addGroupBorder($sheet, $column, 3, $metricLast);
                }

                foreach (self::PERIOD_STYLES as $style) {
                    [$start, $end] = explode(':', $style['columns']);
                    $sheet->getStyle("{$start}{$ratingHeader}:{$end}{$ratingHeader}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($style['header']);
                    $sheet->getStyle("{$start}{$ratingFirst}:{$end}{$ratingLast}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($style['data']);
                    $this->addGroupBorder($sheet, $style['start'], $ratingHeader, $ratingLast);
                }

                $sheet->getStyle("B{$metricFirst}:D" . ($metricFirst + 2))
                    ->getNumberFormat()->setFormatCode('0.00');
                $sheet->getStyle("B{$metricLast}:D{$metricLast}")->getNumberFormat()->setFormatCode('0.0%');
                foreach (['C', 'E', 'G'] as $column) {
                    $sheet->getStyle("{$column}{$ratingFirst}:{$column}{$ratingLast}")
                    ->getNumberFormat()->setFormatCode('0.0%');
                }
                $sheet->setSelectedCell('A1');
            },
        ];
    }

    private function addGroupBorder($sheet, string $column, int $firstRow, int $lastRow): void
    {
        $sheet->getStyle("{$column}{$firstRow}:{$column}{$lastRow}")->applyFromArray([
            'borders' => [
                'left' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => '8A9BAC'],
                ],
            ],
        ]);
    }
}
