<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentScore extends Model
{
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'student_scores';

    protected $guarded = ['id'];

    protected $fillable = [
        'student_class_id',
        'score_type_id',
        'score_value',
        'attempt',
        'note',
    ];

    protected $casts = [
        'student_class_id' => 'integer',
        'score_type_id'    => 'integer',
        'score_value'      => 'decimal:2',
        'attempt'          => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Gắn với bảng trung gian học sinh - lớp (theo năm học)
     */
    public function studentClass()
    {
        return $this->belongsTo(StudentsClass::class, 'student_class_id');
    }

    /**
     * Truy xuất nhanh học sinh (through student_class)
     */
    public function student()
    {
        return $this->hasOneThrough(
            Student::class,
            StudentsClass::class,
            'id',          // Foreign key on students_class
            'id',          // Foreign key on students
            'student_class_id',
            'student_id'
        );
    }

    /**
     * Truy xuất nhanh lớp (through student_class)
     */
    public function catechismClass()
    {
        return $this->hasOneThrough(
            CatechismClass::class,
            StudentsClass::class,
            'id',
            'id',
            'student_class_id',
            'class_id'
        );
    }

    /** @deprecated Use catechismClass() */
    public function lop()
    {
        return $this->catechismClass();
    }

    public function scoreType()
    {
        return $this->belongsTo(ScoreType::class, 'score_type_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOfStudentClass($query, $studentClassId)
    {
        return $query->where('student_class_id', $studentClassId);
    }

    public function scopeOfScoreType($query, $scoreTypeId)
    {
        return $query->where('score_type_id', $scoreTypeId);
    }

    public function scopeLatestAttempt($query)
    {
        return $query->orderByDesc('attempt');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFormattedScoreAttribute(): string
    {
        return number_format($this->score_value, 1);
    }

    public function getIsPassingAttribute(): bool
    {
        return $this->score_value >= 5.0;
    }
}
