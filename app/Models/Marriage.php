<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Marriage extends Model
{
    use HasFactory;
    
    protected $table = 'marriage';
    //protected $primaryKey = 'id';
    
    // public $timestamps = false;
    protected $guarded = ['id'];
    
    protected $fillable = [
        'idfamily',
        'date',
        'sohonphoi',
        'marriage_address',
        'marriage_ward',
        'marriage_province',
        'priest',
        'peopleone',
        'peopletwo',
        'tinhtrang',
        'marriage_note',
        'created_at',
        'updated_at'
    ];
    
    // protected $hidden = [];
    protected $dates = ['created_at', 'updated_at'];
    
}
