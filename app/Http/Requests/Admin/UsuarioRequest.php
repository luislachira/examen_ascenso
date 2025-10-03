<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');

        return [
            'dni' => [
                'required',
                'digits:8',
                Rule::unique('usuarios', 'dni')->ignore($usuario?->idUsuario, 'idUsuario')
            ],
            'nombre' => 'required|string|max:200',
            'apellidos' => 'required|string|max:250',
            'correo' => [
                'required',
                'email',
                'max:250',
                Rule::unique('usuarios', 'correo')->ignore($usuario?->idUsuario, 'idUsuario')
            ],
            'rol' => 'required|in:0,1',
            'estado' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 200 caracteres.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.max' => 'Los apellidos no pueden exceder 250 caracteres.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe tener un formato válido.',
            'correo.unique' => 'Este correo ya está registrado.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol debe ser Administrador (0) o Docente (1).',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser Inactivo (0) o Activo (1).',
        ];
    }
}
