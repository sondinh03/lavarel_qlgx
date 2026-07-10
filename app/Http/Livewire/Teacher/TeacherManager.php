<?php

namespace App\Http\Livewire\Teacher;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Teacher;
use App\Models\User;
use App\Models\ParishGroup;
use App\Models\Holymanagement;
use App\Support\UserAccountEmailResolver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Component quản lý Giáo lý viên (CRUD)
 *
 * Features:
 * - List teachers với pagination + search + filter
 * - Create teacher + tự động tạo user account
 * - Edit teacher
 * - Delete teacher + xóa luôn user account
 * - Filter theo giáo họ, giới tính, trạng thái
 */
class TeacherManager extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var string|null Filter theo giáo họ */
    public $filterParishGroup = '';

    /** @var string Filter theo giới tính */
    public $filterGender = '';

    /** @var string Filter theo trạng thái */
    public $filterActive = '';

    // ==================== FORM STATE ====================

    /** @var bool Hiển thị modal form */
    public $showForm = false;

    /** @var int|null ID đang edit (null = create) */
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    public $last_name   = '';
    public $first_name  = '';
    public $gender      = '';
    public $birthday    = '';
    public $phone_number = '';
    public $email       = '';
    public $address     = '';
    public $saint_id    = '';
    public $parish_group_id = '';
    public $is_active   = true;
    public $note        = '';
    public $create_account = true;   // checkbox tạo tài khoản khi thêm mới
    public $reset_password = false;  // checkbox reset mật khẩu khi edit
    public $has_account = false;

    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection */
    public $parishGroups;

    /** @var \Illuminate\Support\Collection */
    public $saints;

    // ==================== VALIDATION ====================

    protected $formRules = [
        'last_name'        => 'required|string|max:50',
        'first_name'       => 'required|string|max:50',
        'gender'           => 'nullable|in:male,female',
        'birthday'         => 'nullable|date',
        'phone_number'     => 'nullable|string|max:20',
        'email'            => 'nullable|email|max:255',
        'address'          => 'nullable|string|max:255',
        'saint_id'         => 'nullable|integer|exists:holymanagements,id',
        'parish_group_id'  => 'nullable|integer|exists:parish_groups,id',
        'is_active'        => 'boolean',
        'note'             => 'nullable|string',
    ];

    protected $messages = [
        'last_name.required'   => 'Vui lòng nhập họ',
        'first_name.required'  => 'Vui lòng nhập tên',
        'gender.in'            => 'Giới tính không hợp lệ',
        'birthday.date'        => 'Ngày sinh không hợp lệ',
        'email.email'          => 'Email không đúng định dạng',
        'saint_id.exists'      => 'Tên thánh không tồn tại',
        'parish_group_id.exists' => 'Giáo họ không tồn tại',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'filterParishGroup' => ['except' => ''],
            'filterGender'      => ['except' => ''],
            'filterActive'      => ['except' => ''],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'         => 'handleRefresh',
        'teacherCreated'  => '$refresh',
        'teacherUpdated'  => '$refresh',
        'teacherDeleted'  => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        $this->requireManager();
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->parishGroups = ParishGroup::where('parish_id', $this->parishId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->saints = Holymanagement::orderBy('name')
            ->get(['id', 'name']);
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSearch(): void
    {
        parent::updatedSearch();
    }

    public function updatedFilterParishGroup(): void
    {
        $this->resetPage();
    }
    public function updatedFilterGender(): void
    {
        $this->resetPage();
    }
    public function updatedFilterActive(): void
    {
        $this->resetPage();
    }

    // ==================== CRUD ACTIONS ====================

    public function create(): void
    {
        $this->requireManager();
        $this->resetForm();
        $this->showForm = true;
        $this->emit('openModal');
    }

    public function edit(int $id): void
    {
        $this->requireManager();

        try {
            $teacher = Teacher::findOrFail($id);

            $this->editingId        = $teacher->id;
            $this->last_name        = $teacher->last_name;
            $this->first_name       = $teacher->first_name;
            $this->gender           = $teacher->gender ?? '';
            $this->birthday         = $teacher->birthday?->format('Y-m-d') ?? '';
            $this->phone_number     = $teacher->phone_number ?? '';
            $this->email            = $teacher->email ?? '';
            $this->address          = $teacher->address ?? '';
            $this->saint_id         = $teacher->saint_id ?? '';
            $this->parish_group_id  = $teacher->parish_group_id ?? '';
            $this->is_active        = $teacher->is_active;
            $this->note             = $teacher->note ?? '';
            $this->has_account    = (bool) $teacher->user_id; // ← để view biết hiển thị gì
            $this->create_account = false;   // khi edit default không tạo thêm
            $this->reset_password = false;

            $this->showForm = true;
            $this->emit('openModal');
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy giáo lý viên này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading teacher for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin giáo lý viên');
        }
    }

    public function save(): void
    {
        $this->requireManager();
        $this->validate($this->formRules, $this->messages);

        // Bắt buộc có ít nhất phone hoặc email để tạo tài khoản
        if (!$this->editingId && empty($this->phone_number) && empty($this->email)) {
            $this->addError('phone_number', 'Vui lòng nhập số điện thoại hoặc email để tạo tài khoản');
            return;
        }

        try {
            DB::beginTransaction();

            if ($this->editingId) {
                $this->updateTeacher();
            } else {
                $this->createTeacher();
            }

            DB::commit();

            session()->flash(
                'message',
                $this->editingId
                    ? 'Cập nhật giáo lý viên thành công'
                    : 'Thêm giáo lý viên thành công'
            );

            $this->resetForm();
            $this->closeModal();
            $this->emit($this->editingId ? 'teacherUpdated' : 'teacherCreated');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving teacher');
            session()->flash('error', 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    public function delete(int $id): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            $teacher = Teacher::findOrFail($id);

            // Xóa user account đi kèm
            if ($teacher->user_id) {
                User::find($teacher->user_id)?->delete();
            }

            $teacher->delete();

            DB::commit();

            session()->flash('message', 'Đã xóa giáo lý viên thành công');
            $this->emit('teacherDeleted');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            session()->flash('error', 'Không tìm thấy giáo lý viên này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting teacher', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi xóa giáo lý viên');
        }
    }

    // ==================== PRIVATE HELPERS ====================

    private function createTeacher(): void
    {
        $user = null;
        $normalizedPhone = $this->phone_number
            ? UserAccountEmailResolver::normalizePhone($this->phone_number)
            : null;

        if ($this->phone_number && $normalizedPhone === null) {
            throw new \Exception('Số điện thoại không hợp lệ');
        }

        if ($this->create_account) {
            if (empty($this->phone_number) && empty($this->email)) {
                throw new \Exception('Cần có SĐT hoặc email để tạo tài khoản');
            }

            $accountEmail = UserAccountEmailResolver::resolveAccountEmail($this->email, $normalizedPhone);

            if (User::where('email', $accountEmail)->exists()) {
                throw new \Exception('Email/SĐT này đã được dùng cho tài khoản khác');
            }

            $user = User::create([
                'name'      => trim($this->last_name . ' ' . $this->first_name),
                'email'     => $accountEmail,
                'parish_id' => $this->parishId,
                'password'  => $normalizedPhone ?: '12345678',
            ]);

            $user->assignRole('catechist');
        }

        // Tạo teacher
        Teacher::create([
            'parish_id'        => $this->parishId,
            'user_id'          => $user?->id,
            'last_name'        => $this->last_name,
            'first_name'       => $this->first_name,
            'gender'           => $this->gender ?: null,
            'birthday'         => $this->birthday ?: null,
            'phone_number'     => $normalizedPhone ?? ($this->phone_number ?: null),
            'email'            => $this->email ?: null,
            'address'          => $this->address ?: null,
            'saint_id'         => $this->saint_id ?: null,
            'parish_group_id'  => $this->parish_group_id ?: null,
            'is_active'        => $this->is_active,
            'note'             => $this->note ?: null,
        ]);
    }

    private function updateTeacher(): void
    {
        $teacher = Teacher::findOrFail($this->editingId);

        $normalizedPhone = $this->phone_number
            ? UserAccountEmailResolver::normalizePhone($this->phone_number)
            : null;

        if ($this->phone_number && $normalizedPhone === null) {
            throw new \Exception('Số điện thoại không hợp lệ');
        }

        $teacher->update([
            'last_name'        => $this->last_name,
            'first_name'       => $this->first_name,
            'gender'           => $this->gender ?: null,
            'birthday'         => $this->birthday ?: null,
            'phone_number'     => $normalizedPhone ?? ($this->phone_number ?: null),
            'email'            => $this->email ?: null,
            'address'          => $this->address ?: null,
            'saint_id'         => $this->saint_id ?: null,
            'parish_group_id'  => $this->parish_group_id ?: null,
            'is_active'        => $this->is_active,
            'note'             => $this->note ?: null,
        ]);

        if ($teacher->user) {
            // Đã có tài khoản → sync tên, và reset mật khẩu nếu được yêu cầu
            $userUpdate = [
                'name'      => trim($this->last_name . ' ' . $this->first_name),
                'parish_id' => $this->parishId,
            ];

            if ($this->reset_password && $normalizedPhone) {
                $userUpdate['password'] = Hash::make($normalizedPhone);
            }

            $teacher->user->update($userUpdate);
        } elseif ($this->create_account) {
            // Chưa có tài khoản → tạo mới nếu manager tích vào
            if (empty($this->phone_number) && empty($this->email)) {
                throw new \Exception('Cần có SĐT hoặc email để tạo tài khoản');
            }

            $accountEmail = UserAccountEmailResolver::resolveAccountEmail($this->email, $normalizedPhone);

            if (User::where('email', $accountEmail)->exists()) {
                throw new \Exception('Email/SĐT này đã được dùng cho tài khoản khác');
            }

            $user = User::create([
                'name'      => trim($this->last_name . ' ' . $this->first_name),
                'email'     => $accountEmail,
                'parish_id' => $this->parishId,
                'password'  => $normalizedPhone ?: '12345678',
            ]);

            $user->assignRole('catechist');
            $teacher->update(['user_id' => $user->id]);
        }
    }

    // ==================== DATA LOADING ====================

    private function getTeachersPaginated()
    {
        try {
            $query = Teacher::with(['parishGroup', 'saint', 'user']);


            // Search
            if (!empty(trim($this->search))) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('phone_number', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            }

            // Filters
            if ($this->filterParishGroup !== '') {
                $query->where('parish_group_id', $this->filterParishGroup);
            }

            if ($this->filterGender !== '') {
                $query->where('gender', $this->filterGender);
            }

            if ($this->filterActive !== '') {
                $query->where('is_active', (bool) $this->filterActive);
            }

            return $query
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading teachers');
            session()->flash('error', 'Có lỗi khi tải danh sách giáo lý viên');

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }
    }

    // ==================== FORM HELPERS ====================

    public function closeModal(): void
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
        $this->emit('closeModal');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'last_name',
            'first_name',
            'gender',
            'birthday',
            'phone_number',
            'email',
            'address',
            'saint_id',
            'parish_group_id',
            'note',
            'create_account',
            'reset_password',
        ]);

        $this->is_active = true;
        $this->create_account = true;   // ← default true
        $this->reset_password = false;
        $this->resetValidation();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterParishGroup', 'filterGender', 'filterActive']);
        $this->resetPage();
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.teacher.teacher-manager', [
            'teachers' => $this->getTeachersPaginated(),
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
