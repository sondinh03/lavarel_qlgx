<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Decen;
use App\Models\Teacher;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

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
        // ===== SUPER_ADMIN (super_admin) =====
        if ($user->hasRole('super_admin')) {
            session([
                'parish_id' => $request->get('giaoxu'), // Admin chọn xứ
                'isAdmin' => true,
                // 'isDecen' => false,
                'isTeacher' => false,
            ]);
            
            return redirect()->intended('/admin/dashboard');
        }

        // ===== PARISH ADMIN (Quản lý xứ) =====
        if ($user->hasRole('parish_admin')) {
            $decen = Decen::where('use', $user->id)
                ->where('status', 1)
                ->first();

            if (!$decen || !$decen->pid) {
                return $this->logoutWithError('Tài khoản quản lý xứ chưa được gán giáo xứ. Vui lòng liên hệ admin.');
            }

            session([
                'parish_id' => $decen->pid,
                'isAdmin' => false,
                // 'isDecen' => true,
                'isTeacher' => false,
            ]);
            
            return redirect()->intended('/dashboard');
        }

        // ===== CATECHIST (Giáo lý viên) =====
        if ($user->hasRole('catechist')) {
            $teacher = Teacher::where('user_id', $user->id)
                ->active()
                ->first();

            if (!$teacher || !$teacher->pid) {
                return $this->logoutWithError('Tài khoản giáo lý viên chưa được gán giáo xứ. Vui lòng liên hệ admin.');
            }

            session([
                'parish_id' => $teacher->pid,
                'isAdmin' => false,
                'isDecen' => false,
                'isTeacher' => true,
            ]);
            
            return redirect()->intended('/dashboard');
        }

        // ===== KHÔNG CÓ ROLE HỢP LỆ =====
        return $this->logoutWithError('Tài khoản không có quyền truy cập hệ thống. Vui lòng liên hệ admin.');
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
}
