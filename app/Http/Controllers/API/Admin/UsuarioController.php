<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsuarioRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index()
    {
        return Usuario::all();
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
        $usuario->update($request->validated());
        return response()->json($usuario);
    }

    public function destroy(Usuario $usuario)
    {
        $usuario->delete();
        return response()->json(null, 204);
    }

    public function approve(Usuario $usuario)
    {
        $usuario->estado = Usuario::ESTADO_ACTIVO;
        $usuario->save();
        return response()->json(['message' => 'Usuario aprobado exitosamente.']);
    }
}
