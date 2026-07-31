<?php

namespace App\Models;

use App\Traits\HasFormattedName;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Venturecraft\Revisionable\RevisionableTrait;

class Deanery extends Model
{
    use CrudTrait;
    use HasFactory;
    use RevisionableTrait;

    public const NAME_PREFIX = 'Giáo hạt';

    protected $table = 'deanerys';

    protected $guarded = ['id'];

    protected $fillable = ['name', 'did', 'status', 'created_at', 'updated_at'];

    protected bool $revisionCleanup = true;

    protected $with = ['slug'];

    public static function normalizeName(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return '';
        }

        $name = trim((string) preg_replace('/^(?:giáo\s*hạt\s*)+/iu', '', $name));

        return self::NAME_PREFIX . ($name !== '' ? ' ' . $name : '');
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = self::normalizeName($value);
    }

    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable', 'model');
    }

    public function diocese()
    {
        return $this->belongsTo(Diocese::class, 'did', 'id');
    }
}
