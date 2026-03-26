<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Venturecraft\Revisionable\RevisionableTrait;

class Sacrament extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    protected $table = 'sacraments';
    protected $guarded = ['id'];

    // Các loại bí tích
    const TYPE_BAPTISM      = 'baptism';        // Rửa tội
    const TYPE_COMMUNION    = 'communion';      // Rước lễ lần đầu
    const TYPE_CONFIRMATION = 'confirmation';   // Thêm sức
    const TYPE_ANOINTING    = 'anointing';      // Xức dầu
    const TYPE_HOLY_ORDERS  = 'holy_orders';    // Truyền chức thánh

    protected $fillable = [
        'parishioner_id',
        'type',
        'received_date',
        'certificate_number',
        'book_number',
        'giver',            // Người ban bí tích
        'sponsor',          // Người đỡ đầu
        'parish_id',
        'parish_name',
        'deanery_id',
        'diocese_id',
        'note',
    ];

    protected $casts = [
        'received_date' => 'date',
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

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function deanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'deanery_id');
    }

    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'diocese_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeBaptism(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_BAPTISM);
    }

    public function scopeCommunion(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_COMMUNION);
    }

    public function scopeConfirmation(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_CONFIRMATION);
    }

    public function scopeHolyOrders(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_HOLY_ORDERS);
    }

    public function scopeAnointing(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_ANOINTING);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getTypeNameAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_BAPTISM      => 'Rửa tội',
            self::TYPE_COMMUNION    => 'Rước lễ lần đầu',
            self::TYPE_CONFIRMATION => 'Thêm sức',
            self::TYPE_ANOINTING    => 'Xức dầu bệnh nhân',
            self::TYPE_HOLY_ORDERS  => 'Truyền chức thánh',
            default                 => $this->type,
        };
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_BAPTISM      => 'Rửa tội',
            self::TYPE_COMMUNION    => 'Rước lễ lần đầu',
            self::TYPE_CONFIRMATION => 'Thêm sức',
            self::TYPE_ANOINTING    => 'Xức dầu bệnh nhân',
            self::TYPE_HOLY_ORDERS  => 'Truyền chức thánh',
        ];
    }
}
