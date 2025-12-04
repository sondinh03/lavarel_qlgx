<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Venturecraft\Revisionable\RevisionableTrait;

class Teacher extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'teacher';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    //protected $fillable = ['pid', 'deid', 'did', 'paid', 'name', 'birthday', 'year', 'phone', 'note', 'status'];
    // protected $hidden = [];
    // protected $dates = [];

    protected $appends = ['teacher'];
    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    
    public function getTeacherAttribute()
    {        
        $id = $this->id;        
        $teacher = DB::table('teacher')
            ->where('id', $id)
            ->orderBy('id', 'ASC')
            ->first();
        if(!empty($teacher)){
            $namhoc = NamHoc::where('id', $teacher->namhoc)->get()->first();
            $this->attributes['name'] = $teacher->name . ' (' . $namhoc->name . ')';
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
