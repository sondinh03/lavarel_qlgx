<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Venturecraft\Revisionable\RevisionableTrait;

class ClassTeacher extends Pivot
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $table = 'class_teachers';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $guarded = ['id'];

    protected $fillable = [
        'teacher_id',
        'class_id',
        'namhoc_id',
        'role',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
    ];

    protected $appends = ['status_label', 'role_label'];

    // Định nghĩa constants cho role
    const ROLE_CHU_NHIEM = 1;
    const ROLE_PHO = 2;

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function lop()
    {
        return $this->belongsTo(Lop::class);
    }

    public function namhoc()
    {
        return $this->belongsTo(NamHoc::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute()
    {
        return $this->status
            ? '<span class="badge badge-success">Đang dạy</span>'
            : '<span class="badge badge-danger">Nghỉ dạy</span>';
    }

    public function getRoleLabelAttribute()
    {
        $roles = [
            self::ROLE_CHU_NHIEM => '<span class="badge badge-primary">Chủ nhiệm</span>',
            self::ROLE_PHO => '<span class="badge badge-warning">Phụ trách</span>',
        ];

        return $roles[$this->role] ?? '<span class="badge badge-secondary">Không xác định</span>';
    }

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function openLink(): string
    {
        if (!backpack_user()) return '';

        $teacherSlug = slug($this->teacher) . config('settings.url_prefix', '');
        $lopSlug = slug($this->lop) . config('settings.url_prefix', '');

        return '<a href="' . url($teacherSlug) . '" target="_blank">GV</a> | <a href="' . url($lopSlug) . '" target="_blank">Lớp</a>';
    }

    public function isChuNhiem(): bool
    {
        return $this->role === self::ROLE_CHU_NHIEM;
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

    public function scopeChuNhiem($query)
    {
        return $query->where('role', self::ROLE_CHU_NHIEM);
    }

    public function scopeByNamhoc($query, $namhocId)
    {
        return $query->where('namhoc_id', $namhocId);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }
}
