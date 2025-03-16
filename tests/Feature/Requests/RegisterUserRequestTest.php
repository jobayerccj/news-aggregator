<?php

namespace Tests\Feature\Requests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterUserRequestTest extends TestCase
{
    use RefreshDatabase;

    private const REGISTER_ENDPOINT = '/api/v1/register';

    public function testRegistrationRequiresName()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testNameMustBeAString()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 123,
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testNameMustNotBeLongerThan255Characters()
    {
        $longName = str_repeat('a', 256);
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => $longName,
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testRegistrationRequiresEmail()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Test User',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testEmailMustBeAValidEmail()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testEmailMustBeUnique()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testRegistrationRequiresPassword()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function testPasswordMustBeAtLeast8Characters()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '4565',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function testSuccessfulRegistration()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'message', 'result']);
    }
}
