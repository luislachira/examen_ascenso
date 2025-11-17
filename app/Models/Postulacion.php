<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postulacion extends Model
{
    use HasFactory;

    protected $table = 'postulaciones';
    protected $primaryKey = 'idPostulacion';
    public $incrementing = true;

    protected $fillable = [
        'idExamen',
        'nombre',
        'descripcion',
        'tipo_aprobacion',
    ];

    protected $casts = [
        'idExamen' => 'integer',
    ];

    // Relaciones
    public function examen()
    {
        return $this->belongsTo(Examen::class, 'idExamen', 'idExamen');
    }

    public function reglasPuntaje()
    {
        return $this->hasMany(ReglaPuntaje::class, 'idPostulacion', 'idPostulacion');
    }

    public function intentosExamen()
    {
        return $this->hasMany(IntentoExamen::class, 'idPostulacion', 'idPostulacion');
    }
}
