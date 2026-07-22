<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAttendanceRecord extends Model
{
    protected $table = 'teacher_attendance_records';

    protected $fillable = [
        'session_id',
        'teacher_id',
        'status',
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public const STATUS_PRESENT = 1;
    public const STATUS_ABSENT_EXCUSED = 2;
    public const STATUS_ABSENT_UNEXCUSED = 3;

    public function session()
    {
        return $this->belongsTo(TeacherAttendanceSession::class, 'session_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public static function isValidStatus($status): bool
    {
        return in_array((int) $status, [
            self::STATUS_PRESENT,
            self::STATUS_ABSENT_EXCUSED,
            self::STATUS_ABSENT_UNEXCUSED,
        ], true);
    }
}
