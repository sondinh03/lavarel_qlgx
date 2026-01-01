<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Student extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'student';
    protected $guarded = ['id'];

    protected $dates = [
        'baptism_date',
        'more_power_date',
        'promise_day',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'holy_name',
        'full_name',
        'sex_label',
        'status_label',
    ];

    protected $casts = [
        'sex' => 'integer',
        'status' => 'integer',
        'holy' => 'integer',
        'baptism_date' => 'date',
        'more_power_date' => 'date',
        'promise_day' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | CONSTANTS
    |--------------------------------------------------------------------------
    */

    const SEX_MALE = 1;
    const SEX_FEMALE = 2;

    const STATUS_STUDYING = 1;
    const STATUS_GRADUATED = 2;
    const STATUS_TRANSFERRED = 3;
    const STATUS_DROPPED = 4;

    const HOLY_BAPTISM = 1;
    const HOLY_CONFIRMATION = 2;
    const HOLY_MARRIAGE = 3;

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Giáo xứ hiện tại
     */
    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class, 'pid');
    }

    /**
     * Giáo phận
     */
    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'did');
    }

    /**
     * Giáo hạt
     */
    public function deanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'deid');
    }

    /**
     * Lớp học (class)
     */
    public function lop()
    {
        return $this->belongsToMany(
            Lop::class,
            'student_class',
            'student_id',
            'class_id'
        )->withPivot('status');
    }

    /**
     * Lớp giáo lý
     */
    // public function giaolyClass(): BelongsTo
    // {
    //     return $this->belongsTo(GiaolyClass::class, 'magdcg');
    // }

    /**
     * Bậc thánh
     */
    public function holyRelation(): BelongsTo
    {
        return $this->belongsTo(Holymanagement::class, 'holy');
    }

    /**
     * Giáo xứ thanh toán
     */
    public function paidRelation(): BelongsTo
    {
        return $this->belongsTo(Parish::class, 'paid');
    }

    /*
    |--------------------------------------------------------------------------
    | BAPTISM RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Cha rửa tội
     */
    public function baptismGiver(): BelongsTo
    {
        return $this->belongsTo(Priest::class, 'baptism_giver');
    }

    /**
     * Cha/Mẹ đỡ đầu rửa tội
     */
    public function baptismSponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class, 'baptism_sponsor');
    }

    /**
     * Giáo phận nơi rửa tội
     */
    public function baptismDiocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'baptism_dioceses');
    }

    /**
     * Giáo hạt nơi rửa tội
     */
    public function baptismDeanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'baptism_deanerys');
    }

    /**
     * Giáo xứ nơi rửa tội
     */
    public function baptismParish(): BelongsTo
    {
        return $this->belongsTo(Parish::class, 'baptism_parish');
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRMATION (MORE POWER) RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Đức cha ban phép thêm sức
     */
    // public function morePowerGiver(): BelongsTo
    // {
    //     return $this->belongsTo(Bishop::class, 'more_power_giver');
    // }

    /**
     * Cha/Mẹ đỡ đầu thêm sức
     */
    public function morePowerSponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class, 'more_power_sponsor');
    }

    /**
     * Giáo phận nơi thêm sức
     */
    public function morePowerDiocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'more_power_dioceses');
    }

    /**
     * Giáo hạt nơi thêm sức
     */
    public function morePowerDeanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'more_power_deanerys');
    }

    /**
     * Giáo xứ nơi thêm sức
     */
    public function morePowerParish(): BelongsTo
    {
        return $this->belongsTo(Parish::class, 'more_power_parish');
    }

    /*
    |--------------------------------------------------------------------------
    | MANY-TO-MANY RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Student targets (MorphToMany)
     */
    public function lopTargets(): MorphToMany
    {
        return $this->morphToMany(StudentTarget::class, 'target', 'student_target');
    }

    /**
     * Classes (Many-to-Many)
     */
    public function lops(): BelongsToMany
    {
        return $this->belongsToMany(Lop::class, 'students_class', 'student_id', 'class_id')
            ->using(StudentsClass::class)
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Slug relationship
     */
    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable', 'model');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS & MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Get full name
     */
    protected function getFullNameAttribute()
    {
        return trim(($this->last_name ?? '') . ' ' . ($this->name ?? ''));
    }

    /**
     * Get holy name from relationship
     */
    protected function getHolyNameAttribute()
    {
        return $this->holyRelation ? $this->holyRelation->name : null;
    }

    /**
     * Get sex label
     */
    protected function getSexLabelAttribute()
    {
        return match ($this->sex) {
            self::SEX_MALE => 'Nam',
            self::SEX_FEMALE => 'Nữ',
            default => 'Chưa xác định',
        };
    }

    /**
     * Get status label
     */
    protected function getStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_STUDYING => 'Đang học',
            self::STATUS_GRADUATED => 'Đã tốt nghiệp',
            self::STATUS_TRANSFERRED => 'Đã chuyển đi',
            self::STATUS_DROPPED => 'Đã nghỉ học',
            default => 'Không xác định',
        };
    }

    /**
     * Format birthday for display
     * Note: Keep the old getBirthdayAttribute for backward compatibility
     */
    public function getBirthdayAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : '-';
    }

    /**
     * Format baptism date for display
     */
    // protected function baptismDateFormatted(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn() => $this->baptism_date ? $this->baptism_date->format('d/m/Y') : null
    //     );
    // }

    // /**
    //  * Format more power date for display
    //  */
    // protected function morePowerDateFormatted(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn() => $this->more_power_date ? $this->more_power_date->format('d/m/Y') : null
    //     );
    // }

    // /**
    //  * Format promise day for display
    //  */
    // protected function promiseDayFormatted(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn() => $this->promise_day ? $this->promise_day->format('d/m/Y') : null
    //     );
    // }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Students by parish
     */
    public function scopeByParish($query, $parishId)
    {
        return $query->where('pid', $parishId);
    }

    /**
     * Scope: Active students (studying)
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_STUDYING);
    }

    /**
     * Scope: By status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: By sex
     */
    public function scopeBySex($query, $sex)
    {
        return $query->where('sex', $sex);
    }

    /**
     * Scope: By class
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('lop', $classId);
    }

    /**
     * Scope: Search by name
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('mah', 'like', "%{$search}%")
                ->orWhereRaw("CONCAT(last_name, ' ', name) like ?", ["%{$search}%"]);
        });
    }

    /**
     * Scope: Has baptism
     */
    public function scopeHasBaptism($query)
    {
        return $query->whereNotNull('baptism_date');
    }

    /**
     * Scope: Has confirmation
     */
    public function scopeHasConfirmation($query)
    {
        return $query->whereNotNull('more_power_date');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Check if student is male
     */
    public function isMale(): bool
    {
        return $this->sex === self::SEX_MALE;
    }

    /**
     * Check if student is female
     */
    public function isFemale(): bool
    {
        return $this->sex === self::SEX_FEMALE;
    }

    /**
     * Check if student is studying
     */
    public function isStudying(): bool
    {
        return $this->status === self::STATUS_STUDYING;
    }

    /**
     * Check if student has been baptized
     */
    public function hasBaptism(): bool
    {
        return !is_null($this->baptism_date);
    }

    /**
     * Check if student has received confirmation
     */
    public function hasConfirmation(): bool
    {
        return !is_null($this->more_power_date);
    }

    /**
     * Get age from birthday
     */
    public function getAge(): ?int
    {
        if (!$this->birthday) {
            return null;
        }

        try {
            // Birthday is already formatted as 'd-m-Y' by accessor
            // Need to parse it back to calculate age
            $birthDate = Carbon::createFromFormat('d-m-Y', $this->birthday);
            return $birthDate->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_STUDYING => 'bg-green-100 text-green-800',
            self::STATUS_GRADUATED => 'bg-blue-100 text-blue-800',
            self::STATUS_TRANSFERRED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_DROPPED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get open link for Backpack CRUD
     */
    public function openLink(): string
    {
        $slug = slug($this) . config('settings.url_prefix', '');
        return '<a target="_blank" href="' . url($slug) . '"><i class="las la-link"></i>Liên kết</a>';
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get sex options for select
     */
    public static function getSexOptions(): array
    {
        return [
            self::SEX_MALE => 'Nam',
            self::SEX_FEMALE => 'Nữ',
        ];
    }

    /**
     * Get status options for select
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_STUDYING => 'Đang học',
            self::STATUS_GRADUATED => 'Đã tốt nghiệp',
            self::STATUS_TRANSFERRED => 'Đã chuyển đi',
            self::STATUS_DROPPED => 'Đã nghỉ học',
        ];
    }

    /**
     * Get holy options for select
     */
    public static function getHolyOptions(): array
    {
        return [
            self::HOLY_BAPTISM => 'Rửa tội',
            self::HOLY_CONFIRMATION => 'Thêm sức',
            self::HOLY_MARRIAGE => 'Hôn phối',
        ];
    }
}
