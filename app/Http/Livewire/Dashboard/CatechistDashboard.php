<?php

namespace App\Http\Livewire\Dashboard;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Lop;
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

        $this->activeSchoolYear = NamHoc::ofParish($this->parishId)
            ->active()
            ->orderByDesc('name')
            ->first();

        $this->loadMyClasses();
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

        $this->myClasses = Lop::with(['blockRelation', 'activeStudents'])
            ->ofParish($this->parishId)
            ->where('schoolyear', $this->activeSchoolYear->id)
            ->whereHas('catechists', fn($q) => $q->where('teacher_id', $teacherId))
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