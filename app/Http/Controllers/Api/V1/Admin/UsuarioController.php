<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsuarioRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Muestra una lista de los usuarios.
     * Permite filtrar por estado (ej. /usuarios?estado=0 para pendientes).
     */
    public function index(Request $request)
    {
        $query = Usuario::query();

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        return $query->get();
    }

    public function store(UsuarioRequest $request)
    {
        $usuario = Usuario::create($request->validated());
        return response()->json($usuario, 201);
    }

    public function show(Usuario $usuario)
    {
        return response()->json($usuario);
    }

    public function update(UsuarioRequest $request, Usuario $usuario)
    {
        // Prevenimos que un admin se quite su propio rol por error
        if ($usuario->idUsuario === $request->user()->idUsuario && $request->rol !== Usuario::ROL_ADMINISTRADOR) {
            return response()->json(['message' => 'No puedes cambiar tu propio rol.'], 403);
        }

        $usuario->update($request->validated());
        return response()->json($usuario);
    }

    public function destroy(Request $request, Usuario $usuario)
    {
        // Prevenimos que un admin se elimine a sí mismo
        if ($usuario->idUsuario === $request->user()->idUsuario) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta.'], 403);
        }

        $usuario->delete();
        return response()->json(null, 204);
    }

    /**
     * Cambia el estado de un usuario a 'Activo'.
     */
    public function approve(Usuario $usuario)
    {
        // Un administrador no puede cambiar el estado de otro administrador
        if ($usuario->rol === Usuario::ROL_ADMINISTRADOR) {
            return response()->json(['message' => 'No se puede cambiar el estado de un administrador.'], 403);
        }

        $usuario->estado = Usuario::ESTADO_ACTIVO;
        $usuario->save();

        // Opcional: Aquí podrías enviar una notificación por correo al usuario.
        return response()->json(['message' => 'Usuario aprobado exitosamente.']);
    }

    /**
     * Cambia el estado de un usuario a 'Suspendido'.
     * Este método faltaba en tu implementación.
     */
    public function suspend(Usuario $usuario)
    {
        // Un administrador no puede cambiar el estado de otro administrador
        if ($usuario->rol === Usuario::ROL_ADMINISTRADOR) {
            return response()->json(['message' => 'No se puede cambiar el estado de un administrador.'], 403);
        }

        $usuario->estado = Usuario::ESTADO_INACTIVO;
        $usuario->save();

        return response()->json(['message' => 'Usuario suspendido exitosamente.']);
    }
}
