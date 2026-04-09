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

    const TYPE_BAPTISM      = 'baptism';      // Rửa tội
    const TYPE_COMMUNION    = 'communion';    // Rước lễ lần đầu
    const TYPE_CONFIRMATION = 'confirmation'; // Thêm sức
    const TYPE_ANOINTING    = 'anointing';    // Xức dầu bệnh nhân
    const TYPE_HOLY_ORDERS  = 'holy_orders';  // Truyền chức thánh

    protected $fillable = [
        'parishioner_id',       // FK → parishioners_new
        'type',                 // Loại bí tích (enum)
        'anointing_condition',  // Tình trạng khi xức dầu (chỉ dùng khi type = anointing) — mục 54
        'received_date',        // Ngày lãnh bí tích
        'certificate_number',   // Số chứng thư / số sách
        'book_number',          // Số quyển sổ
        'giver',                // Người ban bí tích (linh mục / giám mục)
        'sponsor',              // Người đỡ đầu
        'parish_id',            // FK → parishes (nơi lãnh bí tích)
        'parish_name',          // Tên giáo xứ nơi lãnh bí tích
        'church_name',          // Tên nhà thờ / họ đạo cụ thể (có thể khác parish_name)
        'deanery_id',           // FK → deaneries
        'diocese_id',           // FK → dioceses
        'note',                 // Ghi chú
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

    public function getIsAnointingAttribute(): bool
    {
        return $this->type === self::TYPE_ANOINTING;
    }
}