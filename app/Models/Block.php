<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;


use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Support\Facades\Auth;
use Aws\RolesAnywhere\Exception\RolesAnywhereException;

class Block extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'block';
    protected $guarded = ['id'];
    protected $fillable = ['did', 'deid', 'pid', 'paid', 'name', 'namhoc', 'weight', 'status', 'created_at', 'updated_at'];
    protected $appends = ['display_name'];

    // ===== STATUS CONSTANTS =====
    public const STATUS_ACTIVE = 1;
    public const STATUS_ARCHIVED = 0;

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function getDisplayNameAttribute(): string
    {
        if ($this->relationLoaded('namHoc') || $this->namHoc) {
            return $this->name . ' (' . $this->namHoc->name . ')';
        }

        return $this->name;
    }


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function namHoc()
    {
        return $this->belongsTo(NamHoc::class, 'namhoc');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($q)
    {
        return $q->where('status', self::STATUS_ACTIVE);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    /**
     * Tự động format tên: Chữ hoa đầu mỗi từ
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }
}
