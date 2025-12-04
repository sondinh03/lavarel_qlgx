<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slug extends Model
{
    use CrudTrait;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'slugs';

    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    //protected $fillable = ['keyword', 'method', 'controller', 'model', 'sluggable_id'];
    protected $fillable = ['keyword', 'method', 'controller', 'model', 'sluggable_id'];
    // protected $hidden = [];
    // protected $dates = [];
    protected $dates = ['created_at'];
    //protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    //protected $casts = ['deleted_at' => 'datetime'];
    
    

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public static function findBySlug($slug)
    {
        return Slug::where('keyword', $slug)->first();
    }

    public function sluggable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

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
