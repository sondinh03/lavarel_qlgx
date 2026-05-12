<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\GroupMember
 *
 * @property int $id
 * @property int $group_id
 * @property string $memberable_type   'teacher' | 'student'
 * @property int $memberable_id
 * @property string|null $role
 * @property \Carbon\Carbon|null $joined_at
 * @property \Carbon\Carbon|null $left_at
 * @property bool $is_active
 */
class GroupMember extends Model
{
    protected $fillable = [
        'group_id',
        'memberable_type',
        'memberable_id',
        'role',
        'joined_at',
        'left_at',
        'is_active',
    ];

    protected $casts = [
        'joined_at' => 'date',
        'left_at'   => 'date',
        'is_active' => 'boolean',
    ];

    // ==================== MORPH MAP ====================
    // Khai báo trong AppServiceProvider::boot():
    // Relation::morphMap([
    //     'teacher' => Teacher::class,
    //     'student' => Student::class,
    // ]);

    // ==================== RELATIONSHIPS ====================

    /**
     * Resolve về Teacher hoặc Student tùy memberable_type
     */
    public function memberable()
    {
        return $this->morphTo(__FUNCTION__, 'memberable_type', 'memberable_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(GroupAttendanceRecord::class, 'member_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }

    // ==================== ACCESSORS ====================

    /**
     * Tên hiển thị — tự lấy từ Teacher hoặc Student
     * Cần eager load: ->with('memberable')
     */
    public function getDisplayNameAttribute(): string
    {
        $m = $this->memberable;

        if (!$m) return 'Không xác định';

        // Teacher có full_name accessor
        // Student có full_name accessor
        return $m->full_name ?? ($m->last_name . ' ' . $m->first_name);
    }

    public function getRoleDisplayAttribute(): string
    {
        return $this->role ?? 'Thành viên';
    }

    // ==================== HELPERS ====================

    public function isTeacher(): bool
    {
        return $this->memberable_type === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->memberable_type === 'student';
    }

    /**
     * Lấy trạng thái điểm danh trong 1 buổi cụ thể
     */
    public function getStatusInSession(int $sessionId): ?int
    {
        return $this->attendanceRecords()
            ->where('session_id', $sessionId)
            ->value('status');
    }
}