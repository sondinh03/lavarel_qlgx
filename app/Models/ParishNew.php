<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Venturecraft\Revisionable\RevisionableTrait;

class ParishNew extends Model
{
    use HasFactory;
    use CrudTrait;
    use RevisionableTrait;

    public const NAME_PREFIX = 'Giáo xứ';

    protected $table = 'parishes';

    protected $fillable = [
        'name',
        'code',
        'deanery_id',
        'diocese_id',
        'parish_priest_name',
        'ward',
        'province',
        'phone',
        'image',
        'status',
        'scores_entry_open',
    ];

    protected $casts = [
        'status'            => 'boolean',
        'scores_entry_open' => 'boolean',
    ];

    public static function normalizeName(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return '';
        }

        $name = trim((string) preg_replace('/^(?:giáo\s*xứ\s*)+/iu', '', $name));

        return self::NAME_PREFIX . ($name !== '' ? ' ' . $name : '');
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = self::normalizeName($value);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function deanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'deanery_id', 'id');
    }

    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'diocese_id', 'id');
    }

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
