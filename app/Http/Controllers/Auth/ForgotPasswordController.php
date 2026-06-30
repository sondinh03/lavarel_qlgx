<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\UserAccountEmailResolver;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|max:255',
        ], [
            'email.required' => 'Vui lòng nhập email hoặc số điện thoại.',
        ]);

        $resolved = UserAccountEmailResolver::resolveForPasswordReset($request->input('email', ''));

        if ($resolved['error']) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $resolved['error']]);
        }

        $request->merge(['email' => $resolved['email']]);

        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        if ($response === Password::RESET_LINK_SENT) {
            return back()->with('status', __('passwords.sent'));
        }

        return back()
            ->withInput($request->only('email'))
            ->with('status', __('passwords.sent_generic'));
    }
}
