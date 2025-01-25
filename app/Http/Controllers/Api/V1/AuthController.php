<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {

    }

    public function register(RegisterUserRequest $request)
    {
        return $this->authService->registerUser($request->validated());
    }

    public function login(LoginRequest $request)
    {
        return $this->authService->loginUser($request->validated());
    }

    public function logout(Request $request)
    {
        return $this->authService->logoutUser($request);
    }
}
