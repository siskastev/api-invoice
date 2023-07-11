<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AuthRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    public function register(RegisterRequest $request)
    {
        try {

            $userData = User::create($this->mappingPayloadRequest($request->all()));

            $token = $userData->createToken('auth_token')->plainTextToken;

            return response()->json([
                "message" => 'User created successfully',
                'data' => $userData,
                'access_token' => $token
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while creating the user'
            ], 500);
        }
    }

    private function mappingPayloadRequest(array $payload): array
    {
        return [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'role' => User::ROLE_USER
        ];
    }

    public function login(AuthRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Bad credentials'
            ], 401);
        }

        $user = User::where(['email' => $credentials['email'], 'deleted_at' => null])->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "message" => 'Login successfully',
            'data' => $user,
            'access_token' => $token
        ], 200);
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}
