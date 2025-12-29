<?php

namespace App\Http\Livewire\NamHoc;

use App\Models\NamHoc;
use App\Traits\FilterTrait;
use Livewire\Component;

class NamHocManager extends Component
{
    // use FilterTrait;

    public $parish_id;
    public $isAdmin;
    public $showForm = false;


    public $namHocs = [];

    public $editingId = null;
    public $name;
    public $start_date_one;
    public $end_date_one;
    public $start_date_two;
    public $end_date_two;
    public $status = 1;

    protected $rules = [
        'name' => 'required|string|max:255',
        'start_date_one' => 'nullable|date',
        'end_date_one' => 'nullable|date|after_or_equal:start_date_one',
        'start_date_two' => 'nullable|date',
        'end_date_two' => 'nullable|date|after_or_equal:start_date_two',
    ];

    public function mount()
    {
        $this->parish_id = session('parish_id');
        $this->isAdmin   = session('isAdmin');

        abort_if(!$this->parish_id, 403);
        $this->loadNamHocs();
    }

    public function loadNamHocs()
    {
        $this->namHocs = NamHoc::ofParish($this->parish_id)
            ->orderByDesc('start_date_one')
            ->get();
        }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $nh = NamHoc::ofParish($this->parish_id)->findOrFail($id);

        $this->editingId = $nh->id;
        $this->name = $nh->name;
        $this->start_date_one = $nh->start_date_one?->format('Y-m-d');
        $this->end_date_one = $nh->end_date_one?->format('Y-m-d');
        $this->start_date_two = $nh->start_date_two?->format('Y-m-d');
        $this->end_date_two = $nh->end_date_two?->format('Y-m-d');
        $this->status = $nh->status;

        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        NamHoc::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'parish_id' => $this->parish_id,
                'start_date_one' => $this->start_date_one,
                'end_date_one' => $this->end_date_one,
                'start_date_two' => $this->start_date_two,
                'end_date_two' => $this->end_date_two,
                'status' => $this->status,
            ]
        );

        session()->flash('message', 'Lưu năm học thành công');

        $this->resetForm();
        $this->loadNamHocs();
    }



    public function toggleStatus($id)
    {
        $nh = NamHoc::ofParish($this->parish_id)
            ->findOrFail($id);

        $nh->update(['status' => !$nh->status]);

        $this->loadNamHocs();
    }

    public function resetForm()
    {
        $this->reset([
            'editingId',
            'name',
            'start_date_one',
            'end_date_one',
            'start_date_two',
            'end_date_two',
            'status',
        ]);

        $this->showForm = false;
    }

    public function render()
    {
        return view('livewire.nam-hoc.nam-hoc-manager')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
