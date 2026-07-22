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

    public static function typeLabel(int $type): string
    {
        return match ($type) {
            self::TYPE_TEACH => 'Đi dạy',
            self::TYPE_CEREMONY => 'Đi lễ',
            self::TYPE_MEETING => 'Họp',
            default => 'Khác',
        };
    }
}
