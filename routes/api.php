<?php

use App\Http\Controllers\Api\V1\StudentAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [StudentAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'mobile.student'])->group(function (): void {
        Route::get('/me', [StudentAuthController::class, 'me']);
        Route::post('/auth/logout', [StudentAuthController::class, 'logout']);
    });
});
