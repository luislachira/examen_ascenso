<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsuarioRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $usuarios = $query->orderBy('created_at', 'desc')->get();

        // Formatear fechas en formato d-m-Y
        $usuariosFormateados = $usuarios->map(function ($usuario) {
            $usuarioArray = $usuario->toArray();
            // Formatear created_at
            if (isset($usuarioArray['created_at']) && $usuarioArray['created_at']) {
                try {
                    // Si viene como string ISO, parsearlo
                    if (is_string($usuarioArray['created_at'])) {
                        $usuarioArray['created_at'] = \Carbon\Carbon::parse($usuarioArray['created_at'])->format('d-m-Y');
                    } elseif ($usuarioArray['created_at'] instanceof \Carbon\Carbon) {
                        $usuarioArray['created_at'] = $usuarioArray['created_at']->format('d-m-Y');
                    }
                } catch (\Exception $e) {
                    // Si falla, mantener el valor original
                }
            }
            // Formatear updated_at
            if (isset($usuarioArray['updated_at']) && $usuarioArray['updated_at']) {
                try {
                    // Si viene como string ISO, parsearlo
                    if (is_string($usuarioArray['updated_at'])) {
                        $usuarioArray['updated_at'] = \Carbon\Carbon::parse($usuarioArray['updated_at'])->format('d-m-Y');
                    } elseif ($usuarioArray['updated_at'] instanceof \Carbon\Carbon) {
                        $usuarioArray['updated_at'] = $usuarioArray['updated_at']->format('d-m-Y');
                    }
                } catch (\Exception $e) {
                    // Si falla, mantener el valor original
                }
            }
            return $usuarioArray;
        });

        return response()->json($usuariosFormateados);
    }

    public function store(UsuarioRequest $request)
    {
        $data = $request->validated();

        // Hash de la contraseña
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $usuario = Usuario::create($data);

        // Formatear fechas en formato d-m-Y
        $usuarioFormateado = $usuario->toArray();
        if (isset($usuarioFormateado['created_at']) && $usuario->created_at) {
            $usuarioFormateado['created_at'] = \Carbon\Carbon::parse($usuario->created_at)->format('d-m-Y');
        }
        if (isset($usuarioFormateado['updated_at']) && $usuario->updated_at) {
            $usuarioFormateado['updated_at'] = \Carbon\Carbon::parse($usuario->updated_at)->format('d-m-Y');
        }

        return response()->json($usuarioFormateado, 201);
    }

    public function show(Usuario $usuario)
    {
        // Formatear fechas en formato d-m-Y
        $usuarioFormateado = $usuario->toArray();
        if (isset($usuarioFormateado['created_at']) && $usuario->created_at) {
            $usuarioFormateado['created_at'] = \Carbon\Carbon::parse($usuario->created_at)->format('d-m-Y');
        }
        if (isset($usuarioFormateado['updated_at']) && $usuario->updated_at) {
            $usuarioFormateado['updated_at'] = \Carbon\Carbon::parse($usuario->updated_at)->format('d-m-Y');
        }

        return response()->json($usuarioFormateado);
    }

    public function update(UsuarioRequest $request, Usuario $usuario)
    {
        $currentUser = $request->user();
        $isUpdatingSelf = $usuario->idUsuario === $currentUser->idUsuario;

        // Validaciones de seguridad para auto-edición de administradores
        if ($isUpdatingSelf && $currentUser->esAdmin()) {
            $data = $request->validated();

            // Verificar si intenta cambiar su propio rol
            if (isset($data['rol']) && $data['rol'] !== Usuario::ROL_ADMINISTRADOR) {
                return response()->json([
                    'message' => 'Restricción de seguridad: No puedes cambiar tu propio rol de administrador.',
                    'type' => 'security_restriction',
                    'field' => 'rol'
                ], 403);
            }

            // Verificar si intenta cambiar su propio estado
            if (isset($data['estado']) && $data['estado'] !== Usuario::ESTADO_ACTIVO) {
                return response()->json([
                    'message' => 'Restricción de seguridad: No puedes desactivar o suspender tu propia cuenta de administrador.',
                    'type' => 'security_restriction',
                    'field' => 'estado'
                ], 403);
            }
        }

        $data = $request->validated();

        // Hash de la contraseña solo si se proporciona
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Remover password del array si está vacío
            unset($data['password']);
        }

        $usuario->update($data);

        // Formatear fechas en formato d-m-Y
        $usuarioFormateado = $usuario->toArray();
        if (isset($usuarioFormateado['created_at']) && $usuario->created_at) {
            $usuarioFormateado['created_at'] = \Carbon\Carbon::parse($usuario->created_at)->format('d-m-Y');
        }
        if (isset($usuarioFormateado['updated_at']) && $usuario->updated_at) {
            $usuarioFormateado['updated_at'] = \Carbon\Carbon::parse($usuario->updated_at)->format('d-m-Y');
        }

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'usuario' => $usuarioFormateado
        ]);
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

        $usuario->estado = Usuario::ESTADO_SUSPENDIDO;
        $usuario->save();

        return response()->json(['message' => 'Usuario suspendido exitosamente.']);
    }
}
