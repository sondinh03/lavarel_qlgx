<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class AttendanceRecord extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    protected $table = 'attendance_records';
    protected $guarded = ['id'];

    protected $fillable = [
        'session_id',
        'student_id',
        'status',     // 1: có mặt, 2: có phép, 3: không
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * ======================
     * STATUS CONSTANTS
     * ======================
     */
    const STATUS_PRESENT      = 1; // Có mặt
    const STATUS_ABSENT_EXCUSED = 2; // Vắng có phép
    const STATUS_ABSENT_UNEXCUSED = 3; // Vắng không phép

    /**
     * ======================
     * RELATIONS
     * ======================
     */

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public static function isValidStatus($status): bool
    {
        return in_array($status, [
            self::STATUS_PRESENT,
            self::STATUS_ABSENT_EXCUSED,
            self::STATUS_ABSENT_UNEXCUSED,
        ]);
    }
}
