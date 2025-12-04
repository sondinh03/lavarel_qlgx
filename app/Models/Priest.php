<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Priest extends Model
{
    use HasFactory;
    
    protected $table = 'sacrament_givers';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['name', 'created_at', 'updated_at'];
    // protected $hidden = [];
    protected $dates = ['created_at', 'updated_at'];
    
    protected $appends = ['priest'];
    
    /*
     |--------------------------------------------------------------------------
     | FUNCTIONS
     |--------------------------------------------------------------------------
     */
    
    public function getPriestAttribute()
    {
        return $this->attributes['name']; //some logic to return numbers
    }
}
