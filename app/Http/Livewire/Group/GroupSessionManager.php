<?php

namespace App\Http\Livewire\Group;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Group;
use App\Models\GroupSession;
use App\Models\GroupMember;
use App\Models\GroupAttendanceRecord;
use Illuminate\Support\Facades\DB;

/**
 * Quản lý buổi sinh hoạt của nhóm
 * - List sessions + stats điểm danh
 * - Tạo session (single / weekly)
 * - Xóa session
 * - Link sang trang điểm danh
 */
class GroupSessionManager extends BaseComponent
{
    // ==================== PROPS ====================

    public $groupId;
    public ?Group $group = null;

    // ==================== FORM STATE ====================

    public $showForm   = false;
    public $createMode = 'single';  // single | weekly

    // ==================== FORM FIELDS ====================

    public $type       = 1;
    public $shift      = 1;
    public $title      = '';
    public $startDate  = '';
    public $endDate    = '';
    public $startTime  = '';
    public $endTime    = '';
    public $weekDays   = [];   // weekly mode: ['0','1',...]
    public $note       = '';

    // ==================== VALIDATION ====================

    protected $rules = [
        'type'      => 'required|integer|min:1|max:3',
        'shift'     => 'required|integer|in:1,2,3',
        'startDate' => 'required|date',
        'endDate'   => 'nullable|date|after_or_equal:startDate',
        'startTime' => 'nullable|date_format:H:i',
        'endTime'   => 'nullable|date_format:H:i|after:startTime',
        'weekDays'  => 'required_if:createMode,weekly|array',
        'title'     => 'nullable|string|max:255',
        'note'      => 'nullable|string|max:255',
    ];

    protected $messages = [
        'type.required'      => 'Vui lòng chọn loại buổi',
        'shift.required'     => 'Vui lòng chọn ca',
        'startDate.required' => 'Vui lòng chọn ngày',
        'startDate.date'     => 'Ngày không hợp lệ',
        'endDate.after_or_equal' => 'Ngày kết thúc phải sau ngày bắt đầu',
        'weekDays.required_if'   => 'Vui lòng chọn ít nhất 1 ngày trong tuần',
    ];

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'        => '$refresh',
        'sessionCreated' => '$refresh',
        'sessionDeleted' => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount($groupId = null): void
    {
        $this->requireManager();
        $this->groupId = (int) $groupId;
        parent::mount();
        $this->requireParishId();

        $this->group = Group::where('parish_id', $this->parishId)
            ->findOrFail($this->groupId);

        $this->startDate = today()->format('Y-m-d');
    }

    protected function loadInitialData(): void {}

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // ==================== CRUD ====================

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->requireManager();
        $this->validate();

        try {
            DB::beginTransaction();

            $dates   = $this->resolveDates();
            $created = 0;
            $skipped = 0;

            foreach ($dates as $date) {
                $exists = GroupSession::where('group_id', $this->groupId)
                    ->where('date', $date)
                    ->where('shift', $this->shift)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                GroupSession::create([
                    'group_id'   => $this->groupId,
                    'parish_id'  => $this->parishId,
                    'date'       => $date,
                    'shift'      => $this->shift,
                    'type'       => $this->type,
                    'title'      => $this->title ?: null,
                    'start_time' => $this->startTime ?: null,
                    'end_time'   => $this->endTime ?: null,
                    'note'       => $this->note ?: null,
                    'created_by' => auth()->id(),
                ]);

                $created++;
            }

            DB::commit();

            $msg = "Đã tạo {$created} buổi sinh hoạt";
            if ($skipped > 0) {
                $msg .= " (bỏ qua {$skipped} buổi đã tồn tại)";
            }

            session()->flash('message', $msg);
            $this->resetForm();
            $this->closeModal();
            $this->emit('sessionCreated');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error creating group sessions');
            session()->flash('error', 'Có lỗi khi tạo buổi sinh hoạt');
        }
    }

    public function delete(int $sessionId): void
    {
        $this->requireManager();

        try {
            GroupSession::where('group_id', $this->groupId)
                ->findOrFail($sessionId)
                ->delete();

            session()->flash('message', 'Đã xóa buổi sinh hoạt');
            $this->emit('sessionDeleted');
        } catch (\Exception $e) {
            $this->logError($e, 'Error deleting session', ['id' => $sessionId]);
            session()->flash('error', 'Có lỗi khi xóa buổi sinh hoạt');
        }
    }

    // ==================== HELPERS ====================

    /**
     * Tính danh sách ngày cần tạo session
     */
    private function resolveDates(): array
    {
        if ($this->createMode === 'single') {
            return [$this->startDate];
        }

        // Weekly
        $dates  = [];
        $start  = \Carbon\Carbon::parse($this->startDate);
        $end    = $this->endDate
            ? \Carbon\Carbon::parse($this->endDate)
            : $start->copy()->addMonths(3);

        $current = $start->copy();

        while ($current->lte($end)) {
            // dayOfWeek: 0=Sunday, 1=Monday, ...
            if (in_array((string) $current->dayOfWeek, $this->weekDays)) {
                $dates[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        return $dates;
    }

    private function getSessionsPaginated()
    {
        return GroupSession::where('group_id', $this->groupId)
            ->withCount([
                'attendanceRecords as present_count' => fn($q) => $q->where('status', 1),
                'attendanceRecords as excused_count' => fn($q) => $q->where('status', 2),
                'attendanceRecords as absent_count'  => fn($q) => $q->where('status', 3),
                'attendanceRecords as late_count'    => fn($q) => $q->where('status', 4),
            ])
            ->when(!empty(trim($this->search)), function ($q) {
                $q->where('title', 'like', '%' . trim($this->search) . '%');
            })
            ->orderBy('date', 'desc')
            ->orderBy('shift', 'asc')
            ->paginate($this->perPage);
    }

    // ==================== FORM HELPERS ====================

    public function closeModal(): void
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm(): void
    {
        $this->reset(['title', 'endDate', 'startTime', 'endTime', 'weekDays', 'note']);
        $this->type       = 1;
        $this->shift      = 1;
        $this->createMode = 'single';
        $this->startDate  = today()->format('Y-m-d');
        $this->resetValidation();
    }

    // ==================== RENDER ====================

    public function render()
    {
        $typeLabels = $this->group->member_type === 'teacher'
            ? GroupSession::TYPE_LABELS_TEACHER
            : GroupSession::TYPE_LABELS_CHOIR;

        return view('livewire.group.group-session-manager', [
            'sessions'   => $this->getSessionsPaginated(),
            'typeLabels' => $typeLabels,
            'shiftLabels' => GroupSession::SHIFT_LABELS,
            'memberCount' => GroupMember::where('group_id', $this->groupId)
                ->where('is_active', true)->count(),
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}