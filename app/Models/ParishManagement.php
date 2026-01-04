<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\CssSelector\Node\FunctionNode;
use Venturecraft\Revisionable\RevisionableTrait;

class ParishManagement extends Model
{
    use CrudTrait;
    use HasFactory;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /** Giáo xứ */
    protected $table = 'parish_managements';

    protected $fillable = [
        'name',
        'deanerys',
        'diocese',
        'ward',
        'province',
        'phone',
        'image',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'deanerys' => 'integer',
        'diocese' => 'integer',
    ];

    protected $attributes = [
        'status' => 1,
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable', 'model');
    }

    public function deanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'deanerys', 'id');
    }

    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'diocese', 'id');
    }

    /**
     * Các năm học thuộc giáo xứ này
     */
    public function namHocs(): HasMany
    {
        return $this->hasMany(NamHoc::class, 'parish_id', 'id');
    }

    /**
     * Các giáo viên thuộc giáo xứ này
     */
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'pid', 'id');
    }

    /**
     * Các học sinh thuộc giáo xứ này
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'pid', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    /**
     * Get deanery name
     */
    public function getDeaneryNameAttribute(): string
    {
        return $this->deanery?->name ?? 'N/A';
    }

    /**
     * Get diocese name
     */
    public function getDioceseNameAttribute(): string
    {
        return $this->dioceseRelation?->name ?? 'N/A';
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
