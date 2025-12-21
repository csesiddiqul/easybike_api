<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::controller(AuthController::class)->group(function(){
    Route::post('/register', 'register');
    Route::get('/email-verification', 'emailVerification');
    Route::post('/login', 'login');
    Route::post("/forgot-password", 'forgotPassword');
    Route::post("/reset-password", 'resetPassword');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/resend-verify-email', [AuthController::class, 'resendEmailVerification']);
    Route::post("/change-password", [AuthController::class, 'changePassword']);
});