<?php

namespace App\Http\Livewire\Teacher;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Holymanagement;
use App\Models\Teacher;
use App\Models\HolyName;
use App\Models\Parish;
use App\Models\ParishChild;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

    // ==================== ACCOUNT FIELDS ====================

    /** @var bool Checkbox tạo tài khoản */
    public $createAccount = false;

    /** @var string Email cho tài khoản */
    public $accountEmail;

    /** @var string Password được generate */
    public $accountPassword;

    /** @var string Password mới tạo để hiển thị */
    public $generatedPassword = null;

    // ==================== VALIDATION ====================

    protected $formRules = [
        // 'holy_id' => 'nullable|integer|exists:holy_names,id',
        'name' => 'required|string|max:255',
        // 'paid' => 'nullable|integer|exists:parish_child,id',
        'birthday' => 'nullable|date|before:today',
        'phoneNumber' => 'nullable|string|max:20',
        'note' => 'nullable|string|max:500',
        'status' => 'required|boolean',

        // Account fields (chỉ validate khi createAccount = true)
        'createAccount' => 'boolean',
        'accountEmail' => 'required_if:createAccount,true|nullable|unique:users,email',
        // 'accountEmail' => 'required_if:createAccount,true|nullable|email|unique:users,email',
        'accountPassword' => 'required_if:createAccount,true|nullable|string|min:6',
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

        // Account validation messages
        'accountEmail.required_if' => 'Vui lòng nhập email hoặc số điện thoại khi tạo tài khoản',
        'accountEmail.email' => 'Email không hợp lệ',
        'accountEmail.unique' => 'Email đã tồn tại trong hệ thống',
        'accountPassword.required_if' => 'Vui lòng nhập mật khẩu',
        'accountPassword.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
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

    /**
     * Khi checkbox createAccount thay đổi
     */
    public function updatedCreateAccount(): void
    {
        if ($this->createAccount) {
            // ✅ Chỉ generate KHI BẬT checkbox
            $this->generateAccountCredentials();
        } else {
            // ❌ Clear khi TẮT checkbox
            $this->accountEmail = '';
            $this->accountPassword = '';
            $this->resetValidation(['accountEmail', 'accountPassword']);
        }
    }

    /**
     * Khi phoneNumber thay đổi, update email suggestion
     */
    public function updatedPhoneNumber(): void
    {
        if ($this->createAccount && $this->phoneNumber) {
            $this->accountEmail = $this->phoneNumber . '@giaoxu.com';
        }
    }

    /**
     * Khi name thay đổi (có thể dùng để suggest email)
     */
    public function updatedName(): void
    {
        // Nếu đang tạo tài khoản nhưng chưa có phone
        if ($this->createAccount && !$this->phoneNumber && $this->name) {
            // Tạo email từ tên (không dấu)
            $emailPrefix = $this->generateEmailFromName($this->name);
            $this->accountEmail = $emailPrefix . '@giaoxu.com';
        }
    }

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

            // Khi edit: không hiển thị option tạo tài khoản nếu đã có
            $this->createAccount = false;

            $this->showForm = true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy giáo viên này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading teacher for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin giáo viên');
        }
    }

    /**
     * Lưu (create hoặc update) với tự động tạo tài khoản
     */
    public function save(): void
    {
        $this->requireManager();

        // Validate form data
        $this->validate($this->formRules, $this->messages);

        // Nếu đang edit và teacher đã có tài khoản, không cho tạo account mới
        if ($this->editingId) {
            $existingTeacher = Teacher::find($this->editingId);
            if ($existingTeacher && $existingTeacher->user_id && $this->createAccount) {
                session()->flash('warning', 'Giáo viên này đã có tài khoản, không thể tạo mới');
                $this->createAccount = false;
                return;
            }
        }

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

            $userId = null;

            // ✅ Tạo tài khoản nếu checkbox được chọn
            if ($this->createAccount && !$this->editingId) {
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->accountEmail,
                    'password' => Hash::make($this->accountPassword),
                ]);

                // ✅ GÁN ROLE CATECHIST
                try {
                    // Kiểm tra role tồn tại chưa, nếu chưa thì tạo
                    $role = \Spatie\Permission\Models\Role::firstOrCreate(
                        ['name' => 'catechist'],
                        ['guard_name' => 'web']
                    );

                    // Gán role cho user
                    $user->assignRole('catechist');

                    Log::info('Assigned catechist role to user', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                } catch (\Exception $e) {
                    // Log lỗi nhưng không dừng process
                    Log::warning('Could not assign catechist role', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $userId = $user->id;

                // Lưu password để hiển thị sau khi tạo
                $this->generatedPassword = $this->accountPassword;
            }

            // Tạo/cập nhật Teacher
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
                    'user_id' => $userId,  // ✅ Gán user_id nếu có
                    'did' => 0,
                    'deid' => 0,
                ]
            );

            DB::commit();

            $message = $this->editingId
                ? 'Cập nhật giáo viên thành công'
                : 'Thêm giáo viên mới thành công';

            if ($this->createAccount && $this->generatedPassword) {
                $message .= ' và đã tạo tài khoản đăng nhập';
                session()->flash('new_password', $this->generatedPassword);
                session()->flash('account_email', $this->accountEmail);
            }

            session()->flash('message', $message);

            $this->resetForm();
            $this->loadTeachers();

            $this->emit($this->editingId ? 'teacherUpdated' : 'teacherCreated');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Error saving teacher', [
                'editing_id' => $this->editingId,
                'name' => $this->name,
                'create_account' => $this->createAccount,
            ]);

            session()->flash('error', 'Có lỗi khi lưu giáo viên. Vui lòng thử lại.');
        }
    }

    /**
     * Reset password cho teacher
     */
    public function resetPassword(int $teacherId): void
    {
        $this->requireManager();

        try {
            $teacher = Teacher::where('pid', $this->parishId)
                ->with('user')
                ->findOrFail($teacherId);

            if (!$teacher->user_id || !$teacher->user) {
                session()->flash('error', 'Giáo viên này chưa có tài khoản');
                return;
            }

            // Generate new password từ ngày sinh (nếu có)
            $newPassword = $this->generatePasswordFromBirthday($teacher->birthday?->format('Y-m-d'));

            // Update password
            $teacher->user->update([
                'password' => Hash::make($newPassword),
            ]);

            // ✅ Đảm bảo user có role catechist
            if (!$teacher->user->hasRole('catechist')) {
                try {
                    $teacher->user->assignRole('catechist');
                } catch (\Exception $e) {
                    Log::warning('Could not assign catechist role during password reset', [
                        'user_id' => $teacher->user->id,
                    ]);
                }
            }

            session()->flash('message', "Đã reset mật khẩu cho {$teacher->name}");
            session()->flash('new_password', $newPassword);
            session()->flash('account_email', $teacher->user->email);
        } catch (\Exception $e) {
            $this->logError($e, 'Error resetting password', ['teacher_id' => $teacherId]);
            session()->flash('error', 'Có lỗi khi reset mật khẩu');
        }
    }

    /**
     * Unlink account (giữ nguyên từ version trước)
     */
    public function unlinkAccount(int $teacherId): void
    {
        $this->requireAdmin();

        try {
            DB::beginTransaction();

            $teacher = Teacher::where('pid', $this->parishId)
                ->findOrFail($teacherId);

            if (!$teacher->user_id) {
                session()->flash('warning', 'Giáo viên này chưa có tài khoản');
                return;
            }

            $teacher->update(['user_id' => null]);

            DB::commit();

            session()->flash('message', 'Đã gỡ liên kết tài khoản');
            $this->loadTeachers();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error unlinking account', ['teacher_id' => $teacherId]);
            session()->flash('error', 'Có lỗi khi gỡ liên kết tài khoản');
        }
    }

    /**
     * Generate account credentials (email + password)
     */
    private function generateAccountCredentials(): void
    {
        // Email: ưu tiên phone_number, fallback là tên
        if ($this->phoneNumber) {
            $this->accountEmail = $this->phoneNumber;
        } elseif ($this->name) {
            $emailPrefix = $this->generateEmailFromName($this->name);
            $this->accountEmail = $emailPrefix . '@giaoxu.com';
        } else {
            $this->accountEmail = 'glv' . rand(1000, 9999) . '@giaoxu.com';
        }

        $this->accountPassword = $this->generatePasswordFromBirthday($this->birthday);
    }

    /**
     * ✅ Generate password from birthday
     * 
     * Format: ddMMyy (VD: 15/08/1990 → 150890)
     * 
     * @param string|null $birthday Date string (Y-m-d)
     * @return string Generated password
     */
    private function generatePasswordFromBirthday(?string $birthday): string
    {
        if (!empty($birthday)) {
            try {
                $date = Carbon::parse($birthday);

                // Format: ddMMyy
                // VD: 15/08/1990 → 150890
                $password = $date->format('dmy');

                return $password;
            } catch (\Exception $e) {
                // Nếu parse date fail, fallback to random
                return $this->generateRandomPassword();
            }
        }

        // Fallback: Random password khi không có ngày sinh
        return $this->generateRandomPassword();
    }

    /**
     * Generate random password
     */
    private function generateRandomPassword(int $length = 12): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $password;
    }

    /**
     * Generate email prefix from Vietnamese name
     * VD: "Nguyễn Văn A" -> "nguyenvana"
     */
    private function generateEmailFromName(string $name): string
    {
        // Remove Vietnamese accents
        $name = $this->removeVietnameseAccents($name);

        // Convert to lowercase and remove special chars
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9]/', '', $name);

        return substr($name, 0, 20); // Max 20 chars
    }

    /**
     * Remove Vietnamese accents
     */
    private function removeVietnameseAccents(string $str): string
    {
        $accents = [
            'à' => 'a',
            'á' => 'a',
            'ả' => 'a',
            'ã' => 'a',
            'ạ' => 'a',
            'ă' => 'a',
            'ằ' => 'a',
            'ắ' => 'a',
            'ẳ' => 'a',
            'ẵ' => 'a',
            'ặ' => 'a',
            'â' => 'a',
            'ầ' => 'a',
            'ấ' => 'a',
            'ẩ' => 'a',
            'ẫ' => 'a',
            'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e',
            'é' => 'e',
            'ẻ' => 'e',
            'ẽ' => 'e',
            'ẹ' => 'e',
            'ê' => 'e',
            'ề' => 'e',
            'ế' => 'e',
            'ể' => 'e',
            'ễ' => 'e',
            'ệ' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'ỉ' => 'i',
            'ĩ' => 'i',
            'ị' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ỏ' => 'o',
            'õ' => 'o',
            'ọ' => 'o',
            'ô' => 'o',
            'ồ' => 'o',
            'ố' => 'o',
            'ổ' => 'o',
            'ỗ' => 'o',
            'ộ' => 'o',
            'ơ' => 'o',
            'ờ' => 'o',
            'ớ' => 'o',
            'ở' => 'o',
            'ỡ' => 'o',
            'ợ' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'ủ' => 'u',
            'ũ' => 'u',
            'ụ' => 'u',
            'ư' => 'u',
            'ừ' => 'u',
            'ứ' => 'u',
            'ử' => 'u',
            'ữ' => 'u',
            'ự' => 'u',
            'ỳ' => 'y',
            'ý' => 'y',
            'ỷ' => 'y',
            'ỹ' => 'y',
            'ỵ' => 'y',
        ];

        return strtr($str, $accents);
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
            'createAccount',
            'accountEmail',
            'accountPassword',
            'generatedPassword',
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
