<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\LocaleController;

// JWT Authentication Routes
Route::prefix('v1')->group(function () {
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
    Route::post('/user/login', [AuthController::class, 'userLogin']);
    Route::post('/userProfile/change-password', [UserProfileController::class, 'changePassword']);
    Route::middleware(['jwt.auth'])->get('/locales', [LocaleController::class, 'getLocales']);
    Route::middleware(['jwt.auth'])->post('/logout', [AuthController::class, 'logout']);

    // Admin CRUD Routes
    Route::middleware(['jwt.auth'])->prefix('admin/management')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/', [AdminController::class, 'store']);
        Route::get('/{id}', [AdminController::class, 'show']);
        Route::put('/{id}', [AdminController::class, 'update']);
        Route::delete('/{id}', [AdminController::class, 'destroy']);
    });

    // Customer CRUD Routes
    Route::middleware(['jwt.auth'])->prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'destroy']);
    });

    // User CRUD Routes
    Route::middleware(['jwt.auth'])->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });

    // Role Management Routes
    Route::middleware(['jwt.auth'])->prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
        Route::get('/permissions/modules', [RoleController::class, 'getPermissionsByModules']);
        Route::get('/{id}/permissions', [RoleController::class, 'getRolePermissions']);
        Route::put('/{id}/permissions', [RoleController::class, 'updateRolePermissions']);
    });

    // Product Category Management Routes
    Route::middleware(['jwt.auth'])->prefix('productCategory')->group(function () {
        Route::get('/customerProductCategories', [ProductCategoryController::class, 'getCustomerProductCategories']);
        Route::put('/customerProductCategories', [ProductCategoryController::class, 'updateCustomerProductCategories']);
        Route::get('/', [ProductCategoryController::class, 'index']);
        Route::post('/', [ProductCategoryController::class, 'store']);
        Route::get('/{id}', [ProductCategoryController::class, 'show']);
        Route::put('/{id}', [ProductCategoryController::class, 'update']);
        Route::delete('/{id}', [ProductCategoryController::class, 'destroy']);
        Route::get('/{id}/prefixes', [ProductCategoryController::class, 'getHsPrefixes']);
        Route::put('/{id}/prefixes', [ProductCategoryController::class, 'updateHsPrefixes']);
    });
});

