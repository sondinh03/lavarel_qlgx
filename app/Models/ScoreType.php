<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreType extends Model
{
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'score_types';
    protected $guarded = ['id'];
    protected $fillable = [
        'class_id',
        'semester',
        'type',
        'name',
        'order',
        'coefficient',
        'max_score',
        'is_active',
    ];

    protected $casts = [
        'semester' => 'integer',
        'type' => 'integer',
        'order' => 'integer',
        'coefficient' => 'decimal:2',
        'max_score' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ===== TYPE CONSTANTS =====
    public const TYPE_KHAO_KINH = 1;
    public const TYPE_15_PHUT = 2;
    public const TYPE_45_PHUT = 3;
    public const TYPE_GIUA_KY = 4;
    public const TYPE_CUOI_KY = 5;

    // ===== TYPE LABELS =====
    public const TYPE_LABELS = [
        self::TYPE_KHAO_KINH => 'Khảo kinh',
        self::TYPE_15_PHUT => 'Điểm 15 phút',
        self::TYPE_45_PHUT => 'Điểm 45 phút',
        self::TYPE_GIUA_KY => 'Giữa kỳ',
        self::TYPE_CUOI_KY => 'Cuối kỳ',
    ];

    // ===== SEMESTER CONSTANTS =====
    public const SEMESTER_1 = 1;
    public const SEMESTER_2 = 2;

    public const SEMESTER_LABELS = [
        self::SEMESTER_1 => 'Học kỳ I',
        self::SEMESTER_2 => 'Học kỳ II',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function catechismClass()
    {
        return $this->belongsTo(CatechismClass::class, 'class_id');
    }

    /** @deprecated Use catechismClass() */
    public function lop()
    {
        return $this->catechismClass();
    }

    public function studentScores()
    {
        return $this->hasMany(StudentScore::class, 'score_type_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeOfSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? 'Không xác định';
    }

    public function getSemesterLabelAttribute(): string
    {
        return self::SEMESTER_LABELS[$this->semester] ?? 'Không xác định';
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} (Hệ số: {$this->coefficient})";
    }
}
