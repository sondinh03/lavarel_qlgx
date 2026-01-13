<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class AttendanceSession extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    protected $table = 'attendance_sessions';
    protected $guarded = ['id'];

    protected $fillable = [
        'parish_id',
        'class_id',
        'teacher_id',
        'date',
        'type',          // 1: học, 2: lễ
        'title',
        'status',        // 1: chờ xử lý, 2: đang hoạt động, 3: đã đóng, 4: vô hiệu hóa, 5: đã hủy
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time'   => 'datetime:H:i',
        'end_time'     => 'datetime:H:i',
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
    const TYPE_CLASS = 1; // Buổi học giáo lý
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
     * ======================
     *  RELATIONS
     * ======================
     */
    public function class()
    {
        return $this->belongsTo(Lop::class, 'class_id');
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class, 'session_id');
    }

    public function getCheckedCountAttribute()
    {
        return $this->records()->whereNotNull('status')->count();
    }

    /**
     * Tỷ lệ có mặt (%)
     */
    public function getAttendanceRateAttribute(): float
    {
        $total = $this->total_students;
        if ($total === 0) return 0;

        $present = $this->records()
            ->where('status', AttendanceRecord::STATUS_PRESENT)
            ->count();

        return round(($present / $total) * 100, 1);
    }

    /**
     * Có thể chỉnh sửa không?
     */
    public function getIsEditableAttribute(): bool
    {
        return $this->status === self::STATUS_OPENING;
    }

    /**
     * ✅ Get full date display
     */
    public function getFullDateAttribute(): string
    {
        return $this->date->format('d/m');
    }

    /**
     * ✅ Get Vietnamese day name
     */
    public function getDayNameAttribute(): string
    {
        $days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        return $days[$this->date->dayOfWeek];
    }

    /**
     * Đóng phiên điểm danh
     */
    public function close(): bool
    {
        if ($this->status !== self::STATUS_OPENING) {
            return false;
        }

        return $this->update(['status' => self::STATUS_CLOSED]);
    }

    /**
     * Mở lại phiên điểm danh
     */
    public function reopen(): bool
    {
        if ($this->status !== self::STATUS_CLOSED) {
            return false;
        }

        return $this->update(['status' => self::STATUS_OPENING]);
    }

    /**
     * Hủy phiên điểm danh
     */
    public function cancel(): bool
    {
        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Kiểm tra có phải buổi học không
     */
    public function isClass(): bool
    {
        return $this->type === self::TYPE_CLASS;
    }

    /**
     * Kiểm tra có phải buổi lễ không
     */
    public function isCeremony(): bool
    {
        return $this->type === self::TYPE_CEREMONY;
    }

    /**
     * Thống kê điểm danh
     */
    public function getStatistics(): array
    {
        $records = $this->records;
        $total = $records->count();

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

        $present = $records->where('status', AttendanceRecord::STATUS_PRESENT)->count();
        $absentExcused = $records->where('status', AttendanceRecord::STATUS_ABSENT_EXCUSED)->count();
        $absentUnexcused = $records->where('status', AttendanceRecord::STATUS_ABSENT_UNEXCUSED)->count();
        $notChecked = $records->whereNull('status')->count();

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
