<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Ensure2FAEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // User has 2FA enabled - check session flag for verified status
        if ($user->google2fa_enabled) {
            if (! session()->get('2fa_verified')) {
                return redirect()->route('2fa.verify');
            }

            return $next($request);
        }

        // User doesn't have 2FA enabled - redirect to setup
        return redirect()->route('2fa.setup');
    }
}
