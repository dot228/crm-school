<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->status === 'blocked') {
                Auth::logout();
                return back()->withErrors(['email' => 'Ваш акаунт заблоковано. Зверніться до адміністратора.'])->onlyInput('email');
            }

            if ($user->hasTwoFactorEnabled()) {
                session(['2fa_user_id' => $user->id]);
                Auth::logout();
                return redirect()->route('two-factor.verify');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Невірний email або пароль.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
