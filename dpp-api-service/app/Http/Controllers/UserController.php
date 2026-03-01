<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * 獲取用戶列表（支持分頁和過濾）
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'customer_id' => $request->input('customer_id'),
                'keyword'     => $request->input('keyword'), // 統一接收關鍵字
                //'name' => $request->input('name'),
                //'account' => $request->input('account'),
                'enable' => $request->input('enable'),
                'per_page' => $request->input('per_page', 15),
            ];

            $users = $this->userService->getUsers($filters);

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 創建新用戶
     */
    public function store(UserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->all());

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 獲取指定用戶
     */
    public function show(string $id)
    {
        try {
            $user = $this->userService->getUserById($id);

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * 更新用戶
     */
    public function update(UserRequest $request, string $id)
    {
        try {
            $user = $this->userService->updateUser($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 刪除用戶（軟刪除）
     */
    public function destroy(string $id)
    {
        try {
            $this->userService->deleteUser($id);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}