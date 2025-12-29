<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Decen;
use App\Models\SetAdmin;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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

    protected function authenticated($request, $user)
    {
        // Custom logic after user is authenticated
        // For example, log the login time or redirect based on role

        // Xác định parish_id và isAdmin
        $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->first();

        if ($setadmin) {
            session([
                'parish_id' => $request->get('giaoxu'),
                'isAdmin' => true,
                'isDecen' => false
            ]);
        } else {
            $decen = Decen::where('use', $user->id)
                ->where('status', 1)
                ->where('student', 1)->first();
            session([
                'parish_id' => $decen->pid,
                'isAdmin' => false,
                'isDecen' => true
            ]);
        }
    }
}
