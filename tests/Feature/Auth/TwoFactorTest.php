<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_verify_with_valid_code(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $user = User::factory()->create([
            'google2fa_secret' => $secret,
            'google2fa_enabled' => true,
        ]);

        $this->actingAs($user);

        $twoFactorService = new TwoFactorAuthService();
        $code = $twoFactorService->google2fa->getCurrentOtp($secret);

        $response = $this->post(route('2fa.verify'), ['code' => $code]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_verification_fails_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'google2fa_secret' => 'JBSWY3DPEHPK3PXP',
            'google2fa_enabled' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('2fa.verify'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
    }
}
