<?php

namespace App\Models;

use App\Traits\HasFormattedName;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Carbon\Carbon;

class Student extends Model
{
    use CrudTrait;
    use HasFormattedName;

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
        'communion_date',
        'anoint_date',
        'promise_day',
        'die_time',
        'created_at',
        'updated_at',
    ];

    // protected $appends = [
    //     'parish_children_name',
    //     'holy_name',
    //     'full_name',
    //     'sex_label',
    //     'status_label',
    // ];

    protected $append = [];

    protected $casts = [
        'sex' => 'integer',
        'status' => 'integer',
        'holy' => 'integer',
        'baptism_date' => 'date',
        'more_power_date' => 'date',
        'communion_date' => 'date',
        'anoint_date' => 'date',
        'promise_day' => 'date',
        'die_time' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | CONSTANTS
    |--------------------------------------------------------------------------
    */

    const SEX_MALE = 1;
    const SEX_FEMALE = 0;

    const STATUS_STUDYING    = 1; // Hồ sơ đang hoạt động (đang theo học giáo lý)
    const STATUS_GRADUATED   = 2; // Hồ sơ đã hoàn tất chương trình giáo lý
    const STATUS_TRANSFERRED = 3; // Hồ sơ đã chuyển sang xứ/lớp khác
    const STATUS_DROPPED     = 4; // Hồ sơ ngưng theo học / bỏ học

    const HOLY_BAPTISM = 1;
    const HOLY_CONFIRMATION = 2;
    const HOLY_MARRIAGE = 3;

    const ANOINT_STATUS_CRITICAL = 1; // Nguy tử
    const ANOINT_STATUS_NORMAL = 2;   // Thông thường

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
        return $this->belongsTo(ParishManagement::class, 'pid');
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
     * Bậc thánh
     */
    public function holyRelation(): BelongsTo
    {
        return $this->belongsTo(Holymanagement::class, 'holy');
    }

    /**
     * Giáo họ (Parish children)
     */
    public function paidRelation(): BelongsTo
    {
        return $this->belongsTo(Parish::class, 'paid');
    }

    /**
     * Dân tộc
     */
    public function ethnicRelation(): BelongsTo
    {
        return $this->belongsTo(Ethnicmanagement::class, 'ethnic');
    }

    /**
     * Nghề nghiệp
     */
    public function careerRelation(): BelongsTo
    {
        return $this->belongsTo(Careermanagement::class, 'career');
    }

    /**
     * Trình độ
     */
    public function levelRelation(): BelongsTo
    {
        return $this->belongsTo(Levelmanagement::class, 'level');
    }

    /**
     * Chức vụ
     */
    public function positionRelation(): BelongsTo
    {
        return $this->belongsTo(Positionmanagement::class, 'position');
    }

    /**
     * Ngôn ngữ
     */
    public function languageRelation(): BelongsTo
    {
        return $this->belongsTo(Languagemanagement::class, 'language');
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
        return $this->belongsTo(ParishManagement::class, 'baptism_parish');
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRMATION (MORE POWER) RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Cha/Đức cha ban phép thêm sức
     */
    public function morePowerGiver(): BelongsTo
    {
        return $this->belongsTo(Priest::class, 'more_power_giver');
    }

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
        return $this->belongsTo(ParishManagement::class, 'more_power_parish');
    }

    /*
    |--------------------------------------------------------------------------
    | COMMUNION RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Cha ban bí tích rước lễ
     */
    public function communionGiver(): BelongsTo
    {
        return $this->belongsTo(Priest::class, 'communion_giver');
    }

    /**
     * Giáo phận nơi rước lễ
     */
    public function communionDiocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'communion_dioceses');
    }

    /**
     * Giáo hạt nơi rước lễ
     */
    public function communionDeanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'communion_deanerys');
    }

    /**
     * Giáo xứ nơi rước lễ
     */
    public function communionParish(): BelongsTo
    {
        return $this->belongsTo(Parish::class, 'communion_parish');
    }

    /*
    |--------------------------------------------------------------------------
    | ANOINT RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Cha ban bí tích xức dầu
     */
    public function anointGiver(): BelongsTo
    {
        return $this->belongsTo(Priest::class, 'anoint_giver');
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

    public function students()
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
        // return $this->holyRelation ? $this->holyRelation->name : null;
        return $this->relationLoaded('holyRelation')
            ? $this->holyRelation?->name
            : null;
    }

    protected function getParishChildrenNameAttribute()
    {
        return $this->paidRelation ? $this->paidRelation->name : null;
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
        // return $value
        //     ? Carbon::parse($value)->format('d/m/Y')
        //     : '-';
        return $value ?: null;
    }

    public function getBirthdayTextAttribute()
    {
        if (!$this->birthday) return '-';

        return Carbon::parse($this->birthday)->format('d/m/Y');
    }


    // Khi set vào DB
    public function setBirthdayAttribute($value)
    {
        if (!$value) {
            $this->attributes['birthday'] = null;
            return;
        }

        $this->attributes['birthday'] = Carbon::parse($value)
            ->format('Y-m-d');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Students by parish
     */
    public function scopeOfParish($query, $parishId)
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
                ->orWhere('mahv', 'like', "%{$search}%")
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
            // Birthday is already formatted as 'd/m/Y' by accessor
            // Need to parse it back to calculate age
            $birthDate = Carbon::createFromFormat('d/m/Y', $this->birthday);
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

    /**
     * Get anoint status label
     */
    public function getAnointStatusLabel(): string
    {
        return match ($this->anoint_status) {
            self::ANOINT_STATUS_CRITICAL => 'Nguy tử',
            self::ANOINT_STATUS_NORMAL => 'Thông thường',
            default => '',
        };
    }

    /**
     * Get study status label
     */
    public function getStudyLabel(): string
    {
        return match ($this->study) {
            1 => 'Đang học',
            2 => 'Đã học xong',
            3 => 'Nghỉ học',
            default => '',
        };
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

    /**
     * Get anoint status options for select
     */
    public static function getAnointStatusOptions(): array
    {
        return [
            self::ANOINT_STATUS_CRITICAL => 'Nguy tử',
            self::ANOINT_STATUS_NORMAL => 'Thông thường',
        ];
    }
}
