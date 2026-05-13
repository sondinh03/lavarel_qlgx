<?php

namespace App\Http\Livewire\Group;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

/**
 * Quản lý thành viên của nhóm
 * - Hiển thị danh sách thành viên
 * - Thêm teacher hoặc student vào nhóm (tùy member_type của group)
 * - Xóa / vô hiệu hóa thành viên
 */
class GroupMemberManager extends BaseComponent
{
    // ==================== PROPS ====================

    public $groupId;
    public ?Group $group = null;

    // ==================== FILTERS ====================

    public $filterActive = '';

    // ==================== MODAL THÊM THÀNH VIÊN ====================

    public $showAddModal   = false;
    public $modalSearch    = '';
    public $selectedIds    = [];   // IDs chờ thêm vào nhóm

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'        => '$refresh',
        'memberAdded'    => '$refresh',
        'memberRemoved'  => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount($groupId = null): void
    {
        $this->groupId = (int) $groupId;    
        $this->requireManager();
        parent::mount();
        $this->requireParishId();

        $this->group = Group::where('parish_id', $this->parishId)
            ->findOrFail($this->groupId);
    }

    protected function loadInitialData(): void {}

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedModalSearch(): void
    {
        $this->selectedIds = [];
    }

    public function updatedFilterActive(): void
    {
        $this->resetPage();
    }

    // ==================== ACTIONS ====================

    public function openAddModal(): void
    {
        $this->modalSearch  = '';
        $this->selectedIds  = [];
        $this->showAddModal = true;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
        $this->modalSearch  = '';
        $this->selectedIds  = [];
        $this->resetValidation();
    }

    public function addMembers(): void
    {
        $this->requireManager();

        if (empty($this->selectedIds)) {
            session()->flash('error', 'Vui lòng chọn ít nhất 1 thành viên');
            return;
        }

        try {
            DB::beginTransaction();

            $added   = 0;
            $skipped = 0;

            foreach ($this->selectedIds as $id) {
                $exists = GroupMember::where('group_id', $this->groupId)
                    ->where('memberable_type', $this->group->member_type)
                    ->where('memberable_id', $id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                GroupMember::create([
                    'group_id'        => $this->groupId,
                    'memberable_type' => $this->group->member_type,
                    'memberable_id'   => $id,
                    'joined_at'       => today(),
                    'is_active'       => true,
                ]);

                $added++;
            }

            DB::commit();

            $msg = "Đã thêm {$added} thành viên";
            if ($skipped > 0) {
                $msg .= " (bỏ qua {$skipped} đã có trong nhóm)";
            }

            session()->flash('message', $msg);
            $this->closeAddModal();
            $this->emit('memberAdded');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error adding group members');
            session()->flash('error', 'Có lỗi khi thêm thành viên');
        }
    }

    public function toggleActive(int $memberId): void
    {
        $this->requireManager();

        try {
            $member = GroupMember::where('group_id', $this->groupId)
                ->findOrFail($memberId);

            $member->update([
                'is_active' => !$member->is_active,
                'left_at'   => $member->is_active ? today() : null,
            ]);

            session()->flash(
                'message',
                $member->is_active
                    ? 'Đã kích hoạt thành viên'
                    : 'Đã cho thành viên nghỉ'
            );

            $this->emit('memberRemoved');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling member', ['id' => $memberId]);
            session()->flash('error', 'Có lỗi khi cập nhật thành viên');
        }
    }

    public function removeMember(int $memberId): void
    {
        $this->requireManager();

        try {
            GroupMember::where('group_id', $this->groupId)
                ->findOrFail($memberId)
                ->delete();

            session()->flash('message', 'Đã xóa thành viên khỏi nhóm');
            $this->emit('memberRemoved');
        } catch (\Exception $e) {
            $this->logError($e, 'Error removing member', ['id' => $memberId]);
            session()->flash('error', 'Có lỗi khi xóa thành viên');
        }
    }

    // ==================== DATA ====================

    private function getMembersPaginated()
    {
        $query = GroupMember::with('memberable')
            ->where('group_id', $this->groupId);

        if ($this->filterActive !== '') {
            $query->where('is_active', (bool) $this->filterActive);
        }

        // Search theo tên — JOIN tùy member_type
        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';

            if ($this->group->member_type === 'teacher') {
                $query->whereHasMorph('memberable', [Teacher::class], function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('phone_number', 'like', $term);
                });
            } else {
                $query->whereHasMorph('memberable', [Student::class], function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term);
                });
            }
        }

        return $query->orderBy('is_active', 'desc')
            ->orderBy('joined_at')
            ->paginate($this->perPage);
    }

    /**
     * Danh sách teacher/student chưa có trong nhóm — dùng cho modal thêm
     */
    public function getAvailableCandidates()
    {
        $existingIds = GroupMember::where('group_id', $this->groupId)
            ->where('is_active', true)
            ->pluck('memberable_id')
            ->toArray();

        $term = !empty(trim($this->modalSearch))
            ? '%' . trim($this->modalSearch) . '%'
            : null;

        if ($this->group->member_type === 'teacher') {
            $query = Teacher::where('parish_id', $this->parishId)
                ->where('is_active', true)
                ->whereNotIn('id', $existingIds);

            if ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('phone_number', 'like', $term);
                });
            }

            return $query->orderBy('last_name')->orderBy('first_name')->paginate(10);
        }

        // Student
        $query = Student::where('parish_id', $this->parishId)
            ->where('is_active', true)
            ->whereNotIn('id', $existingIds);

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term);
            });
        }

        return $query->orderBy('last_name')->orderBy('first_name')->paginate(10);
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.group.group-member-manager', [
            'members'    => $this->getMembersPaginated(),
            'candidates' => $this->showAddModal ? $this->getAvailableCandidates() : collect(),
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
