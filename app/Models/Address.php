<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Address extends Model
{
    protected $table = 'addresses';
    protected $guarded = ['id'];

    protected $fillable = [
        'parishioner_id',
        'type',             // 'permanent' | 'temporary'
        'ward_id',
        'province',
        'residence',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function parishioner(): BelongsTo
    {
        return $this->belongsTo(Parishioner::class, 'parishioner_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopePermanent(Builder $query): Builder
    {
        return $query->where('type', 'permanent');
    }

    public function scopeTemporary(Builder $query): Builder
    {
        return $query->where('type', 'temporary');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->residence,
            $this->province,
        ]));
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            'permanent' => 'Thường trú',
            'temporary' => 'Tạm trú',
            default     => $this->type,
        };
    }
}
