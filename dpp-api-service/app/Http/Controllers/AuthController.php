<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Models\TokenBlacklist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function adminLogin(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'account' => 'required|string',
                'password' => 'required|string',
            ]);

            $result = $this->authService->authenticateAdmin(
                $credentials['account'],
                $credentials['password'],
                $request->bearerToken() // 傳遞目前的 token
            );

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials or account disabled',
                ], 401);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Admin login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Login failed',
            ], 500);
        }
    }

    public function userLogin(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'code' => 'required|string',
                'account' => 'required|string',
                'password' => 'required|string',
            ]);

            $result = $this->authService->authenticateUser(
                $credentials['code'],
                $credentials['account'],
                $credentials['password'],
                $request->bearerToken() // 傳遞目前的 token
            );

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials or account disabled',
                ], 401);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('User login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Login failed',
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->get('jwt_user');
            $token = $request->bearerToken();

            if (!$user || !$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'User or token not found'
                ], 401);
            }

            // 1. 記錄登出日誌
            Log::info('User logged out', [
                'user_id' => $user->id,
                'account' => $user->account,
                'role' => property_exists($user, 'role') ? $user->role : 'user',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // 2. Token 黑名單處理
            TokenBlacklist::create([
                'token' => hash('sha256', $token),
                'user_id' => $user->id,
                'expires_at' => now()->addDay()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);

        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
            ], 500);
        }
    }
}
