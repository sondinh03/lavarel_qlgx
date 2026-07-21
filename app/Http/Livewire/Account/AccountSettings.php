<?php

namespace App\Http\Livewire\Account;

use App\Services\UploadService;
use App\Support\UserAccountEmailResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class AccountSettings extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $login_identifier = '';

    public bool $login_is_phone = false;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** @var mixed */
    public $avatar_path = null;

    public ?string $existing_avatar = null;

    public function mount(): void
    {
        $user = Auth::user();

        abort_unless($user, 403);

        $this->name = (string) ($user->name ?? '');
        $this->email = (string) ($user->email ?? '');
        $this->login_is_phone = UserAccountEmailResolver::isSyntheticEmail($this->email);
        $this->login_identifier = UserAccountEmailResolver::displayLoginIdentifier($this->email);
        $this->existing_avatar = $user->avatar_path;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $validated = $this->validate([
            'name'        => 'nullable|string|max:255',
            'email'       => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'avatar_path' => 'nullable|image|max:2048',
        ], [
            'email.required'  => 'Email là bắt buộc để đăng nhập.',
            'email.unique'    => 'Email này đã được sử dụng.',
            'avatar_path.image' => 'File phải là ảnh (jpg, png, webp...).',
            'avatar_path.max'   => 'Ảnh không được vượt quá 2MB.',
        ]);

        $displayName = trim((string) ($validated['name'] ?? ''));
        if ($displayName === '') {
            $displayName = strstr($validated['email'], '@', true) ?: $validated['email'];
        }

        $data = [
            'name'  => $displayName,
            'email' => strtolower(trim($validated['email'])),
        ];

        if ($this->avatar_path) {
            $path = app(UploadService::class)->upload($this->avatar_path, 'avatars/users');

            if ($user->avatar_path) {
                delete_stored_media($user->avatar_path);
            }

            $data['avatar_path'] = $path;
            $this->existing_avatar = $path;
            $this->avatar_path = null;
        }

        $user->update($data);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->login_is_phone = UserAccountEmailResolver::isSyntheticEmail($this->email);
        $this->login_identifier = UserAccountEmailResolver::displayLoginIdentifier($this->email);

        $this->emit('toast', 'message', 'Đã cập nhật thông tin tài khoản.');
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->avatar_path) {
            delete_stored_media($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        $this->existing_avatar = null;
        $this->avatar_path = null;

        $this->emit('toast', 'message', 'Đã xóa ảnh đại diện.');
    }

    public function updatePassword(): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        try {
            $this->validate([
                'current_password'      => 'required|string',
                'password'              => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string|min:8',
            ], [
                'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
                'password.confirmed'        => 'Xác nhận mật khẩu không khớp.',
                'password.min'              => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        }

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Mật khẩu hiện tại không đúng.');
            $this->emit('toast', 'error', 'Mật khẩu hiện tại không đúng.');

            return;
        }

        $user->update([
            'password' => $this->password,
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->emit('toast', 'message', 'Đã đổi mật khẩu thành công.');
    }

    public function render()
    {
        $user = Auth::user();

        $layout = match (true) {
            $user === null => 'frontend.layout.landing',
            $user->usesCatechistLayout() => 'frontend.layout.catechist',
            $user->canManageParishioners() && ! $user->canManageCatechism() => 'frontend.layout.parishioner',
            $user->canManageCatechism() => 'frontend.layout.main',
            $user->canManageParishioners() => 'frontend.layout.parishioner',
            default => 'frontend.layout.landing',
        };

        return view('livewire.account.account-settings')
            ->extends($layout)
            ->section('content');
    }
}
