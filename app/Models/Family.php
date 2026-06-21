<?php

namespace App\Models;

use App\Traits\BelongsToParish;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Venturecraft\Revisionable\RevisionableTrait;

class Family extends Model
{
    use CrudTrait;
    use RevisionableTrait;
    use BelongsToParish;

    protected $table = 'families';
    protected $guarded = ['id'];

    protected $fillable = [
        'parish_id',
        'parish_group_id',
        'name',
        'head_id',
        'note',
        'status',
        'member_count',
        'address',
        'ward_id',
        'province',
        'is_transferred',
        'level',
        'is_included_in_stats',
    ];

    protected $casts = [
        'status'               => 'boolean',
        'is_transferred'       => 'boolean',
        'is_included_in_stats' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function parishGroup(): BelongsTo
    {
        return $this->belongsTo(ParishGroup::class, 'parish_group_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(Parishioner::class, 'head_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Parishioner::class, 'family_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOfParish(Builder $query, int $parishId): Builder
    {
        return $query->where('parish_id', $parishId);
    }

    public function scopeOfParishGroup(Builder $query, int $parishGroupId): Builder
    {
        return $query->where('parish_group_id', $parishGroupId);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getMemberCountAttribute(): int
    {
        return $this->relationLoaded('members')
            ? $this->members->count()
            : $this->members()->count();
    }

    public function getStatusNameAttribute(): string
    {
        return $this->status ? 'Hoạt động' : 'Không hoạt động';
    }
}
