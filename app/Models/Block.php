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
    protected $fillable = ['did', 'deid', 'pid', 'paid', 'name', 'namhoc', 'status', 'created_at', 'updated_at'];
    // protected $appends = ['block'];
    protected $appends = ['display_name'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    // public function openLink(): string
    // {
    //     $slug = slug($this) . config('settings.url_prefix');

    //     $user = backpack_user();

    //     $decen = Decen::where('use', 'like', '%' . $user->id . '%')->where('status', '1')->get()->first();

    //     if (!empty($decen)) {
    //         return '<a target="_blank" href="' . url($slug) . '"><i class="las la-link"></i>Liên kết</a>';
    //     } else {
    //         return '';
    //     }
    // }

    // public function slug(): MorphOne
    // {
    //     return $this->morphOne(Slug::class, 'sluggable', 'model');
    // }

    // public function getBlockAttribute()
    // {
    //     $id = $this->id;
    //     $block = Block::where('id', $id)->orderBy('id', 'ASC')->get()->first();
    //     if (!empty($block)) {
    //         $namhoc = NamHoc::where('id', $block->namhoc)->get()->first();
    //         $this->attributes['name'] = $block->name . ' (' . $namhoc->name . ')';
    //         return $this->attributes['name']; //some logic to return numbers
    //     }
    // }

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
}
