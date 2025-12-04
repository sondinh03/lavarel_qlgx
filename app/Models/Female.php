<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Female extends Model
{
    use HasFactory;
    
    protected $table = 'parishioners';
    
    protected $guarded = ['id'];
    protected $fillable = ['name', 'holy', 'status', 'created_at', 'updated_at'];
    
    protected $appends = ['female'];
    
    /*
     |--------------------------------------------------------------------------
     | FUNCTIONS
     |--------------------------------------------------------------------------
     */
    
    public function getFemaleAttribute()
    {
        $holy = DB::table('holymanagements')
        ->where('id', $this->holy)
        ->orderBy('id', 'ASC')
        ->first();
        
        $this->attributes['name'] = $holy->name . ' ' . $this->last_name . ' ' . $this->name;
        return $this->attributes['name']; //some logic to return numbers
    }
}
