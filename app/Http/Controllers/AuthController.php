<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user = Auth::user();

        // revoga os tokens anteriors para evitar acumular no db
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user'  => new UserResource($user),
            ],
            'message' => 'Login successful.',
        ]);
    }

    public function logout(): JsonResponse
    {
        // revoga so o token usado neste request
        auth()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}