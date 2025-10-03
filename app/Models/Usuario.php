<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROL_ADMINISTRADOR = '0';
    const ROL_DOCENTE = '1';

    const ESTADO_PENDIENTE = '0';
    const ESTADO_ACTIVO = '1';
    const ESTADO_INACTIVO = '2';

    protected $table = 'usuarios';
    protected $primaryKey = 'idUsuario';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'idUsuario',
        'dni',
        'nombre',
        'apellidos',
        'correo',
        'password',
        'rol',
        'estado',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getAuthIdentifierName()
    {
        return 'correo';
    }

    // Relaciones
    public function examenes()
    {
        return $this->hasMany(Examen::class, 'idUsuario', 'idUsuario');
    }

    public function resultados()
    {
        return $this->hasMany(Resultado::class, 'idUsuario', 'idUsuario');
    }

    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMINISTRADOR;
    }

    public function esDocente(): bool
    {
        return $this->rol === self::ROL_DOCENTE;
    }
}
