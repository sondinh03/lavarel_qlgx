<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiemThi extends Model
{
    use HasFactory;
    
    /*
     |--------------------------------------------------------------------------
     | GLOBAL VARIABLES
     |--------------------------------------------------------------------------
     */
    
    protected $table = 'diemthi';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['id', 'ihv', 'lop', 'tuan1', 'k1', 'kinh1', 'kq1', 'tuan2', 'k2', 'kinh2', 'kq2', 'canam', 'seploai', 'nghile', 'bohoc', 'hanhkiem', 'ghichu', 'weight', 'status', 'created_at', 'updated_at'];
    // protected $hidden = [];
    // protected $dates = [];
}
