<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Venturecraft\Revisionable\RevisionableTrait;

class Parish extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /** Giáo họ */
    protected $table = 'parishs';

    protected $guarded = ['id'];

    protected $fillable = [
        'pid',     // Giáo xứ
        'deid',    // Giáo hạt
        'did',     // Giáo phận
        'name',
        'status',  // 1: active, 0: inactive
    ];

    protected $casts = [
        'pid'    => 'integer',
        'deid'   => 'integer',
        'did'    => 'integer',
        'status' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Giáo họ thuộc về Giáo xứ
     */
    public function parishManagement(): BelongsTo
    {
        return $this->belongsTo(ParishManagement::class, 'pid', 'id');
    }

    /**
     * Giáo họ thuộc về Giáo hạt
     */
    public function deanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'deid', 'id');
    }

    /**
     * Giáo họ thuộc về Giáo phận
     */
    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'did', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Chỉ lấy giáo họ đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Lọc theo Giáo xứ
     */
    public function scopeOfParish($query, int $pid)
    {
        return $query->where('pid', $pid);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    /**
     * Chuẩn hoá tên giáo họ
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }
}
