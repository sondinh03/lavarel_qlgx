<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cấu hình cách tính điểm trung bình của một giáo xứ.
 *
 * Phạm vi áp dụng đi từ hẹp tới rộng: khối trong một năm học → năm học →
 * mặc định của giáo xứ (school_year_id null). Xem App\Services\GradingWeightResolver.
 */
class GradingSetting extends Model
{
    protected $table = 'grading_settings';

    protected $fillable = [
        'parish_id',
        'school_year_id',
        'grade_level_id',
        'weight_academic',
        'weight_class_attendance',
        'weight_mass_attendance',
        'weight_semester_1',
        'weight_semester_2',
        'excused_credit_percent',
    ];

    protected $casts = [
        'parish_id'               => 'integer',
        'school_year_id'          => 'integer',
        'grade_level_id'          => 'integer',
        'weight_academic'         => 'float',
        'weight_class_attendance' => 'float',
        'weight_mass_attendance'  => 'float',
        'weight_semester_1'       => 'float',
        'weight_semester_2'       => 'float',
        'excused_credit_percent'  => 'float',
    ];

    /** Giữ nguyên cách tính trước khi có tính năng này: 100% điểm TB học tập, cả năm chia đôi hai kỳ. */
    public const DEFAULTS = [
        'weight_academic'         => 100.0,
        'weight_class_attendance' => 0.0,
        'weight_mass_attendance'  => 0.0,
        'weight_semester_1'       => 50.0,
        'weight_semester_2'       => 50.0,
        'excused_credit_percent'  => 50.0,
    ];

    public static function makeDefault(?int $parishId = null, ?int $schoolYearId = null): self
    {
        return new self(array_merge(self::DEFAULTS, [
            'parish_id'      => $parishId,
            'school_year_id' => $schoolYearId,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function parish()
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(NamHoc::class, 'school_year_id');
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /** @return array<string, float> Trọng số các thành phần của TB học kỳ */
    public function semesterComponentWeights(): array
    {
        return [
            'academic'         => (float) $this->weight_academic,
            'class_attendance' => (float) $this->weight_class_attendance,
            'mass_attendance'  => (float) $this->weight_mass_attendance,
        ];
    }

    /** Hệ số quy đổi một buổi vắng có phép sang buổi có mặt (0 → 1). */
    public function excusedCredit(): float
    {
        return max(0.0, min(1.0, ((float) $this->excused_credit_percent) / 100));
    }

    public function usesAttendance(): bool
    {
        return $this->weight_class_attendance > 0 || $this->weight_mass_attendance > 0;
    }

    /** Nhãn phạm vi để hiển thị trên UI. */
    public function scopeLabel(): string
    {
        if (! $this->exists) {
            return 'Mặc định hệ thống';
        }

        if ($this->grade_level_id) {
            return 'Khối ' . ($this->gradeLevel?->name ?? '#' . $this->grade_level_id);
        }

        return $this->school_year_id
            ? 'Toàn xứ · ' . ($this->schoolYear?->name ?? 'năm học #' . $this->school_year_id)
            : 'Toàn xứ · mọi năm học';
    }
}
