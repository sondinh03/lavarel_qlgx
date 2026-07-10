<?php

namespace App\Models;

use App\Traits\BelongsToParish;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class AttendanceSession extends Model
{
    use CrudTrait;
    use RevisionableTrait;
    use BelongsToParish;
    use HasFactory;

    protected $table = 'attendance_sessions';
    protected $guarded = ['id'];

    protected $fillable = [
        'class_id',
        'date',
        'semester',
        'type',      // 1: học, 2: lễ
        'status',    // 1: đang mở, 2: đã đóng, 3: đã hủy
        'start_time',
        'end_time',
        'note',
    ];

    protected $casts = [
        'date'       => 'date',
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
    ];

    protected $appends = [
        'full_date',
        'day_name',
        'is_editable',
        'checked_count',
        'attendance_rate',
    ];

    /**
     * ======================
     *  STATUS CONSTANTS
     * ======================
     */
    const STATUS_OPENING   = 1; // Đang cho phép điểm danh
    const STATUS_CLOSED    = 2; // Đã khóa, không cho chỉnh sửa
    const STATUS_CANCELLED = 3; // Buổi học bị hủy

    /**
     * ======================
     *  SESSION TYPE CONSTANTS
     * ======================
     */
    const TYPE_CLASS    = 1; // Buổi học giáo lý
    const TYPE_CEREMONY = 2; // Thánh lễ

    /**
     * ======================
     *  SCOPES
     * ======================
     */
    public function scopeOfClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Tìm theo ngày (cột date). Hỗ trợ gõ một phần: 12, 12/03, 12/03/2026 hoặc 2026-03-12.
     */
    public function scopeSearchByDate($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $like = '%' . addcslashes($term, '%_\\') . '%';

        return $query->where(function ($q) use ($like) {
            $q->whereRaw("DATE_FORMAT(date, '%d/%m/%Y') LIKE ?", [$like])
                ->orWhereRaw("DATE_FORMAT(date, '%Y-%m-%d') LIKE ?", [$like]);
        });
    }

    /**
     * ======================
     *  RELATIONS
     * ======================
     */
    public function catechismClass()
    {
        return $this->belongsTo(CatechismClass::class, 'class_id');
    }

    /** @deprecated Use catechismClass() */
    public function class()
    {
        return $this->catechismClass();
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class, 'session_id');
    }

    /**
     * ======================
     *  ACCESSORS
     * ======================
     */
    public function getCheckedCountAttribute(): int
    {
        return $this->records()->whereNotNull('status')->count();
    }

    public function getAttendanceRateAttribute(): float
    {
        $total = $this->records()->count();
        if ($total === 0) return 0;

        $present = $this->records()
            ->where('status', AttendanceRecord::STATUS_PRESENT)
            ->count();

        return round(($present / $total) * 100, 1);
    }

    public function getIsEditableAttribute(): bool
    {
        return $this->status === self::STATUS_OPENING;
    }

    public function getFullDateAttribute(): string
    {
        return $this->date->format('d/m/Y');
    }

    public function getDayNameAttribute(): string
    {
        $days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        return $days[$this->date->dayOfWeek];
    }

    /**
     * ======================
     *  METHODS
     * ======================
     */
    public function canEdit(): array
    {
        if ($this->status === self::STATUS_CLOSED) {
            return ['can' => false, 'reason' => 'Buổi học đã khóa, không thể chỉnh sửa'];
        }

        if ($this->status === self::STATUS_CANCELLED) {
            return ['can' => false, 'reason' => 'Buổi học đã bị hủy'];
        }

        return ['can' => true, 'reason' => null];
    }

    public function close(): bool
    {
        if ($this->status !== self::STATUS_OPENING) {
            return false;
        }

        return $this->update(['status' => self::STATUS_CLOSED]);
    }

    public function reopen(): bool
    {
        if ($this->status !== self::STATUS_CLOSED) {
            return false;
        }

        return $this->update(['status' => self::STATUS_OPENING]);
    }

    public function cancel(): bool
    {
        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    public function isClass(): bool
    {
        return $this->type === self::TYPE_CLASS;
    }

    public function isCeremony(): bool
    {
        return $this->type === self::TYPE_CEREMONY;
    }

    public function getStatistics(): array
    {
        $records = $this->records;
        $total   = $records->count();

        if ($total === 0) {
            return [
                'total'            => 0,
                'present'          => 0,
                'absent_excused'   => 0,
                'absent_unexcused' => 0,
                'not_checked'      => 0,
                'present_rate'     => 0,
            ];
        }

        $present         = $records->where('status', AttendanceRecord::STATUS_PRESENT)->count();
        $absentExcused   = $records->where('status', AttendanceRecord::STATUS_ABSENT_EXCUSED)->count();
        $absentUnexcused = $records->where('status', AttendanceRecord::STATUS_ABSENT_UNEXCUSED)->count();
        $notChecked      = $records->whereNull('status')->count();

        return [
            'total'            => $total,
            'present'          => $present,
            'absent_excused'   => $absentExcused,
            'absent_unexcused' => $absentUnexcused,
            'not_checked'      => $notChecked,
            'present_rate'     => round(($present / $total) * 100, 1),
        ];
    }
}
