<?php

namespace App\Http\Livewire\Lop;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\ClassTeacher;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class AssignTeacher extends BaseComponent
{
    // ==================== PROPS ====================

    /** @var int ID của lớp */
    public $classId = 0;

    /** @var \App\Models\CatechismClass */
    public $class;

    // ==================== FORM STATE ====================

    public $showModal = false;
    public $teacherSearch = '';
    public $selectedTeacherId = null;
    public $selectedRole = ClassTeacher::ROLE_PHO;

    // ==================== DATA ====================

    public $currentTeachers;
    public $availableTeachers;
    protected $usePagination = false;

    // ==================== VALIDATION ====================

    protected $rules = [
        'perPage' => 'required|integer|in:10,15,25,50',
    ];

    protected $messages = [
        'selectedTeacherId.required' => 'Vui lòng chọn Giáo lý viên',
        'selectedTeacherId.exists'   => 'Giáo lý viên không tồn tại',
        'selectedRole.required'      => 'Vui lòng chọn vai trò',
        'selectedRole.in'            => 'Vai trò không hợp lệ',
    ];

    // ==================== LISTENERS ====================

    protected $listeners = [
        'openAssignModal' => 'openModal',
        'refresh'         => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount($id = null)
    {
        if (empty($id)) {
            session()->flash('error', 'Thiếu ID lớp học.');
            $this->redirectRoute('classes.index');
            return;
        }

        $this->classId = (int) $id;

        if ($this->classId <= 0) {
            session()->flash('error', 'ID lớp học không hợp lệ.');
            $this->redirectRoute('classes.index');
            return;
        }

        parent::mount();

        $this->requireManager();
        $this->requireParishId();

        $this->loadClass();
    }

    protected function loadInitialData(): void
    {
        if ($this->class) {
            $this->loadCurrentTeachers();
            $this->loadAvailableTeachers();
        } else {
            $this->currentTeachers   = collect();
            $this->availableTeachers = collect();
        }
    }

    protected function loadClass(): void
    {
        try {
            $this->class = CatechismClass::with(['schoolYear', 'gradeLevel'])
                ->findOrFail($this->classId);

            $this->loadInitialData();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class', ['class_id' => $this->classId]);
            session()->flash('error', 'Không tìm thấy lớp học');
            $this->redirectRoute('classes.index');
        }
    }

    protected function loadCurrentTeachers(): void
    {
        try {
            $this->currentTeachers = ClassTeacher::byClass($this->classId)
                ->byNamhoc($this->class->school_year_id)
                ->active()
                ->with('teacher')
                ->orderByRaw('FIELD(role, ?, ?)', [
                    ClassTeacher::ROLE_CHU_NHIEM,
                    ClassTeacher::ROLE_PHO,
                ])
                ->get()
                ->map(function ($ct) {
                    return [
                        'id'           => $ct->id,
                        'teacher_id'   => $ct->teacher_id,
                        'teacher_name' => $ct->teacher->full_name ?? 'N/A',
                        'first_name'   => $ct->teacher->first_name ?? '',
                        'role'         => $ct->role,
                        'role_label'   => $ct->role === ClassTeacher::ROLE_CHU_NHIEM
                            ? 'Chủ nhiệm'
                            : 'Phụ trách',
                        'phone'        => $ct->teacher->phone_number ?? '',
                    ];
                });
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading current teachers');
            $this->currentTeachers = collect();
        }
    }

    protected function loadAvailableTeachers(): void
    {
        try {
            $assignedTeacherIds = ClassTeacher::byClass($this->classId)
                ->byNamhoc($this->class->school_year_id)
                ->active()
                ->pluck('teacher_id')
                ->toArray();

            $query = Teacher::active()
                ->whereNotIn('id', $assignedTeacherIds)
                ;

            if (!empty(trim($this->teacherSearch))) {
                $query->search(trim($this->teacherSearch));
            }

            $this->availableTeachers = $query
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->limit(100)
                ->get(['id', 'last_name', 'first_name', 'phone_number']);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading available teachers');
            $this->availableTeachers = collect();
        }
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedTeacherSearch(): void
    {
        $this->teacherSearch = trim($this->teacherSearch);
        $this->loadAvailableTeachers();
    }

    public function updatedSelectedRole(): void
    {
        if ($this->selectedRole === ClassTeacher::ROLE_CHU_NHIEM) {
            $hasChuNhiem = $this->currentTeachers->contains('role', ClassTeacher::ROLE_CHU_NHIEM);
            if ($hasChuNhiem) {
                session()->flash('warning', 'Lớp đã có Chủ nhiệm. Vui lòng chọn Phụ trách hoặc xóa Chủ nhiệm hiện tại.');
            }
        }
    }

    // ==================== ACTIONS ====================

    public function openModal(): void
    {
        $this->requireManager();
        $this->showModal = true;
        $this->resetForm();
        $this->loadAvailableTeachers();
    }

    public function assign(): void
    {
        $this->requireManager();

        if (!$this->selectedTeacherId) {
            $this->addError('selectedTeacherId', 'Vui lòng chọn Giáo lý viên');
            return;
        }

        try {
            DB::beginTransaction();

            if ($this->selectedRole === ClassTeacher::ROLE_CHU_NHIEM) {
                $existingChuNhiem = ClassTeacher::byClass($this->classId)
                    ->byNamhoc($this->class->school_year_id)
                    ->chuNhiem()
                    ->active()
                    ->exists();

                if ($existingChuNhiem) {
                    session()->flash('error', 'Lớp đã có Chủ nhiệm. Vui lòng xóa Chủ nhiệm hiện tại trước.');
                    return;
                }
            }

            $teacher = Teacher::active()
                ->findOrFail($this->selectedTeacherId);

            $alreadyAssigned = ClassTeacher::byClass($this->classId)
                ->byNamhoc($this->class->school_year_id)
                ->where('teacher_id', $teacher->id)
                ->exists();

            if ($alreadyAssigned) {
                session()->flash('error', 'Giáo lý viên đã được phân công cho lớp này');
                return;
            }

            ClassTeacher::create([
                'teacher_id' => $teacher->id,
                'class_id'   => $this->classId,
                'namhoc_id'  => $this->class->school_year_id,
                'role'       => $this->selectedRole,
                'status'     => 1,
            ]);

            DB::commit();

            $roleLabel = $this->selectedRole === ClassTeacher::ROLE_CHU_NHIEM
                ? 'Chủ nhiệm' : 'Phụ trách';

            session()->flash('message', "Đã phân công {$teacher->full_name} làm {$roleLabel}");

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
                'class_id'   => $this->classId,
                'role'       => $this->selectedRole,
            ]);
            session()->flash('error', 'Có lỗi khi phân công Giáo lý viên');
        }
    }

    public function remove(int $classTeacherId): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            $classTeacher = ClassTeacher::findOrFail($classTeacherId);

            if ($classTeacher->class_id !== $this->classId) {
                throw new \Exception('Invalid class teacher assignment');
            }

            $teacherName = $classTeacher->teacher->full_name ?? 'Giáo lý viên';
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
            $this->logError($e, 'Error removing teacher', ['class_teacher_id' => $classTeacherId]);
            session()->flash('error', 'Có lỗi khi xóa Giáo lý viên');
        }
    }

    public function changeRole(int $classTeacherId, int $newRole): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            if (!in_array($newRole, [ClassTeacher::ROLE_CHU_NHIEM, ClassTeacher::ROLE_PHO])) {
                throw new \Exception('Invalid role');
            }

            $classTeacher = ClassTeacher::findOrFail($classTeacherId);

            if ($classTeacher->class_id !== $this->classId) {
                throw new \Exception('Invalid class teacher assignment');
            }

            if ($newRole === ClassTeacher::ROLE_CHU_NHIEM) {
                $existingChuNhiem = ClassTeacher::byClass($this->classId)
                    ->byNamhoc($this->class->school_year_id)
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
                'new_role'         => $newRole,
            ]);
            session()->flash('error', 'Có lỗi khi thay đổi vai trò');
        }
    }

    // ==================== HELPERS ====================

    protected function resetForm(): void
    {
        $this->reset(['selectedTeacherId', 'teacherSearch', 'selectedRole']);
        $this->selectedRole = ClassTeacher::ROLE_PHO;
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
