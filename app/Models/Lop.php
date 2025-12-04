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
    protected $fillable = ['id', 'did', 'deid', 'pid', 'block', 'start_date_one', 'end_date_one', 'start_date_two', 'end_date_two', 'name', 'symbol', 'teacher', 'note', 'status', 'created_at', 'updated_at'];

    protected $appends = ['lop'];
    protected $casts = [
        'teacher' => 'array',
    ];

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

    public function teachers()
    {
        return Teacher::whereIn('id', $this->teacher ?? [])->get();
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
}
