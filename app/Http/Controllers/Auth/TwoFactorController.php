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

    /**
     * Show 2FA setup page with QR code.
     * Redirects to dashboard if user already has 2FA enabled.
     *
     * @return \Inertia\Response
     */
    public function showSetup()
    {
        $user = Auth::user();

        if ($user->google2fa_enabled) {
            return redirect()->route('dashboard');
        }

        $secret = $this->twoFactorService->generateSecret();
        $qrCodeSvg = $this->twoFactorService->getQRCodeDataUrl($user->email, $secret);

        // Store secret temporarily in session for enable step
        session(['2fa_secret' => $secret]);

        return Inertia::render('Auth/TwoFactorSetup', [
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Enable 2FA for the authenticated user.
     *
     * @param TwoFactorRequest $request
     * @return RedirectResponse
     */
    public function enable2fa(TwoFactorRequest $request): RedirectResponse
    {
        $secret = session('2fa_secret');

        if (!$secret || !$this->twoFactorService->verifyCode($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $user = Auth::user();
        $this->twoFactorService->enable2FA($user, $secret);

        session()->forget('2fa_secret');

        return redirect()->route('dashboard')->with('success', '2FA enabled successfully.');
    }

    /**
     * Show 2FA verification page for login.
     *
     * @return \Inertia\Response
     */
    public function showVerification()
    {
        return Inertia::render('Auth/TwoFactorVerify');
    }

    /**
     * Verify 2FA code during login.
     *
     * @param TwoFactorRequest $request
     * @return RedirectResponse
     */
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
