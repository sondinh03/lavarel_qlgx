<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToParish;

class ParishGroup extends Model
{
    use HasFactory, BelongsToParish;

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
}