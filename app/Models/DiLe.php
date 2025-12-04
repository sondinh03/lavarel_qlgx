<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiLe extends Model
{
    use HasFactory;
    
    /*
     |--------------------------------------------------------------------------
     | GLOBAL VARIABLES
     |--------------------------------------------------------------------------
     */
    
    protected $table = 'dile';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['id', 'idh', 'lophoc', 'hocky', 'thang', 'ngay', 'dile', 'weight', 'status', 'created_at', 'updated_at'];
}
