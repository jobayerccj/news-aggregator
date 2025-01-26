<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

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

    public function sendResetLinkEmail(Request $request)
    {
        return $this->authService->sendResetLinkEmail($request);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['message' => __($status)], 400);
    }
}
