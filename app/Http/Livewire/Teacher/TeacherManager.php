<?php

namespace App\Http\Livewire\Teacher;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

/**
 * Component quản lý Giáo viên (CRUD)
 * 
 * Features:
 * - List giáo viên với pagination
 * - Create/Edit/Delete giáo viên
 * - Toggle status
 * - Search theo tên và số điện thoại
 * - Filter theo giáo xứ
 */
class TeacherManager extends BaseComponent
{
    // ==================== FORM STATE ====================

    /** @var bool Hiển thị form create/edit */
    public $showForm = false;

    /** @var int|null ID của giáo viên đang edit (null = create mode) */
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    /** @var string Tên giáo viên */
    public $name;

    /** @var string|null Ngày sinh */
    public $birthday;

    /** @var string|null Số điện thoại */
    public $phoneNumber;

    /** @var string|null Ghi chú */
    public $note;

    /** @var int Trạng thái (1 = active, 0 = inactive) */
    public $status = 1;

    /** @var int|null Năm (có thể bỏ sau) */
    public $year;

    /** @var int|null Năm học (có thể bỏ sau) */
    public $namhoc;

    // ==================== VALIDATION ====================

    protected $formRules = [
        'name' => 'required|string|max:255',
        'birthday' => 'nullable|date|before:today',
        'phoneNumber' => 'nullable|string|max:20',
        'note' => 'nullable|string|max:500',
        'status' => 'required|boolean',
        'year' => 'nullable|integer|min:1900|max:2100',
        'namhoc' => 'nullable|integer|exists:nam_hoc,id',
    ];

    protected $messages = [
        'name.required' => 'Vui lòng nhập tên giáo viên',
        'name.max' => 'Tên giáo viên không được quá 255 ký tự',
        'birthday.date' => 'Ngày sinh không hợp lệ',
        'birthday.before' => 'Ngày sinh phải trước ngày hôm nay',
        'phoneNumber.max' => 'Số điện thoại không được quá 20 ký tự',
        'note.max' => 'Ghi chú không được quá 500 ký tự',
        'year.integer' => 'Năm phải là số nguyên',
        'year.min' => 'Năm phải từ 1900 trở lên',
        'year.max' => 'Năm không được quá 2100',
        'namhoc.exists' => 'Năm học không hợp lệ',
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
        'teacherCreated' => '$refresh',
        'teacherUpdated' => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        parent::mount();

        // Yêu cầu quyền quản trị (Admin hoặc Decen)
        $this->requireManager();

        // Bắt buộc phải có parish_id
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        // Không cần load gì thêm
        // Data sẽ được load trong render() với pagination
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
            $teacher = Teacher::where('pid', $this->parish_id)
                ->findOrFail($id);

            $this->editingId = $teacher->id;
            $this->name = $teacher->name;
            $this->birthday = $teacher->birthday?->format('Y-m-d');
            $this->phoneNumber = $teacher->phone_number;
            $this->note = $teacher->note;
            $this->status = $teacher->status;
            $this->year = $teacher->year;
            $this->namhoc = $teacher->namhoc;

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
            $exists = Teacher::where('pid', $this->parish_id)
                ->where('name', $this->name)
                ->where('phone_number', $this->phoneNumber)
                ->when($this->editingId, function ($q) {
                    $q->where('id', '!=', $this->editingId);
                })
                ->exists();

            if ($exists) {
                session()->flash('error', 'Giáo viên với tên và số điện thoại này đã tồn tại');
                return;
            }

            Teacher::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'name' => $this->name,
                    'birthday' => $this->birthday ?: null,
                    'phone_number' => $this->phoneNumber ?: null,
                    'note' => $this->note ?: null,
                    'status' => $this->status,
                    'year' => $this->year ?: null,
                    'namhoc' => $this->namhoc ?: null,
                    'pid' => $this->parish_id,
                    'did' => 0,  // Default value
                    'deid' => 0, // Default value
                    'paid' => 0, // Default value
                ]
            );

            DB::commit();

            $message = $this->editingId
                ? 'Cập nhật giáo viên thành công'
                : 'Thêm giáo viên mới thành công';

            session()->flash('message', $message);

            $this->resetForm();
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
            $teacher = Teacher::where('pid', $this->parish_id)
                ->findOrFail($id);

            $teacher->update(['status' => !$teacher->status]);

            $message = $teacher->status
                ? 'Đã kích hoạt giáo viên'
                : 'Đã vô hiệu hóa giáo viên';

            session()->flash('message', $message);
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

            $teacher = Teacher::where('pid', $this->parish_id)
                ->findOrFail($id);

            // TODO: Check nếu giáo viên đang được phân công dạy lớp
            // $hasClasses = \App\Models\Assignment::where('teacher_id', $teacher->id)->exists();
            // if ($hasClasses) {
            //     session()->flash('error', 'Không thể xóa giáo viên đang phân công dạy');
            //     return;
            // }

            $teacher->delete();

            DB::commit();

            session()->flash('message', 'Đã xóa giáo viên thành công');
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
     * Reset form về trạng thái mặc định
     */
    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'birthday',
            'phoneNumber',
            'note',
            'year',
            'namhoc',
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

    // ==================== RENDER ====================

    public function render()
    {
        $query = Teacher::where('pid', $this->parish_id)
            ->with(['parish'])
            ->orderBy('name', 'asc');

        // Apply search filter
        if (!empty($this->search)) {
            $query->search($this->search);
        }

        $teachers = $query->paginate($this->perPage);

        return view('livewire.teacher.teacher-manager', [
            'teachers' => $teachers,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
