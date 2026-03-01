<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use App\Models\Admin;
use App\Models\User;
use App\Models\TokenBlacklist;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JwtAuth
{
    public function __construct(
        private readonly JwtService $jwtService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided'
                ], 401);
            }

            // 檢查 token 是否在黑名單中
            $tokenHash = hash('sha256', $token);
            if (TokenBlacklist::where('token', $tokenHash)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has been blacklisted'
                ], 401);
            }

            // 驗證 token
            $payload = $this->jwtService->verifyToken($token);
            if (!$payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token'
                ], 401);
            }

            // 根據角色載入對應的使用者
            $user = null;
            if ($payload['role'] === 'Admin' || $payload['role'] === 'SysAdmin') {
                $user = Admin::where('id', $payload['uid'])
                    ->where('enable', true)
                    ->where('isDeleted', false)
                    ->first();
            } else if ($payload['role'] === 'User') {
                $user = User::where('id', $payload['uid'])
                    ->where('enable', true)
                    ->where('isDeleted', false)
                    ->first();
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or disabled'
                ], 401);
            }

            // 將使用者和 token payload 附加到 request
            $request->merge(['jwt_user' => $user]);
            $request->merge(['jwt_payload' => $payload]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            return $next($request);

        } catch (\Exception $e) {
            Log::error('JWT middleware error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed'
            ], 401);
        }
    }
}