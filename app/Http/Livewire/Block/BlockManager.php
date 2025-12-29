<?php

namespace App\Http\Livewire\Block;

use App\Models\Block;
use App\Models\NamHoc;
use Livewire\Component;
use Livewire\WithPagination;

class BlockManager extends Component
{
    use WithPagination;

    public $parish_id;
    public $isAdmin;
    public $showForm = false;

    public $selectedNamHoc;
    public $namHocs = [];

    public $blocks; // Livewire v2.12: public property can be Collection

    public $editingId = null;
    public $name;
    public $status = 1;

    public $perPage = 15;
    public $perPageOptions = [10, 15, 25, 50];

    protected $rules = [
        'selectedNamHoc' => 'required|integer|exists:nam_hoc,id',
        'name' => 'required|string|max:255',
        'status' => 'required|boolean',
        'perPage' => 'required|integer|in:10,15,25,50',
    ];

    protected $listeners = [
        'refreshBlocks' => 'loadBlocks'
    ];

    public function mount()
    {
        $this->parish_id = session('parish_id');
        $this->isAdmin   = session('isAdmin');

        abort_if(!$this->parish_id, 403);

        $this->loadNamHocs();
        $this->setDefaultNamHoc();
        $this->loadBlocks();
    }

    public function loadNamHocs()
    {
        $this->namHocs = NamHoc::ofParish($this->parish_id)
            ->orderByDesc('start_date_one')
            ->get();
    }

    private function setDefaultNamHoc()
    {
        if (!$this->selectedNamHoc && $this->namHocs->count()) {
            $this->selectedNamHoc = $this->namHocs->first()->id;
        }
    }

    public function loadBlocks()
    {
        if (!$this->selectedNamHoc) {
            $this->blocks = collect();
            return;
        }

        $this->blocks = Block::where('pid', $this->parish_id)
            ->where('namhoc', $this->selectedNamHoc)
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function updatedSelectedNamHoc()
    {
        $this->resetForm();
        $this->resetPage();
        $this->loadBlocks();
    }

    public function updatedPerPage()
    {
        $this->validateOnly('perPage');
        $this->resetPage();
        $this->loadBlocks();
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $block = Block::where('pid', $this->parish_id)
            ->where('namhoc', $this->selectedNamHoc)
            ->findOrFail($id);

        $this->editingId = $block->id;
        $this->name = $block->name;
        $this->status = $block->status;

        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        Block::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'pid' => $this->parish_id,
                'namhoc' => $this->selectedNamHoc,
                'status' => $this->status,
            ]
        );

        session()->flash('message', 'Lưu khối thành công');

        $this->resetForm();
        $this->loadBlocks();
    }

    public function toggleStatus($id)
    {
        $block = Block::where('pid', $this->parish_id)
            ->where('namhoc', $this->selectedNamHoc)
            ->findOrFail($id);

        $block->update(['status' => !$block->status]);

        $this->loadBlocks();
    }

    public function resetForm()
    {
        $this->reset([
            'editingId',
            'name',
            'status',
        ]);

        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.block.block-manager', [
            'blocks' => $this->blocks,
        ])
        ->extends('frontend.layout.main')
        ->section('content');
    }
}
