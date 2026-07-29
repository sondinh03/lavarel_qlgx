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
        'parish_logo',
        'status',
        'scores_entry_open',
        'attendance_auto_finalize_enabled',
        'attendance_auto_finalize_time',
    ];

    protected $casts = [
        'status'                            => 'boolean',
        'scores_entry_open'                 => 'boolean',
        'attendance_auto_finalize_enabled'  => 'boolean',
    ];

    public const DEFAULT_ATTENDANCE_AUTO_FINALIZE_TIME = '20:00';

    /** Giờ chốt tự động dạng H:i (mặc định 20:00). */
    public function attendanceAutoFinalizeTimeHi(): string
    {
        $raw = $this->attendance_auto_finalize_time;

        if ($raw === null || $raw === '') {
            return self::DEFAULT_ATTENDANCE_AUTO_FINALIZE_TIME;
        }

        // Cột TIME có thể trả về "20:00:00" hoặc Carbon.
        if ($raw instanceof \DateTimeInterface) {
            return $raw->format('H:i');
        }

        return substr((string) $raw, 0, 5);
    }

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
        return $this->hasMany(User::class, 'parish_id');
    }

    public function students()
    {
        return $this->hasMany(StudentNew::class, 'parish_id');
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'parish_id');
    }

    public function schoolYears()
    {
        return $this->hasMany(NamHoc::class, 'parish_id');
    }

    public function gradeLevels()
    {
        return $this->hasMany(GradeLevel::class, 'parish_id');
    }

    public function classes()
    {
        return $this->hasMany(CatechismClass::class, 'parish_id');
    }

    public function parishGroups()
    {
        return $this->hasMany(ParishGroup::class, 'parish_id');
    }

    public function parishioners()
    {
        return $this->hasMany(Parishioner::class, 'parish_id');
    }
}
