<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Wallet
        Route::get('/wallet', [WalletController::class, 'index']);
        Route::post('/wallet/topup', [WalletController::class, 'topup']);
        Route::get('/wallet/history', [WalletController::class, 'history']);

        // Transactions
        Route::get('/transactions', [\App\Http\Controllers\Api\TransactionController::class, 'index']);
        Route::post('/transactions', [\App\Http\Controllers\Api\TransactionController::class, 'store']);
        Route::get('/transactions/{id}', [\App\Http\Controllers\Api\TransactionController::class, 'show']);

        // Students
        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/{id}', [StudentController::class, 'show']);

        // POS
        Route::get('/pos/student/{qr_code}', [PosController::class, 'getStudentByQrCode']);
        Route::post('/pos/purchase', [PosController::class, 'purchase']);

        // Protected CRUD for Products/Categories (store/update/destroy)
        Route::apiResource('products', ProductController::class)->only(['store','update','destroy']);
        Route::apiResource('categories', CategoryController::class)->only(['store','update','destroy']);

        // Admin user management
        Route::prefix('admin')->group(function () {
            Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
            Route::post('users/{user}/promote', [\App\Http\Controllers\Api\Admin\UserController::class, 'promote']);
        });
    });

    // Public read-only endpoints for Products/Categories
    Route::apiResource('products', ProductController::class)->only(['index','show']);
    Route::apiResource('categories', CategoryController::class)->only(['index','show']);
});
