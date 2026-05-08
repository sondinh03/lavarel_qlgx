<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class GradeLevel extends Model
{
    use CrudTrait;
    use RevisionableTrait;    

    protected $table = 'grade_levels';

    protected $fillable = [
        'name',
        'code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // public function parish()
    // {
    //     return $this->belongsTo(ParishManagement::class, 'parish_id');
    // }

    public function classes()
    {
        return $this->hasMany(CatechismClass::class, 'grade_level_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Local Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
