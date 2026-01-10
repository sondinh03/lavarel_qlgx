<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * ⚠️ WARNING
 *
 * Model này CHỈ dùng cho Backpack / UI.
 * KHÔNG dùng cho Authentication / Authorization / Policy.
 *
 * Auth model thật là: App\Models\User
 */
class Decents extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'users';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    //protected $fillable = ['name', 'email'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    //protected $appends = ['use', 'user'];
    protected $appends = ['use'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getUseAttribute()
    {
        $id = $this->id;
        $teacher = DB::table('users')
            ->select('name', 'email')
            ->where('id', $id)
            ->orderBy('id', 'ASC')
            ->first();

        if (!empty($teacher)) {
            $this->attributes['name'] = $teacher->name . ' - ' . $teacher->email;
            return $this->attributes['name']; //some logic to return numbers
        }
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
