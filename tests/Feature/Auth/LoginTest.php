<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => bcrypt($password),
            'google2fa_enabled' => false,
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_to_2fa_when_enabled(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => bcrypt($password),
            'google2fa_enabled' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect(route('2fa.verify'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'nonexistent@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
