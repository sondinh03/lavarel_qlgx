<?php

namespace App\Exports;

use App\Models\CatechismClass;
use App\Models\GradingSetting;
use App\Models\ScoreType;
use App\Models\StudentNew;
use App\Models\StudentScore;
use App\Services\SemesterScoreCalculator;
use App\Support\StudentRating;
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
    private ?GradingSetting $gradingSettings = null;

    /** [students_class.id => ['semesters' => [1 => breakdown, 2 => breakdown], 'year' => ?float]] */
    private array $breakdowns = [];

    private const FIXED_COLUMNS = 5;

    private const SUMMARY_COLUMNS = 4;

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
            $students = $students->filter(
                fn ($student) => $this->getStudentRating($this->yearAverage((int) $student->pivot_id))
                    === $this->filterByRating
            )->values();
        }

        return $students;
    }

    public function headings(): array
    {
        $this->loadScoreTypes();

        $headings = [
            'STT',
            'Tên thánh',
            'Họ tên đệm',
            'Tên',
            'Giáo họ',
        ];

        foreach ($this->semesterOneTypes ?? [] as $type) {
            $headings[] = $type->name;
        }
        foreach ($this->componentColumns() as $label) {
            $headings[] = $label;
        }
        $headings[] = 'Trung bình học kỳ 1';

        foreach ($this->semesterTwoTypes ?? [] as $type) {
            $headings[] = $type->name;
        }
        foreach ($this->componentColumns() as $label) {
            $headings[] = $label;
        }
        $headings[] = 'Trung bình học kỳ 2';
        $headings[] = 'Trung bình cả năm';
        $headings[] = 'Xếp loại';

        return $headings;
    }

    /**
     * Ba hạng mục điểm của một học kỳ. Luôn xuất đủ, kèm tỉ lệ đang áp dụng
     * để người đọc file tự đối chiếu được cột trung bình học kỳ.
     *
     * @return array<string, string> [component key => nhãn cột]
     */
    private function componentColumns(): array
    {
        $settings = $this->settings();

        return [
            SemesterScoreCalculator::COMPONENT_ACADEMIC =>
                'Trung bình học tập (' . $this->formatPercent($settings->weight_academic) . ')',
            SemesterScoreCalculator::COMPONENT_CLASS_ATTENDANCE =>
                'Chuyên cần học (' . $this->formatPercent($settings->weight_class_attendance) . ')',
            SemesterScoreCalculator::COMPONENT_MASS_ATTENDANCE =>
                'Chuyên cần lễ (' . $this->formatPercent($settings->weight_mass_attendance) . ')',
        ];
    }

    private function formatPercent(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, ',', ''), '0'), ',') . '%';
    }

    /** Ghi rõ cách tính ngay trên file để người nhận không phải hỏi lại. */
    private function describeFormula(): string
    {
        $settings = $this->settings();

        return 'TB học kỳ = trung bình học tập '
            . $this->formatPercent($settings->weight_academic)
            . ' + chuyên cần học ' . $this->formatPercent($settings->weight_class_attendance)
            . ' + chuyên cần lễ ' . $this->formatPercent($settings->weight_mass_attendance)
            . ' · TB cả năm = học kỳ 1 ' . $this->formatPercent($settings->weight_semester_1)
            . ' + học kỳ 2 ' . $this->formatPercent($settings->weight_semester_2);
    }

    public function map($student): array
    {
        $this->loadScoreTypes();
        $this->loadScoresMap();

        $row = [
            ++$this->rowIndex,
            $student->saint?->name ?? '',
            $student->last_name,
            $student->first_name,
            $student->parishGroup?->name ?? '',
        ];

        foreach ($this->semesterOneTypes ?? [] as $type) {
            $score = $this->scoresMap[$student->pivot_id][$type->id]['value'] ?? null;
            $row[] = $score ?? '';
        }
        $row = array_merge($row, $this->semesterColumns((int) $student->pivot_id, ScoreType::SEMESTER_1));

        foreach ($this->semesterTwoTypes ?? [] as $type) {
            $score = $this->scoresMap[$student->pivot_id][$type->id]['value'] ?? null;
            $row[] = $score ?? '';
        }
        $row = array_merge($row, $this->semesterColumns((int) $student->pivot_id, ScoreType::SEMESTER_2));

        $yearAverage = $this->yearAverage((int) $student->pivot_id);
        $row[] = $yearAverage ?? '';
        $row[] = $this->getRatingLabel($yearAverage);

        return $row;
    }

    /** Điểm thành phần (nếu có) và TB của một học kỳ. */
    private function semesterColumns(int $pivotId, int $semester): array
    {
        $breakdown = $this->semesterBreakdown($pivotId, $semester);

        $values = [];

        foreach (array_keys($this->componentColumns()) as $component) {
            $values[] = $breakdown[$component] ?? '';
        }

        $values[] = $breakdown['total'] ?? '';

        return $values;
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

                $componentCount   = count($this->componentColumns());
                $typesOneCount    = $this->semesterOneTypes?->count() ?? 0;
                $typesTwoCount    = $this->semesterTwoTypes?->count() ?? 0;
                $semesterOneCount = $typesOneCount + $componentCount;
                $semesterTwoCount = $typesTwoCount + $componentCount;
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

                $sheet->setCellValue(
                    'A2',
                    'Ngày xuất: ' . now()->format('d/m/Y H:i:s') . ' · ' . $this->describeFormula()
                );
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

                // Cột hạng mục điểm (trung bình học tập, chuyên cần học, chuyên cần lễ) của hai kỳ
                $componentCols = [];
                for ($offset = 0; $offset < $componentCount; $offset++) {
                    $componentCols[] = Coordinate::stringFromColumnIndex(
                        $semesterOneStartIndex + $typesOneCount + $offset
                    );
                    $componentCols[] = Coordinate::stringFromColumnIndex(
                        $semesterTwoStartIndex + $typesTwoCount + $offset
                    );
                }

                // Các cột trung bình và hạng mục đặt bề rộng cố định để tiêu đề dài wrap xuống dòng,
                // các cột còn lại autosize theo nội dung.
                $fixedWidthCols = array_merge(
                    [$semesterOneEndCol, $semesterTwoEndCol, $summaryStartCol],
                    $componentCols
                );

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

                $sheet->getStyle("F{$headerRow}:{$lastCol}{$dataLastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                if ($this->rowIndex > 0) {
                    // Cột Tên in đậm cho dễ dò danh sách
                    $sheet->getStyle('D' . ($headerRow + 1) . ":D{$dataLastRow}")
                        ->getFont()
                        ->setBold(true);

                    foreach ($componentCols as $col) {
                        $sheet->getStyle("{$col}" . ($headerRow + 1) . ":{$col}{$dataLastRow}")
                            ->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F2F7FB'],
                                ],
                            ]);
                    }
                }

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

                // Cố định hàng 1-4 và các cột đến Tên (A-D).
                $sheet->freezePane('E' . ($headerRow + 1));
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

    private function settings(): GradingSetting
    {
        return $this->gradingSettings ??= app(SemesterScoreCalculator::class)
            ->settingsForClass($this->classId);
    }

    private function loadBreakdowns(): void
    {
        if ($this->breakdowns !== [] || ! $this->classId) {
            return;
        }

        $this->breakdowns = app(SemesterScoreCalculator::class)
            ->forClassYear((int) $this->classId);
    }

    private function semesterBreakdown(int $pivotId, int $semester): array
    {
        $this->loadBreakdowns();

        return $this->breakdowns[$pivotId]['semesters'][$semester] ?? [
            SemesterScoreCalculator::COMPONENT_ACADEMIC         => null,
            SemesterScoreCalculator::COMPONENT_CLASS_ATTENDANCE => null,
            SemesterScoreCalculator::COMPONENT_MASS_ATTENDANCE  => null,
            'total'                                             => null,
            'missing'                                           => [],
        ];
    }

    private function yearAverage(int $pivotId): ?float
    {
        $this->loadBreakdowns();

        return $this->breakdowns[$pivotId]['year'] ?? null;
    }

    private function getStudentRating(?float $average): ?string
    {
        return StudentRating::keyFor($average);
    }

    private function getRatingLabel(?float $average): string
    {
        return StudentRating::labelFor($average);
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
        $sheet->mergeCells('A3:E3');

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
