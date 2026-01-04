<?php

namespace App\Http\Livewire\Lop;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Block;
use App\Models\ClassTeacher;
use App\Models\Lop;
use App\Models\NamHoc;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class LopForm extends BaseComponent
{
    // ==================== ROUTE PARAMS ====================

    /** @var int|null ID của lớp (null = create mode) */
    public $classId = null;

    /** @var bool Edit mode flag */
    public $isEdit = false;

    // ==================== FORM DATA ====================

    /** @var array Form fields  */
    public $form = [
        'symbol' => '',
        'name' => '',
        'schoolyear' => '',
        'block' => '',
        'note' => '',
        'status' => 1,
    ];

    // ==================== TEACHER MANAGEMENT ====================

    /** @var int|null Danh sách teacher_id được chọn */
    public $mainTeacherId = null;

    /** @var array ID của giáo lý viên chủ nhiệm */
    public $assistantTeacherIds = [];

    // ==================== DROPDOWN DATA ====================

    /** @var array School years dropdown */
    public $schoolyears = [];

    /** @var array Danh sách khối (dynamic based on schoolyear) */
    public $blocks = [];

    /** @var array Danh sách giáo lý viên */
    public $teachers = [];

    // ==================== PROTECTED DATA ====================

    /** @var \App\Models\Lop|null Lớp model instance (for edit mode) */
    protected $lopModel = null;

    // ==================== VALIDATION ====================


    protected $rules = [
        'form.symbol' => 'required|string|max:50',
        'form.name' => 'required|string|max:255',
        'form.schoolyear' => 'required|integer|exists:nam_hoc,id',
        'form.block' => 'required|integer|exists:block,id',
        'form.note' => 'nullable|string|max:1000',
        'form.status' => 'required|integer|in:0,1',
    ];

    protected $messages = [
        'form.symbol.required' => 'Mã lớp là bắt buộc',
        'form.symbol.max' => 'Mã lớp không được quá 50 ký tự',
        'form.name.required' => 'Tên lớp là bắt buộc',
        'form.name.max' => 'Tên lớp không được quá 255 ký tự',
        'form.schoolyear.required' => 'Vui lòng chọn năm học',
        'form.schoolyear.exists' => 'Năm học không tồn tại',
        'form.block.required' => 'Vui lòng chọn khối',
        'form.block.exists' => 'Khối không tồn tại',
        'form.note.max' => 'Ghi chú không được quá 1000 ký tự',
    ];

    // ==================== LIFECYCLE ====================

    /**
     * Component initialization
     */

    public function mount($id = null)
    {
        $this->classId = $id ? (int) $id : null;
        $this->isEdit = !is_null($this->classId);

        parent::mount();

        $this->requireManager();
        $this->requireParishId();
    }

    /**
     * Load dữ liệu ban đầu (required by BaseComponent)
     */
    protected function loadInitialData(): void
    {
        $this->loadDropdownData();

        if ($this->isEdit) {
            $this->loadClassData();
        }
    }

    /**
     * Override validateUserAccess - Form này cần quyền manager
     */
    protected function validateUserAccess(): void
    {
        parent::validateUserAccess();

        // Component này cần quyền quản lý (Admin hoặc Decen)
        if (!$this->isAdmin && !$this->isDecen) {
            abort(403, 'Chỉ quản trị viên mới có quyền tạo/sửa lớp học');
        }
    }

    // ==================== DATA LOADING ====================

    /**
     * Load dropdown data (schoolyears, teachers)
     */
    private function loadDropdownData(): void
    {
        try {
            // Load năm học của giáo xứ
            $this->schoolyears = NamHoc::where('parish_id', $this->parish_id)
                ->orderBy('name', 'desc')
                ->pluck('name', 'id')
                ->toArray();

            if (empty($this->schoolyears)) {
                session()->flash('warning', 'Chưa có năm học nào. Vui lòng tạo năm học trước.');
            }

            // Load giáo lý viên (có thể filter theo parish nếu cần)
            $teacherQuery = Teacher::query();

            // Nếu không phải admin, chỉ load teachers của parish
            if (!$this->isAdmin && $this->parish_id) {
                $teacherQuery->where('pid', $this->parish_id);
            }

            $this->teachers = $teacherQuery
                ->where('status', 1)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading dropdown data');
            session()->flash('error', 'Không thể tải dữ liệu dropdown.');

            // Set empty defaults
            $this->schoolyears = [];
            $this->teachers = [];
        }
    }

    /**
     * Load class data for edit mode
     */
    private function loadClassData(): void
    {
        try {
            $this->lopModel = Lop::with(['teachers', 'blockRelation', 'schoolYear'])
                ->findOrFail($this->classId);

            // Authorization: Check if user can edit this class
            $this->validateClassOwnership();

            // Populate form
            $this->form = [
                'symbol' => $this->lopModel->symbol ?? '',
                'name' => $this->lopModel->name ?? '',
                'schoolyear' => $this->lopModel->schoolyear ?? '',
                'block' => $this->lopModel->block ?? '',
                'note' => $this->lopModel->note ?? '',
                'status' => $this->lopModel->status ?? 1,
            ];

            // Load giáo lý viên của lớp
            $this->loadClassTeachers();

            // Load blocks based on schoolyear
            if ($this->form['schoolyear']) {
                $this->loadBlocks($this->form['schoolyear']);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy lớp học này.');
            $this->logError($e, 'Class not found', ['class_id' => $this->classId]);
            $this->redirectRoute('ds-lop');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class data', ['class_id' => $this->classId]);
            session()->flash('error', 'Có lỗi khi tải thông tin lớp học.');
            $this->redirectRoute('ds-lop');
        }
    }

    /**
     * Load giáo lý viên đang dạy lớp này
     */
    private function loadClassTeachers(): void
    {
        if (!$this->lopModel || !$this->lopModel->relationLoaded('classTeachers')) {
            $this->mainTeacherId = null;
            $this->assistantTeacherIds = [];
            return;
        }

        $this->mainTeacherId = null;
        $this->assistantTeacherIds = [];

        foreach ($this->lopModel->classTeachers as $ct) {
            if ($ct->teacher && $ct->teacher->status == 1) {
                // Phân biệt chủ nhiệm vs phụ trách
                if ($ct->role == ClassTeacher::ROLE_CHU_NHIEM) {
                    $this->mainTeacherId = $ct->teacher_id;
                } else {
                    $this->assistantTeacherIds[] = $ct->teacher_id;
                }
            }
        }

        // Remove duplicates và convert to integers
        $this->assistantTeacherIds = array_values(array_unique(array_map('intval', $this->assistantTeacherIds)));
    }

    /**
     * Validate user can edit this class (parish ownership)
     */
    private function validateClassOwnership(): void
    {
        if (!$this->lopModel) {
            return;
        }

        // Admin có thể edit mọi lớp
        if ($this->isAdmin) {
            return;
        }

        // Decen chỉ có thể edit lớp của parish mình
        if ($this->isDecen) {
            if ($this->lopModel->pid != $this->parish_id) {
                abort(403, 'Bạn không có quyền chỉnh sửa lớp học này.');
            }
            return;
        }

        // Các trường hợp khác: không có quyền
        abort(403, 'Không có quyền chỉnh sửa lớp học này.');
    }

    /**
     * Load blocks based on schoolyear
     * 
     * @param int $schoolyearId
     */
    private function loadBlocks($schoolyearId): void
    {
        try {
            $this->blocks = Block::where('namhoc', $schoolyearId)
                ->where('pid', $this->parish_id)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();

            // Validate current block selection
            if ($this->form['block'] && !isset($this->blocks[$this->form['block']])) {
                $this->form['block'] = '';
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading blocks', [
                'schoolyear_id' => $schoolyearId
            ]);
            $this->blocks = [];
            session()->flash('warning', 'Không thể tải danh sách khối.');
        }
    }

// ==================== PROPERTY UPDATERS ====================

    /**
     * When schoolyear changes, reload blocks
     */
    public function updatedFormSchoolyear($value): void
    {
        // Sanitize
        $schoolyearId = is_numeric($value) ? (int) $value : null;

        if (!$schoolyearId) {
            $this->blocks = [];
            $this->form['block'] = '';
            return;
        }

        // Validate schoolyear exists
        try {
            $this->validateOnly('form.schoolyear');
        } catch (ValidationException $e) {
            $this->blocks = [];
            $this->form['block'] = '';
            session()->flash('warning', 'Năm học không hợp lệ.');
            return;
        }

        // Load blocks for this schoolyear
        $this->loadBlocks($schoolyearId);

        // Reset block selection
        $this->form['block'] = '';
    }

    /**
     * Khi thêm/bỏ giáo lý viên, validate chủ nhiệm
     */
    // public function updatedSelectedTeachers($value): void
    // {
    //     // Nếu chủ nhiệm bị xóa khỏi danh sách
    //     if ($this->mainTeacherId && !in_array($this->mainTeacherId, $this->selectedTeachers)) {
    //         $this->mainTeacherId = null;
    //     }
    // }

    // /**
    //  * Khi đổi chủ nhiệm, tự động thêm vào selectedTeachers
    //  */
    // public function updatedChuNhiemId($value): void
    // {
    //     if ($value && !in_array($value, $this->selectedTeachers)) {
    //         $this->selectedTeachers[] = (int) $value;
    //     }
    // }

    // ==================== FORM ACTIONS ====================

    /**
     * Save class (create or update)
     */
    public function save(): void
    {
        // Validate form
        try {
            $this->validate();
        } catch (ValidationException $e) {
            session()->flash('error', 'Vui lòng kiểm tra lại thông tin nhập vào.');
            $this->logError($e, 'Validation failed', ['form' => $this->form]);
            return;
        }

        // Additional business logic validation
        if (!$this->validateBusinessRules()) {
            return;
        }

        // Save with transaction
        DB::beginTransaction();

        try {
            if ($this->isEdit) {
                $this->updateClass();
                $message = 'Cập nhật lớp học thành công!';
            } else {
                $this->createClass();
                $message = 'Tạo lớp học thành công!';
            }

            DB::commit();

            session()->flash('success', $message);
            $this->redirectRoute('ds-lop');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Error saving class', [
                'is_edit' => $this->isEdit,
                'class_id' => $this->classId,
                'form' => $this->form,
            ]);

            session()->flash('error', 'Có lỗi xảy ra khi lưu lớp học. Vui lòng thử lại.');
        }
    }

    /**
     * Create new class
     * 
     * @throws \Exception
     */
    private function createClass(): void
    {
        // Add parish_id to form data
        $data = array_merge($this->form, [
            'pid' => $this->parish_id,
        ]);

        $class = Lop::create($data);

        // Sync teachers
        $this->syncTeachers($class);

        // Log activity (optional)
        Log::info('Class created', [
            'class_id' => $class->id,
            'user_id' => auth()->id(),
            'parish_id' => $this->parish_id,
        ]);

        // TODO: Sync teachers, schedule, etc. if needed
    }

    /**
     * Update existing class
     * 
     * @throws \Exception
     */
    private function updateClass(): void
    {
        if (!$this->lopModel) {
            throw new \Exception('Lớp học không tồn tại');
        }

        // Re-validate ownership before update
        $this->validateClassOwnership();

        // Update class
        $this->lopModel->update($this->form);

        // Sync teachers
        $this->syncTeachers($this->lopModel);

        // Log activity (optional)
        Log::info('Class updated', [
            'class_id' => $this->lopModel->id,
            'user_id' => auth()->id(),
            'parish_id' => $this->parish_id,
            'changes' => $this->lopModel->getChanges(),
        ]);

        // TODO: Sync teachers, schedule, etc. if needed
    }

    /**
     * Sync giáo lý viên với lớp học
     * 
     * @param Lop $class
     */
    private function syncTeachers(Lop $class): void
    {
        if (empty($this->selectedTeachers)) {
            // Xóa tất cả giáo lý viên
            ClassTeacher::where('lop_id', $class->id)->delete();
            return;
        }

        // Xóa giáo lý viên cũ không còn trong danh sách
        ClassTeacher::where('lop_id', $class->id)
            ->whereNotIn('teacher_id', $this->selectedTeachers)
            ->delete();

        // Thêm/cập nhật giáo lý viên
        foreach ($this->selectedTeachers as $teacherId) {
            $role = ($teacherId == $this->mainTeacherId)
                ? ClassTeacher::ROLE_CHU_NHIEM
                : ClassTeacher::ROLE_PHO;

            ClassTeacher::updateOrCreate(
                [
                    'lop_id' => $class->id,
                    'teacher_id' => $teacherId,
                    'namhoc_id' => $this->form['schoolyear'],
                ],
                [
                    'role' => $role,
                    'status' => 1,
                ]
            );
        }
    }


    /**
     * Validate business rules beyond basic validation
     * 
     * @return bool
     */
    private function validateBusinessRules(): bool
    {
        // Check duplicate symbol in same schoolyear
        $duplicate = Lop::where('symbol', $this->form['symbol'])
            ->where('schoolyear', $this->form['schoolyear'])
            ->where('pid', $this->parish_id)
            ->when($this->isEdit, function ($q) {
                $q->where('id', '!=', $this->classId);
            })
            ->exists();

        if ($duplicate) {
            session()->flash('error', 'Mã lớp đã tồn tại trong năm học này.');
            return false;
        }

        // Check block belongs to selected schoolyear
        $blockValid = Block::where('id', $this->form['block'])
            ->where('namhoc', $this->form['schoolyear'])
            ->where('pid', $this->parish_id)
            ->exists();

        if (!$blockValid) {
            session()->flash('error', 'Khối không thuộc năm học đã chọn.');
            return false;
        }

        // Validate chủ nhiệm phải trong danh sách teachers
        if ($this->mainTeacherId && !in_array($this->mainTeacherId, $this->selectedTeachers)) {
            session()->flash('error', 'Giáo lý viên chủ nhiệm phải được chọn trong danh sách giáo lý viên.');
            return false;
        }

        return true;
    }

    /**
     * Cancel and go back to list
     */
    public function cancel(): void
    {
        $this->redirectRoute('ds-lop');
    }

    // ==================== RENDER ====================

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.lop.lop-form', [
            'parishId' => $this->parish_id,
            'pageTitle' => $this->isEdit ? 'Chỉnh sửa lớp học' : 'Tạo lớp học mới',
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
