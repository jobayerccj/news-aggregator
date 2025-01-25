<?php

namespace App\Services;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    use ApiResponse;

    public function registerUser(array $validatedUserData): JsonResponse
    {
        $user = User::create([
            'name' => $validatedUserData['name'],
            'email' => $validatedUserData['email'],
            'password' => Hash::make($validatedUserData['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $result = [
            'user' => $user,
            'token' => $token,
        ];

        return $this->successResponse($result, 'User registered successfully', Response::HTTP_CREATED);
    }

    public function loginUser(array $credentials): JsonResponse
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            $result = [
                'user' => $user,
                'token' => $token,
                'message' => 'Logged in successfully',
                'status' => Response::HTTP_OK,
            ];

            return $this->successResponse($result);
        }

        return $this->errorResponse('Invalid credentials', Response::HTTP_UNAUTHORIZED);
    }

    public function logoutUser(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return $this->successResponse('Logged out successfully', Response::HTTP_OK);
    }
}
