<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correo' => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'correo.required' => 'El correo electrónico es obligatorio',
            'correo.email' => 'Debe ser un correo electrónico válido',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ];
    }
}

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni' => 'required|string|size:8|unique:usuarios,dni',
            'nombre' => 'required|string|max:200',
            'apellidos' => 'required|string|max:250',
            'correo' => 'required|email|max:250|unique:usuarios,correo',
            'password' => 'required|string|min:8|confirmed',
            'rol' => 'required|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio',
            'dni.size' => 'El DNI debe tener 8 dígitos',
            'dni.unique' => 'El DNI ya está registrado',
            'nombre.required' => 'El nombre es obligatorio',
            'apellidos.required' => 'Los apellidos son obligatorios',
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'Debe ser un correo válido',
            'correo.unique' => 'El correo ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'rol.required' => 'El rol es obligatorio',
            'rol.in' => 'El rol debe ser Administrador (0) o Docente (1)',
        ];
    }
}
