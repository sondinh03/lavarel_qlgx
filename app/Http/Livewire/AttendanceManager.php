<?php

namespace App\Http\Livewire;

use App\Models\DiHoc;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceManager extends Component
{
    use WithPagination;

    public $lop;
    public $attendanceType = 'hoc'; // 'học' | 'lễ'
    public $selectedClassId; 
    public $attendanceRecords = []; // mảng [student_id => [date => status]]

    public function mount($lop) {
        $this->lop = $lop;
        $this->selectedClassId = $lop->id;
        $this->loadAttendanceRecords();
    }

    public function loadAttendanceRecords() {
        $records = DiHoc::where('lop');
    }

    public function render()
    {
        return view('livewire.attendance-manager');
    }
}
