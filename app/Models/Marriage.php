<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Venturecraft\Revisionable\RevisionableTrait;

class Marriage extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    protected $table = 'marriages';
    protected $guarded = ['id'];

    const STATUS_VALID   = 'valid';     // Hợp lệ
    const STATUS_INVALID = 'invalid';   // Bất hợp lệ
    const STATUS_WIDOWED = 'widowed';   // Góa
    const STATUS_DIVORCED = 'divorced'; // Ly hôn

    protected $fillable = [
        'husband_id',
        'wife_id',
        'married_date',
        'certificate_number',
        'parish_id',
        'parish_name',
        'status',
        'witness_1',
        'witness_2',
        'note',
    ];

    protected $casts = [
        'married_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function husband(): BelongsTo
    {
        return $this->belongsTo(Parishioner::class, 'husband_id');
    }

    public function wife(): BelongsTo
    {
        return $this->belongsTo(Parishioner::class, 'wife_id');
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VALID);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeOfParish(Builder $query, int $parishId): Builder
    {
        return $query->where('parish_id', $parishId);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_VALID    => 'Hợp lệ',
            self::STATUS_INVALID  => 'Bất hợp lệ',
            self::STATUS_WIDOWED  => 'Góa',
            self::STATUS_DIVORCED => 'Ly hôn',
            default               => $this->status,
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_VALID    => 'bg-green-100 text-green-700',
            self::STATUS_INVALID  => 'bg-red-100 text-red-700',
            self::STATUS_WIDOWED  => 'bg-gray-100 text-gray-600',
            self::STATUS_DIVORCED => 'bg-yellow-100 text-yellow-700',
            default               => '',
        };
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_VALID    => 'Hợp lệ',
            self::STATUS_INVALID  => 'Bất hợp lệ',
            self::STATUS_WIDOWED  => 'Góa',
            self::STATUS_DIVORCED => 'Ly hôn',
        ];
    }

    public function getIsValidAttribute(): bool
    {
        return $this->status === self::STATUS_VALID;
    }
}
