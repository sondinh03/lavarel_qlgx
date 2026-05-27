<?php

namespace App\Exports;

use App\Models\CatechismClass;
use App\Models\StudentNew;
use App\Models\StudentScore;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScoreExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    private int $rowIndex = 0;
    private ?\Illuminate\Support\Collection $scoreTypes = null;
    private array $scoresMap = [];

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
        private ?int $semester = 1,
        private ?string $filterByRating = null,
    ) {}

    public function collection()
    {
        $students = StudentNew::query()
            ->whereHas('classes', fn($q) => $q->where('classes.id', $this->classId))
            ->join('students_class', 'students.id', '=', 'students_class.student_id')
            ->where('students_class.class_id', $this->classId)
            ->with(['saint'])
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->select('students.*', 'students_class.id as pivot_id')
            ->get();

        $this->loadScoreTypes();
        $this->loadScoresMap();

        if ($this->filterByRating) {
            $students = $students->filter(function ($student) {
                $avg = $this->calculateAverage($student->pivot_id);
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
            'Tên thánh',
            'Họ tên đệm',
            'Tên',
            'Ngày sinh',
            'Giới tính',
        ];

        if ($this->scoreTypes !== null) {
            foreach ($this->scoreTypes as $type) {
                $headings[] = $type->name;
            }
        }

        $headings[] = 'Điểm TB';

        return $headings;
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
            $student->birthday?->format('d/m/Y') ?? '',
            match ($student->gender) {
                'male' => 'Nam',
                'female' => 'Nữ',
                default => '',
            },
        ];

        $totalWeight = 0;
        $totalScore = 0;

        if ($this->scoreTypes !== null) {
            foreach ($this->scoreTypes as $type) {
                $score = $this->scoresMap[$student->pivot_id][$type->id]['value'] ?? null;
                $row[] = $score !== null ? $score : '';

                if ($score !== null) {
                    $totalScore += $score * $type->coefficient;
                    $totalWeight += $type->coefficient;
                }
            }
        }

        $average = $totalWeight > 0 ? round($totalScore / $totalWeight, 1) : '';
        $row[] = $average;

        return $row;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            'A1:Z1000' => [
                'font' => [
                    'name' => 'Times New Roman',
                    'size' => 12,
                ],
            ],

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

                $scoreTypeCount = $this->scoreTypes !== null ? $this->scoreTypes->count() : 0;
                $lastCol = chr(65 + 5 + $scoreTypeCount);

                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', "Bảng điểm - Lớp $className - Học kỳ {$this->semester}");
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

                $sheet->setCellValue('A2', 'Ngày xuất: ' . now()->format('d/m/Y H:i:s'));
                $sheet->mergeCells("A2:{$lastCol}2");

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

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

                foreach (range('A', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getStyle("A" . ($headerRow + 1) . ":A{$dataLastRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

                $sheet->freezePane("A" . ($headerRow + 1));
            },
        ];
    }

    private function loadScoreTypes(): void
    {
        if ($this->scoreTypes !== null) {
            return;
        }

        $this->scoreTypes = \App\Models\ScoreType::where('class_id', $this->classId)
            ->where('semester', $this->semester)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('type')
            ->get();
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
}
