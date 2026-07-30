<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Sheet "Chi tiết": mỗi học sinh một dòng, đủ HK1, HK2 và cả năm.
 *
 * Màu theo nhóm cột dùng lại bảng màu của file bảng điểm (App\Exports\ScoreExport)
 * để hai file đọc giống nhau: xanh = học kỳ 1, cam = học kỳ 2, xanh lá = cả năm.
 */
class ScoreDistributionDetailSheetExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents,
    ShouldAutoSize
{
    private const GROUP_ROW = 3;
    private const HEADER_ROW = 4;
    private const LAST_COLUMN = 'L';

    /**
     * Nhóm cột: nhãn, cột đầu / cuối và ba mức màu (tiêu đề nhóm, tiêu đề cột, vùng dữ liệu).
     */
    private const COLUMN_GROUPS = [
        ['label' => 'Thông tin học sinh', 'start' => 'A', 'end' => 'F', 'group' => 'FFFFFF', 'header' => 'FFFFFF', 'data' => null],
        ['label' => 'Học kỳ 1',           'start' => 'G', 'end' => 'H', 'group' => 'C6DBEF', 'header' => 'DCE9F7', 'data' => 'F2F7FB'],
        ['label' => 'Học kỳ 2',           'start' => 'I', 'end' => 'J', 'group' => 'FBE0C4', 'header' => 'FDEEDB', 'data' => 'FFF7ED'],
        ['label' => 'Tổng kết cả năm',    'start' => 'K', 'end' => 'L', 'group' => 'CFEBD9', 'header' => 'E3F5EA', 'data' => 'EAF7EF'],
    ];

    /** Cột điểm trung bình — in đậm và mở đầu mỗi nhóm học kỳ. */
    private const AVERAGE_COLUMNS = ['G', 'I', 'K'];

    private int $rowIndex = 0;

    /**
     * @param  Collection<int, array<string, mixed>>  $students
     */
    public function __construct(
        private Collection $students,
        private string $schoolYearName,
        private string $scopeName,
    ) {}

    public function collection(): Collection
    {
        return $this->students;
    }

    public function headings(): array
    {
        return [
            'STT',
            'Tên thánh',
            'Họ tên đệm',
            'Tên',
            'Khối',
            'Lớp',
            'Điểm TB HK1',
            'Xếp loại HK1',
            'Điểm TB HK2',
            'Xếp loại HK2',
            'Điểm TB cả năm',
            'Xếp loại cả năm',
        ];
    }

    /**
     * @param  array<string, mixed>  $student
     */
    public function map($student): array
    {
        $this->rowIndex++;

        return [
            $this->rowIndex,
            $student['saint_name'],
            $student['last_name'],
            $student['first_name'],
            $student['grade_name'],
            $student['class_name'],
            $student['semester_1_average'],
            $student['semester_1_rating'],
            $student['semester_2_average'],
            $student['semester_2_rating'],
            $student['year_average'],
            $student['year_rating'],
        ];
    }

    public function title(): string
    {
        return 'Chi tiết';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $col     = self::LAST_COLUMN;
                $header  = self::HEADER_ROW;
                $first   = $header + 1;
                $lastRow = max($header, $header + $this->students->count());

                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', 'Danh sách chi tiết kết quả học tập');
                $sheet->mergeCells("A1:{$col}1");

                $sheet->setCellValue(
                    'A2',
                    $this->schoolYearName . ' · ' . $this->scopeName
                    . ' · ' . $this->students->count() . ' học sinh'
                    . ' · Sắp xếp ưu tiên điểm cả năm giảm dần'
                    . ' · Xuất lúc ' . now()->format('H:i d/m/Y')
                );
                $sheet->mergeCells("A2:{$col}2");

                $sheet->getStyle("A1:{$col}{$lastRow}")->getFont()->setName('Times New Roman');
                $sheet->getStyle("A1:{$col}1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 16],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(26);

                $sheet->getStyle("A2:{$col}2")->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $this->styleColumnGroups($sheet, $lastRow);

                $sheet->getStyle("A{$header}:{$col}{$header}")->applyFromArray([
                    'font'      => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);
                $sheet->getRowDimension(self::GROUP_ROW)->setRowHeight(24);
                $sheet->getRowDimension($header)->setRowHeight(34);

                $sheet->getStyle('A' . self::GROUP_ROW . ":{$col}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'B9C6D4'],
                        ],
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color'       => ['rgb' => '8A9BAC'],
                        ],
                    ],
                ]);

                if ($this->students->isNotEmpty()) {
                    $sheet->getStyle("A{$first}:{$col}{$lastRow}")->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A{$first}:B{$lastRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$first}:{$col}{$lastRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$first}:D{$lastRow}")->getFont()->setBold(true);

                    foreach (self::AVERAGE_COLUMNS as $column) {
                        $sheet->getStyle("{$column}{$first}:{$column}{$lastRow}")
                            ->getNumberFormat()->setFormatCode('0.0');
                        $sheet->getStyle("{$column}{$first}:{$column}{$lastRow}")
                            ->getFont()->setBold(true);
                    }
                }

                // Vạch đậm mở đầu mỗi nhóm học kỳ để mắt tách nhóm ngay cả khi in trắng đen.
                foreach (self::AVERAGE_COLUMNS as $column) {
                    $sheet->getStyle("{$column}" . self::GROUP_ROW . ":{$column}{$lastRow}")
                        ->applyFromArray([
                            'borders' => [
                                'left' => [
                                    'borderStyle' => Border::BORDER_MEDIUM,
                                    'color'       => ['rgb' => '8A9BAC'],
                                ],
                            ],
                        ]);
                }

                $sheet->freezePane('E' . $first);
                $sheet->setAutoFilter("A{$header}:{$col}{$lastRow}");
                $sheet->setSelectedCell('A1');
            },
        ];
    }

    private function styleColumnGroups(Worksheet $sheet, int $lastRow): void
    {
        $groupRow = self::GROUP_ROW;
        $header   = self::HEADER_ROW;
        $first    = $header + 1;

        foreach (self::COLUMN_GROUPS as $group) {
            $start = $group['start'];
            $end   = $group['end'];

            $sheet->setCellValue("{$start}{$groupRow}", $group['label']);
            $sheet->mergeCells("{$start}{$groupRow}:{$end}{$groupRow}");
            $sheet->getStyle("{$start}{$groupRow}:{$end}{$groupRow}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $group['group']],
                ],
            ]);

            $sheet->getStyle("{$start}{$header}:{$end}{$header}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $group['header']],
                ],
            ]);

            if ($group['data'] !== null && $this->students->isNotEmpty()) {
                $sheet->getStyle("{$start}{$first}:{$end}{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $group['data']],
                    ],
                ]);
            }
        }
    }
}
