<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Venturecraft\Revisionable\RevisionableTrait;

class Lop extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    protected $table = 'lop';
    protected $guarded = ['id'];
    protected $fillable = ['id', 'did', 'deid', 'pid', 'block', 'start_date_one', 'end_date_one', 'start_date_two', 'end_date_two', 'name', 'symbol', 'teacher', 'schoolyear',  'note', 'status', 'created_at', 'updated_at'];

    protected $appends = ['lop'];
    protected $casts = [
        'teacher' => 'array',
    ];

    // ===== STATUS CONSTANTS =====
    public const STATUS_ACTIVE = 1;
    public const STATUS_ARCHIVED = 0;

    public function openLink(): string
    {
        $slug = slug($this) . config('settings.url_prefix');

        return '<a target="_blank" href="' . url($slug) . '"><i class="las la-link"></i>Liên kết</a>';
    }

    // Hiển thị: "Tên lớp - Năm học"
    public function getLopAttribute()
    {
        if ($this->schoolYear) {
            return $this->name . ' - ' . $this->schoolYear->display_name;
        }

        return $this->name;
    }

    public function getSlugUrlAttribute()
    {
        $keyword = $this->slug->keyword ?? null;
        if (!$keyword) {
            return route('lop.show', $this->id); // fallback
        }

        return url($keyword . config('settings.url_prefix', '.html'));
    }


    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable', 'model');
    }

    // public function teachers()
    // {
    //     return Teacher::whereIn('id', $this->teacher ?? [])->get();
    // }

    public function classTeachers()
    {
        return $this->hasMany(ClassTeacher::class, 'class_id');
    }

    public function getTeacherNamesAttribute()
    {
        $names = [];

        if ($this->relationLoaded('classTeachers')) {
            $classTeachers = $this->classTeachers;
        } else {
            $classTeachers = $this->classTeachers()->where('status', 1)->with('teacher')->get();
        }

        foreach ($classTeachers as $ct) {
            if ($ct->teacher && $ct->teacher->status == 1) {
                $name = $ct->teacher->name;
                if ($ct->role == ClassTeacher::ROLE_CHU_NHIEM) {
                    $names[] = $name . ' (CN)';
                } else {
                    $names[] = $name;
                }
            }
        }

        return $names;
    }

    public function getTeacherCountAttribute()
    {
        return count($this->teacher_names ?? []);
    }

    public function getHasTeacherAttribute()
    {
        return $this->teacher_count > 0;
    }

    public function getChuNhiemNameAttribute()
    {
        if ($this->relationLoaded('classTeachers')) {
            $classTeachers = $this->classTeachers;
        } else {
            $classTeachers = $this->classTeachers()->where('status', 1)->with('teacher')->get();
        }

        foreach ($classTeachers as $ct) {
            if ($ct->role == ClassTeacher::ROLE_CHU_NHIEM && $ct->teacher && $ct->teacher->status == 1) {
                return $ct->teacher->name;
            }
        }

        return null;
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'class_teachers', 'class_id', 'teacher_id')
            ->withPivot('namhoc_id', 'role', 'status')
            ->withTimestamps();
    }

    public function chuNhiem()
    {
        return $this->hasOne(ClassTeacher::class, 'class_id')
            ->where('role', ClassTeacher::ROLE_CHU_NHIEM)
            ->where('status', 1);
    }

    public function getTeachersAttribute()
    {
        $ids = $this->teacher ?? [];

        return Teacher::whereIn('id', $ids)
            ->where('status', 1)
            ->orderByRaw('FIELD(id, ' . implode(',', array_fill(0, count($ids), '?')) . ')', $ids)
            ->get();
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'students_class', 'class_id', 'student_id')
            ->using(StudentsClass::class)
            ->withPivot('status')
            ->withTimestamps();
    }

    public function blockRelation()
    {
        return $this->belongsTo(Block::class, 'block', 'id');
    }
    public function schoolYear()
    {
        return $this->belongsTo(NamHoc::class, 'schoolyear', 'id');
    }

    public function scopeActive($q)
    {
        return $q->where('status', self::STATUS_ACTIVE);
    }
}
