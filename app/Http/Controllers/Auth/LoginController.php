<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $email = request('email') ?? '';
        $throttleKey = 'login-'.request()->ip().'-'.Str::lower($email);
        $throttleUntil = null;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $throttleUntil = now()->addSeconds(RateLimiter::availableIn($throttleKey))->timestamp;
        }

        return Inertia::render('Auth/Login', [
            'throttleUntil' => $throttleUntil,
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $throttleKey = 'login-'.$request->ip().'-'.Str::lower($request->input('email'));

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([
                'email' => 'Too many failed attempts. Please try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ])->onlyInput('email')->with('throttleUntil', now()->addSeconds(RateLimiter::availableIn($throttleKey))->timestamp);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            RateLimiter::clear($throttleKey);

            if ($user->google2fa_enabled) {
                return redirect()->route('2fa.verify');
            }

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        $response = back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $response->with('throttleUntil', now()->addSeconds(RateLimiter::availableIn($throttleKey))->timestamp);
        }

        return $response;
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }
}
