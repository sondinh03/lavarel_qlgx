<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceEditLog extends Model
{
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';

    protected $table = 'attendance_edit_logs';

    protected $fillable = [
        'batch_id',
        'parish_id',
        'session_id',
        'student_id',
        'attendance_record_id',
        'old_status',
        'new_status',
        'old_note',
        'new_note',
        'action',
        'user_id',
    ];

    protected $casts = [
        'parish_id'            => 'integer',
        'session_id'           => 'integer',
        'student_id'           => 'integer',
        'attendance_record_id' => 'integer',
        'old_status'           => 'integer',
        'new_status'           => 'integer',
        'user_id'              => 'integer',
    ];

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentNew::class, 'student_id');
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'Thêm',
            self::ACTION_UPDATED => 'Sửa',
            default              => $this->action,
        };
    }

    public static function statusLabel(?int $status): string
    {
        return match ($status) {
            AttendanceRecord::STATUS_PRESENT          => 'Có mặt',
            AttendanceRecord::STATUS_ABSENT_EXCUSED   => 'Vắng CP',
            AttendanceRecord::STATUS_ABSENT_UNEXCUSED => 'Vắng KP',
            default                                   => '—',
        };
    }
}
