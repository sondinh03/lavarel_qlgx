<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LopTarget extends Model
{
    use HasFactory;
    
    /*
     |--------------------------------------------------------------------------
     | GLOBAL VARIABLES
     |--------------------------------------------------------------------------
     */
    
    protected $table = 'lop';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    //protected $guarded = ['id'];
    //protected $fillable = ['id', 'did', 'deid', 'pid', 'name', 'teacher', 'note', 'status', 'created_at', 'updated_at'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];
    //protected $appends = ['teacher'];
}
