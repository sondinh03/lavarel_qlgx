<?php

namespace App\Http\Controllers\Admin\Auth;

use Backpack\CRUD\app\Http\Controllers\Auth\LoginController as BackpackLoginController;

class LoginController extends BackpackLoginController
{
    protected $redirectAfterLogout;

    public function __construct()
    {
        $this->redirectAfterLogout = route('login');
        parent::__construct();
    }
}
