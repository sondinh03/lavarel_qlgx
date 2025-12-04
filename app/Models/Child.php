<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;
    
    protected $table = 'childrengables';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    //protected $guarded = ['id'];
    protected $fillable = ['children_id', 'childrengable_id', 'childrengable_type'];
    // protected $hidden = [];
    // protected $dates = [];
    
    //protected $appends = ['children'];
}
