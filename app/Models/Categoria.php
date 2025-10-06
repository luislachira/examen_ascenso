<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $primaryKey = 'idCategoria';
    public $timestamps = false;

    protected $fillable = [
        'nombreCategoria',
        'descripcion',
    ];

    protected $casts = [
        'idCategoria' => 'integer',
    ];

    /**
     * Define la relación: Una Categoria tiene muchas Preguntas.
     */
    public function preguntas()
    {
        return $this->hasMany(Pregunta::class, 'idCategoria', 'idCategoria');
    }
    /**
     * Define los exámenes que usan esta categoría para generar preguntas aleatorias.
     */
    public function examenesDondeEsAleatoria()
    {
        return $this->belongsToMany(Examen::class, 'categoria_examen', 'idCategoria', 'idExamen')
            ->withPivot('numero_preguntas');
    }

    // Métodos auxiliares
    public function getPreguntasDisponiblesCount()
    {
        return $this->preguntas()->count();
    }
}
