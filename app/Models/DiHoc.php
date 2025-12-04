<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiHoc extends Model
{
    use HasFactory;
    
    /*
     |--------------------------------------------------------------------------
     | GLOBAL VARIABLES
     |--------------------------------------------------------------------------
     */
    
    protected $table = 'dihoc';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['idh', 'lophoc', 'hocky', 'tuan', 'dihoc', 'weight', 'status', 'created_at', 'updated_at'];
    // protected $hidden = [];
    // protected $dates = [];
}
