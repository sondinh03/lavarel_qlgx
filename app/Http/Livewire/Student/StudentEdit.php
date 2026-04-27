<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\StudentNew;
use App\Models\ParishNew;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Services\UploadService;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class StudentEdit extends BaseComponent
{
    use WithFileUploads;

    // ==================== PROPERTIES ====================
    public $studentId = null;
    public $isEdit = false;
    public $isLoading = true;
    public $activeTab = 'basic';
    protected $usePagination = false;
    public ?int $classId = null;

    // ==================== FORM FIELDS ====================
    public $student_code = '';
    public $first_name = '';
    public $last_name = '';
    public $gender = 'male';
    public $birthday = '';
    public $phone = '';
    public $email = '';
    public $note = '';
    public $is_active = true;
    public $father_name = '';
    public $mother_name = '';
    public $avatar_path; // For file upload

    // Parish
    public $parish_id = null;
    public $parish_group_id = null;
    public $saint_id = null;

    // ==================== DROPDOWN DATA ====================
    public $parishes = [];
    public $parishGroups = [];
    public $saints = [];

    // ==================== VALIDATION ====================
    protected $formRules =
    [
        'first_name'      => 'required|string|max:255',
        'last_name'       => 'required|string|max:255',
        'gender'          => 'required|in:male,female',
        'birthday'        => 'nullable|date',
        'phone'           => 'nullable|string|max:15',
        'email'           => 'nullable|email|max:255',
        'note'            => 'nullable|string|max:1000',
        'is_active'       => 'required|boolean',
        'parish_id'       => 'required|exists:parishes,id',
        'parish_group_id' => 'nullable|exists:parish_groups,id',
        'saint_id'        => 'nullable|exists:holymanagements,id',
        'father_name'       => 'nullable|string|max:255',
        'mother_name'       => 'nullable|string|max:255',
        'avatar_path'            => 'nullable|image|max:2048',
    ];

    protected $messages = [
        'first_name.required' => 'Vui lòng nhập tên',
        'last_name.required'  => 'Vui lòng nhập họ',
        'gender.required'     => 'Vui lòng chọn giới tính',
        'email.email'         => 'Email không hợp lệ',
        'parish_id.required'  => 'Vui lòng chọn giáo xứ',
        'parish_id.exists'    => 'Giáo xứ không tồn tại',
    ];

    // ==================== QUERY STRING ====================
    protected function queryString()
    {
        return [
            'activeTab' => ['except' => 'basic', 'as' => 'tab'],
            'classId' => ['except' => null, 'as' => 'classId'],
        ];
    }

    // ==================== LIFECYCLE ====================

    public function mount($id = null): void
    {
        if ($this->classId) {
            $class = CatechismClass::find($this->classId);
            if (!$class) {
                $this->classId = null;
            }
        }
        $this->studentId = $id ? (int) $id : null;
        $this->isEdit    = $this->studentId !== null;
        parent::mount();
    }

    protected function loadInitialData(): void
    {
        try {
            // Kiểm tra quyền tạo/sửa ngay từ đầu
            if ($this->isEdit) {
                $student = StudentNew::findOrFail($this->studentId);
                $this->authorize('update', $student);
            } else {
                $this->authorize('create', StudentNew::class);
            }

            $this->loadDropdownData();

            if ($this->isEdit) {
                $this->mapToForm(
                    StudentNew::with(['parishGroup', 'saint'])->findOrFail($this->studentId)
                );
            } else {
                $this->initializeDefaults();
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy học sinh');
            $this->redirect(route('classes.index'));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to load initial data');
            $this->emit('toast', 'error', 'Có lỗi khi tải dữ liệu');
        } finally {
            $this->isLoading = false;
        }
    }

    // ==================== DATA LOADING ====================

    protected function loadDropdownData(): void
    {
        // ParishNew dùng đúng model tương ứng bảng students.parish_id
        $this->parishes = ParishNew::orderBy('name')->get(['id', 'name']);
        $this->saints   = Holymanagement::orderBy('name')->get(['id', 'name']);
    }

    protected function mapToForm(StudentNew $student): void
    {
        $this->student_code     = $student->student_code ?? '';
        $this->first_name      = $student->first_name ?? '';
        $this->last_name       = $student->last_name ?? '';
        $this->gender          = $student->gender ?? 'male';
        $this->birthday        = $student->birthday?->format('Y-m-d') ?? '';
        $this->phone           = $student->phone ?? '';
        $this->email           = $student->email ?? '';
        $this->note            = $student->note ?? '';
        $this->is_active       = (bool) ($student->is_active ?? true);
        $this->parish_id       = $student->parish_id;
        $this->parish_group_id = $student->parish_group_id;
        $this->saint_id        = $student->saint_id;
        $this->father_name       = $student->father_name ?? '';
        $this->mother_name       = $student->mother_name ?? '';

        if ($this->parish_id) {
            $this->loadParishGroups();
        }
    }

    protected function initializeDefaults(): void
    {
        // Nếu có parishId từ session/context thì pre-fill
        if ($this->parishId) {
            $this->parish_id = $this->parishId;
            $this->loadParishGroups();
        }
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedParishId(): void
    {
        $this->parish_group_id = null;
        $this->parishGroups    = [];
        $this->loadParishGroups();
    }

    protected function loadParishGroups(): void
    {
        $this->parishGroups = $this->parish_id
            ? ParishGroup::where('parish_id', $this->parish_id)
            ->orderBy('name')
            ->get(['id', 'name'])  // bỏ ->toArray()
            : collect();
    }

    // ==================== ACTIONS ====================

    public function save(): void
    {
        $this->validate($this->formRules, $this->messages);

        try {
            DB::beginTransaction();

            if ($this->isEdit) {
                $student = StudentNew::findOrFail($this->studentId);
                $this->authorize('update', $student);
            } else {
                $student = new StudentNew();
                $this->authorize('create', StudentNew::class);
            }

            $student->fill([
                'first_name'      => $this->first_name,
                'last_name'       => $this->last_name,
                'gender'          => $this->gender,
                'birthday'        => $this->birthday ?: null,
                'phone'           => $this->phone,
                'email'           => $this->email,
                'note'            => $this->note,
                'is_active'       => $this->is_active,
                'parish_id'       => $this->parish_id,
                'parish_group_id' => $this->parish_group_id,
                'saint_id'        => $this->saint_id,
                'father_name'     => $this->father_name,
                'mother_name'     => $this->mother_name,
            ]);

            if ($this->avatar_path) {
                if ($this->isEdit && $student->avatar_path) {
                    @unlink(public_path($student->avatar_path));
                }

                $path = app(UploadService::class)->upload($this->avatar_path, 'avatars');
                $student->avatar_path = $path;
            }

            $student->save();

            if ($this->classId) {
                $student->classes()->attach($this->classId, [
                    'enrolled_at' => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            DB::commit();

            $this->emit(
                'toast',
                'message',
                $this->isEdit ? 'Cập nhật học sinh thành công' : 'Thêm học sinh mới thành công'
            );

            if (!$this->isEdit && $this->classId) {
                $this->redirect(route('students.index', ['class' => $this->classId]));
            } else {
                $this->redirect(route('students.show', $student->id));
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            abort(403, 'Bạn không có quyền thực hiện thao tác này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Failed to save student', [
                'student_id' => $this->studentId,
                'is_edit'    => $this->isEdit,
            ]);
            $this->emit('toast', 'error', 'Có lỗi xảy ra khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['basic', 'other'])) {
            $this->activeTab = $tab;
        }
    }

    public function cancel(): void
    {
        $this->isEdit
            ? $this->redirect(route('students.show', $this->studentId))
            : $this->redirect(route('classes.index'));
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.student.student-edit', [
            'isLoading' => $this->isLoading,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
