<?php

namespace App\Http\Livewire\Teacher;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Teacher;
use App\Models\User;
use App\Support\CatechistDefaultPassword;
use App\Support\UserAccountEmailResolver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class TeacherEdit extends BaseComponent
{
    public $teacherId = null;
    public $isEdit = false;
    public $isLoading = true;
    protected $usePagination = false;

    public $last_name = '';
    public $first_name = '';
    public $gender = '';
    public $birthday = '';
    public $phone_number = '';
    public $email = '';
    public $address = '';
    public $saint_id = '';
    public $parish_group_id = '';
    public $is_active = true;
    public $note = '';
    public $create_account = true;
    public $reset_password = false;
    public $has_account = false;
    public $login_identifier = '';
    public $login_is_phone = false;

    public $parishGroups;
    public $saints;

    protected $formRules = [
        'last_name'       => 'required|string|max:50',
        'first_name'      => 'required|string|max:50',
        'gender'          => 'nullable|in:male,female',
        'birthday'        => 'nullable|date',
        'phone_number'    => 'nullable|string|max:20',
        'email'           => 'nullable|email|max:255',
        'address'         => 'nullable|string|max:255',
        'saint_id'        => 'nullable|integer|exists:holymanagements,id',
        'parish_group_id' => 'nullable|integer|exists:parish_groups,id',
        'is_active'       => 'boolean',
        'note'            => 'nullable|string',
    ];

    protected $messages = [
        'last_name.required'     => 'Vui lòng nhập họ',
        'first_name.required'    => 'Vui lòng nhập tên',
        'gender.in'              => 'Giới tính không hợp lệ',
        'birthday.date'          => 'Ngày sinh không hợp lệ',
        'email.email'            => 'Email không đúng định dạng',
        'saint_id.exists'        => 'Tên thánh không tồn tại',
        'parish_group_id.exists' => 'Giáo họ không tồn tại',
    ];

    public function mount($id = null): void
    {
        $this->requireManager();
        $this->teacherId = $id ? (int) $id : null;
        $this->isEdit = $this->teacherId !== null;
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        try {
            $this->parishGroups = ParishGroup::where('parish_id', $this->parishId)
                ->orderBy('name')
                ->get(['id', 'name']);

            $this->saints = Holymanagement::orderBy('name')->get(['id', 'name']);

            if ($this->isEdit) {
                $teacher = Teacher::where('parish_id', $this->parishId)->findOrFail($this->teacherId);

                $this->last_name       = $teacher->last_name;
                $this->first_name      = $teacher->first_name;
                $this->gender          = $teacher->gender ?? '';
                $this->birthday        = $teacher->birthday?->format('Y-m-d') ?? '';
                $this->phone_number    = $teacher->phone_number ?? '';
                $this->email           = $teacher->email ?? '';
                $this->address         = $teacher->address ?? '';
                $this->saint_id        = $teacher->saint_id ?? '';
                $this->parish_group_id = $teacher->parish_group_id ?? '';
                $this->is_active       = $teacher->is_active;
                $this->note            = $teacher->note ?? '';
                $this->has_account     = (bool) $teacher->user_id;
                $this->create_account  = false;
                $this->reset_password  = false;
                $this->login_identifier = UserAccountEmailResolver::displayLoginIdentifier(
                    $teacher->user->email ?? null,
                    $teacher->phone_number
                );
                $this->login_is_phone = $teacher->user
                    ? UserAccountEmailResolver::isSyntheticEmail((string) $teacher->user->email)
                    : false;
            }
        } catch (ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy giáo lý viên');
            $this->redirectRoute('catechists.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading teacher form', ['id' => $this->teacherId]);
            $this->emit('toast', 'error', 'Có lỗi khi tải dữ liệu');
        } finally {
            $this->isLoading = false;
        }
    }

    public function save(): void
    {
        $this->requireManager();
        $this->validate($this->formRules, $this->messages);

        $needsAccount = (!$this->isEdit && $this->create_account)
            || ($this->isEdit && !$this->has_account && $this->create_account);

        if ($needsAccount && empty($this->phone_number) && empty($this->email)) {
            $this->addError('phone_number', 'Vui lòng nhập số điện thoại hoặc email để tạo tài khoản');
            return;
        }

        try {
            DB::beginTransaction();

            if ($this->isEdit) {
                $this->updateTeacher();
            } else {
                $this->createTeacher();
            }

            DB::commit();

            $this->emit(
                'toast',
                'message',
                $this->isEdit ? 'Cập nhật giáo lý viên thành công' : 'Thêm giáo lý viên thành công'
            );

            if ($this->isEdit) {
                $this->redirect(route('catechists.show', $this->teacherId));
            } else {
                $this->redirect(route('catechists.index'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving teacher');
            $this->emit('toast', 'error', $e->getMessage() ?: 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

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
            $accountEmail = UserAccountEmailResolver::resolveAccountEmail($this->email, $normalizedPhone);

            if (User::where('email', $accountEmail)->exists()) {
                throw new \Exception('Email/SĐT này đã được dùng cho tài khoản khác');
            }

            $user = User::create([
                'name'      => trim($this->last_name . ' ' . $this->first_name),
                'email'     => $accountEmail,
                'parish_id' => $this->parishId,
                'password'  => CatechistDefaultPassword::fromBirthday($this->birthday),
            ]);

            $user->assignRole('catechist');
        }

        Teacher::create([
            'parish_id'       => $this->parishId,
            'user_id'         => $user?->id,
            'last_name'       => $this->last_name,
            'first_name'      => $this->first_name,
            'gender'          => $this->gender ?: null,
            'birthday'        => $this->birthday ?: null,
            'phone_number'    => $normalizedPhone ?? ($this->phone_number ?: null),
            'email'           => $this->email ?: null,
            'address'         => $this->address ?: null,
            'saint_id'        => $this->saint_id ?: null,
            'parish_group_id' => $this->parish_group_id ?: null,
            'is_active'       => $this->is_active,
            'note'            => $this->note ?: null,
        ]);
    }

    private function updateTeacher(): void
    {
        $teacher = Teacher::where('parish_id', $this->parishId)->findOrFail($this->teacherId);

        $normalizedPhone = $this->phone_number
            ? UserAccountEmailResolver::normalizePhone($this->phone_number)
            : null;

        if ($this->phone_number && $normalizedPhone === null) {
            throw new \Exception('Số điện thoại không hợp lệ');
        }

        $teacher->update([
            'last_name'       => $this->last_name,
            'first_name'      => $this->first_name,
            'gender'          => $this->gender ?: null,
            'birthday'        => $this->birthday ?: null,
            'phone_number'    => $normalizedPhone ?? ($this->phone_number ?: null),
            'email'           => $this->email ?: null,
            'address'         => $this->address ?: null,
            'saint_id'        => $this->saint_id ?: null,
            'parish_group_id' => $this->parish_group_id ?: null,
            'is_active'       => $this->is_active,
            'note'            => $this->note ?: null,
        ]);

        if ($teacher->user) {
            $userUpdate = [
                'name'      => trim($this->last_name . ' ' . $this->first_name),
                'parish_id' => $this->parishId,
            ];

            if ($this->reset_password) {
                $userUpdate['password'] = CatechistDefaultPassword::fromBirthday($this->birthday);
            }

            $teacher->user->update($userUpdate);
        } elseif ($this->create_account) {
            $accountEmail = UserAccountEmailResolver::resolveAccountEmail($this->email, $normalizedPhone);

            if (User::where('email', $accountEmail)->exists()) {
                throw new \Exception('Email/SĐT này đã được dùng cho tài khoản khác');
            }

            $user = User::create([
                'name'      => trim($this->last_name . ' ' . $this->first_name),
                'email'     => $accountEmail,
                'parish_id' => $this->parishId,
                'password'  => CatechistDefaultPassword::fromBirthday($this->birthday),
            ]);

            $user->assignRole('catechist');
            $teacher->update(['user_id' => $user->id]);
        }
    }

    public function render()
    {
        return view('livewire.teacher.teacher-edit')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
