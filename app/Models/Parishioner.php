<?php

namespace App\Models;

use App\Support\ParishionerCodeGenerator;
use App\Traits\BelongsToParish;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Venturecraft\Revisionable\RevisionableTrait;

class Parishioner extends Model
{
    use CrudTrait;
    use RevisionableTrait;
    use BelongsToParish;

    protected $table = 'parishioners_new';
    protected $guarded = ['id'];

    protected $fillable = [
        'code',
        // Thông tin cá nhân
        'last_name',            // Họ và tên đệm
        'first_name',           // Tên
        'gender',               // Giới tính: male | female
        'birthday',             // Ngày sinh (dương lịch)
        'birth_place',          // Nơi sinh
        'birth_order',          // Con thứ mấy trong gia đình
        'saint_id',             // Tên thánh (FK → holymanagements)
        'phone',                // Số điện thoại
        'email',                // Email
        'cccd',                 // CMND / CCCD
        'avatar_path',          // Đường dẫn ảnh đại diện
        'note',                 // Ghi chú

        // Phân cấp giáo hội
        'diocese_id',           // Giáo phận
        'deanery_id',           // Giáo hạt
        'parish_id',            // Giáo xứ
        'parish_area_id',       // Giáo họ (parish_group)
        'association_id',       // Hội đoàn (associations)

        // Phân loại cá nhân - xã hội
        'ethnic',               // Dân tộc (tinyint enum)
        'career',               // Nghề nghiệp (tinyint enum)
        'education_level',      // Trình độ học vấn (tinyint enum)
        'specialist_level',     // Trình độ chuyên môn (tinyint enum) — mục 27
        'catechism_level',      // Trình độ giáo lý (tinyint enum)
        'catechism_major',      // Chuyên ngành giáo lý / giáo dục — mục 28
        'position',             // Chức vụ (tinyint enum)
        'language',             // Ngôn ngữ (tinyint enum)
        'holy_order_status',    // Tình trạng thánh chức (tinyint enum)
        'is_new_convert',       // Tân tòng: 0 | 1
        'is_included_in_stats', // Được thống kê: 0 | 1
        'married',              // Tình trạng hôn nhân (int enum)
        'level',                // Cấp bậc / mức độ (tinyint)
        'status',               // Trạng thái hoạt động: 0 | 1

        // Địa chỉ thường trú
        'permanent_ward_id',    // Xã / Phường thường trú
        'permanent_province',   // Tỉnh / TP thường trú
        'permanent_residence',  // Địa chỉ thường trú chi tiết

        // Địa chỉ tạm trú
        'temporary_ward_id',    // Xã / Phường tạm trú
        'temporary_province',   // Tỉnh / TP tạm trú
        'temporary_residence',  // Địa chỉ tạm trú chi tiết

        // Quê quán
        'origin',               // Nguyên quán

        // Gia đình
        'father_name',          // Tên cha (văn bản, khi chưa có tài khoản)
        'mother_name',          // Tên mẹ (văn bản, khi chưa có tài khoản)
        'father_id',            // FK → parishioners (cha)
        'mother_id',            // FK → parishioners (mẹ)
        'family_id',            // FK → families
        'family_role',          // Vai trò trong gia đình: husband=chồng, wife=vợ, child=con, other=khác

        // Gia nhập / chuyển xứ
        'joined_date',          // Ngày gia nhập giáo xứ
        'transferred_from',     // Chuyển đến từ giáo xứ nào (FK → parishes)
        'transferred_date',     // Ngày chuyển đến
        'is_active',            // Đang sinh hoạt tại xứ: 0 | 1
        'left_reason',          // Lý do rời xứ

        // Thông tin tử vong
        'death_date',           // Ngày mất — mục 58
        'death_book_number',    // Số sổ mất — mục 59
        'death_place',          // Nơi qua đời — mục 60
        'burial_place',         // Nơi an táng — mục 61
    ];

    protected $casts = [
        'gender'               => 'string',
        'birthday'             => 'date',
        'joined_date'          => 'date',
        'transferred_date'     => 'date',
        'death_date'           => 'date',
        'is_new_convert'       => 'boolean',
        'is_included_in_stats' => 'boolean',
        'is_active'            => 'boolean',
        'status'               => 'boolean',
        'married'              => 'integer',
        'birth_order'          => 'integer',
        'specialist_level'     => 'integer',
        'family_role'          => 'string',
    ];

    protected static function booted(): void
    {
        static::creating(function (Parishioner $parishioner) {
            if (empty($parishioner->code)) {
                $parishioner->code = ParishionerCodeGenerator::generate($parishioner->parish_id);
            }
        });
    }

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

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class, 'association_id');
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
        return $this->hasMany(Parishioner::class, 'father_id');
    }

    /** Con trong cùng hộ gia đình (theo family_role). */
    public function childrenInFamily(): HasMany
    {
        return $this->hasMany(Parishioner::class, 'family_id', 'family_id')
            ->where('family_role', 'child');
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
        return $this->hasOne(StudentNew::class, 'parishioner_id');
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

    public function scopeOfAssociation(Builder $query, int $associationId): Builder
    {
        return $query->where('association_id', $associationId);
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

    public function scopeAlive(Builder $query): Builder
    {
        return $query->whereNull('death_date');
    }

    public function scopeDeceased(Builder $query): Builder
    {
        return $query->whereNotNull('death_date');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $searchTerm = '%' . trim($term) . '%';

        return $query->where(function ($q) use ($searchTerm) {
            $q->where('last_name', 'like', $searchTerm)
                ->orWhere('first_name', 'like', $searchTerm)
                ->orWhere('code', 'like', $searchTerm)
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
            ? Carbon::parse($this->birthday)->age
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

    public function getIsDeceasedAttribute(): bool
    {
        return $this->death_date !== null;
    }

    public function getMarriageAttribute(): ?Marriage
    {
        return $this->marriageAsHusband ?? $this->marriageAsWife;
    }

    public function getCareerNameAttribute(): ?string
    {
        return config('parishioner.career.' . $this->career);
    }

    public function getLevelNameAttribute(): ?string
    {
        return config('parishioner.level.' . $this->level);
    }

    public function getEthnicNameAttribute(): ?string
    {
        return config('parishioner.ethnic.' . $this->ethnic);
    }

    public function getEducationLevelNameAttribute(): ?string
    {
        return config('parishioner.education_level.' . $this->education_level);
    }

    public function getPermanentWardNameAttribute(): ?string
    {
        return \App\Support\VietnamAddressResolver::wardName($this->permanent_ward_id);
    }

    public function getTemporaryWardNameAttribute(): ?string
    {
        return \App\Support\VietnamAddressResolver::wardName($this->temporary_ward_id);
    }

    public function getFullAddressPermanentAttribute(): string
    {
        return \App\Support\VietnamAddressResolver::formatAddressLine(
            $this->permanent_residence,
            $this->permanent_ward_id,
            $this->permanent_province
        );
    }

    public function getFullAddressTemporaryAttribute(): string
    {
        return \App\Support\VietnamAddressResolver::formatAddressLine(
            $this->temporary_residence,
            $this->temporary_ward_id,
            $this->temporary_province
        );
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
