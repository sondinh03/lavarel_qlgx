<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhaoKinh extends Model
{
    use HasFactory;
    
    /*
     |--------------------------------------------------------------------------
     | GLOBAL VARIABLES
     |--------------------------------------------------------------------------
     */
    
    protected $table = 'khaokinh';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['id', 'idh', 'lophoc', 'hocky', 'ngay', 'khaokinh', 'weight', 'status', 'created_at', 'updated_at'];
    // protected $hidden = [];
    // protected $dates = [];
}
