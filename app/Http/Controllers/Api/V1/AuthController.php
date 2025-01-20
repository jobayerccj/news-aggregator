<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {

    }

    public function register(RegisterUserRequest $request)
    {
        try {
            $response = $this->authService->registerUser($request->validated());

            return response($response, $response['status']);
        } catch (ValidationException $e) {
            return response(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $response = $this->authService->loginUser($request->validated());

            return response($response, $response['status']);
        } catch (ValidationException $e) {
            return response(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function user(Request $request)
    {
        return response([
            'user' => $request->user(),
        ], Response::HTTP_OK);
    }

    public function logout(Request $request)
    {
        $response = $this->authService->logoutUser($request);

        return response($response, $response['status']);
    }
}
