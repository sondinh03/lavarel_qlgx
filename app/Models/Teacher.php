<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Venturecraft\Revisionable\RevisionableTrait;

class Teacher extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'teacher';
    protected $guarded = ['id'];

    protected $fillable = [
        'pid',      // Giáo xứ ID
        'deid',     // deanery ID (Giáo Hạt)
        'did',      // Diocese ID (Giáo phận)
        'paid',     // Giáo họ ID
        'holy_id',  // ID tên thánh
        'name',
        'position',
        'birthday',
        'year',     // Có thể bỏ sau này
        'phone',
        'phone_number',
        'note',     // Có thể bỏ sau này
        'status',   // 1: active, 0: inactive
    ];

    protected $casts = [
        'birthday' => 'date',
        'status' => 'integer',
        'year' => 'integer',
    ];

    // protected $appends = ['teacher'];

    /**
     * Attributes to track for revisions
     * @var bool
     */
    protected $revisionEnabled = true;
    protected $revisionCreationsEnabled = true;

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Teacher thuộc về một Holy
     */
    public function holy(): BelongsTo
    {
        return $this->belongsTo(Holymanagement::class, 'holy_id', 'id');
    }

    /**
     * Teacher thuộc về một Giáo ho (Parish)
     */
    public function parishChild(): BelongsTo
    {
        return $this->belongsTo(Parish::class, 'paid', 'id');
    }

    /**
     * Teacher thuộc về một Giáo xứ (ParishManagement)
     */
    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishManagement::class, 'pid', 'id');
    }

    /**
     * Teacher có thể thuộc về một Decen (Giáo Hạt)
     */
    public function deanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'deid', 'id');
    }

    /**
     * Teacher thuộc về một Giáo phận (Diocese)
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
     * Chỉ lấy giáo viên đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Lọc theo giáo xứ
     */
    public function scopeOfParish($query, int $parishId)
    {
        return $query->where('pid', $parishId);
    }

    /**
     * Tìm kiếm theo tên hoặc số điện thoại
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Lấy tên Giáo họ (Parish Child)
     */
    public function getParishChildNameAttribute(): ?string
    {
        return $this->parishChild?->name;
    }

    /**
     * Lấy tên Holy
     */
    public function getHolyNameAttribute(): ?string
    {
        return $this->holy?->name;
    }

    /**
     * Tự động format tên: Chữ hoa đầu mỗi từ
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Sanitize số điện thoại: chỉ lưu số
     */
    public function setPhoneNumberAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['phone_number'] = null;
            return;
        }

        // Loại bỏ tất cả ký tự không phải số
        $phoneNumber = preg_replace('/[^0-9]/', '', $value);
        $this->attributes['phone_number'] = $phoneNumber ?: null;
    }
}
