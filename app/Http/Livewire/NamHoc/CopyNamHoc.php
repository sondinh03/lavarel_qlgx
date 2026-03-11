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

    /** @var int|null Lớp đích đang chọn để xếp */
    public $targetClassId = null;

    /** @var int|null Lớp nguồn để lấy danh sách học sinh */
    public $sourceClassId = null;

    /** @var array IDs học sinh được tick */
    public $selectedStudents = [];

    /** @var bool Đang lưu xếp lớp */
    public $savingAssignment = false;

    // Data bước 4
    public $targetClasses;     // Lớp trong năm đích (vừa tạo)
    public $sourceClassList;   // Lớp trong năm nguồn (để chọn lấy HS)
    public $availableStudents; // HS chưa có lớp trong năm đích

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        $this->requireManager();
        parent::mount();
    }

    protected function loadInitialData(): void
    {
        $this->namHocs          = NamHoc::ofParish($this->parishId)
            ->orderByDesc('name')
            ->get(['id', 'name']);

        $this->sourceClasses    = collect();
        $this->targetClasses    = collect();
        $this->sourceClassList  = collect();
        $this->availableStudents = collect();
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
        $this->requireManager();
        $this->processing = true;

        $createdClasses   = 0;
        $copiedScoreTypes = 0;
        $skippedClasses   = 0;

        try {
            DB::beginTransaction();

            foreach ($this->sourceClasses as $sourceClass) {
                // Bỏ qua nếu trùng tên trong năm đích
                $alreadyExists = CatechismClass::where('school_year_id', $this->targetNamHocId)
                    ->where('name', $sourceClass->name)
                    ->exists();

                if ($alreadyExists) {
                    $skippedClasses++;
                    continue;
                }

                // Tạo lớp mới
                $newClass = CatechismClass::create([
                    'parish_id'      => $this->parishId,
                    'school_year_id' => $this->targetNamHocId,
                    'grade_level_id' => $sourceClass->grade_level_id,
                    'name'           => $sourceClass->name,
                    'capacity'       => $sourceClass->capacity,
                    'is_active'      => true,
                ]);

                $createdClasses++;

                // Copy score_types nếu chọn
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

            // Load data cho bước 4
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
        // Lớp trong năm đích
        $this->targetClasses = CatechismClass::with('gradeLevel')
            ->where('school_year_id', $this->targetNamHocId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level_id']);

        // Lớp trong năm nguồn (để chọn lấy danh sách HS)
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

    /**
     * Khi chọn lớp nguồn → load học sinh chưa có lớp trong năm đích
     */
    public function updatedSourceClassId(): void
    {
        $this->selectedStudents  = [];
        $this->availableStudents = collect();

        if (!$this->sourceClassId || !$this->targetNamHocId) return;

        // Lấy IDs học sinh đã có lớp trong năm đích
        $assignedStudentIds = StudentsClass::whereIn(
            'class_id',
            CatechismClass::where('school_year_id', $this->targetNamHocId)->pluck('id')
        )->pluck('student_id');

        // Học sinh active trong lớp nguồn, chưa có lớp trong năm đích
        $this->availableStudents = StudentNew::whereHas('studentsClass', function ($q) {
            $q->where('class_id', $this->sourceClassId)
                ->where('status', StudentsClass::STATUS_ENROLLED);
        })
            ->whereNotIn('id', $assignedStudentIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'saint_id']);
    }

    /**
     * Toggle chọn tất cả
     */
    public function toggleSelectAll(): void
    {
        if (count($this->selectedStudents) === $this->availableStudents->count()) {
            $this->selectedStudents = [];
        } else {
            $this->selectedStudents = $this->availableStudents->pluck('id')->map(fn($id) => (string) $id)->toArray();
        }
    }

    /**
     * Lưu xếp lớp
     */
    public function saveAssignment(): void
    {
        $this->requireManager();

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

            // Reset để xếp lớp tiếp
            $this->selectedStudents  = [];
            $this->sourceClassId     = null;
            $this->availableStudents = collect();

            // Reload để cập nhật danh sách
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
