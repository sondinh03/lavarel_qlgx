<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

/**
 * Student Detail Component
 * Hiển thị thông tin chi tiết hồ sơ học sinh
 */
class StudentDetail extends BaseComponent
{
    // ==================== PROPERTIES ====================

    /** @var int Student ID */
    public $studentId;

    /** @var Student Student model instance */
    public $studentTest = null;

    /** @var array Student data for display */
    public $studentData = [];

    /** @var bool Loading state */
    public $isLoading = true;

    /** @var string Active tab */
    public $activeTab = 'basic'; // basic, baptism, more_power, other

    // ==================== QUERY STRING ====================

    protected $queryString = [
        'activeTab' => ['except' => 'basic'],
    ];

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
        $this->studentId = (int) $id;

        if ($this->studentId <= 0) {
            session()->flash('error', 'ID học sinh không hợp lệ.');
            $this->redirectRoute('classes.index');
            return;
        }

        parent::mount();
    }

    /**
     * Load initial data
     */
    protected function loadInitialData(): void
    {
        $this->loadStudentData();
    }
    
    // ==================== DATA LOADING ====================

    /**
     * Load student data from database
     */
    public function loadStudentData(): void
    {
        try {
            $this->isLoading = true;

            // DEBUG: Check student ID
            Log::info('🔍 StudentDetail: Loading data', [
                'student_id' => $this->studentId,
                'parish_id' => $this->parishId,
                'is_admin' => $this->isAdmin,
                'is_decen' => $this->isDecen,
            ]);

            // $student = Student::with([
            //     // ===== Quan hệ hành chính / tổ chức (nếu đã có bảng) =====
            //     'parish:id,pname',              // students.pid → parishes.id
            //     'diocese:id,dname',             // students.did → dioceses.id
            //     'deanery:id,deanery_name',      // students.deid → deaneries.id

            //     'lop:id,name',              // belongsToMany qua student_class

            //     // ===== Quan hệ bậc thánh / trạng thái =====
            //     'holyRelation:id,name',          // students.holy → holies.id
            // ])->findOrFail($this->studentId);

            $student = Student::findOrFail($this->studentId);

            // DEBUG: Check query result
            // Log::info('📊 StudentDetail: Query result', [
            //     'found' => $this->studentModel ? 'YES' : 'NO',
            //     'student_id' => $this->studentModel?->id ?? 'NULL',
            //     'student_name' => $this->studentModel?->name ?? 'NULL',
            // ]);

            if (!$student) {
                dump('chạy if');
                session()->flash('error', 'Không tìm thấy học sinh');
                return;
            }

            // Check authorization
            $this->checkViewPermission($student);

            // Map student data to array for display
            $this->studentData = $this->mapStudentData($student);
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
            'id' => optional($student)->id,
            'code' => optional($student)->mahv ?? 'Chưa có',

            // Basic Info
            'last_name' => optional($student)->last_name ?? '',
            'name' => optional($student)->name ?? '',
            'full_name' => trim((optional($student)->holy_name ?? '') . ' ' . (optional($student)->last_name ?? '') . ' ' . (optional($student)->name ?? '')),
            'sex' => optional($student)->sex,
            'sex_label' => optional($student)->sex_label,
            'birthday' => optional($student)->birthday ?? '',
            'phone' => optional($student)->phone ?? '',
            'email' => optional($student)->email ?? '',

            // Address
            'origin' => optional($student)->origin ?? '',
            'ward' => optional($student)->ward ?? '',
            'province' => optional($student)->province ?? '',

            // Family
            'father' => optional($student)->father ?? '',
            'mother' => optional($student)->mother ?? '',
            'cccd' => optional($student)->cccd ?? '',

            // Class & Parish
            'parish' => optional($student)->parish->pname ?? '',
            'diocese' => optional($student)->diocese->dname ?? '',
            'deanery' => optional($student)->deanery->deanery_name ?? '',
            'lop' => optional($student)->lop->name ?? '',
            'holy' => optional($student)->holy,
            'holy_name' => optional($student)->holy_name ?? '',
            'holy_label' => $this->getHolyLabel($student->holy),

            // Baptism Info
            'baptism_date' => optional($student)->baptism_date ? optional($student)->baptism_date->format('d/m/Y') : '',
            'baptism_number' => optional($student)->baptism_number ?? '',
            'baptism_giver' => optional($student)->baptismGiver->name ?? '',
            'baptism_sponsor' => optional($student)->baptismSponsor->name ?? '',
            'baptism_diocese' => optional($student)->baptismDiocese->dname ?? '',
            'baptism_deanery' => optional($student)->baptismDeanery->deanery_name ?? '',
            'baptism_parish' => optional($student)->baptismParish->pname ?? '',

            // More Power (Thêm Sức)
            'more_power_date' => optional($student)->more_power_date ? optional($student)->more_power_date->format('d/m/Y') : '',
            'more_power_number' => optional($student)->more_power_number ?? '',
            'more_power_giver' => optional($student)->morePowerGiver->name ?? '',
            'more_power_sponsor' => optional($student)->morePowerSponsor->name ?? '',
            'more_power_address' => optional($student)->more_power_address ?? '',
            'more_power_diocese' => optional($student)->morePowerDiocese->dname ?? '',
            'more_power_deanery' => optional($student)->morePowerDeanery->deanery_name ?? '',
            'more_power_parish' => optional($student)->morePowerParish->pname ?? '',

            // Promise & Other
            'promise_day' => optional($student)->promise_day ? optional($student)->promise_day->format('d/m/Y') : '',
            'note' => optional($student)->note ?? '',
            'status' => optional($student)->status,
            'status_label' => optional($student)->status_label,
            'status_badge_class' => optional($student)->getStatusBadgeClass(),

            // Timestamps
            'created_at' => optional($student)->created_at ? optional($student)->created_at->format('d/m/Y H:i') : '',
            'updated_at' => optional($student)->updated_at ? optional($student)->updated_at->format('d/m/Y H:i') : '',
        ];
    }
    
    // ==================== TAB NAVIGATION ====================

    /**
     * Switch active tab
     */
    public function switchTab(string $tab): void
    {
        $allowedTabs = ['basic', 'baptism', 'more_power', 'other'];

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

        // Redirect to edit route - adjust route name as needed
        $this->redirect(route('student.edit', ['id' => $this->studentId]));
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

            session()->flash('success', 'Đã xóa học sinh thành công');
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
        // You can implement print functionality here
        // Option 1: Open print dialog via JavaScript
        $this->dispatch('print-profile');

        // Option 2: Redirect to a print-friendly page
        // $this->redirect(route('student.print', ['id' => $this->studentId]));
    }

    /**
     * Export student data to PDF
     */
    public function exportPDF(): void
    {
        // Implement PDF export functionality
        // This would typically redirect to a controller method that generates PDF
        try {
            $this->redirect(route('student.export-pdf', ['id' => $this->studentId]));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to export PDF', [
                'student_id' => $this->studentId,
            ]);

            session()->flash('error', 'Có lỗi xảy ra khi xuất file PDF');
        }
    }
    
    // ==================== HELPER METHODS ====================

    /**
     * Get holy level label
     */
    protected function getHolyLabel(?int $holy): string
    {
        return match ($holy) {
            Student::HOLY_BAPTISM => 'Rửa tội',
            Student::HOLY_CONFIRMATION => 'Thêm sức',
            Student::HOLY_MARRIAGE => 'Hôn phối',
            default => 'Chưa có',
        };
    }

    /**
     * Get status badge class - used in view
     */
    public function getStatusBadgeClass(): string
    {
        return $this->studentData['status_badge_class'] ?? 'bg-gray-100 text-gray-800';
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
