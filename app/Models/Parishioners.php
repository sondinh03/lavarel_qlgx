<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Venturecraft\Revisionable\RevisionableTrait;

class Parishioners extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    protected $table = 'parishioners';
    protected $guarded = ['id'];

    protected $fillable = [
        'id',
        'last_name',
        'name',
        'pid',
        'deid',
        'did',
        'paid',
        'assid',
        'origin',
        'ward',
        'province',
        'residence',
        'resi_ward',
        'resi_province',
        'professional_level',
        'study',
        'new_convert',
        'married',
        'statistical',
        'note',
        'baptism_date',
        'baptism_number',
        'baptism_giver',
        'baptism_sponsor',
        'baptism_dioceses',
        'baptism_deanerys',
        'baptism_parish',
        'more_power_date',
        'more_power_number',
        'more_power_giver',
        'more_power_sponsor',
        'more_power_dioceses',
        'more_power_deanerys',
        'more_power_parish',
        'communion_date',
        'communion_number',
        'communion_giver',
        'communion_dioceses',
        'communion_deanerys',
        'communion_parish',
        'anoint_date',
        'anoint_status',
        'anoint_giver',
        'anoint_note',
        'die_status',
        'die_time',
        'die_lottery',
        'die_death',
        'die_burial',
        'phone',
        'email',
        'image',
        'father',
        'mother',
        'sex',
        'birthday',
        'cccd',
        'holy',
        'ethnic',
        'career',
        'level',
        'position',
        'language',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $dates = ['created_at', 'updated_at', 'birthday', 'baptism_date', 'more_power_date', 'communion_date', 'anoint_date', 'die_time'];

    protected $appends = ['chon_student', 'full_name', 'age'];

    protected $casts = [
        'sex' => 'integer',
        'married' => 'integer',
        'status' => 'integer',
        'birthday' => 'date',
        'baptism_date' => 'date',
        'more_power_date' => 'date',
        'communion_date' => 'date',
        'anoint_date' => 'date',
        'die_time' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Giáo xứ của giáo dân
     */
    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class, 'pid');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'parishioner_id');
    }


    /**
     * Học sinh liên kết với giáo dân
     * Giả sử bảng students có cột parishioner_id
     */
    // public function students(): HasMany
    // {
    //     return $this->hasMany(Student::class, 'parishioner_id');
    // }

    /**
     * Học sinh đang hoạt động
     */
    public function activeStudents(): HasMany
    {
        return $this->students()->where('status', 1);
    }

    /**
     * Thánh danh
     */
    public function holyName(): BelongsTo
    {
        return $this->belongsTo(HolyManagement::class, 'holy', 'id');
    }

    /**
     * Slug cho URL
     */
    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable', 'model');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Lọc theo giáo xứ
     */
    public function scopeOfParish(Builder $query, ?int $parishId): Builder
    {
        if ($parishId === null) {
            return $query;
        }

        return $query->where('pid', $parishId);
    }

    /**
     * Scope: Chỉ giáo dân đang hoạt động
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /**
     * Scope: Lọc theo giới tính
     */
    public function scopeBySex(Builder $query, int $sex): Builder
    {
        return $query->where('sex', $sex);
    }

    /**
     * Scope: Lọc theo tình trạng hôn nhân
     */
    public function scopeByMarriedStatus(Builder $query, int $married): Builder
    {
        return $query->where('married', $married);
    }

    /**
     * Scope: Lọc theo độ tuổi
     */
    public function scopeByAgeRange(Builder $query, int $minAge, int $maxAge = null): Builder
    {
        $now = now();

        if ($maxAge === null) {
            // Từ minAge trở lên
            $minDate = $now->copy()->subYears($minAge)->format('Y-m-d');
            return $query->where('birthday', '<=', $minDate);
        }

        // Khoảng tuổi
        $minDate = $now->copy()->subYears($maxAge)->format('Y-m-d');
        $maxDate = $now->copy()->subYears($minAge)->format('Y-m-d');

        return $query->whereBetween('birthday', [$minDate, $maxDate]);
    }

    /**
     * Scope: Tìm kiếm theo tên, CCCD, SĐT
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $searchTerm = '%' . trim($term) . '%';

        return $query->where(function ($q) use ($searchTerm) {
            $q->where('last_name', 'like', $searchTerm)
                ->orWhere('name', 'like', $searchTerm)
                ->orWhere('cccd', 'like', $searchTerm)
                ->orWhere('phone', 'like', $searchTerm)
                ->orWhere(DB::raw("CONCAT(last_name, ' ', name)"), 'like', $searchTerm);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor: Họ và tên đầy đủ
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->last_name . ' ' . $this->name);
    }

    /**
     * Accessor: Tên đầy đủ kèm thánh danh
     */
    public function getFullNameWithHolyAttribute(): string
    {
        $holy = $this->holyName;
        $holyName = $holy ? $holy->name . ' ' : '';

        return $holyName . $this->full_name;
    }

    /**
     * Accessor: Tuổi
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birthday) {
            return null;
        }

        return \Carbon\Carbon::parse($this->birthday)->age;
    }

    /**
     * Accessor: Tên giới tính
     */
    public function getSexNameAttribute(): string
    {
        return $this->sex == 1 ? 'Nam' : 'Nữ';
    }

    /**
     * Accessor: Tên trạng thái hôn nhân
     */
    public function getMarriedStatusAttribute(): string
    {
        return $this->married == 1 ? 'Đã kết hôn' : 'Độc thân';
    }

    /**
     * Accessor: Tên trạng thái
     */
    public function getStatusNameAttribute(): string
    {
        return $this->status ? 'Hoạt động' : 'Tắt';
    }

    /**
     * Accessor: Class CSS cho trạng thái
     */
    public function getStatusClassAttribute(): string
    {
        return $this->status
            ? 'bg-green-100 text-green-700'
            : 'bg-slate-200 text-slate-600';
    }

    /**
     * Accessor: Cho select học sinh (legacy support)
     */
    public function getChonStudentAttribute()
    {
        if (!$this->id) {
            return '';
        }

        $holy = DB::table('holymanagements')
            ->where('id', $this->holy)
            ->orderBy('id', 'ASC')
            ->first();

        $holyname = $holy->name ?? '';

        $age = $this->age ?? 0;

        return trim($holyname . ' ' . $this->last_name . ' ' . $this->name . ' - ' . $age . ' tuổi');
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Mutator: Tự động format số điện thoại
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    /**
     * Mutator: Tự động format CCCD
     */
    public function setCccdAttribute($value)
    {
        $this->attributes['cccd'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    /**
     * Mutator: Lowercase email
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value ? strtolower(trim($value)) : null;
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Kiểm tra có học sinh không
     */
    public function hasStudents(): bool
    {
        return $this->students()->exists();
    }

    /**
     * Kiểm tra có học sinh đang hoạt động không
     */
    public function hasActiveStudents(): bool
    {
        return $this->activeStudents()->exists();
    }

    /**
     * Lấy tổng số học sinh
     */
    public function getStudentsCount(): int
    {
        return $this->students()->count();
    }

    /**
     * Lấy nhóm tuổi
     */
    public function getAgeGroup(): string
    {
        $age = $this->age;

        if ($age === null) {
            return 'Không xác định';
        }

        if ($age <= 12) {
            return 'Thiếu nhi (0-12)';
        } elseif ($age <= 18) {
            return 'Thiếu niên (13-18)';
        } elseif ($age <= 35) {
            return 'Thanh niên (19-35)';
        } elseif ($age <= 60) {
            return 'Trung niên (36-60)';
        } else {
            return 'Cao niên (60+)';
        }
    }

    /**
     * Kiểm tra đã làm phép rửa tội chưa
     */
    public function isBaptized(): bool
    {
        return $this->baptism_date !== null;
    }

    /**
     * Kiểm tra đã thêm sức chưa
     */
    public function isConfirmed(): bool
    {
        return $this->more_power_date !== null;
    }

    /**
     * Kiểm tra đã rước lễ lần đầu chưa
     */
    public function hasFirstCommunion(): bool
    {
        return $this->communion_date !== null;
    }

    /**
     * Link mở trong trang chi tiết
     */
    public function openLink(): string
    {
        $slug = slug($this) . config('settings.url_prefix');

        return '<a target="_blank" href="' . url($slug) . '"><i class="las la-link"></i>Liên kết</a>';
    }

    /**
     * Lấy URL ảnh đại diện
     */
    public function getImageUrl(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        // Default avatar based on gender
        return $this->sex == 1
            ? asset('images/default-male-avatar.png')
            : asset('images/default-female-avatar.png');
    }
}
