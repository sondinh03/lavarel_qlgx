<?php

namespace App\Services;

use App\Models\ScoreType;
use App\Models\StudentsClass;
use App\Support\StudentRating;
use Illuminate\Support\Collection;

/**
 * Nguồn dữ liệu dùng chung cho trang thống kê điểm và file Excel phân phối.
 *
 * Mỗi khoảng không chồng lấn: 0 <= điểm < 1, ..., 9 <= điểm <= 10.
 */
class ScoreDistributionReport
{
    public const BUCKET_COUNT = 10;

    public function __construct(private SemesterScoreCalculator $calculator) {}

    /**
     * @param  int[]  $classIds
     * @return array{
     *     averages: array<int, array{avg: float, class_id: int, student_class_id: int, bucket: int}>,
     *     total_students: int,
     *     students_with_score: int
     * }
     */
    public function summary(array $classIds, int $semester): array
    {
        $classIds = $this->normalizeClassIds($classIds);

        if ($classIds === []) {
            return [
                'averages'           => [],
                'total_students'     => 0,
                'students_with_score' => 0,
            ];
        }

        $byClass = $semester === 0
            ? $this->calculator->forClassesYear($classIds)
            : $this->calculator->forClassesSemester($classIds, $semester);

        $averages = [];
        $totalStudents = 0;

        foreach ($byClass as $classId => $students) {
            foreach ($students as $pivotId => $row) {
                $totalStudents++;
                $average = $semester === 0
                    ? ($row['year'] ?? null)
                    : ($row['total'] ?? null);

                if ($average === null) {
                    continue;
                }

                $average = (float) $average;
                $averages[] = [
                    'avg'              => $average,
                    'class_id'         => (int) $classId,
                    'student_class_id' => (int) $pivotId,
                    'bucket'           => self::bucketFor($average),
                ];
            }
        }

        return [
            'averages'            => $averages,
            'total_students'      => $totalStudents,
            'students_with_score' => count($averages),
        ];
    }

    /**
     * Số liệu tổng quan giống thẻ thống kê trên trang thống kê điểm.
     *
     * @param  array<int, array{avg: float}>  $averages
     * @return array{
     *     avg: float, max: float, min: float, count: int, pass: int,
     *     pass_rate: float, total_students: int, missing: int
     * }
     */
    public function overview(array $averages, int $totalStudents = 0): array
    {
        $values = array_map(fn (array $row) => (float) $row['avg'], $averages);
        $count  = count($values);

        if ($count === 0) {
            return [
                'avg'            => 0.0,
                'max'            => 0.0,
                'min'            => 0.0,
                'count'          => 0,
                'pass'           => 0,
                'pass_rate'      => 0.0,
                'total_students' => max(0, $totalStudents),
                'missing'        => max(0, $totalStudents),
            ];
        }

        $pass  = count(array_filter($values, fn (float $value) => $value >= 5.0));
        $total = max($count, $totalStudents);

        return [
            'avg'            => round(array_sum($values) / $count, 2),
            'max'            => round(max($values), 2),
            'min'            => round(min($values), 2),
            'count'          => $count,
            'pass'           => $pass,
            'pass_rate'      => round(($pass / $count) * 100, 1),
            'total_students' => $total,
            'missing'        => $total - $count,
        ];
    }

    /**
     * 10 khoảng điểm kèm số học sinh, tỉ lệ và màu dùng cho cả chart và Excel.
     *
     * @param  array<int, array{avg: float, bucket?: int}>  $averages
     * @return array<int, array{bucket: int, label: string, description: string, count: int, percentage: float, color: string}>
     */
    public function distribution(array $averages): array
    {
        $counts = array_fill(0, self::BUCKET_COUNT, 0);

        foreach ($averages as $row) {
            $bucket = isset($row['bucket'])
                ? (int) $row['bucket']
                : self::bucketFor((float) $row['avg']);
            $counts[max(0, min(self::BUCKET_COUNT - 1, $bucket))]++;
        }

        $total = array_sum($counts);
        $rows  = [];

        for ($bucket = 0; $bucket < self::BUCKET_COUNT; $bucket++) {
            $rows[] = [
                'bucket'      => $bucket,
                'label'       => self::bucketLabel($bucket),
                'description' => self::bucketDescription($bucket),
                'count'       => $counts[$bucket],
                'percentage'  => $total > 0 ? round(($counts[$bucket] / $total) * 100, 1) : 0.0,
                'color'       => self::bucketColor($bucket),
            ];
        }

        return $rows;
    }

    /**
     * Phân bố theo thang xếp loại học lực.
     *
     * @param  array<int, array{avg: float}>  $averages
     * @return array<int, array{key: string, label: string, color: string, count: int, percentage: float}>
     */
    public function ratingBreakdown(array $averages): array
    {
        $counts = [];
        foreach (StudentRating::levels() as $key => $level) {
            $counts[$key] = 0;
        }

        foreach ($averages as $row) {
            $key = StudentRating::keyFor((float) $row['avg']);
            if ($key !== null) {
                $counts[$key]++;
            }
        }

        $total = array_sum($counts);
        $rows  = [];

        foreach (StudentRating::levels() as $key => $level) {
            $rows[] = [
                'key'        => $key,
                'label'      => $level['label'],
                'color'      => $level['hex'],
                'count'      => $counts[$key],
                'percentage' => $total > 0 ? round(($counts[$key] / $total) * 100, 1) : 0.0,
            ];
        }

        return $rows;
    }

    /**
     * Gói dữ liệu đủ cho file Excel: tổng quan + phân phối + chi tiết một sheet.
     *
     * @param  int[]  $classIds
     * @return array{
     *     overview: array<string, mixed>,
     *     ratings: array<int, array<string, mixed>>,
     *     distribution: array<int, array<string, mixed>>,
     *     students: Collection<int, array<string, mixed>>
     * }
     */
    public function payloadForSemester(array $classIds, int $semester): array
    {
        if (! in_array($semester, [ScoreType::SEMESTER_1, ScoreType::SEMESTER_2], true)) {
            return [
                'overview'     => $this->overview([], 0),
                'ratings'      => $this->ratingBreakdown([]),
                'distribution' => $this->distribution([]),
                'students'     => collect(),
            ];
        }

        $report = $this->summary($classIds, $semester);

        return [
            'overview'     => $this->overview($report['averages'], $report['total_students']),
            'ratings'      => $this->ratingBreakdown($report['averages']),
            'distribution' => $this->distribution($report['averages']),
            'students'     => $this->hydrateStudents($report['averages']),
        ];
    }

    /**
     * Gói dữ liệu xuất một lần cho HK1, HK2 và cả năm.
     *
     * @param  int[]  $classIds
     * @return array{
     *     periods: array<string, array{
     *         label: string,
     *         overview: array<string, mixed>,
     *         ratings: array<int, array<string, mixed>>,
     *         distribution: array<int, array<string, mixed>>
     *     }>,
     *     students: Collection<int, array<string, mixed>>
     * }
     */
    public function payloadForAllPeriods(array $classIds): array
    {
        $reports = $this->reportsForAllPeriods($classIds);
        $labels = [
            'semester_1' => 'Học kỳ 1',
            'semester_2' => 'Học kỳ 2',
            'year'       => 'Cả năm',
        ];
        $periods = [];

        foreach ($reports as $key => $report) {
            $periods[$key] = [
                'label'        => $labels[$key],
                'overview'     => $this->overview($report['averages'], $report['total_students']),
                'ratings'      => $this->ratingBreakdown($report['averages']),
                'distribution' => $this->distribution($report['averages']),
            ];
        }

        $students = $this->hydrateStudentsForAllPeriods($reports, $classIds);

        return [
            'periods'  => $periods,
            'students' => $students,
        ];
    }

    /**
     * Tính HK1, HK2 và cả năm trong cùng một lượt để tránh tính hai học kỳ lặp lại.
     *
     * @param  int[]  $classIds
     * @return array<string, array{
     *     averages: array<int, array{avg: float, class_id: int, student_class_id: int, bucket: int}>,
     *     total_students: int,
     *     students_with_score: int
     * }>
     */
    private function reportsForAllPeriods(array $classIds): array
    {
        $classIds = $this->normalizeClassIds($classIds);
        $reports = [
            'semester_1' => ['averages' => [], 'total_students' => 0, 'students_with_score' => 0],
            'semester_2' => ['averages' => [], 'total_students' => 0, 'students_with_score' => 0],
            'year'       => ['averages' => [], 'total_students' => 0, 'students_with_score' => 0],
        ];

        if ($classIds === []) {
            return $reports;
        }

        foreach ($this->calculator->forClassesYear($classIds) as $classId => $students) {
            foreach ($students as $pivotId => $row) {
                foreach ($reports as &$report) {
                    $report['total_students']++;
                }
                unset($report);

                $values = [
                    'semester_1' => $row['semesters'][ScoreType::SEMESTER_1]['total'] ?? null,
                    'semester_2' => $row['semesters'][ScoreType::SEMESTER_2]['total'] ?? null,
                    'year'       => $row['year'] ?? null,
                ];

                foreach ($values as $period => $average) {
                    if ($average === null) {
                        continue;
                    }

                    $average = (float) $average;
                    $reports[$period]['averages'][] = [
                        'avg'              => $average,
                        'class_id'         => (int) $classId,
                        'student_class_id' => (int) $pivotId,
                        'bucket'           => self::bucketFor($average),
                    ];
                    $reports[$period]['students_with_score']++;
                }
            }
        }

        return $reports;
    }

    /**
     * Danh sách chi tiết học sinh có TB học kỳ, đã chia sẵn vào 10 khoảng.
     *
     * @param  int[]  $classIds
     * @return Collection<int, array<string, mixed>>
     */
    public function studentsForSemester(array $classIds, int $semester): Collection
    {
        if (! in_array($semester, [ScoreType::SEMESTER_1, ScoreType::SEMESTER_2], true)) {
            return collect();
        }

        return $this->hydrateStudents($this->summary($classIds, $semester)['averages']);
    }

    /**
     * @param  array<int, array{avg: float, class_id: int, student_class_id: int, bucket: int}>  $averageRows
     * @return Collection<int, array{
     *     student_class_id: int,
     *     student_id: int,
     *     saint_name: string,
     *     last_name: string,
     *     first_name: string,
     *     grade_name: string,
     *     class_name: string,
     *     average: float,
     *     rating: string,
     *     bucket: int,
     *     bucket_label: string
     * }>
     */
    private function hydrateStudents(array $averageRows): Collection
    {
        $averages = collect($averageRows)->keyBy('student_class_id');

        if ($averages->isEmpty()) {
            return collect();
        }

        return StudentsClass::query()
            ->with([
                'student.saint',
                'catechismClass.gradeLevel',
            ])
            ->whereIn('id', $averages->keys()->all())
            ->get()
            ->map(function (StudentsClass $pivot) use ($averages) {
                $averageRow = $averages->get((int) $pivot->id);
                $student = $pivot->student;
                $class = $pivot->catechismClass;
                $average = (float) $averageRow['avg'];

                return [
                    'student_class_id' => (int) $pivot->id,
                    'student_id'       => (int) $pivot->student_id,
                    'saint_name'       => (string) ($student?->saint?->name ?? ''),
                    'last_name'        => (string) ($student?->last_name ?? ''),
                    'first_name'       => (string) ($student?->first_name ?? ''),
                    'grade_name'       => (string) ($class?->gradeLevel?->name ?? ''),
                    'class_name'       => (string) ($class?->name ?? ''),
                    'average'          => $average,
                    'rating'           => StudentRating::labelFor($average, '—'),
                    'bucket'           => (int) $averageRow['bucket'],
                    'bucket_label'     => self::bucketLabel((int) $averageRow['bucket']),
                ];
            })
            ->sortBy([
                ['average', 'desc'],
                ['grade_name', 'asc'],
                ['class_name', 'asc'],
                ['first_name', 'asc'],
                ['last_name', 'asc'],
            ])
            ->values();
    }

    /**
     * @param  array<string, array{averages: array<int, array<string, mixed>>}>  $reports
     * @param  int[]  $classIds
     * @return Collection<int, array<string, mixed>>
     */
    private function hydrateStudentsForAllPeriods(array $reports, array $classIds): Collection
    {
        $averagesByPeriod = collect($reports)->map(
            fn (array $report) => collect($report['averages'])->keyBy('student_class_id')
        );

        $classIds = $this->normalizeClassIds($classIds);
        if ($classIds === []) {
            return collect();
        }

        return StudentsClass::query()
            ->with([
                'student.saint',
                'catechismClass.gradeLevel',
            ])
            ->whereIn('class_id', $classIds)
            ->get()
            ->map(function (StudentsClass $pivot) use ($averagesByPeriod) {
                $student = $pivot->student;
                $class   = $pivot->catechismClass;
                $values  = [];

                foreach (['semester_1', 'semester_2', 'year'] as $period) {
                    $averageRow = $averagesByPeriod->get($period)->get((int) $pivot->id);
                    $average = $averageRow['avg'] ?? null;
                    $values[$period . '_average'] = $average !== null ? (float) $average : null;
                    $values[$period . '_rating'] = StudentRating::labelFor($average, '—');
                }

                return array_merge([
                    'student_class_id' => (int) $pivot->id,
                    'student_id'       => (int) $pivot->student_id,
                    'saint_name'       => (string) ($student?->saint?->name ?? ''),
                    'last_name'        => (string) ($student?->last_name ?? ''),
                    'first_name'       => (string) ($student?->first_name ?? ''),
                    'grade_name'       => (string) ($class?->gradeLevel?->name ?? ''),
                    'class_name'       => (string) ($class?->name ?? ''),
                ], $values);
            })
            ->sortBy([
                ['year_average', 'desc'],
                ['semester_2_average', 'desc'],
                ['semester_1_average', 'desc'],
                ['grade_name', 'asc'],
                ['class_name', 'asc'],
                ['first_name', 'asc'],
                ['last_name', 'asc'],
            ])
            ->values();
    }

    public static function bucketFor(float $average): int
    {
        if ($average >= 10) {
            return self::BUCKET_COUNT - 1;
        }

        return max(0, min(self::BUCKET_COUNT - 1, (int) floor($average)));
    }

    public static function bucketLabel(int $bucket): string
    {
        $bucket = max(0, min(self::BUCKET_COUNT - 1, $bucket));

        return $bucket . '-' . ($bucket + 1);
    }

    public static function bucketDescription(int $bucket): string
    {
        $bucket = max(0, min(self::BUCKET_COUNT - 1, $bucket));

        return $bucket === self::BUCKET_COUNT - 1
            ? 'Từ 9 đến 10'
            : 'Từ ' . $bucket . ' đến dưới ' . ($bucket + 1);
    }

    /**
     * Màu theo từng mốc 1 điểm, cố tình khác thang xếp loại vì mốc ở đây là 0-1 ... 9-10.
     */
    public static function bucketColor(int $bucket): string
    {
        return match (true) {
            $bucket >= 9 => '#10b981',
            $bucket >= 8 => '#3b82f6',
            $bucket >= 6 => '#f59e0b',
            $bucket >= 5 => '#eab308',
            $bucket >= 3 => '#f97316',
            default      => '#ef4444',
        };
    }

    /**
     * @param  int[]  $classIds
     * @return int[]
     */
    private function normalizeClassIds(array $classIds): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $classIds),
            fn (int $id) => $id > 0
        )));
    }
}
