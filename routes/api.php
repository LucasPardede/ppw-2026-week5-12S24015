<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BukuApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ==========================================
    // 1. OTENTIKASI STATELESS (Sanctum Token)
    // ==========================================
    Route::post('/auth/login', [AuthTokenController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthTokenController::class, 'me']);
        Route::post('/auth/logout', [AuthTokenController::class, 'logout']);
    });

    // ==========================================
    // 2. KATALOG BUKU & KATEGORI (Publik Read)
    // ==========================================
    Route::get('/bukus', [BukuApiController::class, 'index']);
    Route::get('/bukus/{id}', [BukuApiController::class, 'show'])->whereNumber('id');

    // ==========================================
    // 3. MUTASI KATALOG BUKU (Admin Only)
    // ==========================================
    Route::middleware(['auth:sanctum', 'api.admin'])->group(function () {
        Route::post('/bukus', [BukuApiController::class, 'store']);
        Route::put('/bukus/{id}', [BukuApiController::class, 'update'])->whereNumber('id');
        Route::patch('/bukus/{id}', [BukuApiController::class, 'update'])->whereNumber('id');
        Route::delete('/bukus/{id}', [BukuApiController::class, 'destroy'])->whereNumber('id');
    });
});
