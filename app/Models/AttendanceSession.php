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
        'session_date',
        'type',          // 1: học, 2: lễ
        'title',
        'status',        // 1: chờ xử lý, 2: đang hoạt động, 3: đã đóng, 4: vô hiệu hóa, 5: đã hủy
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'session_date' => 'date',
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
     *  RELATIONS
     * ======================
     */
    public function class() {
        return $this->belongsTo(Lop::class, 'class_id');
    }

    public function records() {
        return $this->hasMany(AttendanceRecord::class, 'session_id');
    }

    public function getCheckedCountAttribute() {
        return $this->records()->whereNotNull('status')->count();
    }
}
