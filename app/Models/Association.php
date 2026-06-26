<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Venturecraft\Revisionable\RevisionableTrait;

class Association extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    /** Hội đoàn */
    protected $table = 'associations';

    protected $guarded = ['id'];

    protected $fillable = [
        'pid',            // Giáo xứ
        'deid',           // Giáo hạt
        'did',            // Giáo phận
        'name',
        'ngaybonmang',    // Ngày bổn mạng
        'ngaythanhlap',   // Ngày thành lập
        'thanhbonmang',   // Thánh bổn mạng
        'note',
        'status',         // 1: active, 0: inactive
    ];

    protected $casts = [
        'pid'            => 'integer',
        'deid'           => 'integer',
        'did'            => 'integer',
        'ngaybonmang'    => 'date',
        'ngaythanhlap'   => 'date',
        'status'         => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function openLink(): string
    {
        $slug = slug($this) . config('settings.url_prefix');

        return '<a target="_blank" href="' . url($slug) . '"><i class="las la-link"></i>Liên kết</a>';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable', 'model');
    }

    /** Giáo xứ (parishes — id đồng bộ từ parish_managements) */
    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'pid', 'id');
    }

    /** @deprecated Dùng parish() — bảng parish_managements có thể đã gỡ */
    public function parishManagement(): BelongsTo
    {
        return $this->belongsTo(ParishManagement::class, 'pid', 'id');
    }

    /** Giáo hạt */
    public function deanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'deid', 'id');
    }

    /** Giáo phận */
    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'did', 'id');
    }

    /** Giáo dân thuộc hội đoàn (bảng cũ) */
    public function parishioners(): HasMany
    {
        return $this->hasMany(GiaoDan::class, 'assid');
    }

    /** Giáo dân module mới */
    public function parishionersNew(): HasMany
    {
        return $this->hasMany(Parishioner::class, 'association_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOfParish($query, int $pid)
    {
        return $query->where('pid', $pid);
    }

    public function scopeOfDeanery($query, int $deid)
    {
        return $query->where('deid', $deid);
    }

    public function scopeOfDiocese($query, int $did)
    {
        return $query->where('did', $did);
    }
}
