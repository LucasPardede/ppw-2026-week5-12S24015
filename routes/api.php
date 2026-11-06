<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
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
});
