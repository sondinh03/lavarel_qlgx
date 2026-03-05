<?php

namespace App\Http\Livewire;

use App\Models\CatechismClass;
use App\Models\StudentNew;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ClassStudentManager extends Component
{
    public $classId;

    public $search = '';
    public $modalSearch = '';
    public $studentsToAdd = [];

    public $showAddModal = false;

    protected $listeners = ['refreshStudents' => '$refresh'];

    public function mount($id)
    {
        $this->classId = $id;

        // Global scope sẽ tự giới hạn parish
        CatechismClass::findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Computed Properties
    |--------------------------------------------------------------------------
    */

    public function getClassProperty()
    {
        return CatechismClass::findOrFail($this->classId);
    }

    public function getCurrentStudentsProperty()
    {
        return $this->class
            ->students()
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('student_code', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('last_name')
            ->get();
    }

    public function getAvailableStudentsProperty()
    {
        return StudentNew::query()
            ->whereDoesntHave('classes', function ($q) {
                $q->where('catechism_classes.id', $this->classId);
            })
            ->when($this->modalSearch, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('first_name', 'like', "%{$this->modalSearch}%")
                        ->orWhere('last_name', 'like', "%{$this->modalSearch}%")
                        ->orWhere('student_code', 'like', "%{$this->modalSearch}%");
                });
            })
            ->orderBy('last_name')
            ->limit(50)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function openAddModal()
    {
        $this->reset(['studentsToAdd', 'modalSearch']);
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
    }

    public function addStudents()
    {
        if (empty($this->studentsToAdd)) {
            session()->flash('warning', 'Vui lòng chọn học sinh');
            return;
        }

        DB::transaction(function () {
            $this->class->students()->attach($this->studentsToAdd);
        });

        session()->flash('message', 'Ghi danh thành công');
        $this->closeAddModal();
        $this->dispatch('refreshStudents');
    }

    public function removeStudent($studentId)
    {
        $this->class->students()->detach($studentId);
        session()->flash('message', 'Đã xóa khỏi lớp');
    }

    public function render()
    {
        return view('livewire.class-student-manager')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
