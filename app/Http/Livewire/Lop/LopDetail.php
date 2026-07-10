<?php

namespace App\Http\Livewire\Lop;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\ClassTeacher;
use Illuminate\Support\Facades\Cache;

class LopDetail extends BaseComponent
{
    public $lopId;

    public $lopData = [];
    public $teachers = [];
    public $statistics = [];

    protected $classModel = null;
    protected $gradeLevel = null;
    protected $namHoc = null;

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'filtersChanged' => 'handleFiltersChanged',
    ];

    public function mount($id = null)
    {
        $this->lopId = (int) $id;

        if ($this->lopId <= 0) {
            session()->flash('error', 'ID lớp học không hợp lệ.');
            $this->redirectRoute('classes.index');
            return;
        }

        parent::mount();
    }

    protected function loadInitialData(): void
    {
        $this->loadClassDetails();

        if (!$this->classModel) {
            $this->redirectRoute('classes.index');
            return;
        }

        $this->loadStatistics();
    }

    protected function validateUserAccess(): void
    {
    }

    private function loadClassDetails(): void
    {
        try {
            $this->classModel = CatechismClass::with([
                'gradeLevel',
                'schoolYear',
                'classTeachers' => function ($q) {
                    $q->where('status', 1)
                        ->with('teacher')
                        ->orderBy('role', 'asc');
                },
            ])
                ->withCount('students')
                ->findOrFail($this->lopId);

            $this->loadTeachers();

            $this->gradeLevel = $this->classModel->gradeLevel;
            $this->namHoc = $this->classModel->schoolYear;

            $this->lopData = [
                'id' => $this->classModel->id,
                'name' => $this->classModel->name ?? '',
                'capacity' => $this->classModel->capacity,
                'students_count' => (int) ($this->classModel->students_count ?? 0),
                'parish_id' => (int) ($this->classModel->parish_id ?? 0),
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy lớp học này.');
            $this->logError($e, 'Class not found', ['class_id' => $this->lopId]);
            $this->classModel = null;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class details', ['class_id' => $this->lopId]);
            session()->flash('error', 'Có lỗi trong lúc tải thông tin lớp.');
            $this->classModel = null;
        }
    }

    private function loadTeachers(): void
    {
        $teachersData = [];
        $schoolYearId = $this->classModel->school_year_id ?? null;

        foreach ($this->classModel->classTeachers as $ct) {
            if (
                $ct->teacher &&
                $ct->teacher->is_active &&
                (is_null($schoolYearId) || $ct->namhoc_id == $schoolYearId)
            ) {
                $teacher = $ct->teacher;

                $teachersData[] = [
                    'id' => $teacher->id,
                    'name' => $teacher->full_name_with_saint,
                    'birthday' => $teacher->birthday?->format('d/m/Y'),
                    'phone' => $teacher->phone_number ?? '',
                    'is_chu_nhiem' => $ct->role == ClassTeacher::ROLE_CHU_NHIEM,
                ];
            }
        }

        $this->teachers = $teachersData;
    }

    private function loadStatistics(): void
    {
        if (!$this->classModel) {
            $this->statistics = ['total' => 0, 'male' => 0, 'female' => 0];
            return;
        }

        try {
            $schoolYearId = $this->classModel->school_year_id ?? 'none';
            $cacheKey = "class_stats:{$this->classModel->id}:{$schoolYearId}";
            $ttlSeconds = 300;

            $this->statistics = Cache::remember($cacheKey, $ttlSeconds, function () {
                $result = $this->classModel->students()
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male,
                        SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female
                    ")
                    ->getQuery()
                    ->first();

                return [
                    'total'  => (int) ($result->total ?? 0),
                    'male'   => (int) ($result->male ?? 0),
                    'female' => (int) ($result->female ?? 0),
                ];
            });
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading statistics', ['class_id' => $this->lopId]);
            $this->statistics = ['total' => 0, 'male' => 0, 'female' => 0];
        }
    }

    public function handleFiltersChanged($filters): void
    {
        if (!is_array($filters)) {
            return;
        }

        $newClassId = $filters['lop'] ?? null;

        if ($newClassId && (int) $newClassId !== (int) $this->lopId) {
            $this->redirect(route('classes.show', $newClassId));
        }
    }

    public function handleRefresh(): void
    {
        if ($this->classModel) {
            $schoolYearId = $this->classModel->school_year_id ?? 'none';
            Cache::forget("class_stats:{$this->classModel->id}:{$schoolYearId}");
        }

        $this->loadClassDetails();
        $this->loadStatistics();

        session()->flash('message', 'Đã làm mới thông tin lớp học');
    }

    public function render()
    {
        return view('livewire.lop.lop-detail', [
            'parishId' => $this->parishId,
            'gradeLevel' => $this->gradeLevel,
            'namHoc' => $this->namHoc,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
