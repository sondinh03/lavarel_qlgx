<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\GroupAttendanceRecord
 *
 * @property int $id
 * @property int $session_id
 * @property int $member_id    FK → group_members.id
 * @property int $status       1=có mặt, 2=vắng có phép, 3=vắng không phép, 4=đi trễ
 * @property string|null $note
 */
class GroupAttendanceRecord extends Model
{
    protected $fillable = [
        'session_id',
        'member_id',
        'status',
        'note',
        'created_by',
        'updated_by',
    ];

    // ==================== CONSTANTS ====================

    const STATUS_PRESENT  = 1;
    const STATUS_EXCUSED  = 2;
    const STATUS_ABSENT   = 3;
    const STATUS_LATE     = 4;

    const STATUS_LABELS = [
        self::STATUS_PRESENT => 'Có mặt',
        self::STATUS_EXCUSED => 'Vắng có phép',
        self::STATUS_ABSENT  => 'Vắng không phép',
        self::STATUS_LATE    => 'Đi trễ',
    ];

    const STATUS_COLORS = [
        self::STATUS_PRESENT => 'green',
        self::STATUS_EXCUSED => 'yellow',
        self::STATUS_ABSENT  => 'red',
        self::STATUS_LATE    => 'orange',
    ];

    // ==================== RELATIONSHIPS ====================

    public function session()
    {
        return $this->belongsTo(GroupSession::class, 'session_id');
    }

    public function member()
    {
        return $this->belongsTo(GroupMember::class, 'member_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==================== SCOPES ====================

    public function scopeForSession(Builder $query, int $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForMember(Builder $query, int $memberId): Builder
    {
        return $query->where('member_id', $memberId);
    }

    public function scopePresent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PRESENT);
    }

    public function scopeAbsent(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ABSENT, self::STATUS_EXCUSED]);
    }

    // ==================== ACCESSORS ====================

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? 'Không xác định';
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getIsAbsentAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_ABSENT, self::STATUS_EXCUSED]);
    }

    public function getIsPresentAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_PRESENT, self::STATUS_LATE]);
    }
}