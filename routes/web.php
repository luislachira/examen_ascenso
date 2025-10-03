<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use Inertia\Inertia;

// Rutas OAuth (redirección y callback)
Route::get('/oauth/redirect/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('/oauth/callback/{provider}', [AuthController::class, 'handleProviderCallback']);

// Home -> Login
Route::get('/', function () {
    return Inertia::render('LoginPage');
});

// Rutas de la SPA con Inertia (renderizan componentes React de resources/js/pages)
Route::get('/login', function () {
    return Inertia::render('LoginPage');
});

Route::get('/oauth/success', function () {
    return Inertia::render('auth/oauth-success');
});

Route::get('/admin/dashboard', function () {
    return Inertia::render('admin/dashboard');
});

Route::get('/docente/examen', function () {
    return Inertia::render('docente/Examenes');
});

// Rutas adicionales para el sidebar
Route::get('/admin/usuarios', function () {
    return Inertia::render('admin/UserManagement');
});

Route::get('/admin/banco', function () {
    return Inertia::render('admin/banco');
});

Route::get('/admin/examenes', function () {
    return Inertia::render('admin/examenes');
});

Route::get('/admin/resultados', function () {
    return Inertia::render('admin/resultados');
});

Route::get('/docente/resultados', function () {
    return Inertia::render('docente/resultados');
});

Route::get('/register', function () {
    return Inertia::render('auth/register');
});
