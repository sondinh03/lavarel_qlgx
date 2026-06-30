<?php

namespace App\Http\Livewire\ParishAdmin;

use App\Models\ParishAdminRegistrationRequest;
use App\Models\ParishNew;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ParishAdminSelfRegistration extends Component
{
    public ?int $targetParishId = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $note = '';

    public bool $submitted = false;

    public ?string $referenceCode = null;

    public array $parishOptions = [];

    public function mount(?int $parish = null): void
    {
        if (config('parish-admin-registration.require_invite', false)) {
            abort(404);
        }

        $activeParishes = ParishNew::query()
            ->where('status', 1)
            ->with('diocese:id,name')
            ->orderBy('name')
            ->get();

        $this->parishOptions = $activeParishes
            ->map(fn ($row) => [
                'id'   => (string) $row->id,
                'name' => $this->formatParishOptionLabel($row),
            ])
            ->values()
            ->toArray();

        if ($parish && $activeParishes->contains('id', $parish)) {
            $this->targetParishId = $parish;
        } elseif ($activeParishes->count() === 1) {
            $this->targetParishId = $activeParishes->first()->id;
        }
    }

    protected function formatParishOptionLabel(ParishNew $parish): string
    {
        $dioceseName = $parish->diocese?->name;

        return $dioceseName
            ? $parish->name . ' — ' . $dioceseName
            : $parish->name;
    }

    protected function rules(): array
    {
        return [
            'targetParishId'        => 'required|integer|exists:parishes,id',
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|max:255',
            'phone'                 => 'nullable|string|max:20',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'note'                  => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'targetParishId.required' => 'Vui lòng chọn giáo xứ.',
            'password.confirmed'      => 'Xác nhận mật khẩu không khớp.',
        ];
    }

    public function submit(): void
    {
        $maxAttempts = (int) config('parish-admin-registration.rate_limit.max_attempts', 5);
        $decaySeconds = (int) config('parish-admin-registration.rate_limit.decay_seconds', 3600);

        $ipKey = 'parish-admin-registration:ip:' . request()->ip();

        if (RateLimiter::tooManyAttempts($ipKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($ipKey);
            $message = 'Bạn đã gửi quá nhiều lần. Vui lòng thử lại sau ' . $seconds . ' giây.';
            $this->addError('submit', $message);

            return;
        }

        try {
            $this->validate($this->rules(), $this->messages());
        } catch (ValidationException $e) {
            throw $e;
        }

        $emailKey = 'parish-admin-registration:email:' . strtolower(trim($this->email));

        if (RateLimiter::tooManyAttempts($emailKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($emailKey);
            $message = 'Email này đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau ' . $seconds . ' giây.';
            $this->addError('email', $message);

            return;
        }

        if (ParishAdminRegistrationRequest::emailIsBlocked($this->email)) {
            $message = 'Email đã được sử dụng hoặc đang chờ duyệt.';
            $this->addError('email', $message);

            return;
        }

        try {
            $request = ParishAdminRegistrationRequest::create([
                'reference_code' => ParishAdminRegistrationRequest::generateReferenceCode(),
                'parish_id'      => $this->targetParishId,
                'status'         => ParishAdminRegistrationRequest::STATUS_PENDING,
                'name'           => trim($this->name),
                'email'          => strtolower(trim($this->email)),
                'phone'          => trim($this->phone) ?: null,
                'password_hash'  => Hash::make($this->password),
                'note'           => trim($this->note) ?: null,
                'ip_address'     => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            $message = 'Có lỗi khi gửi đăng ký. Vui lòng thử lại sau.';
            $this->addError('submit', $message);

            return;
        }

        RateLimiter::hit($ipKey, $decaySeconds);
        RateLimiter::hit($emailKey, $decaySeconds);

        $this->reset(['name', 'email', 'phone', 'password', 'password_confirmation', 'note']);
        $this->submitted = true;
        $this->referenceCode = $request->reference_code;
    }

    public function render()
    {
        return view('livewire.parish-admin.parish-admin-self-registration')
            ->extends('frontend.layout.landing')
            ->section('content');
    }
}
