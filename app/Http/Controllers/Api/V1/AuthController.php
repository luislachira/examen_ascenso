<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $usuario = Usuario::create(array_merge(
            $request->validated(),
            [
                'rol' => Usuario::ROL_DOCENTE, // Por defecto se registran como docentes
                'estado' => Usuario::ESTADO_PENDIENTE, // El admin debe aprobar la cuenta
            ]
        ));

        return response()->json([
            'message' => 'Registro exitoso. Su cuenta está pendiente de aprobación por un administrador.'
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $usuario = Usuario::where('correo', $request->correo)->first();

        // Verificamos si el usuario existe y la contraseña es correcta
        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Verificamos el estado de la cuenta
        if ($usuario->estado !== Usuario::ESTADO_ACTIVO) { // Solo permitimos usuarios activos
            $status = $usuario->estado === Usuario::ESTADO_PENDIENTE ? 'pendiente de aprobación' : 'suspendida';
            return response()->json(['message' => "Su cuenta está {$status}."], 403);
        }

        // --- LÓGICA DE PASSPORT ---
        // Hacemos una petición interna a la ruta /oauth/token de Passport
        $response = Http::asForm()->post(url('/oauth/token'), [
            'grant_type' => 'password',
            'client_id' => '2', // <-- El ID de tu "Password Grant Client"
            'client_secret' => 'tu_client_secret', // <-- El Secret de tu "Password Grant Client"
            'username' => $request->correo,
            'password' => $request->password,
            'scope' => '',
        ]);

        // Si la petición a Passport falla, devolvemos el error
        if ($response->failed()) {
            return response()->json(['message' => 'Error de autenticación.'], 401);
        }

        // Si todo es correcto, devolvemos la respuesta de Passport (que incluye el token)
        return response()->json($response->json());
    }

    public function logout(Request $request)
    {
        // Revoca el token de acceso que se usó para autenticar la llamada actual
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }
}
