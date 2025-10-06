<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
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

    protected $fillable = [
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

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellidos}";
    }

    public function getRolNameAttribute()
    {
        return $this->rol === '0' ? 'Administrador' : 'Docente';
    }

    public function getEstadoNameAttribute()
    {
        return match ($this->estado) {
            '0' => 'Inactivo',
            '1' => 'Activo',
            '2' => 'Suspendido',
            default => 'Desconocido'
        };
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', '1');
    }

    public function scopeDocentes($query)
    {
        return $query->where('rol', '1');
    }

    public function scopeAdministradores($query)
    {
        return $query->where('rol', '0');
    }

    // Métodos de verificación
    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMINISTRADOR;
    }

    public function esDocente(): bool
    {
        return $this->rol === self::ROL_DOCENTE;
    }

    public function isActivo()
    {
        return $this->estado === '1';
    }
}
