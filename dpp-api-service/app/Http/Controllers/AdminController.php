<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function __construct(
        private readonly AdminService $adminService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            // 自動處理 URL 中傳來的 "true"/"false" 字串轉為布林值，避免驗證器失效
            if ($request->has('enable')) {
                $request->merge([
                    'enable' => $request->boolean('enable')
                ]);
            }

            $validated = $request->validate([
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'keyword' => 'nullable|string|max:255',
                'enable' => 'nullable|boolean'
            ]);

            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 15;
            $keyword = $validated['keyword'] ?? null;
            $enable = $validated['enable'] ?? null;

            $result = $this->adminService->getAdminsList($page, $perPage, $keyword, $enable);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admins'
            ], 500);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $admin = $this->adminService->getAdminById($id);

            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $admin
            ]);

        } catch (\Exception $e) {
            Log::error('Admin show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admin'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'account' => 'required|string|max:191|unique:admins,account',
                'password' => 'required|string|min:6',
                'enable' => 'nullable|boolean'
            ]);

            $currentAdmin = $request->get('jwt_user');
            $validated['created_by'] = $currentAdmin->id;
            $validated['updated_by'] = $currentAdmin->id;

            $admin = $this->adminService->createAdmin($validated);

            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully',
                'data' => $admin
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin'
            ], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email',
                'account' => 'nullable|string|max:191|unique:admins,account,' . $id,
                'password' => 'nullable|string|min:6',
                'enable' => 'nullable|boolean'
            ]);

            $isDefault = $request->input('isDefault');
            if ($isDefault !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify isDefault field through this API'
                ], 400);
            }

            $currentAdmin = $request->get('jwt_user');
            $validated['updated_by'] = $currentAdmin->id;

            $admin = $this->adminService->updateAdmin($id, $validated);

            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin updated successfully',
                'data' => $admin
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin'
            ], 500);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $currentAdmin = $request->get('jwt_user');
            $result = $this->adminService->deleteAdmin($id, $currentAdmin->id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete default admin or admin not found'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Admin destroy error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete admin'
            ], 500);
        }
    }
}