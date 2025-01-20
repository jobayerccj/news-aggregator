<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function registerUser(array $validatedUserData)
    {
        $user = User::create([
            'name' => $validatedUserData['name'],
            'email' => $validatedUserData['email'],
            'password' => Hash::make($validatedUserData['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'message' => 'User registered successfully',
            'status' => Response::HTTP_CREATED,
        ];
    }

    public function loginUser(array $credentials): array
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
                'message' => 'Logged in successfully',
                'status' => Response::HTTP_OK,
            ];
        }

        return [
            'message' => 'Invalid credentials',
            'status' => Response::HTTP_UNAUTHORIZED,
        ];
    }

    public function logoutUser(Request $request): array
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        // $request->user()->tokens->each(function ($token) {
        //     $token->delete(); // Delete the user's token(s)
        // });

        //dd('logout', $request->user());
        return [
            'message' => 'Logged out successfully',
            'status' => Response::HTTP_OK,
        ];
    }
}
