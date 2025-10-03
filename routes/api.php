<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Admin\UsuarioController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\ProfileController;

// Rutas Públicas de Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Recuperación de contraseña
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

// Rutas Protegidas (Requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Perfil del usuario
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Rutas solo para Administradores
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            // Lógica simple para datos de dashboard
            return response()->json([
                'users' => ['total' => 150, 'new' => 5],
                'exams' => ['total' => 20, 'active' => 3],
            ]);
        });
        Route::apiResource('/usuarios', UsuarioController::class);
        Route::patch('/usuarios/{usuario}/approve', [UsuarioController::class, 'approve']);
    });
});
