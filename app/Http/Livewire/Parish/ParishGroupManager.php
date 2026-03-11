<?php

namespace App\Http\Livewire\Parish;

use App\Http\Livewire\Base\BaseComponent;

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

    // ==================== SETTINGS ====================

    /** Không cần pagination */
    protected $usePagination = false;

    // ==================== VALIDATION ====================

    protected $rules = [
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
        $this->initializeUser();
        $this->authorize('viewAny', \App\Models\ParishGroup::class);
        parent::mount();
    }

    protected function loadInitialData(): void
    {
        // Data được load trực tiếp trong render()
        // không cần lưu vào property vì BelongsToParish scope
        // tự filter mỗi lần render
    }

    // ==================== CRUD ACTIONS ====================

    public function create(): void
    {
        $this->authorize('create', \App\Models\ParishGroup::class);
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        try {
            $group = \App\Models\ParishGroup::findOrFail($id);

            $this->authorize('update', $group);

            $this->editingId = $group->id;
            $this->name      = $group->name;
            $this->status    = $group->status;
            $this->showForm  = true;
        } catch (ModelNotFoundException) {
            session()->flash('error', 'Không tìm thấy giáo họ');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parish group for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin giáo họ');
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

        $this->validate();

        // Kiểm tra tên trùng trong cùng xứ
        $exists = \App\Models\ParishGroup::where('name', $this->name)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if ($exists) {
            session()->flash('error', 'Tên giáo họ đã tồn tại');
            return;
        }

        try {
            DB::beginTransaction();

            \App\Models\ParishGroup::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'name'   => $this->name,
                    'status' => $this->status,
                    // parish_id được Observer tự gán
                ]
            );

            DB::commit();

            session()->flash(
                'message',
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
            session()->flash('error', 'Có lỗi khi lưu giáo họ. Vui lòng thử lại.');
        }
    }

    public function toggleStatus(int $id): void
    {
        try {
            $group = \App\Models\ParishGroup::findOrFail($id);

            $this->authorize('update', $group);

            $group->update(['status' => !$group->status]);

            session()->flash(
                'message',
                $group->status ? 'Đã kích hoạt giáo họ' : 'Đã tắt giáo họ'
            );
        } catch (ModelNotFoundException) {
            session()->flash('error', 'Không tìm thấy giáo họ');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling parish group status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        try {
            $group = \App\Models\ParishGroup::findOrFail($id);

            $this->authorize('delete', $group);

            // Kiểm tra còn học sinh không
            if ($group->students()->exists()) {
                session()->flash('error', 'Không thể xóa giáo họ đang có học sinh');
                return;
            }

            $group->delete();

            session()->flash('message', 'Đã xóa giáo họ');
        } catch (ModelNotFoundException) {
            session()->flash('error', 'Không tìm thấy giáo họ');
        } catch (\Exception $e) {
            $this->logError($e, 'Error deleting parish group', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi xóa giáo họ');
        }
    }

    // ==================== FORM HELPERS ====================

    public function closeModal(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name']);
        $this->status = true;
        $this->showForm = false;
        $this->resetValidation();
    }

    // ==================== RENDER ====================

    public function render()
    {
        // Query trực tiếp trong render — BelongsToParish scope tự filter
        $groups = \App\Models\ParishGroup::orderBy('name')->get();

        return view('livewire.parish.parish-group-manager', [
            'groups' => $groups,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
