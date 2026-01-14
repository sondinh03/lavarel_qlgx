<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClassSummary extends Model
{
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'student_class_summaries';
    protected $guarded = ['id'];
    protected $fillable = [
        'student_id',
        'class_id',
        'avg_hk1',
        'avg_hk2',
        'avg_year',
        'ranking',
        'result',
        'note',
    ];

    protected $casts = [
        'student_id' => 'integer',
        'class_id' => 'integer',
        'avg_hk1' => 'decimal:2',
        'avg_hk2' => 'decimal:2',
        'avg_year' => 'decimal:2',
        'result' => 'integer',
    ];

    // ===== RANKING CONSTANTS =====
    public const RANK_GIOI = 'Giỏi';
    public const RANK_KHA = 'Khá';
    public const RANK_TRUNG_BINH = 'Trung bình';
    public const RANK_YEU = 'Yếu';

    public const RANKINGS = [
        self::RANK_GIOI,
        self::RANK_KHA,
        self::RANK_TRUNG_BINH,
        self::RANK_YEU,
    ];

    // ===== RESULT CONSTANTS =====
    public const RESULT_PASS = 1;      // Lên lớp
    public const RESULT_FAIL = 0;      // Ở lại

    public const RESULT_LABELS = [
        self::RESULT_PASS => 'Lên lớp',
        self::RESULT_FAIL => 'Ở lại',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'class_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOfStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeOfClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopePassed($query)
    {
        return $query->where('result', self::RESULT_PASS);
    }

    public function scopeFailed($query)
    {
        return $query->where('result', self::RESULT_FAIL);
    }

    public function scopeByRanking($query, $ranking)
    {
        return $query->where('ranking', $ranking);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getResultLabelAttribute(): ?string
    {
        return $this->result !== null
            ? (self::RESULT_LABELS[$this->result] ?? 'Không xác định')
            : null;
    }

    public function getFormattedAvgYearAttribute(): string
    {
        return $this->avg_year ? number_format($this->avg_year, 1) : '-';
    }

    public function getIsPassingAttribute(): bool
    {
        return $this->result === self::RESULT_PASS;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Tính xếp loại dựa trên điểm trung bình năm
     */
    public static function calculateRanking(?float $avgYear): ?string
    {
        if ($avgYear === null) {
            return null;
        }

        if ($avgYear >= 8.0) {
            return self::RANK_GIOI;
        }

        if ($avgYear >= 6.5) {
            return self::RANK_KHA;
        }

        if ($avgYear >= 5.0) {
            return self::RANK_TRUNG_BINH;
        }

        return self::RANK_YEU;
    }

    /**
     * Tự động set ranking khi lưu
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($summary) {
            if ($summary->avg_year !== null && !$summary->ranking) {
                $summary->ranking = self::calculateRanking($summary->avg_year);
            }
        });
    }
}
