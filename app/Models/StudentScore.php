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
        'student_id',
        'class_id',
        'score_type_id',
        'score_value',
        'attempt',
        'note',
    ];

    protected $casts = [
        'student_id' => 'integer',
        'class_id' => 'integer',
        'score_type_id' => 'integer',
        'score_value' => 'decimal:2',
        'attempt' => 'integer',
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

    public function scoreType()
    {
        return $this->belongsTo(ScoreType::class, 'score_type_id');
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
