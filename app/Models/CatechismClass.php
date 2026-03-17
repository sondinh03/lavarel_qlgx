<?php

namespace App\Models;

use App\Traits\BelongsToParish;
use Illuminate\Database\Eloquent\Model;

class CatechismClass extends Model
{
    use BelongsToParish;

    protected $table = "classes";

    protected $fillable = [
        'parish_id',
        'school_year_id',
        'grade_level_id',
        'name',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'parish_id' => 'integer',
        'school_year_id' => 'integer',
        'grade_level_id' => 'integer',
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
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

    public function scoreTypes()
    {
        return $this->hasMany(ScoreType::class, 'class_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'class_teachers',
            'class_id',
            'teacher_id'
        )
            ->withTimestamps();
    }

    public function students()
    {
        return $this->belongsToMany(
            StudentNew::class,
            'students_class',
            'class_id',
            'student_id'
        )
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (local)
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTeacherNamesAttribute(): array
    {
        return $this->teachers->pluck('full_name_with_saint')->toArray();
    }
}
