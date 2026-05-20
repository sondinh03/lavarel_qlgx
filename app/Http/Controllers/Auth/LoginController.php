<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if ($user->isParishAdmin() || $user->isCatechist()) {
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
