<?php

namespace App\Http\Livewire\ParishAdmin;

use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\ParishAdminRegistrationRequest;
use App\Models\ParishNew;
use App\Models\User;
use App\Notifications\ParishAdminRegistrationSubmitted;
use App\Services\Admin\SystemOverviewService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ParishAdminSelfRegistration extends Component
{
    public $dioceseId = null;

    public $deaneryId = null;

    public $targetParishId = null;

    public bool $useCustomParish = false;

    public string $customParishName = '';

    /** @var list<string> */
    public array $parishGroupNames = [''];

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $note = '';

    /** Một trong các role: parish_admin | parishioner_admin | catechism_admin */
    public string $selectedRole = 'parish_admin';

    public bool $submitted = false;

    public ?string $referenceCode = null;

    public array $dioceseOptions = [];

    public array $deaneryOptions = [];

    public array $parishOptions = [];

    public function mount(?int $parish = null): void
    {
        if (config('parish-admin-registration.require_invite', false)) {
            abort(404);
        }

        $this->dioceseOptions = Diocese::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($row) => [
                'id'   => (string) $row->id,
                'name' => $row->name,
            ])
            ->values()
            ->toArray();

        if ($parish) {
            $existing = ParishNew::query()
                ->where('status', 1)
                ->find($parish);

            if ($existing) {
                $this->dioceseId = $existing->diocese_id ? (int) $existing->diocese_id : null;
                $this->deaneryId = $existing->deanery_id ? (int) $existing->deanery_id : null;
                $this->targetParishId = (int) $existing->id;
                $this->loadDeaneryOptions();
                $this->loadParishOptions();
            }
        }
    }

    public function updatedDioceseId(): void
    {
        $this->deaneryId = null;
        $this->targetParishId = null;
        $this->customParishName = '';
        $this->useCustomParish = false;
        $this->parishGroupNames = [''];
        $this->loadDeaneryOptions();
        $this->parishOptions = [];
    }

    public function updatedDeaneryId(): void
    {
        $this->targetParishId = null;
        $this->customParishName = '';
        $this->useCustomParish = false;
        $this->parishGroupNames = [''];
        $this->loadParishOptions();
    }

    public function updatedUseCustomParish($value): void
    {
        if ($value) {
            $this->targetParishId = null;
            if ($this->parishGroupNames === []) {
                $this->parishGroupNames = [''];
            }
        } else {
            $this->customParishName = '';
            $this->parishGroupNames = [''];
        }
    }

    public function updatedTargetParishId(): void
    {
        if ($this->targetParishId) {
            $this->useCustomParish = false;
            $this->customParishName = '';
            $this->parishGroupNames = [''];
        }
    }

    public function addParishGroupRow(): void
    {
        $this->parishGroupNames[] = '';
    }

    public function removeParishGroupRow(int $index): void
    {
        if (count($this->parishGroupNames) <= 1) {
            $this->parishGroupNames = [''];

            return;
        }

        unset($this->parishGroupNames[$index]);
        $this->parishGroupNames = array_values($this->parishGroupNames);
    }

    protected function loadDeaneryOptions(): void
    {
        if (! $this->dioceseId) {
            $this->deaneryOptions = [];

            return;
        }

        $this->deaneryOptions = Deanery::query()
            ->where('did', $this->dioceseId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($row) => [
                'id'   => (string) $row->id,
                'name' => $row->name,
            ])
            ->values()
            ->toArray();
    }

    protected function loadParishOptions(): void
    {
        if (! $this->deaneryId) {
            $this->parishOptions = [];

            return;
        }

        $this->parishOptions = ParishNew::query()
            ->where('status', 1)
            ->where('deanery_id', $this->deaneryId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($row) => [
                'id'   => (string) $row->id,
                'name' => $row->name,
            ])
            ->values()
            ->toArray();
    }

    protected function rules(): array
    {
        $roleKeys = array_keys(config('parish-admin-registration.roles', []));

        return [
            'dioceseId'         => 'required|integer|exists:dioceses,id',
            'deaneryId'         => 'required|integer|exists:deanerys,id',
            'targetParishId'    => [
                Rule::requiredIf(fn () => ! $this->useCustomParish),
                'nullable',
                'integer',
                'exists:parishes,id',
            ],
            'customParishName'  => [
                Rule::requiredIf(fn () => $this->useCustomParish),
                'nullable',
                'string',
                'max:255',
            ],
            'parishGroupNames'  => [
                Rule::requiredIf(fn () => $this->useCustomParish),
                'array',
            ],
            'parishGroupNames.*' => 'nullable|string|max:255',
            'selectedRole'      => ['required', 'string', Rule::in($roleKeys)],
            'name'              => 'nullable|string|max:255',
            'email'             => 'required|email|max:255',
            'phone'             => 'nullable|string|max:20',
            'password'          => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'note'              => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'dioceseId.required'        => 'Vui lòng chọn giáo phận.',
            'deaneryId.required'        => 'Vui lòng chọn giáo hạt.',
            'targetParishId.required'   => 'Vui lòng chọn giáo xứ hoặc nhập tên giáo xứ mới.',
            'customParishName.required' => 'Vui lòng nhập tên giáo xứ.',
            'parishGroupNames.required' => 'Vui lòng nhập ít nhất một giáo họ.',
            'selectedRole.required'     => 'Vui lòng chọn một quyền quản trị.',
            'selectedRole.in'           => 'Quyền quản trị không hợp lệ.',
            'password.confirmed'        => 'Xác nhận mật khẩu không khớp.',
        ];
    }

    public function selectRole(string $role): void
    {
        $roleKeys = array_keys(config('parish-admin-registration.roles', []));

        if (in_array($role, $roleKeys, true)) {
            $this->selectedRole = $role;
        }
    }

    public function submit(): void
    {
        $maxAttempts = (int) config('parish-admin-registration.rate_limit.max_attempts', 5);
        $decaySeconds = (int) config('parish-admin-registration.rate_limit.decay_seconds', 3600);

        $ipKey = 'parish-admin-registration:ip:' . request()->ip();

        if (RateLimiter::tooManyAttempts($ipKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($ipKey);
            $this->addError('submit', 'Bạn đã gửi quá nhiều lần. Vui lòng thử lại sau ' . $seconds . ' giây.');

            return;
        }

        try {
            $this->validate($this->rules(), $this->messages());
        } catch (ValidationException $e) {
            throw $e;
        }

        $groupNames = $this->normalizedParishGroupNames();

        if ($this->useCustomParish && $groupNames === []) {
            $this->addError('parishGroupNames', 'Vui lòng nhập ít nhất một giáo họ.');

            return;
        }

        $deanery = Deanery::query()->find($this->deaneryId);

        if (! $deanery || (int) $deanery->did !== (int) $this->dioceseId) {
            $this->addError('deaneryId', 'Giáo hạt không thuộc giáo phận đã chọn.');

            return;
        }

        if ($this->targetParishId) {
            $parish = ParishNew::query()->find($this->targetParishId);

            if (! $parish
                || (int) $parish->deanery_id !== (int) $this->deaneryId
                || (int) $parish->diocese_id !== (int) $this->dioceseId
            ) {
                $this->addError('targetParishId', 'Giáo xứ không thuộc giáo hạt / giáo phận đã chọn.');

                return;
            }
        }

        $emailKey = 'parish-admin-registration:email:' . strtolower(trim($this->email));

        if (RateLimiter::tooManyAttempts($emailKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($emailKey);
            $this->addError('email', 'Email này đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau ' . $seconds . ' giây.');

            return;
        }

        if (ParishAdminRegistrationRequest::emailIsBlocked($this->email)) {
            $this->addError('email', 'Email đã được sử dụng hoặc đang chờ duyệt.');

            return;
        }

        $roles = [$this->selectedRole];

        try {
            $request = ParishAdminRegistrationRequest::create([
                'reference_code'     => ParishAdminRegistrationRequest::generateReferenceCode(),
                'parish_id'          => $this->useCustomParish ? null : (int) $this->targetParishId,
                'diocese_id'         => (int) $this->dioceseId,
                'deanery_id'         => (int) $this->deaneryId,
                'custom_parish_name' => $this->useCustomParish ? trim($this->customParishName) : null,
                'requested_parish_groups' => $this->useCustomParish ? $groupNames : null,
                'status'             => ParishAdminRegistrationRequest::STATUS_PENDING,
                'name'               => trim($this->name) ?: null,
                'email'              => strtolower(trim($this->email)),
                'phone'              => trim($this->phone) ?: null,
                'password_hash'      => Hash::make($this->password),
                'note'               => trim($this->note) ?: null,
                'requested_roles'    => $roles,
                'ip_address'         => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            $this->addError('submit', 'Có lỗi khi gửi đăng ký. Vui lòng thử lại sau.');

            return;
        }

        RateLimiter::hit($ipKey, $decaySeconds);
        RateLimiter::hit($emailKey, $decaySeconds);

        $superAdmins = User::role('super_admin')->get();
        notify_users($superAdmins, new ParishAdminRegistrationSubmitted($request));
        app(SystemOverviewService::class)->forget();

        $this->reset([
            'name',
            'email',
            'phone',
            'password',
            'password_confirmation',
            'note',
            'customParishName',
            'useCustomParish',
            'parishGroupNames',
        ]);
        $this->parishGroupNames = [''];
        $this->selectedRole = 'parish_admin';
        $this->submitted = true;
        $this->referenceCode = $request->reference_code;
    }

    /** @return list<string> */
    protected function normalizedParishGroupNames(): array
    {
        return collect($this->parishGroupNames)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->values()
            ->all();
    }

    public function getRoleCatalogProperty(): array
    {
        return config('parish-admin-registration.roles', []);
    }

    public function render()
    {
        return view('livewire.parish-admin.parish-admin-self-registration')
            ->extends('frontend.layout.landing')
            ->section('content');
    }
}
