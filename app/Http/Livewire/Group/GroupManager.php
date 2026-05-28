<?php

namespace App\Http\Livewire\Group;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Group;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Quản lý nhóm (GLV, ca đoàn, ...)
 *
 * Features:
 * - List groups với search + filter theo type
 * - Create / Edit / Delete group
 * - Xem số thành viên mỗi nhóm
 */
class GroupManager extends BaseComponent
{
    // ==================== FILTERS ====================

    public $filterType = '';

    // ==================== FORM STATE ====================

    public $showForm  = false;
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    public $name        = '';
    public $type        = '';
    public $member_type = '';
    public $is_active   = true;
    public $note        = '';

    // ==================== VALIDATION ====================

    protected $rules = [
        'name'        => 'required|string|max:255',
        'type'        => 'required|integer|in:1,2,3,4,5,6',
        'member_type' => 'required|in:teacher,student,parishioner',
        'is_active'   => 'boolean',
        'note'        => 'nullable|string|max:255',
    ];

    protected $messages = [
        'name.required'        => 'Vui lòng nhập tên nhóm',
        'type.required'        => 'Vui lòng chọn loại nhóm',
        'type.in'              => 'Loại nhóm không hợp lệ',
        'member_type.required' => 'Vui lòng chọn loại thành viên',
        'member_type.in'       => 'Loại thành viên không hợp lệ',
    ];

    // ==================== QUERY STRING ====================

    protected $queryString = [
        'search'     => ['except' => ''],
        'filterType' => ['except' => ''],
    ];

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'      => '$refresh',
        'groupCreated' => '$refresh',
        'groupUpdated' => '$refresh',
        'groupDeleted' => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        $this->requireManager();
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        // Không cần load gì thêm
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    /**
     * Khi chọn type → tự động set member_type
     * GLV (type=1)         → teacher
     * Ca đoàn (type=2,3)   → student
     * Khác (type=4)        → để user tự chọn
     */
    public function updatedType($value): void
    {
        if ($value == Group::TYPE_TEACHER_GROUP) {
            $this->member_type = Group::MEMBER_TYPE_TEACHER;
        } elseif (in_array($value, [Group::TYPE_CHOIR_YOUTH, Group::TYPE_CHOIR_ADULT])) {
            $this->member_type = Group::MEMBER_TYPE_STUDENT;
        } else {
            $this->member_type = '';
        }
    }

    // ==================== CRUD ====================

    public function create(): void
    {
        $this->requireManager();
        $this->resetForm();
        $this->showForm = true;
        $this->emit('openModal');
    }

    public function edit(int $id): void
    {
        $this->requireManager();

        try {
            $group = Group::findOrFail($id);

            $this->editingId   = $group->id;
            $this->name        = $group->name;
            $this->type        = $group->type;
            $this->member_type = $group->member_type;
            $this->is_active   = $group->is_active;
            $this->note        = $group->note ?? '';

            $this->showForm = true;
            $this->emit('openModal');
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy nhóm này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading group for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin nhóm');
        }
    }

    public function save(): void
    {
        $this->requireManager();
        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editingId) {
                $group = Group::findOrFail($this->editingId);
                $group->update([
                    'name'        => $this->name,
                    'type'        => $this->type,
                    'member_type' => $this->member_type,
                    'is_active'   => $this->is_active,
                    'note'        => $this->note ?: null,
                ]);
                $event = 'groupUpdated';
                $msg   = 'Cập nhật nhóm thành công';
            } else {
                Group::create([
                    'parish_id'   => $this->parishId,
                    'name'        => $this->name,
                    'type'        => $this->type,
                    'member_type' => $this->member_type,
                    'is_active'   => $this->is_active,
                    'note'        => $this->note ?: null,
                ]);
                $event = 'groupCreated';
                $msg   = 'Thêm nhóm thành công';
            }

            DB::commit();

            session()->flash('message', $msg);
            $this->resetForm();
            $this->closeModal();
            $this->emit($event);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving group');
            session()->flash('error', 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    public function delete(int $id): void
    {
        $this->requireManager();

        try {
            $group = Group::findOrFail($id);

            // Kiểm tra còn thành viên active không
            if ($group->activeMembers()->exists()) {
                session()->flash('error', 'Không thể xóa nhóm còn thành viên đang hoạt động');
                return;
            }

            $group->delete();

            session()->flash('message', 'Đã xóa nhóm thành công');
            $this->emit('groupDeleted');
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy nhóm này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error deleting group', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi xóa nhóm');
        }
    }

    public function toggleActive(int $id): void
    {
        $this->requireManager();

        try {
            $group = Group::findOrFail($id);
            $group->update(['is_active' => !$group->is_active]);

            session()->flash(
                'message',
                $group->is_active
                    ? 'Đã kích hoạt nhóm'
                    : 'Đã vô hiệu hóa nhóm'
            );
            $this->emit('groupUpdated');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling group active', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi cập nhật trạng thái');
        }
    }

    // ==================== DATA ====================

    private function getGroupsPaginated()
    {
        try {
            $query = Group::withCount(['activeMembers', 'sessions'])
                ->where('parish_id', $this->parishId);

            if (!empty(trim($this->search))) {
                $query->where('name', 'like', '%' . trim($this->search) . '%');
            }

            if ($this->filterType !== '') {
                $query->where('type', $this->filterType);
            }

            return $query->orderBy('type')->orderBy('name')->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading groups');
            session()->flash('error', 'Có lỗi khi tải danh sách nhóm');

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }
    }

    // ==================== FORM HELPERS ====================

    public function closeModal(): void
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
        $this->emit('closeModal');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'type',
            'member_type',
            'note',
        ]);
        $this->is_active = true;
        $this->resetValidation();
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.group.group-manager', [
            'groups'     => $this->getGroupsPaginated(),
            'typeLabels' => Group::TYPE_LABELS,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
