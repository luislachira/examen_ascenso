<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    use HasFactory;

    protected $table = 'preguntas';
    protected $primaryKey = 'idPregunta';
    public $timestamps = false;

    protected $fillable = [
        'idCategoria',
        'enunciado',
        'tipoPregunta',
        'adminRegistro', // Considera cambiar esto a 'creado_por_id_usuario'
        'fechaRegistro',
    ];

    protected $casts = [
        'idCategoria' => 'integer',
        'tipoPregunta' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Define la relación inversa: Una Pregunta pertenece a una Categoria.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }

    /**
     * Define la relación: Una Pregunta tiene muchas Opciones.
     */
    public function opciones()
    {
        return $this->hasMany(Opcion::class, 'idPregunta', 'idPregunta');
    }

    /**
     * Define la relación muchos a muchos: Una Pregunta puede estar en muchos Examenes.
     */
    public function examenes()
    {
        return $this->belongsToMany(Examen::class, 'examen_pregunta', 'idPregunta', 'idExamen');
    }

    // Accessors
    public function getTipoPreguntaNameAttribute()
    {
        return $this->tipoPregunta === '0' ? 'Única respuesta' : 'Múltiple respuesta';
    }

    // Scopes
    public function scopePorCategoria($query, $idCategoria)
    {
        return $query->where('idCategoria', $idCategoria);
    }

    public function scopeUnicaRespuesta($query)
    {
        return $query->where('tipoPregunta', '0');
    }

    public function scopeMultipleRespuesta($query)
    {
        return $query->where('tipoPregunta', '1');
    }

    // Métodos auxiliares
    public function isMultipleRespuesta()
    {
        return $this->tipoPregunta === '1';
    }

    public function getOpcionesCorrectasIds()
    {
        return $this->opciones()->where('esCorrecta', true)->pluck('idOpciones')->toArray();
    }
}
