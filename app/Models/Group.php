<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Group
 *
 * @property int $id
 * @property int $parish_id
 * @property string $name
 * @property int $type         1=nhóm GLV, 2=ca đoàn thiếu nhi, 3=ca đoàn người lớn, 4=khác
 * @property string $member_type  'teacher' | 'student'
 * @property bool $is_active
 * @property string|null $note
 */
class Group extends Model
{
    protected $fillable = [
        'parish_id',
        'name',
        'type',
        'member_type',
        'is_active',
        'note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ==================== CONSTANTS ====================

    const TYPE_TEACHER_GROUP = 1;
    const TYPE_CHOIR_YOUTH   = 2;
    const TYPE_CHOIR_ADULT   = 3;
    const TYPE_OTHER         = 4;

    const MEMBER_TYPE_TEACHER = 'teacher';
    const MEMBER_TYPE_STUDENT = 'student';

    const TYPE_LABELS = [
        self::TYPE_TEACHER_GROUP => 'Nhóm Giáo lý viên',
        self::TYPE_CHOIR_YOUTH   => 'Ca đoàn thiếu nhi',
        self::TYPE_CHOIR_ADULT   => 'Ca đoàn người lớn',
        self::TYPE_OTHER         => 'Khác',
    ];

    // ==================== RELATIONSHIPS ====================

    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function activeMembers()
    {
        return $this->hasMany(GroupMember::class)->where('is_active', true);
    }

    public function sessions()
    {
        return $this->hasMany(GroupSession::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForParish(Builder $query, int $parishId): Builder
    {
        return $query->where('parish_id', $parishId);
    }

    public function scopeOfType(Builder $query, int $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeTeacherGroups(Builder $query): Builder
    {
        return $query->where('member_type', self::MEMBER_TYPE_TEACHER);
    }

    public function scopeStudentGroups(Builder $query): Builder
    {
        return $query->where('member_type', self::MEMBER_TYPE_STUDENT);
    }

    // ==================== ACCESSORS ====================

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? 'Không xác định';
    }

    public function getIsTeacherGroupAttribute(): bool
    {
        return $this->member_type === self::MEMBER_TYPE_TEACHER;
    }

    public function getIsStudentGroupAttribute(): bool
    {
        return $this->member_type === self::MEMBER_TYPE_STUDENT;
    }
}