<?php

namespace App\Http\Livewire\Lop;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\ClassTeacher;
use App\Models\Lop;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

/**
 * Component phân công Giáo lý viên cho lớp
 * 
 * Features:
 * - Search teacher với debounce
 * - Chọn role (Chủ nhiệm / Phụ trách)
 * - Quick assign/remove
 * - Validate: Chỉ 1 chủ nhiệm/lớp
 * - Show current teachers với role badges
 */
class AssignTeacher extends BaseComponent
{
    // ==================== PROPS ====================

    /** @var int ID của lớp */
    public $lopId = 0;

    /** @var \App\Models\Lop */
    public $lop;
    
    // ==================== FORM STATE ====================

    /** @var bool Hiển thị modal */
    public $showModal = false;

    /** @var string Search query cho teacher */
    public $teacherSearch = '';

    /** @var int|null Selected teacher ID */
    public $selectedTeacherId = null;

    /** @var int Selected role (1=Chủ nhiệm, 2=Phụ trách) */
    public $selectedRole = ClassTeacher::ROLE_PHO; // Default: Phụ trách
    
    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection Danh sách GLV hiện tại */
    public $currentTeachers;

    /** @var \Illuminate\Support\Collection Danh sách GLV có thể chọn */
    public $availableTeachers;

    /** @var bool Không dùng pagination */
    protected $usePagination = false;

    // ==================== VALIDATION ====================

    protected $rules = [
        // 'selectedTeacherId' => 'required|integer|exists:teacher,id',
        // 'selectedRole' => 'required|integer|in:1,2',
        'perPage' => 'required|integer|in:10,15,25,50',
    ];

    protected $messages = [
        'selectedTeacherId.required' => 'Vui lòng chọn Giáo lý viên',
        'selectedTeacherId.exists' => 'Giáo lý viên không tồn tại',
        'selectedRole.required' => 'Vui lòng chọn vai trò',
        'selectedRole.in' => 'Vai trò không hợp lệ',
    ];

    // ==================== LISTENERS ====================

    protected $listeners = [
        'openAssignModal' => 'openModal',
        'refresh' => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount($lopId = null)
    {
        // 1️⃣ Validate input TRƯỚC
        if (empty($lopId)) {
            session()->flash('error', 'Thiếu ID lớp học.');
            $this->redirectRoute('ds-lop');
            return;
        }

        $this->lopId = (int) $lopId;

        if ($this->lopId <= 0) {
            session()->flash('error', 'ID lớp học không hợp lệ.');
            $this->redirectRoute('ds-lop');
            return;
        }

        // 2️⃣ Gọi parent::mount() TRƯỚC để init user, session, etc.
        parent::mount();

        // 3️⃣ Check permissions
        $this->requireManager();
        $this->requireParishId();

        $this->loadLop();
    }

    protected function loadInitialData(): void
    {
        if ($this->lop) {
            $this->loadCurrentTeachers();
            $this->loadAvailableTeachers();
        } else {
            $this->currentTeachers = collect();
            $this->availableTeachers = collect();
        }
    }

    /**
     * Load thông tin lớp với relationships
     */
    protected function loadLop(): void
    {
        try {
            $this->lop = Lop::ofParish($this->parish_id)
                ->with(['schoolYear', 'blockRelation'])
                ->findOrFail($this->lopId);

            $this->loadInitialData();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading lop', ['lop_id' => $this->lopId]);
            session()->flash('error', 'Không tìm thấy lớp học');
            $this->redirectRoute('ds-lop');
        }
    }

    /**
     * Load danh sách GLV hiện tại của lớp
     */
    protected function loadCurrentTeachers(): void
    {
        try {
            // Load từ bảng pivot class_teachers
            $this->currentTeachers = ClassTeacher::byClass($this->lopId)
                ->byNamhoc($this->lop->schoolyear)
                ->active()
                ->with('teacher')
                ->orderByRaw('FIELD(role, ?, ?)', [
                    ClassTeacher::ROLE_CHU_NHIEM,
                    ClassTeacher::ROLE_PHO
                ])
                ->get()
                ->map(function ($ct) {
                    return [
                        'id' => $ct->id,
                        'teacher_id' => $ct->teacher_id,
                        'teacher_name' => $ct->teacher->name ?? 'N/A',
                        'role' => $ct->role,
                        'role_label' => $ct->role === ClassTeacher::ROLE_CHU_NHIEM
                            ? 'Chủ nhiệm'
                            : 'Phụ trách',
                        'phone' => $ct->teacher->phone_number ?? '',
                    ];
                });
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading current teachers');
            $this->currentTeachers = collect();
        }
    }

    /**
     * Load danh sách GLV có thể chọn (chưa được assign)
     */
    protected function loadAvailableTeachers(): void
    {
        try {
            // Lấy IDs của teachers đã được assign
            $assignedTeacherIds = ClassTeacher::byClass($this->lopId)
                ->byNamhoc($this->lop->schoolyear)
                ->active()
                ->pluck('teacher_id')
                ->toArray();

            $query = Teacher::ofParish($this->parish_id)
                ->active()
                ->whereNotIn('id', $assignedTeacherIds);

            // Search
            if (!empty(trim($this->teacherSearch))) {
                $query->search(trim($this->teacherSearch));
            }

            $this->availableTeachers = $query
                ->orderBy('name')
                ->limit(100)
                ->get(['id', 'name', 'phone_number', 'position']);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading available teachers');
            $this->availableTeachers = collect();
        }
    }
    
    // ==================== PROPERTY UPDATERS ====================

    /**
     * Khi search thay đổi
     */
    public function updatedTeacherSearch(): void
    {
        $this->teacherSearch = trim($this->teacherSearch);
        $this->loadAvailableTeachers();
    }

    /**
     * Khi chọn role khác, reset teacher đã chọn
     */
    public function updatedSelectedRole(): void
    {
        // Nếu chọn Chủ nhiệm, check xem đã có chưa
        if ($this->selectedRole === ClassTeacher::ROLE_CHU_NHIEM) {
            $hasChuNhiem = $this->currentTeachers->contains('role', ClassTeacher::ROLE_CHU_NHIEM);

            if ($hasChuNhiem) {
                session()->flash('warning', 'Lớp đã có Chủ nhiệm. Vui lòng chọn vai trò Phụ trách hoặc xóa Chủ nhiệm hiện tại.');
            }
        }
    }
    
    // ==================== ACTIONS ====================

    /**
     * Mở modal phân công
     */
    public function openModal(): void
    {
        $this->requireManager();
        $this->showModal = true;
        $this->resetForm();
        $this->loadAvailableTeachers();
    }

    /**
     * Phân công GLV vào lớp
     */
    public function assign(): void
    {
        $this->requireManager();
        $this->validate();

        try {
            DB::beginTransaction();

            // Validate: Chỉ 1 Chủ nhiệm/lớp
            if ($this->selectedRole === ClassTeacher::ROLE_CHU_NHIEM) {
                $existingChuNhiem = ClassTeacher::byClass($this->lopId)
                    ->byNamhoc($this->lop->schoolyear)
                    ->chuNhiem()
                    ->active()
                    ->exists();

                if ($existingChuNhiem) {
                    session()->flash('error', 'Lớp đã có Chủ nhiệm. Vui lòng xóa Chủ nhiệm hiện tại trước.');
                    return;
                }
            }

            // Kiểm tra teacher thuộc parish
            $teacher = Teacher::ofParish($this->parish_id)
                ->active()
                ->findOrFail($this->selectedTeacherId);

            // Kiểm tra teacher đã được assign chưa
            $alreadyAssigned = ClassTeacher::byClass($this->lopId)
                ->byNamhoc($this->lop->schoolyear)
                ->where('teacher_id', $teacher->id)
                ->exists();

            if ($alreadyAssigned) {
                session()->flash('error', 'Giáo lý viên đã được phân công cho lớp này');
                return;
            }

            // Tạo assignment
            ClassTeacher::create([
                'teacher_id' => $teacher->id,
                'class_id' => $this->lopId,
                'namhoc_id' => $this->lop->schoolyear,
                'role' => $this->selectedRole,
                'status' => 1,
            ]);

            DB::commit();

            $roleLabel = $this->selectedRole === ClassTeacher::ROLE_CHU_NHIEM
                ? 'Chủ nhiệm'
                : 'Phụ trách';

            session()->flash('message', "Đã phân công {$teacher->name} làm {$roleLabel}");

            $this->resetForm();
            $this->loadCurrentTeachers();
            $this->loadAvailableTeachers();

            $this->emit('teacherAssigned');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            session()->flash('error', 'Giáo lý viên không tồn tại');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error assigning teacher', [
                'teacher_id' => $this->selectedTeacherId,
                'lop_id' => $this->lopId,
                'role' => $this->selectedRole,
            ]);
            session()->flash('error', 'Có lỗi khi phân công Giáo lý viên');
        }
    }

    /**
     * Xóa GLV khỏi lớp
     */
    public function remove(int $classTeacherId): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            $classTeacher = ClassTeacher::findOrFail($classTeacherId);

            // Validate: Chỉ xóa assignment của lớp hiện tại
            if ($classTeacher->class_id !== $this->lopId) {
                throw new \Exception('Invalid class teacher assignment');
            }

            $teacherName = $classTeacher->teacher->name ?? 'Giáo lý viên';

            // Soft delete: set status = 0
            $classTeacher->delete();

            DB::commit();

            session()->flash('message', "Đã xóa {$teacherName} khỏi lớp");

            $this->loadCurrentTeachers();
            $this->loadAvailableTeachers();

            $this->emit('teacherRemoved');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            session()->flash('error', 'Không tìm thấy phân công này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error removing teacher', [
                'class_teacher_id' => $classTeacherId,
            ]);
            session()->flash('error', 'Có lỗi khi xóa Giáo lý viên');
        }
    }

    /**
     * Thay đổi role của teacher
     */
    public function changeRole(int $classTeacherId, int $newRole): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            // Validate role
            if (!in_array($newRole, [ClassTeacher::ROLE_CHU_NHIEM, ClassTeacher::ROLE_PHO])) {
                throw new \Exception('Invalid role');
            }

            $classTeacher = ClassTeacher::findOrFail($classTeacherId);

            // Validate ownership
            if ($classTeacher->class_id !== $this->lopId) {
                throw new \Exception('Invalid class teacher assignment');
            }

            // Nếu đổi thành Chủ nhiệm, check xem đã có chưa
            if ($newRole === ClassTeacher::ROLE_CHU_NHIEM) {
                $existingChuNhiem = ClassTeacher::byClass($this->lopId)
                    ->byNamhoc($this->lop->schoolyear)
                    ->chuNhiem()
                    ->active()
                    ->where('id', '!=', $classTeacherId)
                    ->exists();

                if ($existingChuNhiem) {
                    session()->flash('error', 'Lớp đã có Chủ nhiệm. Vui lòng xóa Chủ nhiệm hiện tại trước.');
                    return;
                }
            }

            $classTeacher->update(['role' => $newRole]);

            DB::commit();

            $roleLabel = $newRole === ClassTeacher::ROLE_CHU_NHIEM ? 'Chủ nhiệm' : 'Phụ trách';
            session()->flash('message', "Đã thay đổi vai trò thành {$roleLabel}");

            $this->loadCurrentTeachers();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error changing role', [
                'class_teacher_id' => $classTeacherId,
                'new_role' => $newRole,
            ]);
            session()->flash('error', 'Có lỗi khi thay đổi vai trò');
        }
    }

    // ==================== HELPERS ====================

    protected function resetForm(): void
    {
        $this->reset(['selectedTeacherId', 'teacherSearch', 'selectedRole']);
        $this->selectedRole = ClassTeacher::ROLE_PHO; // Reset về Phụ trách
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.lop.assign-teacher')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
