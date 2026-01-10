<?php

namespace App\Http\Livewire\Parish;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Parish;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ParishChild extends BaseComponent
{
    // ==================== FORM STATE ====================

    public $showForm = false;
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    public $name;
    public $status = 1;

    // ==================== DATA ====================

    public $parishes = [];

    // ==================== VALIDATION ====================

    protected $rules = [
        'name' => 'required|string|max:255',
        'status' => 'required|boolean',
    ];

    protected $messages = [
        'name.required' => 'Vui lòng nhập tên giáo họ',
        'name.max' => 'Tên giáo họ không được quá 255 ký tự',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        parent::mount();

        $this->requireManager();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->loadParishes();
    }

    // ==================== DATA LOAD ====================

    public function loadParishes(): void
    {
        try {
            $this->parishes = Parish::ofParish($this->parishId)
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parishes');
            session()->flash('error', 'Có lỗi khi tải danh sách giáo họ');
            $this->parishes = collect();
        }
    }

    // ==================== CRUD ====================

    public function create(): void
    {
        $this->requireManager();
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->requireManager();

        try {
            $parish = Parish::where('pid', $this->parishId)->findOrFail($id);

            $this->editingId = $parish->id;
            $this->name = $parish->name;
            $this->status = $parish->status;

            $this->showForm = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Không tìm thấy giáo họ');
        }
    }

    public function save(): void
    {
        $this->requireManager();
        $this->validate();

        try {
            DB::beginTransaction();

            $exists = Parish::where('pid', $this->parishId)
                ->where('name', $this->name)
                ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($exists) {
                session()->flash('error', 'Tên giáo họ đã tồn tại');
                return;
            }

            Parish::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'name' => $this->name,
                    'status' => $this->status,
                    'pid' => $this->parishId,
                    'deid' => 0,
                    'did' => 0,
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
            $this->loadParishes();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving parish');
            session()->flash('error', 'Có lỗi khi lưu giáo họ');
        }
    }

    public function toggleStatus(int $id): void
    {
        $this->requireManager();

        try {
            $parish = Parish::where('pid', $this->parishId)->findOrFail($id);
            $parish->update(['status' => !$parish->status]);

            session()->flash('message', 'Đã cập nhật trạng thái giáo họ');
            $this->loadParishes();
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi khi đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        $this->requireAdmin();

        try {
            Parish::where('pid', $this->parishId)->findOrFail($id)->delete();
            session()->flash('message', 'Đã xóa giáo họ');
            $this->loadParishes();
        } catch (\Exception $e) {
            session()->flash('error', 'Không thể xóa giáo họ');
        }
    }

    // ==================== HELPERS ====================

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'status']);
        $this->status = 1;
        $this->showForm = false;
        $this->resetValidation();
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.parish.parish-child', [
            'parishes' => $this->parishes,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
