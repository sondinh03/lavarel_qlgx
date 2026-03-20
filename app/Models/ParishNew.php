<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Venturecraft\Revisionable\RevisionableTrait;

class ParishNew extends Model
{
    use HasFactory;
    use CrudTrait;
    use RevisionableTrait;

    protected $table = 'parishes';

    protected $fillable = [
        'name',
        'code',
        'deanery_id',
        'diocese_id',
        'ward',
        'province',
        'phone',
        'image',
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

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function students()
    {
        return $this->hasMany(StudentNew::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function schoolYears()
    {
        return $this->hasMany(NamHoc::class);
    }

    public function gradeLevels()
    {
        return $this->hasMany(GradeLevel::class);
    }

    public function classes()
    {
        return $this->hasMany(CatechismClass::class);
    }

    public function parishGroups()
    {
        return $this->hasMany(ParishGroup::class);
    }
}
