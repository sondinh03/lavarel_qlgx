<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\GroupSession
 *
 * @property int $id
 * @property int $group_id
 * @property int $parish_id
 * @property \Carbon\Carbon $date
 * @property int $shift      1=sáng, 2=chiều, 3=tối
 * @property int $type       GLV: 1=dạy, 2=họp, 3=sinh hoạt | Ca đoàn: 1=tập, 2=lễ, 3=biểu diễn
 * @property string|null $title
 * @property string|null $start_time
 * @property string|null $end_time
 * @property string|null $note
 */
class GroupSession extends Model
{
    protected $fillable = [
        'group_id',
        'parish_id',
        'date',
        'shift',
        'type',
        'title',
        'start_time',
        'end_time',
        'note',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ==================== CONSTANTS ====================

    const SHIFT_MORNING   = 1;
    const SHIFT_AFTERNOON = 2;
    const SHIFT_EVENING   = 3;

    const SHIFT_LABELS = [
        self::SHIFT_MORNING   => 'Ca sáng',
        self::SHIFT_AFTERNOON => 'Ca chiều',
        self::SHIFT_EVENING   => 'Ca tối',
    ];

    // Type labels tùy theo group type — dùng static helper bên dưới
    const TYPE_LABELS_TEACHER = [
        1 => 'Dạy giáo lý',
        2 => 'Họp nhóm',
        3 => 'Sinh hoạt',
    ];

    const TYPE_LABELS_CHOIR = [
        1 => 'Tập hát',
        2 => 'Thánh lễ',
        3 => 'Biểu diễn',
    ];

    // ==================== RELATIONSHIPS ====================

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function parish()
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(GroupAttendanceRecord::class, 'session_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== SCOPES ====================

    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeForParish(Builder $query, int $parishId): Builder
    {
        return $query->where('parish_id', $parishId);
    }

    /**
     * Lọc theo date range — dùng cho báo cáo theo nam_hoc
     * Không cần lưu nam_hoc_id trong bảng
     *
     * Ví dụ:
     * $namHoc = NamHoc::find($id);
     * GroupSession::inDateRanges([
     *     [$namHoc->start_date_one, $namHoc->end_date_one],
     *     [$namHoc->start_date_two, $namHoc->end_date_two],
     * ])->get();
     */
    public function scopeInDateRanges(Builder $query, array $ranges): Builder
    {
        return $query->where(function ($q) use ($ranges) {
            foreach ($ranges as [$start, $end]) {
                if ($start && $end) {
                    $q->orWhereBetween('date', [$start, $end]);
                }
            }
        });
    }

    public function scopeInMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    // ==================== ACCESSORS ====================

    public function getShiftLabelAttribute(): string
    {
        return self::SHIFT_LABELS[$this->shift] ?? 'Ca ' . $this->shift;
    }

    public function getTypeLabelAttribute(): string
    {
        // Cần load group để biết dùng label nào
        $memberType = $this->group?->member_type;

        $labels = $memberType === 'teacher'
            ? self::TYPE_LABELS_TEACHER
            : self::TYPE_LABELS_CHOIR;

        return $labels[$this->type] ?? 'Khác';
    }

    public function getTimeRangeAttribute(): string
    {
        if (!$this->start_time && !$this->end_time) return '';

        return ($this->start_time ?? '--:--') . ' - ' . ($this->end_time ?? '--:--');
    }

    public function getDisplayTitleAttribute(): string
    {
        if ($this->title) return $this->title;

        return $this->shift_label . ' - ' . $this->date->format('d/m/Y');
    }

    // ==================== STATS ====================

    /**
     * Thống kê nhanh cho 1 buổi
     * Dùng khi đã load attendanceRecords
     */
    public function getStatsAttribute(): array
    {
        $records = $this->attendanceRecords;

        $present  = $records->where('status', 1)->count();
        $excused  = $records->where('status', 2)->count();
        $absent   = $records->where('status', 3)->count();
        $late     = $records->where('status', 4)->count();
        $total    = $records->count();

        return [
            'present'      => $present,
            'excused'      => $excused,
            'absent'       => $absent,
            'late'         => $late,
            'total'        => $total,
            'present_rate' => $total > 0 ? round(($present + $late) / $total * 100, 1) : 0,
        ];
    }
}