<?php

namespace App\Http\Livewire\Parish;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\ParishGroup;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Component quản lý Giáo họ
 *
 * Features:
 * - Danh sách giáo họ theo giáo xứ (BelongsToParish scope tự filter)
 * - CRUD với modal form
 * - Toggle status
 * - Chỉ parish_admin mới quản lý được
 */

class ParishGroupManager extends BaseComponent
{
    // ==================== FORM STATE ====================

    /** @var bool Hiển thị modal form */
    public $showForm = false;

    /** @var int|null ID đang edit (null = create) */
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    /** @var string Tên giáo họ */
    public $name = '';

    /** @var bool Trạng thái */
    public $status = true;

    protected array $allowedSortFields = ['name', 'students_count', 'status'];

    // ==================== VALIDATION ====================
    protected $formRules = [
        'name'   => 'required|string|max:255',
        'status' => 'required|boolean',
    ];

    protected $messages = [
        'name.required' => 'Vui lòng nhập tên giáo họ',
        'name.max'      => 'Tên giáo họ không được quá 255 ký tự',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        parent::mount();
        $this->authorize('viewAny', \App\Models\ParishGroup::class);
    }

    protected function loadInitialData(): void {}

    // ==================== CRUD ACTIONS ====================

    public function create(): void
    {
        $this->authorize('create', ParishGroup::class);
        $this->resetForm();
        $this->emit('openModal');
    }

    public function edit(int $id): void
    {
        try {
            $group = \App\Models\ParishGroup::findOrFail($id);

            $this->authorize('update', $group);

            $this->editingId = $group->id;
            $this->name      = $group->name;
            $this->status    = $group->status;
            $this->emit('openModal');
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy giáo họ');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parish group for edit', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi tải thông tin giáo họ');
        }
    }

    public function save(): void
    {
        if ($this->editingId) {
            $group = \App\Models\ParishGroup::findOrFail($this->editingId);
            $this->authorize('update', $group);
        } else {
            $this->authorize('create', \App\Models\ParishGroup::class);
        }

        $this->validate($this->formRules, $this->messages);

        try {
            DB::beginTransaction();

            // Check trùng tên TRONG transaction để tránh race condition
            $exists = \App\Models\ParishGroup::where('name', $this->name)
                ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($exists) {
                DB::rollBack();
                $this->addError('name', 'Tên giáo họ đã tồn tại');  // ← hiện inline
                return;
            }

            \App\Models\ParishGroup::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'parish_id' => $this->parishId,
                    'name'   => $this->name,
                    'status' => $this->status,
                ]
            );

            DB::commit();
            cache()->forget("parish_groups_{$this->parish_id}");

            $this->emit('toast', 
                'success',
                $this->editingId
                    ? 'Cập nhật giáo họ thành công'
                    : 'Thêm giáo họ mới thành công'
            );

            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving parish group', [
                'editing_id' => $this->editingId,
                'name'       => $this->name,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu giáo họ. Vui lòng thử lại.');
        }
    }

    public function toggleStatus(int $id): void
    {
        try {
            $group = \App\Models\ParishGroup::findOrFail($id);

            $this->authorize('update', $group);

            $group->update(['status' => !$group->status]);
            $group->refresh();

            $this->emit('toast', 
                'success',
                $group->status ? 'Đã kích hoạt giáo họ' : 'Đã lưu trữ giáo họ'
            );
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy giáo họ');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling parish group status', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        try {
            $group = \App\Models\ParishGroup::findOrFail($id);

            $this->authorize('delete', $group);

            // Kiểm tra còn học sinh không
            if ($group->students()->exists()) {
                $this->emit('toast', 'error', 'Không thể xóa giáo họ đang có học sinh');
                return;
            }

            $group->delete();
            cache()->forget("parish_groups_{$this->parish_id}");

            $this->emit('toast', 'success', 'Đã xóa giáo họ');
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy giáo họ thành công');
        } catch (\Exception $e) {
            $this->logError($e, 'Error deleting parish group', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa giáo họ');
        }
    }

    // ==================== FORM HELPERS ====================

    public function closeModal(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name']);
        $this->status = true;
        $this->showForm = false;
        $this->resetValidation();
        $this->emit('closeModal');
    }

    // ==================== RENDER ====================

    public function render()
    {
        $query = ParishGroup::withCount('students')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));

        $this->applySorting($query);

        $groups = $query->paginate($this->perPage);
        return view('livewire.parish.parish-group-manager', [
            'groups' => $groups,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
