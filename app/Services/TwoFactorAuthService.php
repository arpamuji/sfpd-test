<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    public Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new 2FA secret key.
     *
     * @return string Base32-encoded secret key
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Generate QR code SVG for 2FA setup.
     *
     * @param string $email User's email address
     * @param string $secret 2FA secret key
     * @return string SVG markup for QR code
     */
    public function getQRCodeDataUrl(string $email, string $secret): string
    {
        $companyName = config('app.name');

        $qrUrl = $this->google2fa->getQRCodeUrl(
            $companyName,
            $email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($qrUrl);
    }

    /**
     * Verify a TOTP code against a secret.
     *
     * @param string $secret 2FA secret key
     * @param string $code 6-digit TOTP code
     * @return bool True if code is valid
     */
    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Enable 2FA for a user.
     *
     * @param User $user
     * @param string $secret 2FA secret key
     * @return void
     */
    public function enable2FA(User $user, string $secret): void
    {
        $user->update([
            'google2fa_secret' => $secret,
            'google2fa_enabled' => true,
        ]);
    }

    /**
     * Disable 2FA for a user.
     *
     * @param User $user
     * @return void
     */
    public function disable2FA(User $user): void
    {
        $user->update([
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
        ]);
    }
}
