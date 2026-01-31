<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Student;

/**
 * Student Detail Component
 * Hiển thị thông tin chi tiết hồ sơ học sinh
 * 
 * Features:
 * - View student profile with tabs
 * - Display all sacrament information
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

    /** @var string Active tab */
    public $activeTab = 'basic'; // basic, baptism, more_power, communion, other

    /** @var bool Disable pagination cho component này */
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

    /**
     * Mount component with student ID
     */
    public function mount($id = null): void
    {
        // Validate student ID
        $this->studentId = (int) $id;

        if ($this->studentId <= 0) {
            session()->flash('error', 'ID học sinh không hợp lệ.');
            $this->redirectRoute('classes.index');
            return;
        }

        parent::mount();
    }

    /**
     * Load initial data (required by BaseComponent)
     */
    protected function loadInitialData(): void
    {
        $this->loadStudentData();
    }
    
    // ==================== DATA LOADING ====================

    /**
     * Load student data from database with all relationships
     */
    public function loadStudentData(): void
    {
        try {
            $this->isLoading = true;

            // Load student với tất cả relationships cần thiết
            $student = Student::with([
                // Parish relationships
                'parish:id,name,diocese,deanerys',
                'parish.diocese:id,name',
                'parish.deanery:id,name',

                // Diocese và Deanery trực tiếp
                'diocese:id,name',
                'deanery:id,name',

                // // Giáo họ
                'paidRelation:id,name',

                // // Classes (many-to-many)
                'lops:id,name,symbol',

                // Holy
                'holyRelation:id,name',

                // Career & Education
                'ethnicRelation:id,name',
                'careerRelation:id,name',
                'levelRelation:id,name',
                'positionRelation:id,name',
                'languageRelation:id,name',

                // // Baptism relationships
                'baptismGiver:id,name',
                'baptismSponsor:id,name',
                'baptismDiocese:id,name',
                'baptismDeanery:id,name',
                'baptismParish:id,name',

                // // More Power relationships  
                'morePowerGiver:id,name',
                'morePowerSponsor:id,name',
                'morePowerDiocese:id,name',
                'morePowerDeanery:id,name',
                'morePowerParish:id,name',

                // // Communion relationships
                'communionGiver:id,name',
                'communionDiocese:id,name',
                'communionDeanery:id,name',
                'communionParish:id,name',

                // // Anoint relationship
                'anointGiver:id,name',
            ])->findOrFail($this->studentId);

            // Check authorization
            $this->checkViewPermission($student);

            // Map student data to array for display
            $this->studentData = $this->mapStudentData($student);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->logError($e, 'Student not found', [
                'student_id' => $this->studentId,
            ]);

            session()->flash('error', 'Không tìm thấy học sinh');
            $this->redirectRoute('classes.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to load student data', [
                'student_id' => $this->studentId,
            ]);

            session()->flash('error', 'Có lỗi xảy ra khi tải thông tin học sinh');
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Check if user has permission to view this student
     */
    protected function checkViewPermission(Student $student): void
    {
        // Admin can view all
        if ($this->isAdmin) {
            return;
        }

        // Decen can only view students in their parish
        if ($this->isDecen) {
            if ($student->pid != $this->parishId) {
                abort(403, 'Bạn không có quyền xem học sinh này');
            }
            return;
        }

        abort(403, 'Không có quyền truy cập');
    }

    /**
     * Map student model to display array
     */
    protected function mapStudentData(Student $student): array
    {
        return [
            // Basic identification
            'id' => $student->id,
            'code' => $student->mahv ?? 'Chưa có mã',

            // Personal Info - Sử dụng accessors từ Model
            'last_name' => $student->last_name ?? '',
            'name' => $student->name ?? '',
            'full_name' => $student->full_name, // Từ accessor
            'sex' => $student->sex,
            'sex_label' => $student->sex_label, // Từ accessor
            'birthday' => $student->birthday, // Đã được format bởi accessor
            'phone' => $student->phone ? '0' . $student->phone : '',
            'email' => $student->email ?? '',
            'cccd' => $student->cccd ?? '',

            // Address - Nguyên quán
            'origin' => $student->origin ?? '',
            'ward' => $student->ward ?? '',
            'province' => $student->province ?? '',

            // Address - Trú quán
            'residence' => $student->residence ?? '',
            'resi_ward' => $student->resi_ward ?? '',
            'resi_province' => $student->resi_province ?? '',

            // Family
            'father' => $student->father ?? '',
            'mother' => $student->mother ?? '',

            // Parish & Class
            'diocese' => $student->diocese->name ?? '',
            'deanery' => $student->deanery->name ?? '',
            'parish' => $student->parish->name ?? '',
            'paid' => $student->parish_children_name ?? '', // Từ accessor
            'lop_name' => $this->getCurrentClassName($student),

            // Holy & Education
            'holy' => $student->holy,
            'holy_name' => $student->holy_name, // Từ accessor
            'study' => $student->getStudyLabel(), // Từ Model method
            'new_convert' => $student->new_convert ? 'Có' : '',
            'married' => $student->married ? 'Có' : '',
            'statistical' => $student->statistical ? 'Có' : '',

            // Career & Education
            'ethnic' => $student->ethnicRelation->name ?? '',
            'career' => $student->careerRelation->name ?? '',
            'level' => $student->levelRelation->name ?? '',
            'position' => $student->positionRelation->name ?? '',
            'language' => $student->languageRelation->name ?? '',
            'professional_level' => $student->professional_level ?? '',

            // === BAPTISM (Rửa tội) ===
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

            // === MORE POWER (Thêm Sức) ===
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

            // === COMMUNION (Rước lễ) ===
            'communion_date' => $student->communion_date ? $student->communion_date->format('d/m/Y') : '',
            'communion_number' => $student->communion_number ?? '',
            'communion_giver' => $student->communionGiver->name ?? '',
            'communion_diocese' => $student->communionDiocese->name ?? '',
            'communion_deanery' => $student->communionDeanery->name ?? '',
            'communion_parish' => $student->communionParish->name ?? '',
            'communion_full_location' => $this->buildFullLocation(
                $student->communionParish->name ?? '',
                $student->communionDeanery->name ?? '',
                $student->communionDiocese->name ?? ''
            ),

            // === ANOINT (Xức dầu) ===
            'anoint_date' => $student->anoint_date ? $student->anoint_date->format('d/m/Y') : '',
            'anoint_status' => $student->getAnointStatusLabel(), // Từ Model method
            'anoint_giver' => $student->anointGiver->name ?? '',
            'anoint_note' => $student->anoint_note ?? '',

            // === DIE STATUS ===
            'die_status' => $student->die_status,
            'die_status_label' => $student->die_status == 1 ? 'Đã mất' : 'Còn sống',
            'die_time' => $student->die_time ? $student->die_time->format('d/m/Y') : '',
            'die_lottery' => $student->die_lottery ?? '',
            'die_death' => $student->die_death ?? '',
            'die_burial' => $student->die_burial ?? '',

            // Other info
            'promise_day' => $student->promise_day ? $student->promise_day->format('d/m/Y') : '',
            'note' => $student->note ?? '',

            // Status - Sử dụng methods từ Model
            'status' => $student->status,
            'status_label' => $student->status_label, // Từ accessor
            'status_badge_class' => $student->getStatusBadgeClass(), // Từ Model method

            // Timestamps
            'created_at' => $student->created_at ? $student->created_at->format('d/m/Y H:i') : '',
            'updated_at' => $student->updated_at ? $student->updated_at->format('d/m/Y H:i') : '',
        ];
    }

    /**
     * Get current class name for student
     * Student có thể có nhiều lớp (many-to-many), lấy lớp đang active
     */
    protected function getCurrentClassName(Student $student): string
    {
        // Nếu có relationship lops (many-to-many)
        if ($student->relationLoaded('lops') && $student->lops->isNotEmpty()) {
            // Lấy lớp đầu tiên hoặc lớp có status active
            $activeLop = $student->lops->first(function ($lop) {
                return $lop->pivot->status == 1;
            });

            return $activeLop ? $activeLop->name : ($student->lops->first()->name ?? '');
        }

        return '';
    }
    
    // ==================== TAB NAVIGATION ====================

    /**
     * Switch active tab
     */
    public function switchTab(string $tab): void
    {
        $allowedTabs = ['basic', 'baptism', 'more_power', 'communion', 'anoint', 'other'];

        if (in_array($tab, $allowedTabs)) {
            $this->activeTab = $tab;
        }
    }
    
    // ==================== ACTIONS ====================

    /**
     * Navigate to edit page
     */
    public function edit(): void
    {
        // Check permission
        if (!$this->isAdmin && !$this->isDecen) {
            session()->flash('error', 'Bạn không có quyền chỉnh sửa');
            return;
        }

        // Redirect to edit route
        $this->redirect(route('students.edit', ['id' => $this->studentId]));
    }

    /**
     * Delete student (soft delete)
     */
    public function delete(): void
    {
        try {
            // Check permission
            $this->requireManager();

            $student = Student::find($this->studentId);

            if (!$student) {
                session()->flash('error', 'Không tìm thấy học sinh');
                return;
            }

            // Decen can only delete students in their parish
            if ($this->isDecen && $student->pid != $this->parishId) {
                abort(403, 'Bạn không có quyền xóa học sinh này');
            }

            $student->delete();

            session()->flash('message', 'Đã xóa học sinh thành công');
            $this->redirect(route('classes.index'));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to delete student', [
                'student_id' => $this->studentId,
            ]);

            session()->flash('error', 'Có lỗi xảy ra khi xóa học sinh');
        }
    }

    /**
     * Print student profile
     */
    public function printProfile(): void
    {
        // Dispatch browser event to trigger print
        $this->dispatch('print-profile');
    }

    /**
     * Export student data to PDF
     */
    public function exportPDF(): void
    {
        try {
            // Redirect to PDF export route
            $this->redirect(route('student.export-pdf', ['id' => $this->studentId]));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to export PDF', [
                'student_id' => $this->studentId,
            ]);

            session()->flash('error', 'Có lỗi xảy ra khi xuất file PDF');
        }
    }

    /**
     * Export to Word - Lý lịch cá nhân
     */
    public function exportLyLichCanhan(): void
    {
        try {
            $this->redirect(route('student.export-lylich', ['id' => $this->studentId]));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to export lý lịch', [
                'student_id' => $this->studentId,
            ]);
            session()->flash('error', 'Có lỗi xảy ra khi xuất file Word');
        }
    }

    /**
     * Export to Word - Bí tích
     */
    public function exportBiTich(): void
    {
        try {
            $this->redirect(route('student.export-bitich', ['id' => $this->studentId]));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to export bí tích', [
                'student_id' => $this->studentId,
            ]);
            session()->flash('error', 'Có lỗi xảy ra khi xuất file Word');
        }
    }
    
    // ==================== HELPER METHODS ====================

    /**
     * Build full location string
     */
    protected function buildFullLocation(string $parish, string $deanery, string $diocese): string
    {
        $parts = array_filter([$parish, $deanery, $diocese]);
        return implode(', ', $parts);
    }
    
    // ==================== RENDER ====================

    /**
     * Render component
     */
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
