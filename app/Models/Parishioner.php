<?php

namespace App\Models;

use App\Traits\BelongsToParish;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Venturecraft\Revisionable\RevisionableTrait;

class Parishioner extends Model
{
    use CrudTrait;
    use RevisionableTrait;
    use BelongsToParish;

    protected $table = 'parishioners_new';
    protected $guarded = ['id'];

    protected $fillable = [
        // Thông tin cá nhân
        'last_name',
        'first_name',
        'gender',
        'birthday',
        'saint_id',
        'phone',
        'email',
        'cccd',
        'avatar_path',
        'note',

        // Phân cấp giáo hội
        'diocese_id',
        'deanery_id',
        'parish_id',
        'parish_area_id',       // parish_group_id

        // Phân loại
        'ethnic',
        'career',
        'education_level',
        'catechism_level',
        'position',
        'language',
        'holy_order_status',
        'is_new_convert',
        'is_included_in_stats',
        'married',
        'level',
        'status',

        // Địa chỉ thường trú
        'permanent_ward_id',
        'permanent_province',
        'permanent_residence',

        // Địa chỉ tạm trú
        'temporary_ward_id',
        'temporary_province',
        'temporary_residence',

        // Quê quán
        'origin',

        // Gia đình
        'father_name',
        'mother_name',
        'father_id',
        'mother_id',
        'family_id',

        // Gia nhập / chuyển xứ
        'joined_date',
        'transferred_from',
        'transferred_date',
        'is_active',
        'left_reason',
    ];

    protected $casts = [
        'gender'               => 'string',
        'birthday'             => 'date',
        'joined_date'          => 'date',
        'transferred_date'     => 'date',
        'is_new_convert'       => 'boolean',
        'is_included_in_stats' => 'boolean',
        'is_active'            => 'boolean',
        'status'               => 'boolean',
        'married'              => 'integer',
    ];

    protected $appends = [
        'full_name',
        'age',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'diocese_id');
    }

    public function deanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'deanery_id');
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function parishGroup(): BelongsTo
    {
        return $this->belongsTo(ParishGroup::class, 'parish_area_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function father(): BelongsTo
    {
        return $this->belongsTo(Parishioner::class, 'father_id');
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(Parishioner::class, 'mother_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Parishioner::class, 'father_id')
            ->orWhere('mother_id', $this->id);
    }

    public function transferredFromParish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'transferred_from');
    }

    public function saint(): BelongsTo
    {
        return $this->belongsTo(Holymanagement::class, 'saint_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'parishioner_id');
    }

    public function permanentAddress(): HasOne
    {
        return $this->hasOne(Address::class, 'parishioner_id')
            ->where('type', 'permanent');
    }

    public function temporaryAddress(): HasOne
    {
        return $this->hasOne(Address::class, 'parishioner_id')
            ->where('type', 'temporary');
    }

    public function sacraments(): HasMany
    {
        return $this->hasMany(Sacrament::class, 'parishioner_id');
    }

    public function baptism(): HasOne
    {
        return $this->hasOne(Sacrament::class, 'parishioner_id')
            ->where('type', 'baptism');
    }

    public function communion(): HasOne
    {
        return $this->hasOne(Sacrament::class, 'parishioner_id')
            ->where('type', 'communion');
    }

    public function confirmation(): HasOne
    {
        return $this->hasOne(Sacrament::class, 'parishioner_id')
            ->where('type', 'confirmation');
    }

    public function holyOrders(): HasOne
    {
        return $this->hasOne(Sacrament::class, 'parishioner_id')
            ->where('type', 'holy_orders');
    }

    public function anointing(): HasOne
    {
        return $this->hasOne(Sacrament::class, 'parishioner_id')
            ->where('type', 'anointing');
    }

    public function marriageAsHusband(): HasOne
    {
        return $this->hasOne(Marriage::class, 'husband_id');
    }

    public function marriageAsWife(): HasOne
    {
        return $this->hasOne(Marriage::class, 'wife_id');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'parishioner_id');
    }

    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable', 'model');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOfParish(Builder $query, ?int $parishId): Builder
    {
        if ($parishId === null) {
            return $query;
        }
        return $query->where('parish_id', $parishId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeByGender(Builder $query, string $gender): Builder
    {
        return $query->where('gender', $gender);
    }

    public function scopeByMarriedStatus(Builder $query, int $married): Builder
    {
        return $query->where('married', $married);
    }

    public function scopeByAgeRange(Builder $query, int $minAge, ?int $maxAge = null): Builder
    {
        $now = now();

        if ($maxAge === null) {
            return $query->where('birthday', '<=', $now->copy()->subYears($minAge)->format('Y-m-d'));
        }

        return $query->whereBetween('birthday', [
            $now->copy()->subYears($maxAge)->format('Y-m-d'),
            $now->copy()->subYears($minAge)->format('Y-m-d'),
        ]);
    }

    public function scopeOfParishGroup(Builder $query, int $parishGroupId): Builder
    {
        return $query->where('parish_area_id', $parishGroupId);
    }

    public function scopeIsActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeNewConvert(Builder $query): Builder
    {
        return $query->where('is_new_convert', true);
    }

    public function scopeIncludedInStats(Builder $query): Builder
    {
        return $query->where('is_included_in_stats', true);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $searchTerm = '%' . trim($term) . '%';

        return $query->where(function ($q) use ($searchTerm) {
            $q->where('last_name', 'like', $searchTerm)
                ->orWhere('first_name', 'like', $searchTerm)
                ->orWhere('cccd', 'like', $searchTerm)
                ->orWhere('phone', 'like', $searchTerm)
                ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", [$searchTerm]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return trim($this->last_name . ' ' . $this->first_name);
    }

    public function getFullNameWithSaintAttribute(): string
    {
        $saintName = $this->saint?->name ?? '';
        return trim($saintName . ' ' . $this->full_name);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthday
            ? \Carbon\Carbon::parse($this->birthday)->age
            : null;
    }

    public function getGenderNameAttribute(): string
    {
        return $this->gender === 'male' ? 'Nam' : 'Nữ';
    }

    public function getMarriedStatusNameAttribute(): string
    {
        return match ($this->married) {
            1       => 'Đã kết hôn',
            2       => 'Góa',
            3       => 'Ly hôn',
            default => 'Độc thân',
        };
    }

    public function getStatusNameAttribute(): string
    {
        return $this->status ? 'Hoạt động' : 'Không hoạt động';
    }

    public function getStatusClassAttribute(): string
    {
        return $this->status
            ? 'bg-green-100 text-green-700'
            : 'bg-slate-200 text-slate-600';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }

        return $this->gender === 'male'
            ? asset('images/default-male-avatar.png')
            : asset('images/default-female-avatar.png');
    }

    public function getAgeGroupAttribute(): string
    {
        $age = $this->age;

        if ($age === null) return 'Không xác định';
        if ($age <= 12)    return 'Thiếu nhi (0-12)';
        if ($age <= 18)    return 'Thiếu niên (13-18)';
        if ($age <= 35)    return 'Thanh niên (19-35)';
        if ($age <= 60)    return 'Trung niên (36-60)';

        return 'Cao niên (60+)';
    }

    public function getIsBaptizedAttribute(): bool
    {
        return $this->relationLoaded('baptism')
            ? $this->baptism !== null
            : $this->sacraments()->where('type', 'baptism')->exists();
    }

    public function getIsConfirmedAttribute(): bool
    {
        return $this->relationLoaded('confirmation')
            ? $this->confirmation !== null
            : $this->sacraments()->where('type', 'confirmation')->exists();
    }

    public function getMarriageAttribute(): ?Marriage
    {
        return $this->marriageAsHusband ?? $this->marriageAsWife;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $value
            ? preg_replace('/[^0-9]/', '', $value)
            : null;
    }

    public function setCccdAttribute($value): void
    {
        $this->attributes['cccd'] = $value
            ? preg_replace('/[^0-9]/', '', $value)
            : null;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value
            ? strtolower(trim($value))
            : null;
    }
}
