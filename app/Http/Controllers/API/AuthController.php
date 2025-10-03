<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                'estado' => Usuario::ESTADO_INACTIVO, // El admin debe aprobar la cuenta
            ]
        ));

        return response()->json([
            'message' => 'Usuario registrado exitosamente. Esperando aprobación del administrador.'
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $usuario = Usuario::where('correo', $request->correo)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if ($usuario->estado === Usuario::ESTADO_INACTIVO) {
            return response()->json(['message' => 'Tu cuenta está pendiente de aprobación.'], 403);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'usuario' => [
                'id' => $usuario->idUsuario,
                'dni' => $usuario->dni,
                'nombre' => $usuario->nombre,
                'apellidos' => $usuario->apellidos,
                'correo' => $usuario->correo,
                'rol' => $usuario->rol,
            ]
        ]);
    }

    // --- OAuth ---
    public function redirectToProvider(string $provider)
    {
        if (!in_array($provider, ['google', 'microsoft'])) {
            abort(404);
        }
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        $oauthUser = Socialite::driver($provider)->user();

        $usuario = Usuario::firstOrCreate(
            ['correo' => $oauthUser->getEmail()],
            [
                // Para OAuth, no tenemos DNI. Generamos placeholder que el usuario puede actualizar luego.
                'dni' => substr(preg_replace('/\D/', '', (string)($oauthUser->id ?? '')), 0, 8) ?: (string)random_int(10000000, 99999999),
                'nombre' => $oauthUser->user['given_name'] ?? ($oauthUser->user['name'] ?? ''),
                'apellidos' => $oauthUser->user['family_name'] ?? ($oauthUser->user['surname'] ?? ''),
                'password' => Str::random(32),
                'rol' => Usuario::ROL_DOCENTE,
                'estado' => Usuario::ESTADO_ACTIVO,
            ]
        );

        if ($usuario->estado === Usuario::ESTADO_INACTIVO) {
            return response()->json(['message' => 'Tu cuenta está pendiente de aprobación.'], 403);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        $base = url('/');
        $successUrl = $base . 'oauth/success#access_token=' . urlencode($token)
            . '&id=' . urlencode($usuario->idUsuario)
            . '&dni=' . urlencode($usuario->dni)
            . '&nombre=' . urlencode($usuario->nombre)
            . '&apellidos=' . urlencode($usuario->apellidos)
            . '&correo=' . urlencode($usuario->correo)
            . '&rol=' . urlencode($usuario->rol);

        return redirect()->to($successUrl);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
