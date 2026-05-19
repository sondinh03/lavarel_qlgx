<?php

namespace App\Http\Livewire\Holy;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Holymanagement;
use Illuminate\Support\Str;

/**
 * Component quản lý Holy (CRUD)
 *
 * Features:
 * - List Holy với pagination
 * - Create / Edit / Delete
 * - Validation cơ bản
 */
class HolyManager extends BaseComponent
{
    /** ========== Form fields ========== */
    public ?int $holyId = null;
    public string $name = '';

    /** ========== UI states ========== */
    public bool $showModal = false;

    protected $paginationTheme = 'tailwind';

    /** ========== Validation ========== */
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    protected $messages = [
        'name.required' => 'Vui lòng nhập tên',
    ];

    /**
     * Load dữ liệu ban đầu (implement từ BaseComponent)
     */
    protected function loadInitialData(): void {}

    /** ========== Render ========== */
    public function render()
    {
        $holies = Holymanagement::query()
            ->withCount('students')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . trim($this->search) . '%');
            })
            ->orderBy('name', 'asc')
            ->paginate($this->perPage);

        return view('livewire.holy.holy-manager', [
            'holies' => $holies,
        ])
            ->extends('frontend.layout.parishioner')
            ->section('content');
    }

    /** ========== Actions ========== */
    public function create()
    {
        $this->authorize('create', Holymanagement::class);
        $this->resetForm();
        $this->emit('openModal');
    }

    public function edit(int $id)
    {
        $holy = Holymanagement::findOrFail($id);

        $this->authorize('update', $holy);
        $this->holyId = $holy->id;
        $this->name   = $holy->name;

        $this->emit('openModal');
    }

    public function save()
    {
        $this->validate();

        if ($this->holyId) {
            $holy = Holymanagement::findOrFail($this->holyId);
            $this->authorize('update', $holy);
        } else {
            $this->authorize('create', Holymanagement::class);
        }

        $exists = Holymanagement::where('name', Str::title(trim($this->name)))
            ->when($this->holyId, fn($q) => $q->where('id', '!=', $this->holyId))
            ->exists();

        if ($exists) {
            $this->addError('name', 'Tên thánh này đã tồn tại');
            return;
        }

        Holymanagement::updateOrCreate(
            ['id' => $this->holyId],
            ['name' => $this->name]
        );

        $this->emit('toast', 'success', $this->holyId ? 'Đã cập nhật tên thánh thành công' : 'Đã tạo tên thánh thành công');

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        try {
            $holy = Holymanagement::findOrFail($id);
            $this->authorize('delete', $holy);

            $holy->delete();

            $this->emit('toast', 'success', 'Đã xóa tên thánh thành công');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->emit('toast', 'error', 'Bạn không có quyền xóa tên thánh');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy tên thánh');
        } catch (\Exception $e) {
            $this->logError($e, 'Error deleting holy', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa tên thánh');
        }
    }

    /** ========== Helpers ========== */
    private function resetForm()
    {
        $this->reset(['holyId', 'name']);
        $this->resetValidation();
        $this->emit('closeModal');
    }

    public function closeModal()
    {
        $this->resetForm();
    }
}
