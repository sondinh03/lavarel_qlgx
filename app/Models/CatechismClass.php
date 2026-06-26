<?php

namespace App\Models;

use App\Traits\BelongsToParish;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatechismClass extends Model
{
    use BelongsToParish;
    use CrudTrait;
    use HasFactory;

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


    public function getTeacherNamesAttribute(): array
    {
        return $this->teachers->pluck('full_name_with_saint')->toArray();
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

    public function scopeOrdered($query)
    {
        return $query
            ->join('grade_levels', 'classes.grade_level_id', '=', 'grade_levels.id')
            ->orderBy('grade_levels.sort_order')
            ->orderBy('classes.name');
    }

    public function openLink(): string
    {
        return '<a target="_blank" href="' . route('classes.show', $this->id) . '"><i class="las la-link"></i>Liên kết</a>';
    }
}
