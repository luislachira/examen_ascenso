<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    use HasFactory;

    protected $table = 'examenes';
    protected $primaryKey = 'idExamen';
    /**
     * Indica si el modelo debe tener timestamps.
     * En tu migración no usaste timestamps(), por lo que lo desactivamos.
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'idUsuario',
        'titulo',
        'descripcion',
        'fechaCreacion',
        'fechaInicio',
        'fechaFin',
        'duracionMin',
        'estado',
        'tipo_seleccion',
        'numero_preguntas_aleatorias',
    ];

    protected $casts = [
        'idUsuario' => 'integer',
        'fechaCreacion' => 'datetime',
        'fechaInicio' => 'datetime',
        'fechaFin' => 'datetime',
        'duracionMin' => 'integer',
        'estado' => 'string',
        'tipo_seleccion' => 'string',
        'numero_preguntas_aleatorias' => 'integer',
    ];

    /**
     * Define la relación inversa: Un Examen pertenece a un Usuario (el creador).
     */
    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }

    /**
     * Relación para exámenes MANUALES: Un Examen tiene muchas Preguntas fijas.
     */
    public function preguntas()
    {
        return $this->belongsToMany(Pregunta::class, 'examen_pregunta', 'idExamen', 'idPregunta');
    }

    /**
     * Relacion para exámenes aleatorios, define las categorías y reglas para seleccionar preguntas al azar.
     */
    public function categoriasParaAleatorio()
    {
        return $this->belongsToMany(Categoria::class, 'categoria_examen', 'idExamen', 'idCategoria')
            ->withPivot('numero_preguntas'); // <-- ¡Importante! Para poder leer la cantidad.
    }

    /**
     * Define la relación: Un Examen puede tener muchos Resultados.
     */
    public function resultados()
    {
        return $this->hasMany(Resultado::class, 'idExamen', 'idExamen');
    }

    // Accessors
    public function getEstadoNameAttribute()
    {
        return match ($this->estado) {
            '0' => 'Borrador',
            '1' => 'Publicado',
            '2' => 'Cerrado',
            default => 'Desconocido'
        };
    }

    public function getTipoSeleccionNameAttribute()
    {
        return $this->tipo_seleccion === '0' ? 'Manual (Fijas)' : 'Aleatorio (Dinámicas)';
    }

    // Scopes
    public function scopePublicados($query)
    {
        return $query->where('estado', '1');
    }

    public function scopeBorradores($query)
    {
        return $query->where('estado', '0');
    }

    public function scopeCerrados($query)
    {
        return $query->where('estado', '2');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', '1')
            ->where('fechaInicio', '<=', now())
            ->where('fechaFin', '>=', now());
    }

    public function scopeAleatorios($query)
    {
        return $query->where('tipo_seleccion', '1');
    }

    public function scopeManuales($query)
    {
        return $query->where('tipo_seleccion', '0');
    }

    // Métodos auxiliares
    public function isPublicado()
    {
        return $this->estado === '1';
    }

    public function isAleatorio()
    {
        return $this->tipo_seleccion === '1';
    }

    public function isManual()
    {
        return $this->tipo_seleccion === '0';
    }

    public function estaActivo()
    {
        return $this->estado === '1'
            && $this->fechaInicio <= now()
            && $this->fechaFin >= now();
    }

    public function yaInicio()
    {
        return $this->fechaInicio <= now();
    }

    public function yaFinalizo()
    {
        return $this->fechaFin < now();
    }
}
