<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\UserAccountEmailResolver;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function credentials(Request $request): array
    {
        return [
            'email'    => UserAccountEmailResolver::resolveLoginIdentifier($request->input('email', '')),
            'password' => $request->input('password'),
        ];
    }

    protected function attemptLogin(Request $request): bool
    {
        $credentials = $this->credentials($request);

        if ($this->guard()->attempt($credentials, $request->boolean('remember'))) {
            return true;
        }

        $raw = trim((string) $request->input('email', ''));

        if ($raw === '') {
            return false;
        }

        // SĐT hoặc email ảo @giaoly.local → tìm email thật gắn với teacher
        $phoneForLookup = null;

        if (! UserAccountEmailResolver::isEmail($raw)) {
            $phoneForLookup = $raw;
        } elseif (UserAccountEmailResolver::isSyntheticEmail($credentials['email'])) {
            $phoneForLookup = explode('@', $credentials['email'], 2)[0] ?? null;
        }

        if ($phoneForLookup) {
            $realEmail = UserAccountEmailResolver::findUserEmailByPhone($phoneForLookup);

            if ($realEmail && strtolower($realEmail) !== strtolower($credentials['email'])) {
                return $this->guard()->attempt([
                    'email'    => $realEmail,
                    'password' => $credentials['password'],
                ], $request->boolean('remember'));
            }
        }

        return false;
    }

    /**
     * Thông báo lỗi đăng nhập cụ thể hơn (tài khoản không tồn tại / sai mật khẩu).
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [$this->failedLoginMessage($request)],
        ]);
    }

    protected function failedLoginMessage(Request $request): string
    {
        $raw = trim((string) $request->input('email', ''));

        if ($raw === '') {
            return __('auth.failed');
        }

        if ($this->findLoginUser($raw)) {
            return __('auth.failed_password');
        }

        return __('auth.failed_user');
    }

    protected function findLoginUser(string $raw): ?User
    {
        $resolved = UserAccountEmailResolver::resolveLoginIdentifier($raw);
        $user = User::where('email', $resolved)->first();

        if ($user) {
            return $user;
        }

        $phone = UserAccountEmailResolver::isEmail($raw)
            ? (UserAccountEmailResolver::isSyntheticEmail($resolved)
                ? (explode('@', $resolved, 2)[0] ?? null)
                : null)
            : $raw;

        if ($phone) {
            return UserAccountEmailResolver::findUserByPhone($phone);
        }

        return null;
    }

    /**
     * ✅ Xử lý sau khi đăng nhập thành công
     *
     * Logic:
     * 1. Xác định role từ Spatie Permission
     * 2. Lưu session (backward compatibility)
     * 3. Redirect theo role
     */
    protected function authenticated($request, $user)
    {
        if ($request->filled('remember')) {
            config(['session.remember_expire' => 60 * 24 * 30]);
        }

        if (!$user->hasRole('super_admin') && !$user->parish_id) {
            return $this->logoutWithError('Tài khoản chưa được gán giáo xứ. Vui lòng liên hệ quản trị hệ thống.');
        }

        if ($user->isSuperAdmin()) {
            return redirect('/admin/dashboard');
        }

        if ($user->canManage() || $user->isCatechist()) {
            return redirect()->route('module.select');
        }

        return $this->logoutWithError('Tài khoản không có quyền truy cập. Vui lòng liên hệ quản trị hệ thống.');
    }

    /**
     * ✅ Helper: Logout và trả về error
     */
    private function logoutWithError(string $message)
    {
        Auth::logout();

        return redirect()->route('login')
            ->withErrors(['email' => $message]);
    }

    protected function loggedOut(Request $request)
    {
        return redirect()->route('login');
    }
}
