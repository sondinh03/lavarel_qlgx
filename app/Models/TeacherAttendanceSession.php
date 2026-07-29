<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAttendanceSession extends Model
{
    protected $table = 'teacher_attendance_sessions';

    protected $fillable = [
        'parish_id',
        'namhoc_id',
        'date',
        'type',
        'status',
        'start_time',
        'end_time',
        'note',
    ];

    protected $casts = [
        'date'       => 'date',
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
        'type'       => 'integer',
        'status'     => 'integer',
    ];

    public const STATUS_OPENING = 1;
    public const STATUS_CLOSED = 2;
    public const STATUS_CANCELLED = 3;

    public const TYPE_TEACH = 1;
    public const TYPE_CEREMONY = 2;
    public const TYPE_MEETING = 3;

    public function parish()
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(NamHoc::class, 'namhoc_id');
    }

    public function records()
    {
        return $this->hasMany(TeacherAttendanceRecord::class, 'session_id');
    }

    public function isEditable(): bool
    {
        return (int) $this->status === self::STATUS_OPENING;
    }

    public function close(): bool
    {
        if ((int) $this->status !== self::STATUS_OPENING) {
            return false;
        }

        return $this->update(['status' => self::STATUS_CLOSED]);
    }

    public function reopen(): bool
    {
        if ((int) $this->status !== self::STATUS_CLOSED) {
            return false;
        }

        return $this->update(['status' => self::STATUS_OPENING]);
    }

    public function cancel(): bool
    {
        if (! in_array((int) $this->status, [self::STATUS_OPENING, self::STATUS_CLOSED], true)) {
            return false;
        }

        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    public function restore(): bool
    {
        if ((int) $this->status !== self::STATUS_CANCELLED) {
            return false;
        }

        return $this->update(['status' => self::STATUS_OPENING]);
    }

    public static function typeLabel(int $type): string
    {
        return match ($type) {
            self::TYPE_TEACH => 'Đi dạy',
            self::TYPE_CEREMONY => 'Đi lễ',
            self::TYPE_MEETING => 'Họp',
            default => 'Khác',
        };
    }

    public static function statusLabel(int $status): string
    {
        return AttendanceSession::statusLabel($status);
    }

    public static function statusClass(int $status): string
    {
        return AttendanceSession::statusClass($status);
    }

    public function getStatistics(): array
    {
        $records = $this->relationLoaded('records') ? $this->records : $this->records()->get();
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

        $present         = $records->where('status', TeacherAttendanceRecord::STATUS_PRESENT)->count();
        $absentExcused   = $records->where('status', TeacherAttendanceRecord::STATUS_ABSENT_EXCUSED)->count();
        $absentUnexcused = $records->where('status', TeacherAttendanceRecord::STATUS_ABSENT_UNEXCUSED)->count();
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
