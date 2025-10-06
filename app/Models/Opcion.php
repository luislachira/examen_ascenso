<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opcion extends Model
{
    use HasFactory;

    protected $table = 'opciones';
    // He ajustado la PK según mi sugerencia anterior, si usaste 'idRespuesta', cámbialo aquí.
    protected $primaryKey = 'idOpcion';
    public $timestamps = false;

    protected $fillable = [
        'idPregunta',
        'texto_respuesta',
        'esCorrecta',
    ];

    protected $casts = [
        'idPregunta' => 'integer',
        'esCorrecta' => 'boolean',
    ];

    /**
     * Define la relación inversa: Una Opcion pertenece a una Pregunta.
     */
    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'idPregunta', 'idPregunta');
    }
}
