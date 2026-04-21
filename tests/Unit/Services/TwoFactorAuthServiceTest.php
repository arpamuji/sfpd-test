<?php

namespace Tests\Unit\Services;

use App\Services\TwoFactorAuthService;
use Tests\TestCase;

class TwoFactorAuthServiceTest extends TestCase
{
    private TwoFactorAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TwoFactorAuthService();
    }

    public function test_generate_secret_returns_valid_string(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertIsString($secret);
        $this->assertNotEmpty($secret);
    }

    public function test_verify_code_with_valid_code(): void
    {
        $secret = $this->service->generateSecret();

        // Generate a valid code using the secret and current timestamp
        $key = $this->service->google2fa->getCurrentOtp($secret);

        $this->assertTrue($this->service->verifyCode($secret, $key));
    }

    public function test_verify_code_with_invalid_code(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertFalse($this->service->verifyCode($secret, '000000'));
    }
}
