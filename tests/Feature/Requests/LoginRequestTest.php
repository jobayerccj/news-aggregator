<?php

namespace Tests\Feature\Requests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    private const LOGIN_ENDPOINT = '/api/v1/login';

    public function test_login_requires_email()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_valid_email()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'invalid-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_successful()
    {
        $user = User::factory()->create();

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
        $response->assertJsonStructure(['user', 'token', 'message']);
    }
}
