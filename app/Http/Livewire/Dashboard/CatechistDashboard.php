<?php

namespace App\Http\Livewire\Dashboard;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\NamHoc;
use Carbon\Carbon;

class CatechistDashboard extends BaseComponent
{
    public $myClasses;
    public $activeSchoolYear;
    public $todayLabel;

    protected function loadInitialData(): void
    {
        $this->requireParishId();

        $this->todayLabel = Carbon::now()
            ->locale('vi')
            ->isoFormat('dddd, D/M/YYYY');

        $this->activeSchoolYear = NamHoc::query()
            ->active()
            ->orderByDesc('name')
            ->first();

        $this->loadMyClasses();
    }

    public function mount(): void
    {
        if (auth()->user()?->isParishAdmin()) {
            redirect()->route('parish-admin.dashboard');
        }

        parent::mount();
    }

    protected function loadMyClasses(): void
    {
        if (!$this->activeSchoolYear) {
            $this->myClasses = collect();
            return;
        }

        // Lấy lớp mà catechist này đang dạy
        $teacherId = auth()->user()->teacher?->id;

        if (!$teacherId) {
            $this->myClasses = collect();
            return;
        }

        $this->myClasses = CatechismClass::with('gradeLevel')
            ->withCount('students')
            ->where('school_year_id', $this->activeSchoolYear->id)
            ->whereHas('teachers', fn($q) => $q->where('teachers.id', $teacherId))
            ->active()
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.catechist-dashboard', [
            'myClasses'        => $this->myClasses,
            'activeSchoolYear' => $this->activeSchoolYear,
            'todayLabel'       => $this->todayLabel,
        ])
            ->extends('frontend.layout.catechist')
            ->section('content');
    }
}
