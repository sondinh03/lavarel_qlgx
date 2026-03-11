<?php

namespace App\Http\Livewire\NamHoc;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\NamHoc;
use App\Models\ScoreType;
use App\Models\StudentNew;
use App\Models\StudentsClass;
use Illuminate\Support\Facades\DB;

/**
 * Component copy cấu trúc lớp + xếp học sinh
 *
 * Bước 1: Chọn năm nguồn + năm đích + tuỳ chọn copy score_types
 * Bước 2: Xác nhận — preview danh sách lớp
 * Bước 3: Hoàn tất copy cấu trúc
 * Bước 4: Xếp học sinh — chọn lớp đích → chọn lớp nguồn → tick học sinh → lưu
 *
 * Hỗ trợ vào thẳng bước 4 qua URL:
 * /school-years/copy?source=1&target=2
 */
class CopyNamHoc extends BaseComponent
{
    // ==================== BƯỚC 1-3: Copy cấu trúc ====================

    public $sourceNamHocId = null;
    public $targetNamHocId = null;
    public $copyScoreTypes = true;

    public $step = 1;
    public $processing = false;
    public $result = [];

    public $namHocs;
    public $sourceClasses;

    // ==================== BƯỚC 4: Xếp học sinh ====================

    public $targetClassId    = null;
    public $sourceClassId    = null;
    public $selectedStudents = [];
    public $savingAssignment = false;

    public $targetClasses;
    public $sourceClassList;
    public $sourceScoreTypesList;
    public $availableStudents;

    // ==================== QUERY STRING ====================

    /**
     * Hỗ trợ bookmark / share URL bước 4:
     * /school-years/copy?source=1&target=2
     */
    protected function queryString()
    {
        return [
            'sourceNamHocId' => ['as' => 'source', 'except' => null],
            'targetNamHocId' => ['as' => 'target', 'except' => null],
        ];
    }

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        $this->authorize('viewAny', NamHoc::class);
        parent::mount();
    }

    protected function loadInitialData(): void
    {
        $this->namHocs = NamHoc::ofParish($this->parishId)
            ->orderByDesc('name')
            ->get(['id', 'name']);

        $this->sourceClasses     = collect();
        $this->targetClasses     = collect();
        $this->sourceClassList   = collect();
        $this->availableStudents = collect();

        // Nếu có ?source=X&target=Y trên URL → nhảy thẳng bước 4
        if ($this->sourceNamHocId && $this->targetNamHocId) {
            $this->sourceNamHocId = (int) $this->sourceNamHocId;
            $this->targetNamHocId = (int) $this->targetNamHocId;

            $validSource = $this->namHocs->contains('id', $this->sourceNamHocId);
            $validTarget = $this->namHocs->contains('id', $this->targetNamHocId);
            $different   = $this->sourceNamHocId !== $this->targetNamHocId;

            if ($validSource && $validTarget && $different) {
                $this->result = [
                    'source_name' => $this->namHocs->find($this->sourceNamHocId)?->name,
                    'target_name' => $this->namHocs->find($this->targetNamHocId)?->name,
                ];

                $this->loadStep4Data();
                $this->step = 4;
            }
        }
    }

    // ==================== BƯỚC 1: Watchers ====================

    public function updatedSourceNamHocId(): void
    {
        $this->sourceClasses = collect();
        if (!$this->sourceNamHocId) return;

        $this->sourceClasses = CatechismClass::with('gradeLevel')
            ->where('school_year_id', $this->sourceNamHocId)
            ->active()
            ->withCount('scoreTypes as score_types_count')
            ->orderBy('name')
            ->get();
    }

    // ==================== BƯỚC 1 → 2 ====================

    public function proceedToConfirm(): void
    {
        $this->validate([
            'sourceNamHocId' => 'required|integer|different:targetNamHocId',
            'targetNamHocId' => 'required|integer',
        ], [
            'sourceNamHocId.required' => 'Vui lòng chọn năm nguồn',
            'targetNamHocId.required' => 'Vui lòng chọn năm đích',
            'sourceNamHocId.different' => 'Năm nguồn và năm đích phải khác nhau',
        ]);

        if ($this->sourceClasses->isEmpty()) {
            session()->flash('warning', 'Năm nguồn chưa có lớp nào để copy');
            return;
        }

        $this->step = 2;
    }

    public function backToSelectYear(): void
    {
        $this->step = 1;
        $this->resetValidation();
    }

    // ==================== BƯỚC 2 → 3: Thực hiện copy ====================

    public function confirmCopy(): void
    {
        $this->authorize('create', CatechismClass::class);
        $this->processing = true;

        $createdClasses   = 0;
        $copiedScoreTypes = 0;
        $skippedClasses   = 0;

        try {
            DB::beginTransaction();

            foreach ($this->sourceClasses as $sourceClass) {
                $alreadyExists = CatechismClass::where('school_year_id', $this->targetNamHocId)
                    ->where('name', $sourceClass->name)
                    ->exists();

                if ($alreadyExists) {
                    $skippedClasses++;
                    continue;
                }

                $newClass = CatechismClass::create([
                    'parish_id'      => $this->parishId,
                    'school_year_id' => $this->targetNamHocId,
                    'grade_level_id' => $sourceClass->grade_level_id,
                    'name'           => $sourceClass->name,
                    'capacity'       => $sourceClass->capacity,
                    'is_active'      => true,
                ]);

                $createdClasses++;

                if ($this->copyScoreTypes) {
                    $scoreTypes = ScoreType::ofClass($sourceClass->id)->get();
                    foreach ($scoreTypes as $st) {
                        ScoreType::create([
                            'class_id'    => $newClass->id,
                            'semester'    => $st->semester,
                            'type'        => $st->type,
                            'name'        => $st->name,
                            'order'       => $st->order,
                            'coefficient' => $st->coefficient,
                            'max_score'   => $st->max_score,
                            'is_active'   => $st->is_active,
                        ]);
                        $copiedScoreTypes++;
                    }
                }
            }

            DB::commit();

            $this->result = [
                'source_name'        => $this->namHocs->find($this->sourceNamHocId)?->name,
                'target_name'        => $this->namHocs->find($this->targetNamHocId)?->name,
                'created_classes'    => $createdClasses,
                'skipped_classes'    => $skippedClasses,
                'copied_score_types' => $copiedScoreTypes,
            ];

            $this->loadStep4Data();
            $this->step = 3;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error copying nam hoc', [
                'source' => $this->sourceNamHocId,
                'target' => $this->targetNamHocId,
            ]);
            session()->flash('error', 'Có lỗi khi copy dữ liệu. Vui lòng thử lại.');
        } finally {
            $this->processing = false;
        }
    }

    // ==================== BƯỚC 4: Xếp học sinh ====================

    protected function loadStep4Data(): void
    {
        $this->targetClasses = CatechismClass::with('gradeLevel')
            ->where('school_year_id', $this->targetNamHocId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level_id']);

        $this->sourceClassList = CatechismClass::with('gradeLevel')
            ->where('school_year_id', $this->sourceNamHocId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level_id']);
    }

    public function proceedToAssign(): void
    {
        $this->loadStep4Data();
        $this->step = 4;
    }

    public function updatedSourceClassId(): void
    {
        $this->selectedStudents  = [];
        $this->availableStudents = collect();

        if (!$this->sourceClassId || !$this->targetNamHocId) return;

        $assignedStudentIds = StudentsClass::whereIn(
            'class_id',
            CatechismClass::where('school_year_id', $this->targetNamHocId)->pluck('id')
        )->pluck('student_id');

        $this->availableStudents = StudentNew::whereHas('studentsClass', function ($q) {
            $q->where('class_id', $this->sourceClassId)
                ->where('status', StudentsClass::STATUS_ENROLLED);
        })
            ->whereNotIn('id', $assignedStudentIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'saint_id']);
    }

    public function toggleSelectAll(): void
    {
        if (count($this->selectedStudents) === $this->availableStudents->count()) {
            $this->selectedStudents = [];
        } else {
            $this->selectedStudents = $this->availableStudents
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        }
    }

    public function saveAssignment(): void
    {
        $this->authorize('create', CatechismClass::class);

        if (!$this->targetClassId) {
            session()->flash('error', 'Vui lòng chọn lớp đích');
            return;
        }

        if (empty($this->selectedStudents)) {
            session()->flash('error', 'Vui lòng chọn ít nhất 1 học sinh');
            return;
        }

        $this->savingAssignment = true;

        try {
            DB::beginTransaction();

            $inserted = 0;

            foreach ($this->selectedStudents as $studentId) {
                $exists = StudentsClass::where('class_id', $this->targetClassId)
                    ->where('student_id', $studentId)
                    ->exists();

                if ($exists) continue;

                StudentsClass::create([
                    'student_id'  => $studentId,
                    'class_id'    => $this->targetClassId,
                    'status'      => StudentsClass::STATUS_ENROLLED,
                    'enrolled_at' => now()->toDateString(),
                ]);

                $inserted++;
            }

            DB::commit();

            session()->flash('message', "Đã xếp {$inserted} học sinh vào lớp");

            $this->selectedStudents  = [];
            $this->sourceClassId     = null;
            $this->availableStudents = collect();

            $this->loadStep4Data();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error assigning students', [
                'target_class' => $this->targetClassId,
                'students'     => $this->selectedStudents,
            ]);
            session()->flash('error', 'Có lỗi khi xếp lớp. Vui lòng thử lại.');
        } finally {
            $this->savingAssignment = false;
        }
    }

    // ==================== RESET ====================

    public function startOver(): void
    {
        $this->reset([
            'sourceNamHocId',
            'targetNamHocId',
            'copyScoreTypes',
            'result',
            'processing',
            'targetClassId',
            'sourceClassId',
            'selectedStudents',
            'savingAssignment',
        ]);
        $this->copyScoreTypes    = true;
        $this->sourceClasses     = collect();
        $this->targetClasses     = collect();
        $this->sourceClassList   = collect();
        $this->availableStudents = collect();
        $this->step = 1;
        $this->resetValidation();
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.nam-hoc.copy-nam-hoc')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
