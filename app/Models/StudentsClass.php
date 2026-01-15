<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Venturecraft\Revisionable\RevisionableTrait;

class StudentsClass extends Pivot
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $table = 'students_class';

    public $incrementing = true;
    // protected $keyType = 'bigInteger';

    protected $guarded = ['id'];

    protected $fillable = [
        'student_id',
        'class_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
    ];

    protected $appends = ['status_label'];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class, 'class_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute()
    {
        return $this->status
            ? '<span class="badge badge-success">Đang học</span>'
            : '<span class="badge badge-danger">Nghỉ học</span>';
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function openLink(): string
    {
        if (!backpack_user()) return '';

        $studentSlug = slug($this->student) . config('settings.url_prefix', '');
        $lopSlug = slug($this->lop) . config('settings.url_prefix', '');

        return '<a href="' . url($studentSlug) . '" target="_blank">HS</a> | <a href="' . url($lopSlug) . '" target="_blank">Lớp</a>';
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
