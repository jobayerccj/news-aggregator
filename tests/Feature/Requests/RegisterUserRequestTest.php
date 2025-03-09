<?php

namespace Tests\Feature\Requests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterUserRequestTest extends TestCase
{
    use RefreshDatabase;

    private const REGISTER_ENDPOINT = '/api/v1/register';

    public function test_registration_requires_name()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_name_must_be_a_string()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 123,
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_name_must_not_be_longer_than_255_characters()
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

    public function test_registration_requires_email()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Test User',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_email_must_be_a_valid_email()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_email_must_be_unique()
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

    public function test_registration_requires_password()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '4565',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_successful_registration()
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
