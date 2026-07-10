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

    protected $guarded = ['id'];

    protected $fillable = [
        'student_id',
        'class_id',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
    ];

    protected $appends = ['status_label'];

    /*
    |--------------------------------------------------------------------------
    | CONSTANTS
    |--------------------------------------------------------------------------
    */
    const STATUS_ENROLLED  = 1; // Đang học
    const STATUS_COMPLETED = 2; // Hoàn thành
    const STATUS_DROPPED  = 3; // Nghỉ


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function student()
    {
        return $this->belongsTo(StudentNew::class);
    }

    public function catechismClass()
    {
        return $this->belongsTo(CatechismClass::class, 'class_id');
    }

    /** @deprecated Use catechismClass() */
    public function lop()
    {
        return $this->catechismClass();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ENROLLED =>
            '<span class="badge badge-success">Đang học</span>',

            self::STATUS_COMPLETED =>
            '<span class="badge badge-primary">Hoàn thành</span>',

            self::STATUS_DROPPED =>
            '<span class="badge badge-danger">Nghỉ</span>',

            default =>
            '<span class="badge badge-secondary">Không xác định</span>',
        };
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
        return $query->where('status', self::STATUS_ENROLLED);
    }
}
