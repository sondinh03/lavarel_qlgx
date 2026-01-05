<?php

namespace App\Http\Livewire\Holy;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Holymanagement;

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
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . trim($this->search) . '%');
            })
            ->orderBy('name', 'asc')
            ->paginate($this->perPage);

        return view('livewire.holy.holy-manager', [
            'holies' => $holies,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }

    /** ========== Actions ========== */
    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $holy = Holymanagement::findOrFail($id);

        $this->holyId = $holy->id;
        $this->name   = $holy->name;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        Holymanagement::updateOrCreate(
            ['id' => $this->holyId],
            ['name' => $this->name]
        );

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Lưu thành công',
        ]);

        $this->closeModal();
    }

    public function delete(int $id)
    {
        Holymanagement::findOrFail($id)->delete();

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Đã xóa',
        ]);
    }

    /** ========== Helpers ========== */
    private function resetForm()
    {
        $this->reset(['holyId', 'name']);
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->showModal = false;
    }
}
