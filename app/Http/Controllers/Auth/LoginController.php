<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->google2fa_enabled) {
                return redirect()->route('2fa.verify');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }
}
