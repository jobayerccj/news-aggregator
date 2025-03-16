<?php

namespace App\Services;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

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

        return $this->successResponse(null, 'Logged out successfully', Response::HTTP_OK);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $credentials = $request->only('email');
        $response = Password::sendResetLink($request->only('email'));

        if ($response === Password::RESET_LINK_SENT) {
            $user = User::where('email', $credentials['email'])->first();

            if ($user) {
                $token = Password::getRepository()->create($user);

                $link = URL::to('/api/v1/password/reset') . '?token=' . $token . '&email=' . urlencode($user->email);

                return $this->successResponse(['link' => $link], 'Reset link sent to your email.', Response::HTTP_OK);
            }
        }
    }
}
