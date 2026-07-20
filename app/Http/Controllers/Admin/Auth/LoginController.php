<?php

namespace App\Http\Controllers\Admin\Auth;

use Backpack\CRUD\app\Http\Controllers\Auth\LoginController as BackpackLoginController;
use Illuminate\Http\Request;

class LoginController extends BackpackLoginController
{
    protected $redirectAfterLogout;

    public function __construct()
    {
        $this->redirectAfterLogout = route('login');
        parent::__construct();
    }

    protected function authenticated(Request $request, $user)
    {
        if (! $user->isActive()) {
            $this->guard()->logout();

            return redirect()->route('login')
                ->withErrors([$this->username() => __('auth.inactive')]);
        }
    }
}
