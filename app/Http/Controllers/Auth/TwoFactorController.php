<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactorRequest;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorAuthService $twoFactorService) {}

    public function showVerification()
    {
        return Inertia::render('Auth/TwoFactorVerify');
    }

    public function verify(TwoFactorRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $secret = $user->google2fa_secret;

        if ($this->twoFactorService->verifyCode($secret, $request->input('code'))) {
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['code' => 'Invalid authentication code.']);
    }
}
