<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\GradingSetting;
use App\Models\ScoreType;
use App\Models\StudentScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Nơi duy nhất tính điểm trung bình học kỳ và cả năm.
 *
 * TB học kỳ  = điểm TB học tập × %HT + chuyên cần học × %CCH + chuyên cần lễ × %CCL
 * TB cả năm  = TB kỳ 1 × %HK1 + TB kỳ 2 × %HK2
 *
 * Tỉ lệ phần trăm lấy từ App\Models\GradingSetting. Thành phần có trọng số > 0
 * mà thiếu dữ liệu thì chưa tính TB (trả về null) để không cho ra điểm sai.
 */
class SemesterScoreCalculator
{
    public const COMPONENT_ACADEMIC         = 'academic';
    public const COMPONENT_CLASS_ATTENDANCE = 'class_attendance';
    public const COMPONENT_MASS_ATTENDANCE  = 'mass_attendance';

    public const COMPONENT_LABELS = [
        self::COMPONENT_ACADEMIC         => 'điểm trung bình học tập',
        self::COMPONENT_CLASS_ATTENDANCE => 'chuyên cần học',
        self::COMPONENT_MASS_ATTENDANCE  => 'chuyên cần lễ',
    ];

    public function __construct(private GradingWeightResolver $resolver) {}

    public function settingsForClass(CatechismClass|int|null $class): GradingSetting
    {
        return $this->resolver->forClass($class);
    }

    /**
     * Điểm thành phần + TB học kỳ của mọi học sinh trong một lớp.
     *
     * @param  GradingSetting|null  $override  Tỉ lệ dùng thay cho cấu hình đã lưu (xem trước)
     * @return array<int, array> [students_class.id => breakdown]
     */
    public function forClassSemester(int $classId, int $semester, ?GradingSetting $override = null): array
    {
        return $this->forClassesSemester([$classId], $semester, $override)[$classId] ?? [];
    }

    /**
     * Bản gộp cho nhiều lớp — dùng ở trang thống kê để không bắn query theo từng lớp.
     *
     * @param  int[]  $classIds
     * @return array<int, array<int, array>> [class_id => [students_class.id => breakdown]]
     */
    public function forClassesSemester(array $classIds, int $semester, ?GradingSetting $override = null): array
    {
        $classIds = array_values(array_unique(array_map('intval', $classIds)));

        if ($classIds === []) {
            return [];
        }

        $classes = CatechismClass::query()
            ->whereIn('id', $classIds)
            ->get(['id', 'parish_id', 'school_year_id', 'grade_level_id'])
            ->keyBy('id');

        if ($classes->isEmpty()) {
            return [];
        }

        $pivotsByClass = $this->pivotsByClass($classes->keys()->all());

        if ($pivotsByClass === []) {
            return [];
        }

        $settingsByClass = $classes
            ->mapWithKeys(fn ($class) => [
                (int) $class->id => $override ?? $this->resolver->forClass($class),
            ])
            ->all();

        $academicByClass   = $this->academicScores($classes->keys()->all(), $semester, $pivotsByClass);
        $attendanceByClass = $this->attendanceScores($semester, $pivotsByClass, $settingsByClass);

        $result = [];

        foreach ($pivotsByClass as $classId => $pivots) {
            $settings   = $settingsByClass[$classId] ?? GradingSetting::makeDefault();
            $attendance = $attendanceByClass[$classId] ?? [];

            foreach ($pivots as $pivotId => $studentId) {
                $result[$classId][$pivotId] = $this->combineComponents([
                    self::COMPONENT_ACADEMIC         => $academicByClass[$classId][$pivotId] ?? null,
                    self::COMPONENT_CLASS_ATTENDANCE => $attendance[AttendanceSession::TYPE_CLASS][$studentId] ?? null,
                    self::COMPONENT_MASS_ATTENDANCE  => $attendance[AttendanceSession::TYPE_CEREMONY][$studentId] ?? null,
                ], $settings);
            }
        }

        return $result;
    }

    /**
     * TB hai học kỳ và TB cả năm của mọi học sinh trong một lớp.
     *
     * @return array<int, array{semesters: array<int, array>, year: ?float}>
     */
    public function forClassYear(int $classId): array
    {
        return $this->forClassesYear([$classId])[$classId] ?? [];
    }

    /**
     * @param  int[]  $classIds
     * @return array<int, array<int, array{semesters: array<int, array>, year: ?float}>>
     */
    public function forClassesYear(array $classIds): array
    {
        $semesterOne = $this->forClassesSemester($classIds, ScoreType::SEMESTER_1);
        $semesterTwo = $this->forClassesSemester($classIds, ScoreType::SEMESTER_2);

        $classes = CatechismClass::query()
            ->whereIn('id', $classIds)
            ->get(['id', 'parish_id', 'school_year_id', 'grade_level_id'])
            ->keyBy('id');

        $result = [];

        foreach (array_unique(array_merge(array_keys($semesterOne), array_keys($semesterTwo))) as $classId) {
            $settings = $this->resolver->forClass($classes->get($classId));
            $one      = $semesterOne[$classId] ?? [];
            $two      = $semesterTwo[$classId] ?? [];

            foreach (array_unique(array_merge(array_keys($one), array_keys($two))) as $pivotId) {
                $result[(int) $classId][(int) $pivotId] = [
                    'semesters' => [
                        ScoreType::SEMESTER_1 => $one[$pivotId] ?? null,
                        ScoreType::SEMESTER_2 => $two[$pivotId] ?? null,
                    ],
                    'year' => $this->yearAverage(
                        $one[$pivotId]['total'] ?? null,
                        $two[$pivotId]['total'] ?? null,
                        $settings
                    ),
                ];
            }
        }

        return $result;
    }

    /**
     * Gộp các điểm thành phần theo trọng số.
     *
     * Public để form cài đặt xem trước kết quả với tỉ lệ chưa lưu.
     *
     * @param  array<string, ?float>  $components
     * @return array{academic: ?float, class_attendance: ?float, mass_attendance: ?float, total: ?float, missing: string[]}
     */
    public function combineComponents(array $components, GradingSetting $settings): array
    {
        $values = [
            self::COMPONENT_ACADEMIC         => $components[self::COMPONENT_ACADEMIC] ?? null,
            self::COMPONENT_CLASS_ATTENDANCE => $components[self::COMPONENT_CLASS_ATTENDANCE] ?? null,
            self::COMPONENT_MASS_ATTENDANCE  => $components[self::COMPONENT_MASS_ATTENDANCE] ?? null,
        ];

        $missing     = [];
        $weighted    = 0.0;
        $totalWeight = 0.0;

        foreach ($settings->semesterComponentWeights() as $component => $weight) {
            if ($weight <= 0) {
                continue;
            }

            if ($values[$component] === null) {
                $missing[] = $component;
                continue;
            }

            $weighted    += $values[$component] * $weight;
            $totalWeight += $weight;
        }

        $total = ($missing === [] && $totalWeight > 0)
            ? round($weighted / $totalWeight, 1)
            : null;

        return $values + [
            'total'   => $total,
            'missing' => $missing,
        ];
    }

    public function yearAverage(?float $semesterOne, ?float $semesterTwo, GradingSetting $settings): ?float
    {
        if ($semesterOne === null || $semesterTwo === null) {
            return null;
        }

        $weightOne = (float) $settings->weight_semester_1;
        $weightTwo = (float) $settings->weight_semester_2;
        $sum       = $weightOne + $weightTwo;

        if ($sum <= 0) {
            return null;
        }

        return round((($semesterOne * $weightOne) + ($semesterTwo * $weightTwo)) / $sum, 1);
    }

    /** Mô tả các thành phần còn thiếu, để UI nói rõ vì sao chưa có TB. */
    public function describeMissing(array $missing): ?string
    {
        if ($missing === []) {
            return null;
        }

        $labels = array_map(
            fn (string $component) => self::COMPONENT_LABELS[$component] ?? $component,
            $missing
        );

        return 'Chưa có ' . implode(', ', $labels);
    }

    /*
    |--------------------------------------------------------------------------
    | DATA LOADING
    |--------------------------------------------------------------------------
    */

    /**
     * @param  int[]  $classIds
     * @return array<int, array<int, int>> [class_id => [students_class.id => student_id]]
     */
    private function pivotsByClass(array $classIds): array
    {
        $rows = DB::table('students_class')
            ->whereIn('class_id', $classIds)
            ->get(['id', 'class_id', 'student_id']);

        $result = [];

        foreach ($rows as $row) {
            $result[(int) $row->class_id][(int) $row->id] = (int) $row->student_id;
        }

        return $result;
    }

    /**
     * Điểm trung bình học tập: Σ(điểm × hệ số) / Σ(hệ số) trên các loại điểm đang bật.
     *
     * @param  int[]  $classIds
     * @param  array<int, array<int, int>>  $pivotsByClass
     * @return array<int, array<int, ?float>> [class_id => [students_class.id => điểm]]
     */
    private function academicScores(array $classIds, int $semester, array $pivotsByClass): array
    {
        $types = ScoreType::query()
            ->whereIn('class_id', $classIds)
            ->where('semester', $semester)
            ->where('is_active', true)
            ->get(['id', 'class_id', 'type', 'coefficient']);

        if ($types->isEmpty()) {
            return [];
        }

        $typesByClass = $types->groupBy('class_id');

        $pivotIds = [];
        foreach ($pivotsByClass as $pivots) {
            $pivotIds = array_merge($pivotIds, array_keys($pivots));
        }

        $matrix = [];

        if ($pivotIds !== []) {
            $scores = StudentScore::query()
                ->whereIn('student_class_id', $pivotIds)
                ->whereIn('score_type_id', $types->pluck('id')->all())
                ->get(['student_class_id', 'score_type_id', 'score_value']);

            foreach ($scores as $score) {
                $matrix[(int) $score->student_class_id][(int) $score->score_type_id] = (float) $score->score_value;
            }
        }

        $result = [];

        foreach ($pivotsByClass as $classId => $pivots) {
            $types = $typesByClass->get($classId);

            if ($types === null || $types->isEmpty()) {
                continue;
            }

            foreach (array_keys($pivots) as $pivotId) {
                $result[$classId][$pivotId] = $this->academicFromRow($matrix[$pivotId] ?? [], $types);
            }
        }

        return $result;
    }

    /**
     * @param  array<int, float>  $row  [score_type_id => điểm]
     * @param  Collection<int, ScoreType>  $types
     */
    private function academicFromRow(array $row, Collection $types): ?float
    {
        $totalScore  = 0.0;
        $totalWeight = 0.0;

        foreach ($types as $type) {
            $value = $row[(int) $type->id] ?? null;

            if ($value === null) {
                // Thiếu điểm giữa kỳ / cuối kỳ thì chưa chốt được điểm TB học tập.
                if (in_array((int) $type->type, [ScoreType::TYPE_GIUA_KY, ScoreType::TYPE_CUOI_KY], true)) {
                    return null;
                }

                continue;
            }

            $totalScore  += $value * (float) $type->coefficient;
            $totalWeight += (float) $type->coefficient;
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 1) : null;
    }

    /**
     * Điểm chuyên cần: 10 × (có mặt + hệ số × vắng có phép) / số buổi đã điểm danh.
     *
     * Buổi bị hủy và buổi giáo lý viên chưa điểm danh không vào mẫu số.
     *
     * @param  array<int, array<int, int>>  $pivotsByClass
     * @param  array<int, GradingSetting>  $settingsByClass
     * @return array<int, array<int, array<int, float>>> [class_id => [session type => [student_id => điểm]]]
     */
    private function attendanceScores(int $semester, array $pivotsByClass, array $settingsByClass): array
    {
        $classIds   = array_keys($pivotsByClass);
        $studentIds = [];
        foreach ($pivotsByClass as $pivots) {
            $studentIds = array_merge($studentIds, array_values($pivots));
        }

        $studentIds = array_values(array_unique($studentIds));

        if ($studentIds === []) {
            return [];
        }

        $rows = DB::table('attendance_records')
            ->join('attendance_sessions', 'attendance_records.session_id', '=', 'attendance_sessions.id')
            ->whereIn('attendance_sessions.class_id', $classIds)
            ->where('attendance_sessions.semester', $semester)
            ->where('attendance_sessions.status', '!=', AttendanceSession::STATUS_CANCELLED)
            ->whereIn('attendance_records.student_id', $studentIds)
            ->whereNotNull('attendance_records.status')
            ->groupBy(
                'attendance_sessions.class_id',
                'attendance_sessions.type',
                'attendance_records.student_id'
            )
            ->selectRaw(
                'attendance_sessions.class_id as class_id,
                 attendance_sessions.type as session_type,
                 attendance_records.student_id as student_id,
                 COUNT(*) as counted,
                 SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as present,
                 SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as excused',
                [AttendanceRecord::STATUS_PRESENT, AttendanceRecord::STATUS_ABSENT_EXCUSED]
            )
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $classId = (int) $row->class_id;
            $counted = (int) $row->counted;

            if ($counted <= 0) {
                continue;
            }

            $settings = $settingsByClass[$classId] ?? GradingSetting::makeDefault();
            $credited = (int) $row->present + $settings->excusedCredit() * (int) $row->excused;

            $result[$classId][(int) $row->session_type][(int) $row->student_id]
                = round(10 * $credited / $counted, 1);
        }

        return $result;
    }
}
