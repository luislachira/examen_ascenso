<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Usuario;

class RoleMiddleware
{
    /**
     * Maneja la verificación de rol del usuario autenticado.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role  El rol requerido ('0' para Admin, '1' para Docente)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Verificar que el usuario esté autenticado
        if (!$request->user()) {
            return response()->json([
                'message' => 'No autenticado. Por favor, inicia sesión.'
            ], 401);
        }

        // Verificar que el rol del usuario coincida con el requerido
        if ($request->user()->rol !== $role) {
            $roleName = $role === Usuario::ROL_ADMINISTRADOR ? 'Administrador' : 'Docente';
            
            return response()->json([
                'message' => "Acceso denegado. Esta ruta requiere permisos de {$roleName}."
            ], 403);
        }

        // Si todo está correcto, continuar con la petición
        return $next($request);
    }
}
