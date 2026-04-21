<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuthService
{
    public Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQRCodeDataUrl(string $email, string $secret): string
    {
        $companyName = config('app.name');

        $qrUrl = $this->google2fa->getQRCodeUrl(
            $companyName,
            $email,
            $secret
        );

        $writer = new Writer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );

        return $writer->writeString($qrUrl);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    public function enable2FA(User $user, string $secret): void
    {
        $user->update([
            'google2fa_secret' => $secret,
            'google2fa_enabled' => true,
        ]);
    }

    public function disable2FA(User $user): void
    {
        $user->update([
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
        ]);
    }
}
