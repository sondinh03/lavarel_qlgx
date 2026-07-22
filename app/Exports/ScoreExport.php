<?php

namespace App\Exports;

use App\Models\CatechismClass;
use App\Models\ScoreType;
use App\Models\StudentNew;
use App\Models\StudentScore;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScoreExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    private int $rowIndex = 0;
    private ?Collection $scoreTypes = null;
    private ?Collection $semesterOneTypes = null;
    private ?Collection $semesterTwoTypes = null;
    private array $scoresMap = [];

    private const FIXED_COLUMNS = 7;

    private const SUMMARY_COLUMNS = 4;

    private const RATING_LEVELS = [
        'XUAT_SAC' => ['min' => 9.5, 'max' => 10],
        'GIOI'     => ['min' => 8.0, 'max' => 9.5],
        'KHA'      => ['min' => 6.5, 'max' => 8.0],
        'TRUNG_BINH' => ['min' => 5.0, 'max' => 6.5],
        'YEU'      => ['min' => 3.5, 'max' => 5.0],
        'KEM'      => ['min' => 0, 'max' => 3.5],
    ];

    public function __construct(
        private ?int $classId,
        private ?string $filterByRating = null,
    ) {}

    public function collection()
    {
        $students = StudentNew::query()
            ->whereHas('classes', fn($q) => $q->where('classes.id', $this->classId))
            ->join('students_class', 'students.id', '=', 'students_class.student_id')
            ->where('students_class.class_id', $this->classId)
            ->with(['saint', 'parishGroup'])
            ->orderBy('students.first_name')
            ->orderBy('students.last_name')
            ->select('students.*', 'students_class.id as pivot_id')
            ->get();

        $this->loadScoreTypes();
        $this->loadScoresMap();

        if ($this->filterByRating) {
            $students = $students->filter(function ($student) {
                $avg = $this->calculateYearAverage(
                    $this->calculateAverage($student->pivot_id, $this->semesterOneTypes),
                    $this->calculateAverage($student->pivot_id, $this->semesterTwoTypes),
                );
                return $this->getStudentRating($avg) === $this->filterByRating;
            })->values();
        }

        return $students;
    }

    public function headings(): array
    {
        $this->loadScoreTypes();

        $headings = [
            'STT',
            'Mã học sinh',
            'Tên thánh',
            'Họ tên đệm',
            'Tên',
            'Ngày sinh',
            'Giáo họ',
        ];

        foreach ($this->semesterOneTypes ?? [] as $type) {
            $headings[] = $type->name;
        }
        $headings[] = 'Trung bình học kỳ 1';

        foreach ($this->semesterTwoTypes ?? [] as $type) {
            $headings[] = $type->name;
        }
        $headings[] = 'Trung bình học kỳ 2';
        $headings[] = 'Trung bình cả năm';
        $headings[] = 'Xếp loại';

        return $headings;
    }

    public function map($student): array
    {
        $this->loadScoreTypes();
        $this->loadScoresMap();

        $row = [
            ++$this->rowIndex,
            $student->student_code ?? '',
            $student->saint?->name ?? '',
            $student->last_name,
            $student->first_name,
            $student->birthday?->format('d/m/Y') ?? '',
            $student->parishGroup?->name ?? '',
        ];

        foreach ($this->semesterOneTypes ?? [] as $type) {
            $score = $this->scoresMap[$student->pivot_id][$type->id]['value'] ?? null;
            $row[] = $score ?? '';
        }
        $semesterOneAverage = $this->calculateAverage($student->pivot_id, $this->semesterOneTypes);
        $row[] = $semesterOneAverage ?? '';

        foreach ($this->semesterTwoTypes ?? [] as $type) {
            $score = $this->scoresMap[$student->pivot_id][$type->id]['value'] ?? null;
            $row[] = $score ?? '';
        }
        $semesterTwoAverage = $this->calculateAverage($student->pivot_id, $this->semesterTwoTypes);
        $row[] = $semesterTwoAverage ?? '';

        $yearAverage = $this->calculateYearAverage($semesterOneAverage, $semesterTwoAverage);
        $row[] = $yearAverage ?? '';
        $row[] = $this->getRatingLabel($yearAverage);

        return $row;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
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
                $this->loadScoreTypes();

                $class = CatechismClass::find($this->classId);
                $className = $class->name ?? '-';

                $headerRow = 4;
                $dataLastRow = $this->rowIndex > 0
                    ? $this->rowIndex + $headerRow
                    : $headerRow;

                $semesterOneCount = $this->semesterOneTypes?->count() ?? 0;
                $semesterTwoCount = $this->semesterTwoTypes?->count() ?? 0;
                $semesterOneStartIndex = self::FIXED_COLUMNS + 1;
                $semesterOneEndIndex = $semesterOneStartIndex + $semesterOneCount;
                $semesterTwoStartIndex = $semesterOneEndIndex + 1;
                $semesterTwoEndIndex = $semesterTwoStartIndex + $semesterTwoCount;
                $summaryStartIndex = $semesterTwoEndIndex + 1;
                $lastColIndex = self::FIXED_COLUMNS
                    + $semesterOneCount
                    + $semesterTwoCount
                    + self::SUMMARY_COLUMNS;

                $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
                $semesterOneStartCol = Coordinate::stringFromColumnIndex($semesterOneStartIndex);
                $semesterOneEndCol = Coordinate::stringFromColumnIndex($semesterOneEndIndex);
                $semesterTwoStartCol = Coordinate::stringFromColumnIndex($semesterTwoStartIndex);
                $semesterTwoEndCol = Coordinate::stringFromColumnIndex($semesterTwoEndIndex);
                $summaryStartCol = Coordinate::stringFromColumnIndex($summaryStartIndex);

                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', "Bảng điểm cả năm - Lớp {$className}");
                $sheet->mergeCells("A1:{$lastCol}1");

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'name' => 'Times New Roman',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->setCellValue('A2', 'Ngày xuất: ' . now()->format('d/m/Y H:i:s'));
                $sheet->mergeCells("A2:{$lastCol}2");

                $this->appendGroupHeader(
                    $sheet,
                    $semesterOneStartCol,
                    $semesterOneEndCol,
                    $semesterTwoStartCol,
                    $semesterTwoEndCol,
                    $summaryStartCol,
                    $lastCol,
                );

                $sheet->getStyle("A1:{$lastCol}{$dataLastRow}")
                    ->getFont()
                    ->setName('Times New Roman')
                    ->setSize(12);

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EAF7EF'],
                    ],
                ]);

                $sheet->getStyle("A3:{$lastCol}{$dataLastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Các cột trung bình đặt bề rộng cố định để tiêu đề dài wrap xuống dòng,
                // các cột còn lại autosize theo nội dung.
                $fixedWidthCols = [$semesterOneEndCol, $semesterTwoEndCol, $summaryStartCol];

                for ($columnIndex = 1; $columnIndex <= $lastColIndex; $columnIndex++) {
                    $col = Coordinate::stringFromColumnIndex($columnIndex);

                    if (in_array($col, $fixedWidthCols, true)) {
                        $sheet->getColumnDimension($col)->setAutoSize(false);
                        $sheet->getColumnDimension($col)->setWidth(12);
                    } else {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }
                }

                $sheet->getStyle("A" . ($headerRow + 1) . ":{$lastCol}{$dataLastRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("H{$headerRow}:{$lastCol}{$dataLastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                foreach ([$semesterOneEndCol, $semesterTwoEndCol, $summaryStartCol] as $col) {
                    $sheet->getStyle("{$col}{$headerRow}:{$col}{$dataLastRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF7ED'],
                        ],
                        'borders' => [
                            'left' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                }

                $sheet->getRowDimension(3)->setRowHeight(24);
                $sheet->getRowDimension($headerRow)->setRowHeight(36);

                // Cố định hàng 1-4 và các cột thông tin học sinh A-G.
                $sheet->freezePane('H' . ($headerRow + 1));
            },
        ];
    }

    private function loadScoreTypes(): void
    {
        if ($this->scoreTypes !== null) {
            return;
        }

        $this->scoreTypes = ScoreType::where('class_id', $this->classId)
            ->where('is_active', true)
            ->orderBy('semester')
            ->orderBy('order')
            ->orderBy('type')
            ->get();

        $this->semesterOneTypes = $this->scoreTypes
            ->where('semester', ScoreType::SEMESTER_1)
            ->values();
        $this->semesterTwoTypes = $this->scoreTypes
            ->where('semester', ScoreType::SEMESTER_2)
            ->values();
    }

    private function loadScoresMap(): void
    {
        if (!empty($this->scoresMap)) {
            return;
        }

        $this->loadScoreTypes();

        if ($this->scoreTypes === null || $this->scoreTypes->isEmpty()) {
            return;
        }

        $scoreTypeIds = $this->scoreTypes->pluck('id')->toArray();

        $scores = StudentScore::whereIn('score_type_id', $scoreTypeIds)
            ->get();

        foreach ($scores as $score) {
            $this->scoresMap[$score->student_class_id][$score->score_type_id] = [
                'value' => (float) $score->score_value,
            ];
        }
    }

    private function calculateAverage(int $studentClassId, ?Collection $scoreTypes): ?float
    {
        if ($scoreTypes === null || $scoreTypes->isEmpty()) {
            return null;
        }

        $totalWeight = 0.0;
        $totalScore  = 0.0;

        foreach ($scoreTypes as $type) {
            $score = $this->scoresMap[$studentClassId][$type->id]['value'] ?? null;

            if ($score === null) {
                if (in_array($type->type, [ScoreType::TYPE_GIUA_KY, ScoreType::TYPE_CUOI_KY])) {
                    return null;
                }
                continue;
            }

            $totalScore  += $score * $type->coefficient;
            $totalWeight += $type->coefficient;
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 1) : null;
    }

    private function calculateYearAverage(?float $semesterOne, ?float $semesterTwo): ?float
    {
        if ($semesterOne === null || $semesterTwo === null) {
            return null;
        }

        return round(($semesterOne + $semesterTwo) / 2, 1);
    }

    private function getStudentRating(?float $average): ?string
    {
        if ($average === null || $average < 0) {
            return null;
        }

        foreach (self::RATING_LEVELS as $key => $rating) {
            if (
                $average >= $rating['min']
                && ($average < $rating['max'] || ($rating['max'] === 10 && $average <= 10))
            ) {
                return $key;
            }
        }

        return null;
    }

    private function getRatingLabel(?float $average): string
    {
        return match ($this->getStudentRating($average)) {
            'XUAT_SAC' => 'Xuất sắc',
            'GIOI' => 'Giỏi',
            'KHA' => 'Khá',
            'TRUNG_BINH' => 'Trung bình',
            'YEU' => 'Yếu',
            'KEM' => 'Kém',
            default => '',
        };
    }

    private function appendGroupHeader(
        Worksheet $sheet,
        string $semesterOneStartCol,
        string $semesterOneEndCol,
        string $semesterTwoStartCol,
        string $semesterTwoEndCol,
        string $summaryStartCol,
        string $lastCol,
    ): void {
        $sheet->setCellValue('A3', 'Thông tin học sinh');
        $sheet->mergeCells('A3:G3');

        $sheet->setCellValue("{$semesterOneStartCol}3", 'Học kỳ 1');
        $sheet->mergeCells("{$semesterOneStartCol}3:{$semesterOneEndCol}3");

        $sheet->setCellValue("{$semesterTwoStartCol}3", 'Học kỳ 2');
        $sheet->mergeCells("{$semesterTwoStartCol}3:{$semesterTwoEndCol}3");

        $sheet->setCellValue("{$summaryStartCol}3", 'Tổng kết cả năm');
        $sheet->mergeCells("{$summaryStartCol}3:{$lastCol}3");

        $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
            'font' => [
                'bold' => true,
                'name' => 'Times New Roman',
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DCE6F1'],
            ],
        ]);
    }
}
