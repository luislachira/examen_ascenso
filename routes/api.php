<?php

use Illuminate\Support\Facades\Route;

// Controladores de Administrador
use App\Http\Controllers\Api\V1\Admin\UsuarioController as AdminUsuarioController;
use App\Http\Controllers\Api\V1\Admin\CategoriaController;
use App\Http\Controllers\Api\V1\Admin\PreguntaController;
use App\Http\Controllers\Api\V1\Admin\ExamenController as AdminExamenController;

// Controladores de Docente
use App\Http\Controllers\Api\V1\Docente\ExamenController as DocenteExamenController;
use App\Http\Controllers\Api\V1\Docente\ExamenAttemptController;

// --- Controladores ---
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ForgotPasswordController;
use App\Http\Controllers\Api\V1\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes V1
|--------------------------------------------------------------------------
*/

// Rutas Públicas de Autenticación
// Agrupamos las rutas bajo el prefijo 'v1' para versionar la API.
Route::prefix('v1')->group(function () {
    // Endpoint para el registro de nuevos usuarios (docentes).
    // URL: POST /api/v1/registro
    Route::post('/register', [AuthController::class, 'register']);

    // Endpoint para el inicio de sesión.
    // URL: POST /api/v1/login
    Route::post('/login', [AuthController::class, 'login']);

    // Recuperación de contraseña
    Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->name('api.v1.password.email');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('api.v1.password.update');
});

// --- Rutas Protegidas (Requieren un Token de Acceso Válido) ---
Route::middleware('auth:api')->prefix('v1')->group(function () {

    // Ruta común para cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');

    /*
    |--------------------------------------------------------------------------
    | Rutas para Administradores
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:0')->prefix('admin')->name('admin.')->group(function () { // '0' es el rol de Admin

        // Gestión de Usuarios (Aprobación, Suspensión, etc.)
        Route::patch('usuarios/{usuario}/approve', [AdminUsuarioController::class, 'approve'])->name('usuarios.approve');
        Route::patch('usuarios/{usuario}/suspend', [AdminUsuarioController::class, 'suspend'])->name('usuarios.suspend');
        Route::apiResource('usuarios', AdminUsuarioController::class)->except(['store']);

        // Gestión de Exámenes (CRUD Completo)
        Route::apiResource('examenes', AdminExamenController::class);

        // Gestión del Banco de Preguntas
        Route::apiResource('categorias', CategoriaController::class);
        Route::apiResource('preguntas', PreguntaController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Rutas para Docentes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:1')->prefix('docente')->name('docente.')->group(function () { // '1' es el rol de Docente

        // Listar y ver detalles de los exámenes disponibles
        Route::get('/examenes', [DocenteExamenController::class, 'index'])->name('examenes.index');
        Route::get('/examenes/{examen}', [DocenteExamenController::class, 'show'])->name('examenes.show');

        // Flujo para rendir un examen
        Route::post('/examenes/{examen}/start', [ExamenAttemptController::class, 'start'])->name('examenes.start');
        Route::post('/attempts/{resultado}/submit', [ExamenAttemptController::class, 'submit'])->name('attempts.submit');
    });
});
