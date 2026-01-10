<?php

namespace App\Http\Livewire\Teacher;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Holymanagement;
use App\Models\Teacher;
use App\Models\HolyName;
use App\Models\Parish;
use App\Models\ParishChild;
use Illuminate\Support\Facades\DB;

/**
 * Component quản lý Giáo viên (CRUD)
 * 
 * Features:
 * - List giáo viên với pagination
 * - Create/Edit/Delete giáo viên
 * - Toggle status
 * - Search theo tên và số điện thoại
 * - Select tên thánh và giáo họ
 */
class TeacherManager extends BaseComponent
{
    // ==================== FORM STATE ====================

    /** @var bool Hiển thị form create/edit */
    public $showForm = false;

    /** @var int|null ID của giáo viên đang edit (null = create mode) */
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    /** @var int|null ID tên thánh */
    public $holy_id;

    /** @var string Tên giáo viên */
    public $name;

    /** @var int|null ID giáo họ */
    public $paid;

    /** @var string|null Ngày sinh */
    public $birthday;

    /** @var string|null Số điện thoại */
    public $phoneNumber;

    /** @var string|null Ghi chú */
    public $note;

    /** @var int Trạng thái (1 = active, 0 = inactive) */
    public $status = 1;

    // ==================== DATA ====================

    /** @var \Illuminate\Pagination\LengthAwarePaginator Danh sách giáo viên */
    protected $teachers;

    /** @var \Illuminate\Support\Collection Danh sách tên thánh */
    public $holyNames;

    /** @var \Illuminate\Support\Collection Danh sách giáo họ */
    public $parishChildren;

    // ==================== VALIDATION ====================

    protected $formRules = [
        // 'holy_id' => 'nullable|integer|exists:holy_names,id',
        'name' => 'required|string|max:255',
        // 'paid' => 'nullable|integer|exists:parish_child,id',
        'birthday' => 'nullable|date|before:today',
        'phoneNumber' => 'nullable|string|max:20',
        'note' => 'nullable|string|max:500',
        'status' => 'required|boolean',
    ];

    protected $messages = [
        // 'holy_id.exists' => 'Tên thánh không hợp lệ',
        'name.required' => 'Vui lòng nhập tên giáo viên',
        'name.max' => 'Tên giáo viên không được quá 255 ký tự',
        // 'paid.exists' => 'Giáo họ không hợp lệ',
        'birthday.date' => 'Ngày sinh không hợp lệ',
        'birthday.before' => 'Ngày sinh phải trước ngày hôm nay',
        'phoneNumber.max' => 'Số điện thoại không được quá 20 ký tự',
        'note.max' => 'Ghi chú không được quá 500 ký tự',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return [
            'search' => ['except' => ''],
            'perPage' => ['except' => 15],
            'page' => ['except' => 1],
            'showForm' => ['except' => false],
        ];
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'teacherCreated' => 'loadTeachers',
        'teacherUpdated' => 'loadTeachers',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        parent::mount();

        // Yêu cầu quyền quản trị (Admin hoặc Decen)
        $this->requireManager();

        // Bắt buộc phải có parishId
        $this->requireParishId();
    }

    /**
     * Load dữ liệu ban đầu (implement từ BaseComponent)
     */
    protected function loadInitialData(): void
    {
        $this->loadHolyNames();
        $this->loadParishChildren();
        $this->loadTeachers();
    }

    /**
     * Load danh sách tên thánh
     */
    protected function loadHolyNames(): void
    {
        try {
            $this->holyNames = Holymanagement::query()
                ->when($this->search, function ($q) {
                    $q->where('name', 'like', '%' . trim($this->search) . '%');
                })
                ->orderBy('name')
                ->pluck('name', 'id');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading holy names');
            $this->holyNames = collect();
        }
    }

    /**
     * Load danh sách giáo họ thuộc giáo xứ
     */
    protected function loadParishChildren(): void
    {
        try {
            $this->parishChildren = Parish::where('pid', $this->parishId)
                ->active()
                ->orderBy('name')
                ->pluck('name', 'id');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parish children');
            $this->parishChildren = collect();
        }
    }

    /**
     * Load danh sách giáo viên với pagination
     */
    public function loadTeachers(): void
    {
        try {
            $query = Teacher::ofParish($this->parishId)
                ->with(['holy', 'parishChild'])
                ->orderBy('name', 'asc');

            // Apply search filter
            if (!empty($this->search)) {
                $query->search($this->search);
            }

            $this->teachers = $query->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading teachers');
            session()->flash('error', 'Có lỗi khi tải danh sách giáo viên');

            $this->teachers = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $this->perPage,
                $this->page ?? 1
            );
        }
    }

    // ==================== PROPERTY UPDATERS ====================

    /**
     * Khi search thay đổi, reload data
     */
    public function updatedSearch(): void
    {
        parent::updatedSearch(); // Reset page
        $this->loadTeachers();    // Reload data
    }

    /**
     * Khi perPage thay đổi, reload data
     */
    public function updatedPerPage(): void
    {
        parent::updatedPerPage(); // Sanitize + validate + reset page
        $this->loadTeachers();     // Reload data
    }

    // ==================== CRUD ACTIONS ====================

    /**
     * Mở form tạo mới
     */
    public function create(): void
    {
        $this->requireManager();
        $this->resetForm();
        $this->showForm = true;
    }

    /**
     * Mở form edit
     */
    public function edit(int $id): void
    {
        $this->requireManager();

        try {
            $teacher = Teacher::where('pid', $this->parishId)
                ->findOrFail($id);

            $this->editingId = $teacher->id;
            $this->holy_id = $teacher->holy_id;
            $this->name = $teacher->name;
            $this->paid = $teacher->paid;
            $this->birthday = $teacher->birthday?->format('Y-m-d');
            $this->phoneNumber = $teacher->phone_number;
            $this->note = $teacher->note;
            $this->status = $teacher->status;

            $this->showForm = true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy giáo viên này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading teacher for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin giáo viên');
        }
    }

    /**
     * Lưu (create hoặc update)
     */
    public function save(): void
    {
        $this->requireManager();

        // Validate form data
        $this->validate($this->formRules, $this->messages);

        try {
            DB::beginTransaction();

            // Check trùng tên và số điện thoại trong cùng xứ
            if ($this->phoneNumber) {
                $exists = Teacher::where('pid', $this->parishId)
                    ->where('name', $this->name)
                    ->where('phone_number', $this->phoneNumber)
                    ->when($this->editingId, function ($q) {
                        $q->where('id', '!=', $this->editingId);
                    })
                    ->exists();

                if ($exists) {
                    DB::rollBack();
                    session()->flash('error', 'Giáo viên với tên và số điện thoại này đã tồn tại');
                    return;
                }
            }

            Teacher::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'holy_id' => $this->holy_id ?: null,
                    'name' => $this->name,
                    'paid' => $this->paid ?: 0,
                    'birthday' => $this->birthday ?: null,
                    'phone_number' => $this->phoneNumber ?: null,
                    'note' => $this->note ?: null,
                    'status' => $this->status,
                    'pid' => $this->parishId,
                    'did' => 0,  // Default value
                    'deid' => 0, // Default value
                ]
            );

            DB::commit();

            $message = $this->editingId
                ? 'Cập nhật giáo viên thành công'
                : 'Thêm giáo viên mới thành công';

            session()->flash('message', $message);

            $this->resetForm();
            $this->loadTeachers();

            // Emit event
            $this->emit($this->editingId ? 'teacherUpdated' : 'teacherCreated');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Error saving teacher', [
                'editing_id' => $this->editingId,
                'name' => $this->name,
            ]);

            session()->flash('error', 'Có lỗi khi lưu giáo viên. Vui lòng thử lại.');
        }
    }

    /**
     * Toggle status giáo viên
     */
    public function toggleStatus(int $id): void
    {
        $this->requireManager();

        try {
            $teacher = Teacher::where('pid', $this->parishId)
                ->findOrFail($id);

            $teacher->update(['status' => !$teacher->status]);

            $message = $teacher->status
                ? 'Đã kích hoạt giáo viên'
                : 'Đã vô hiệu hóa giáo viên';

            session()->flash('message', $message);

            $this->loadTeachers();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy giáo viên này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling teacher status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái giáo viên');
        }
    }

    /**
     * Xóa giáo viên
     */
    public function delete(int $id): void
    {
        // Chỉ Admin mới được xóa
        $this->requireAdmin();

        try {
            DB::beginTransaction();

            $teacher = Teacher::where('pid', $this->parishId)
                ->findOrFail($id);

            // Check nếu giáo viên đang được phân công dạy lớp
            // $hasAssignments = \App\Models\Assignment::where('teacher_id', $teacher->id)
            //     ->exists();

            // if ($hasAssignments) {
            //     DB::rollBack();
            //     session()->flash('error', 'Không thể xóa giáo viên đang phân công dạy lớp');
            //     return;
            // }

            $teacher->delete();

            DB::commit();

            session()->flash('message', 'Đã xóa giáo viên thành công');

            $this->loadTeachers();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            session()->flash('error', 'Không tìm thấy giáo viên này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting teacher', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi xóa giáo viên');
        }
    }

    // ==================== FORM HELPERS ====================

    /**
     * Đóng modal
     */
    public function closeModal(): void
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
    }

    /**
     * Reset form về trạng thái mặc định
     */
    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'holy_id',
            'name',
            'paid',
            'birthday',
            'phoneNumber',
            'note',
        ]);

        $this->status = 1; // Default active
        $this->showForm = false;

        // Clear validation errors
        $this->resetValidation();
    }

    /**
     * Cancel và đóng form
     */
    public function cancel(): void
    {
        $this->resetForm();
    }

    /**
     * Override handleRefresh để reload data
     */
    public function handleRefresh(): void
    {
        $this->resetPage();
        $this->loadTeachers();
        session()->flash('message', 'Đã làm mới danh sách');
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.teacher.teacher-manager', [
            'teachers' => $this->teachers,
            'holyNames' => $this->holyNames,
            'parishChildren' => $this->parishChildren,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
