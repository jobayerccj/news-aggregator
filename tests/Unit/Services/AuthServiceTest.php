<?php

namespace Tests\Unit\Services;

use App\Services\AuthService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_registerUser_creates_user_and_returns_token()
    {
        $validatedUserData = $this->getValidUserData();
        $mockUser = $this->createMockUser($validatedUserData);
        $authService = app(AuthService::class);
        $result = $authService->registerUser($validatedUserData);
        $resultArray = $result->getData(true);
        $this->assertHasUserTokenAndStatus($resultArray);

        $this->assertEquals(Response::HTTP_CREATED, $result->getStatusCode());
    }

    public function testLoginUserSuccess()
    {
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $this->mockAuthAttempt(true, $credentials);
        $userMock = $this->mockAuthenticatedUser();
        $authService = app(AuthService::class);
        $result = $authService->loginUser($credentials);
        $resultArray = $result->getData(true);
        $this->assertHasUserTokenAndStatus($resultArray);
        $this->assertEquals(Response::HTTP_OK, $result->getStatusCode());
    }

    public function testLoginUserFailure()
    {
        $invalidCredentials = $this->getInValidCredentials();

        Auth::shouldReceive('attempt')
            ->once()
            ->with($invalidCredentials)
            ->andReturn(false);

        $authService = app(AuthService::class);
        $result = $authService->loginUser($invalidCredentials);
        $resultArray = $result->getData(true);
        
        $this->assertArrayHasKey('success', $resultArray);
        $this->assertArrayHasKey('message', $resultArray);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $result->getStatusCode());
    }

    protected function getValidUserData(): array
    {
        return [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];
    }

    protected function createMockUser(array $userData): Mockery\MockInterface
    {
        $mockUser = Mockery::mock('alias:App\Models\User');
        $hashedPassword = 'hashed_password';
        $authToken = 'test_token';

        $mockUser->shouldReceive('create')
            ->once()
            ->with([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $hashedPassword,
            ])
            ->andReturnSelf();

        $mockUser->shouldReceive('createToken')
            ->once()
            ->with('auth_token')
            ->andReturn((object) ['plainTextToken' => $authToken]);

        Hash::shouldReceive('make')
            ->once()
            ->with($userData['password'])
            ->andReturn($hashedPassword);

        return $mockUser;
    }

    protected function mockAuthAttempt(bool $attemptResult, array $credentials)
    {
        Auth::shouldReceive('attempt')
            ->once()
            ->with($credentials)
            ->andReturn($attemptResult);
    }

    protected function mockAuthenticatedUser()
    {
        $userMock = Mockery::mock('alias:App\Models\User');
        $userMock->shouldReceive('createToken')
            ->once()
            ->with('auth_token')
            ->andReturn((object) ['plainTextToken' => 'test_token']);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($userMock);

        return $userMock;
    }

    protected function getInValidCredentials(): array
    {
        return [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ];
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    private function assertHasUserTokenAndStatus(array $resultArray)
    {
        $this->assertArrayHasKey('success', $resultArray);
        $this->assertArrayHasKey('message', $resultArray);
        $this->assertArrayHasKey('result', $resultArray);
    }
}
