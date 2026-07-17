<?php

namespace App\Models;

use App\Traits\BelongsToParish;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParishGroup extends Model
{
    use BelongsToParish, CrudTrait, HasFactory;

    protected $table = 'parish_groups';

    protected $fillable = [
        'parish_id',
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function parish()
    {
        return $this->belongsTo(ParishNew::class);
    }

    public function students()
    {
        return $this->hasMany(StudentNew::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
