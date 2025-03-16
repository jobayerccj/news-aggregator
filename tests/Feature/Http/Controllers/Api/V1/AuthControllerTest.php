<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private const REGISTER_ENDPOINT = '/api/v1/register';
    private const LOGIN_ENDPOINT = '/api/v1/login';
    private const LOGOUT_ENDPOINT = '/api/v1/logout';

    public function testUserCanRegisterSuccessfully()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson(self::REGISTER_ENDPOINT, $userData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['success', 'message', 'result']);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function testUserRegistrationValidationFails()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson(self::REGISTER_ENDPOINT, $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function testUserRegistrationEmailAlreadyExists()
    {
        $existingUser = User::factory()->create(['email' => 'test@example.com']);
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson(self::REGISTER_ENDPOINT, $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    public function testUserCanLoginSuccessfully()
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['success', 'message', 'result']);

        $this->assertAuthenticatedAs($user);
    }

    public function testUserLoginWithInvalidCredentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(['message' => 'Invalid credentials']);

        $this->assertGuest();
    }

    public function testUserLoginValidationFails()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function testUserLoginValidationFailsMissingPassword()
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email', 'password']);

        $this->assertArrayHasKey('errors', $response);
    }

    public function testUserCanLogoutSuccessfully()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(self::LOGOUT_ENDPOINT);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Logged out successfully']);
    }

    public function testUserCannotLogoutWhenNotAuthenticated()
    {
        $response = $this->postJson(self::LOGOUT_ENDPOINT);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(['message' => 'Unauthenticated']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
