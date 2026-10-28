<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::apiResource('applications', ApplicationController::class);
    });
});
