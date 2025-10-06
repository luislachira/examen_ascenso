<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Este archivo es para rutas que retornan vistas HTML y usan sesiones web.
| Para una aplicación que funciona principalmente como una API, este archivo
| usualmente se mantiene simple.
|
*/

Route::get('/', function () {
    return response()->json([
        'aplicacion' => 'API del Sistema de Exámenes de Ascenso para Docentes',
        'estado' => 'Operacional',
        'documentacion' => 'Próximamente disponible.'
    ]);
});
