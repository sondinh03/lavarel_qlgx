<?php

namespace App\Http\Livewire\Group;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupSession;
use App\Models\GroupAttendanceRecord;
use Illuminate\Support\Facades\DB;

/**
 * Điểm danh buổi sinh hoạt nhóm
 *
 * Pattern: Alpine.js giữ draft state, Livewire chỉ save batch
 * Tương tự attendance học sinh nhưng đơn giản hơn (1 buổi, không có grid nhiều cột)
 */
class GroupAttendance extends BaseComponent
{
    // ==================== PROPS ====================

    public $groupId;
    public $sessionId;
    public ?Group       $group   = null;
    public ?GroupSession $session = null;

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount($groupId = null, $sessionId = null): void
    {
        $this->groupId   = (int) $groupId;
        $this->sessionId = (int) $sessionId;

        parent::mount();
        $this->requireParishId();

        $this->group = Group::where('parish_id', $this->parishId)
            ->findOrFail($this->groupId);

        $this->session = GroupSession::where('group_id', $this->groupId)
            ->findOrFail($this->sessionId);
    }

    protected function loadInitialData(): void {}

    // ==================== SAVE ====================

    /**
     * Nhận draft từ Alpine và lưu vào DB
     * draft format: { "memberId_sessionId": { status: 1, note: "" }, ... }
     */
    public function saveFromClient(array $draft): void
    {
        if (empty($draft)) return;

        try {
            DB::beginTransaction();

            $userId = auth()->id();

            foreach ($draft as $key => $data) {
                // key format: "{memberId}_{sessionId}"
                [$memberId, $sessionId] = explode('_', $key);
                $memberId  = (int) $memberId;
                $sessionId = (int) $sessionId;

                // Bảo vệ: chỉ cho phép save đúng session này
                if ($sessionId !== $this->sessionId) continue;

                // Bảo vệ: member phải thuộc group này
                $memberExists = GroupMember::where('id', $memberId)
                    ->where('group_id', $this->groupId)
                    ->exists();

                if (!$memberExists) continue;

                $status = (int) ($data['status'] ?? 1);
                $note   = $data['note'] ?? null;

                GroupAttendanceRecord::updateOrCreate(
                    [
                        'session_id' => $sessionId,
                        'member_id'  => $memberId,
                    ],
                    [
                        'status'     => $status,
                        'note'       => $note ?: null,
                        'updated_by' => $userId,
                        'created_by' => $userId,
                    ]
                );
            }

            DB::commit();

            // Reload records và trả về Alpine
            $records = $this->buildRecordsForAlpine();

            $this->emit('toast', 'success', 'Đã lưu điểm danh');
            $this->dispatchBrowserEvent('attendance-saved', ['records' => $records]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving group attendance');
            $this->dispatchBrowserEvent('attendance-save-error');
            $this->emit('toast', 'error', 'Có lỗi khi lưu điểm danh');
        }
    }

    // ==================== DATA ====================

    private function getMembers()
    {
        return GroupMember::with('memberable.saint', 'memberable.parishGroup')
            ->where('group_id', $this->groupId)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn($m) => $m->memberable?->last_name . $m->memberable?->first_name);
    }

    /**
     * Build records array cho Alpine
     * Format: { "memberId_sessionId": { status, note } }
     */
    private function buildRecordsForAlpine(): array
    {
        $records = GroupAttendanceRecord::where('session_id', $this->sessionId)
            ->get();

        $result = [];
        foreach ($records as $record) {
            $key          = $record->member_id . '_' . $record->session_id;
            $result[$key] = [
                'status' => $record->status,
                'note'   => $record->note ?? '',
            ];
        }

        return $result;
    }

    private function getStats(): array
    {
        $records = GroupAttendanceRecord::where('session_id', $this->sessionId)->get();

        return [
            'present'  => $records->whereIn('status', [1, 4])->count(),
            'excused'  => $records->where('status', 2)->count(),
            'absent'   => $records->where('status', 3)->count(),
            'total'    => $records->count(),
        ];
    }

    // ==================== RENDER ====================

    public function render()
    {
        $members         = $this->getMembers();
        $attendanceRecords = $this->buildRecordsForAlpine();
        $stats           = $this->getStats();

        return view('livewire.group.group-attendance', [
            'members'           => $members,
            'attendanceRecords' => $attendanceRecords,
            'stats'             => $stats,
            'memberCount'       => $members->count(),
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}