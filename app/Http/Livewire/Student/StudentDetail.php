<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\StudentNew;

/**
 * Student Detail Component - Dùng model StudentNew
 *
 * Fields thực tế từ StudentNew:
 * - student_code, qr_token, avatar_path
 * - parishioner_id, parish_id, parish_group_id, saint_id
 * - first_name, last_name, father_name, mother_name
 * - birthday, gender, phone, email, is_active, note
 *
 * Relationships: parish, parishioner, parishGroup, saint, classes (many-to-many → CatechismClass)
 *
 * Features:
 * - Xem hồ sơ với 2 tab: Cơ bản, Lịch sử học tập
 * - Export PDF / Word
 * - In hồ sơ
 * - Chỉnh sửa & xóa (có phân quyền)
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
    public $activeTab = 'basic'; // basic | history

    /** @var bool Tắt pagination */
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
        'refresh'        => 'loadStudentData',
        'studentUpdated' => 'loadStudentData',
    ];

    // ==================== LIFECYCLE ====================

    public function mount($id = null): void
    {
        dd($id);
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

    public function loadStudentData(): void
    {
        try {
            $this->isLoading = true;

            $student = StudentNew::with([
                'parish:id,name',
                'parishGroup:id,name',
                'saint:id,name',
                'parishioner',

                // Lịch sử lớp học (many-to-many qua students_class)
                'classes' => function ($query) {
                    $query->select(
                        'catechism_classes.id',
                        'catechism_classes.name',
                        'catechism_classes.symbol',
                        'catechism_classes.schoolyear'
                    )
                        ->with('schoolYear:id,name')
                        ->orderByDesc('students_class.created_at');
                },
            ])->findOrFail($this->studentId);

            $this->checkViewPermission($student);
            $this->studentData = $this->mapStudentData($student);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->logError($e, 'StudentNew not found', ['student_id' => $this->studentId]);
            session()->flash('error', 'Không tìm thấy học sinh');
            $this->redirectRoute('classes.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to load StudentNew data', ['student_id' => $this->studentId]);
            session()->flash('error', 'Có lỗi xảy ra khi tải thông tin học sinh');
        } finally {
            $this->isLoading = false;
        }
    }

    protected function checkViewPermission(StudentNew $student): void
    {
        if ($this->isAdmin) {
            return;
        }

        if ($this->isDecen) {
            if ($student->parish_id != $this->parishId) {
                abort(403, 'Bạn không có quyền xem học sinh này');
            }
            return;
        }

        abort(403, 'Không có quyền truy cập');
    }

    /**
     * Map StudentNew sang array dùng cho blade
     */
    protected function mapStudentData(StudentNew $student): array
    {
        return [
            // ========== ĐỊNH DANH ==========
            'id'           => $student->id,
            'student_code' => $student->student_code ?? 'Chưa có mã',
            'qr_token'     => $student->qr_token ?? '',
            'avatar_path'  => $student->avatar_path ?? '',

            // ========== THÔNG TIN CÁ NHÂN ==========
            'first_name'   => $student->first_name ?? '',
            'last_name'    => $student->last_name ?? '',
            'full_name'    => $student->full_name,  // accessor: last_name + first_name
            'gender'       => $student->gender,
            'gender_label' => match ((string) $student->gender) {
                '1', 'male', 'nam' => 'Nam',
                '0', 'female', 'nu' => 'Nữ',
                default => 'Chưa xác định',
            },
            'birthday' => $student->birthday?->format('d/m/Y') ?? '',
            'phone'    => $student->phone ?? '',
            'email'    => $student->email ?? '',

            // ========== GIA ĐÌNH ==========
            'father_name' => $student->father_name ?? '',
            'mother_name' => $student->mother_name ?? '',

            // ========== GIÁO XỨ ==========
            'parish'       => $student->parish->name ?? '',
            'parish_group' => $student->parishGroup->name ?? '',  // Giáo họ
            'saint_name'   => $student->saint->name ?? '',         // Thánh bổn mạng / Bậc thánh

            // ========== THÔNG TIN TỪ PARISHIONER (nếu có liên kết) ==========
            // Các thông tin bổ sung như địa chỉ, CCCD lấy từ parishioner
            'cccd'    => $student->parishioner?->cccd ?? '',
            'address' => $student->parishioner?->address ?? '',

            // ========== LỚP HỌC ==========
            'current_class' => $this->getCurrentClassName($student),
            'class_history' => $this->getClassHistory($student),

            // ========== TRẠNG THÁI ==========
            'is_active'          => $student->is_active,
            'status_label'       => $student->is_active ? 'Đang học' : 'Ngừng học',
            'status_badge_class' => $student->is_active
                ? 'bg-green-100 text-green-700'
                : 'bg-slate-200 text-slate-600',

            // ========== GHI CHÚ ==========
            'note' => $student->note ?? '',

            // ========== TIMESTAMPS ==========
            'created_at' => $student->created_at?->format('d/m/Y H:i') ?? '',
            'updated_at' => $student->updated_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    /**
     * Lấy tên lớp hiện tại (lớp mới nhất)
     */
    protected function getCurrentClassName(StudentNew $student): string
    {
        if ($student->relationLoaded('classes') && $student->classes->isNotEmpty()) {
            return $student->classes->first()->name ?? '';
        }

        return '';
    }

    /**
     * Lấy lịch sử học tập từ bảng students_class
     */
    protected function getClassHistory(StudentNew $student): array
    {
        if (!$student->relationLoaded('classes') || $student->classes->isEmpty()) {
            return [];
        }

        return $student->classes->map(function ($class) {
            return [
                'class_name'   => $class->name ?? '',
                'class_symbol' => $class->symbol ?? '',
                'school_year'  => $class->schoolYear->name ?? '',
                'joined_at'    => $class->pivot->created_at
                    ? $class->pivot->created_at->format('d/m/Y')
                    : '',
            ];
        })->toArray();
    }

    // ==================== TAB NAVIGATION ====================

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['basic', 'history'])) {
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

            $student = StudentNew::find($this->studentId);

            if (!$student) {
                session()->flash('error', 'Không tìm thấy học sinh');
                return;
            }

            if ($this->isDecen && $student->parish_id != $this->parishId) {
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

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.student.student-detail', [
            'student'   => $this->studentData,
            'isLoading' => $this->isLoading,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
