<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Student;

/**
 * Student Detail Component - Optimized for Catechism Students
 * 
 * ✅ CHỈ HIỂN THỊ THÔNG TIN HỌC SINH GIÁO LÝ (bảng student)
 * ❌ KHÔNG bao gồm: Rước lễ, Xức dầu, Qua đời, Trú quán (thuộc parishioners)
 * 
 * Features:
 * - View student profile with 4 tabs: Basic, Baptism, More Power, Other
 * - Display learning history from students_class table
 * - Export to PDF and Word
 * - Print profile
 * - Edit and delete actions (authorized users)
 */
class StudentDetail extends BaseComponent
{
    // ==================== PROPERTIES ====================

    /** @var int Student ID */
    public $studentId;

    /** @var array Student data for display */
    public $studentData = [];

    /** @var bool Loading state */
    public $isLoading = true;

    /** @var string Active tab - CHỈ 4 TAB */
    public $activeTab = 'basic'; // basic, baptism, more_power, other

    /** @var bool Disable pagination */
    protected $usePagination = false;

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return [
            'activeTab' => ['except' => 'basic', 'as' => 'tab'],
        ];
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'loadStudentData',
        'studentUpdated' => 'loadStudentData',
    ];

    // ==================== LIFECYCLE HOOKS ====================

    public function mount($id = null): void
    {
        $this->studentId = (int) $id;

        if ($this->studentId <= 0) {
            session()->flash('error', 'ID học sinh không hợp lệ.');
            $this->redirectRoute('classes.index');
            return;
        }

        parent::mount();
    }

    protected function loadInitialData(): void
    {
        $this->loadStudentData();
    }
    
    // ==================== DATA LOADING ====================

    /**
     * ✅ Load ONLY student data (catechism student info)
     */
    public function loadStudentData(): void
    {
        try {
            $this->isLoading = true;

            $student = Student::with([
                // Parish structure
                'diocese:id,name',
                'deanery:id,name',
                'parish:id,name',
                'paidRelation:id,name',

                // Basic references
                'holyRelation:id,name',
                'ethnicRelation:id,name',
                'careerRelation:id,name',
                'levelRelation:id,name',
                'positionRelation:id,name',
                'languageRelation:id,name',

                // ✅ Classes history (many-to-many với pivot data)
                'lops' => function ($query) {
                    $query->select('lop.id', 'lop.name', 'lop.symbol', 'lop.schoolyear')
                        ->with('schoolYear:id,name')
                        ->orderByDesc('students_class.created_at');
                },

                // ✅ BAPTISM - Student có đầy đủ
                'baptismGiver:id,name',
                'baptismSponsor:id,name',
                'baptismDiocese:id,name',
                'baptismDeanery:id,name',
                'baptismParish:id,name',

                // ✅ MORE POWER - Student có đầy đủ
                'morePowerGiver:id,name',
                'morePowerSponsor:id,name',
                'morePowerDiocese:id,name',
                'morePowerDeanery:id,name',
                'morePowerParish:id,name',
            ])->findOrFail($this->studentId);

            $this->checkViewPermission($student);
            $this->studentData = $this->mapStudentData($student);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->logError($e, 'Student not found', ['student_id' => $this->studentId]);
            session()->flash('error', 'Không tìm thấy học sinh');
            $this->redirectRoute('classes.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to load student data', ['student_id' => $this->studentId]);
            session()->flash('error', 'Có lỗi xảy ra khi tải thông tin học sinh');
        } finally {
            $this->isLoading = false;
        }
    }

    protected function checkViewPermission(Student $student): void
    {
        if ($this->isAdmin) {
            return;
        }

        if ($this->isDecen) {
            if ($student->pid != $this->parishId) {
                abort(403, 'Bạn không có quyền xem học sinh này');
            }
            return;
        }

        abort(403, 'Không có quyền truy cập');
    }

    /**
     * ✅ Map ONLY fields that exist in student table
     */
    protected function mapStudentData(Student $student): array
    {
        return [
            // ========== BASIC INFO ==========
            'id' => $student->id,
            'code' => $student->mahv ?? 'Chưa có mã',

            // Personal Info
            'last_name' => $student->last_name ?? '',
            'name' => $student->name ?? '',
            'full_name' => $student->full_name,
            'sex' => $student->sex,
            'sex_label' => $student->sex_label,
            'birthday' => $student->birthday_text,
            // 'phone' => $student->phone ? '0' . $student->phone : '',
            'phone' => $student->phone_number ?? '',
            'email' => $student->email ?? '',
            'cccd' => $student->cccd ?? '',

            // ✅ Address - CHỈ NGUYÊN QUÁN (student table only has origin)
            'origin' => $student->origin ?? '',
            'ward' => $student->ward ?? '',
            'province' => $student->province ?? '',

            // Family
            'father' => $student->father ?? '',
            'mother' => $student->mother ?? '',

            // ========== PARISH & CLASS ==========
            'diocese' => $student->diocese->name ?? '',
            'deanery' => $student->deanery->name ?? '',
            'parish' => $student->parish->name ?? '',
            'paid' => $student->paidRelation->name ?? '',

            // Current class
            'lop_name' => $this->getCurrentClassName($student),

            // ✅ Class history (từ students_class table)
            'class_history' => $this->getClassHistory($student),

            // ========== EDUCATION ==========
            'holy' => $student->holy,
            'holy_name' => $student->holyRelation->name ?? '',

            // Career & Education
            'ethnic' => $student->ethnicRelation->name ?? '',
            'career' => $student->careerRelation->name ?? '',
            'level' => $student->levelRelation->name ?? '',
            'position' => $student->positionRelation->name ?? '',
            'language' => $student->languageRelation->name ?? '',
            'professional_level' => $student->professional_level ?? '',

            // ========== BAPTISM (Rửa tội) ==========
            'baptism_date' => $student->baptism_date ? $student->baptism_date->format('d/m/Y') : '',
            'baptism_number' => $student->baptism_number ?? '',
            'baptism_giver' => $student->baptismGiver->name ?? '',
            'baptism_sponsor' => $student->baptismSponsor->name ?? '',
            'baptism_diocese' => $student->baptismDiocese->name ?? '',
            'baptism_deanery' => $student->baptismDeanery->name ?? '',
            'baptism_parish' => $student->baptismParish->name ?? '',
            'baptism_full_location' => $this->buildFullLocation(
                $student->baptismParish->name ?? '',
                $student->baptismDeanery->name ?? '',
                $student->baptismDiocese->name ?? ''
            ),

            // ========== MORE POWER (Thêm Sức) ==========
            'more_power_date' => $student->more_power_date ? $student->more_power_date->format('d/m/Y') : '',
            'more_power_number' => $student->more_power_number ?? '',
            'more_power_giver' => $student->morePowerGiver->name ?? '',
            'more_power_sponsor' => $student->morePowerSponsor->name ?? '',
            'more_power_diocese' => $student->morePowerDiocese->name ?? '',
            'more_power_deanery' => $student->morePowerDeanery->name ?? '',
            'more_power_parish' => $student->morePowerParish->name ?? '',
            'more_power_full_location' => $this->buildFullLocation(
                $student->morePowerParish->name ?? '',
                $student->morePowerDeanery->name ?? '',
                $student->morePowerDiocese->name ?? ''
            ),

            // ========== OTHER INFO ==========
            'promise_day' => $student->promise_day ? $student->promise_day->format('d/m/Y') : '',
            'note' => $student->note ?? '',

            // Status
            'status' => $student->status,
            'status_label' => $student->status_label,
            'status_badge_class' => $student->getStatusBadgeClass(),

            // Timestamps
            'created_at' => $student->created_at ? $student->created_at->format('d/m/Y H:i') : '',
            'updated_at' => $student->updated_at ? $student->updated_at->format('d/m/Y H:i') : '',
        ];
    }

    /**
     * ✅ Get current active class name
     */
    protected function getCurrentClassName(Student $student): string
    {
        if ($student->relationLoaded('lops') && $student->lops->isNotEmpty()) {
            // Lấy lớp có status active trong pivot table
            $activeLop = $student->lops->first(function ($lop) {
                return $lop->pivot->status == 1;
            });

            return $activeLop ? $activeLop->name : ($student->lops->first()->name ?? '');
        }

        return '';
    }

    /**
     * ✅ NEW: Get class learning history
     * Lấy lịch sử học tập từ bảng students_class
     */
    protected function getClassHistory(Student $student): array
    {
        if (!$student->relationLoaded('lops') || $student->lops->isEmpty()) {
            return [];
        }

        return $student->lops->map(function ($lop) {
            return [
                'class_name' => $lop->name,
                'class_symbol' => $lop->symbol,
                'school_year' => $lop->schoolYear->name ?? '',
                'status' => $lop->pivot->status,
                'status_label' => $lop->pivot->status == 1 ? 'Đang học' : 'Đã hoàn thành',
                'status_class' => $lop->pivot->status == 1
                    ? 'bg-green-100 text-green-700'
                    : 'bg-slate-100 text-slate-600',
                'joined_at' => $lop->pivot->created_at
                    ? $lop->pivot->created_at->format('d/m/Y')
                    : '',
            ];
        })->toArray();
    }
    
    // ==================== TAB NAVIGATION ====================

    /**
     * ✅ Switch tab - CHỈ 4 TAB
     */
    public function switchTab(string $tab): void
    {
        $allowedTabs = ['basic', 'baptism', 'more_power', 'other'];

        if (in_array($tab, $allowedTabs)) {
            $this->activeTab = $tab;
        }
    }

    // ==================== ACTIONS ====================

    public function edit(): void
    {
        if (!$this->isAdmin && !$this->isDecen) {
            session()->flash('error', 'Bạn không có quyền chỉnh sửa');
            return;
        }

        $this->redirect(route('students.edit', ['id' => $this->studentId]));
    }

    public function delete(): void
    {
        try {
            $this->requireManager();

            $student = Student::find($this->studentId);

            if (!$student) {
                session()->flash('error', 'Không tìm thấy học sinh');
                return;
            }

            if ($this->isDecen && $student->pid != $this->parishId) {
                abort(403, 'Bạn không có quyền xóa học sinh này');
            }

            $student->delete();

            session()->flash('message', 'Đã xóa học sinh thành công');
            $this->redirect(route('classes.index'));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to delete student', ['student_id' => $this->studentId]);
            session()->flash('error', 'Có lỗi xảy ra khi xóa học sinh');
        }
    }

    public function printProfile(): void
    {
        $this->dispatch('print-profile');
    }

    public function exportPDF(): void
    {
        try {
            $this->redirect(route('student.export-pdf', ['id' => $this->studentId]));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to export PDF', ['student_id' => $this->studentId]);
            session()->flash('error', 'Có lỗi xảy ra khi xuất file PDF');
        }
    }

    public function exportLyLichCanhan(): void
    {
        try {
            $this->redirect(route('student.export-lylich', ['id' => $this->studentId]));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to export lý lịch', ['student_id' => $this->studentId]);
            session()->flash('error', 'Có lỗi xảy ra khi xuất file Word');
        }
    }

    public function exportBiTich(): void
    {
        try {
            $this->redirect(route('student.export-bitich', ['id' => $this->studentId]));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to export bí tích', ['student_id' => $this->studentId]);
            session()->flash('error', 'Có lỗi xảy ra khi xuất file Word');
        }
    }

    // ==================== HELPER METHODS ====================

    protected function buildFullLocation(string $parish, string $deanery, string $diocese): string
    {
        $parts = array_filter([$parish, $deanery, $diocese]);
        return implode(', ', $parts);
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.student.student-detail', [
            'student' => $this->studentData,
            'isLoading' => $this->isLoading,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
