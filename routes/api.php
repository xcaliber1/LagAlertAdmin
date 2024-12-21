<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\Api\ResponseTeamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmergencyController;


Route::post('/report-emergency', [EmergencyController::class, 'sendEmergencyEmail']);

// QR Code Verification
Route::post('/verify-qr', [ResponseTeamController::class, 'verifyQrCode']);

// Register a new user
Route::post('/register', [AuthController::class, 'register']);

// Login an existing user
Route::post('/login', [AuthController::class, 'login']);

// Logout the authenticated user
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Get the authenticated user (requires authentication)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'user' => $request->user(),
    ]);
});

// Email Verification Routes
Route::post('/send-verification-code', [EmailVerificationController::class, 'sendVerificationCode']);
Route::post('/verify-code', [EmailVerificationController::class, 'verifyCode']);
