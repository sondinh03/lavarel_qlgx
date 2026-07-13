<?php

namespace App\Http\Middleware;

use App\Support\AuthUser;
use Closure;
use Illuminate\Http\Request;

class RedirectIfAuthenticatedToDashboard
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = AuthUser::user();

            // Sau này có thể tách theo role
            if ($user->canManage() || $user->isCatechist()) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
