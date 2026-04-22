<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactorRequest;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorAuthService $twoFactorService) {}

    /**
     * Show 2FA setup page with QR code.
     * Redirects to dashboard if user already has 2FA enabled.
     *
     * @return Response
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
     */
    public function enable2fa(TwoFactorRequest $request): RedirectResponse
    {
        $secret = session('2fa_secret');

        if (! $secret || ! $this->twoFactorService->verifyCode($secret, $request->input('code'))) {
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
     * @return Response
     */
    public function showVerification()
    {
        $user = Auth::user();
        $throttleKey = '2fa-verify-'.$user->id;

        // Pass throttle info if user is rate limited
        $throttleUntil = null;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $throttleUntil = now()->addSeconds(RateLimiter::availableIn($throttleKey))->timestamp;
        }

        return Inertia::render('Auth/TwoFactorVerify', [
            'throttleUntil' => $throttleUntil,
        ]);
    }

    /**
     * Verify 2FA code during login.
     */
    public function verify(TwoFactorRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $secret = $user->google2fa_secret;
        $throttleKey = '2fa-verify-'.$user->id;

        // Check if throttled FIRST
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([
                'code' => 'Too many failed attempts. Please try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ])->onlyInput('code')->with('throttleUntil', now()->addSeconds(RateLimiter::availableIn($throttleKey))->timestamp);
        }

        // Verify the code
        if ($this->twoFactorService->verifyCode($secret, $request->input('code'))) {
            // Clear throttle on success and mark 2FA as verified for this session
            RateLimiter::clear($throttleKey);
            session(['2fa_verified' => true]);

            return redirect()->intended(route('dashboard'));
        }

        // Invalid code - increment throttle counter
        RateLimiter::hit($throttleKey, 60);
        $remaining = 5 - RateLimiter::attempts($throttleKey);

        // If now throttled, pass the throttle until time
        $response = back()->withErrors([
            'code' => 'Invalid authentication code. '.$remaining.' attempts remaining.',
        ])->onlyInput('code');

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $response->with('throttleUntil', now()->addSeconds(RateLimiter::availableIn($throttleKey))->timestamp);
        }

        return $response;
    }
}
