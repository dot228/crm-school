<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->hasTwoFactorEnabled() && !session('2fa_verified')) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Будь ласка, увійдіть знову та підтвердіть 2FA.']);
        }

        return $next($request);
    }
}
