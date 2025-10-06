<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultado extends Model
{
    use HasFactory;

    protected $table = 'resultados';
    protected $primaryKey = 'idResultado';
    public $timestamps = false;

    protected $fillable = [
        'idExamen',
        'idUsuario',
        'intento',
        'puntaje',
        'fechaRealizacion',
        'tiempo_usado',
        'estado',
        'ip_address',
        'dispositivo',
    ];

    protected $casts = [
        'idExamen' => 'integer',
        'idUsuario' => 'integer',
        'intento' => 'integer',
        'puntaje' => 'decimal:2',
        'aprobado' => 'boolean',
        'fechaRealizacion' => 'datetime',
        'tiempo_usado' => 'integer',
        'estado' => 'string',
    ];

    /**
     * Define la relación inversa: Un Resultado pertenece a un Usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }

    /**
     * Define la relación inversa: Un Resultado pertenece a un Examen.
     */
    public function examen()
    {
        return $this->belongsTo(Examen::class, 'idExamen', 'idExamen');
    }

    // Accessors
    public function getEstadoNameAttribute()
    {
        return match ($this->estado) {
            '0' => 'Iniciado',
            '1' => 'Finalizado',
            '2' => 'Anulado',
            default => 'Desconocido'
        };
    }

    public function getTiempoUsadoFormateadoAttribute()
    {
        $minutos = floor($this->tiempo_usado / 60);
        $segundos = $this->tiempo_usado % 60;
        return sprintf('%02d:%02d', $minutos, $segundos);
    }

    // Scopes
    public function scopeFinalizados($query)
    {
        return $query->where('estado', '1');
    }

    public function scopeIniciados($query)
    {
        return $query->where('estado', '0');
    }

    public function scopeAnulados($query)
    {
        return $query->where('estado', '2');
    }

    public function scopeAprobados($query)
    {
        return $query->whereRaw('puntaje >= 11.00');
    }

    public function scopeDesaprobados($query)
    {
        return $query->whereRaw('puntaje < 11.00');
    }

    // Métodos auxiliares
    public function isFinalizado()
    {
        return $this->estado === '1';
    }

    public function isAprobado()
    {
        return $this->puntaje >= 11.00;
    }
}
