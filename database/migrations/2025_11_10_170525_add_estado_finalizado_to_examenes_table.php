<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agregar el estado '2' = 'Finalizado' al ENUM de estado en la tabla examenes
     */
    public function up(): void
    {
        // Modificar el ENUM para incluir el estado '2' = 'Finalizado'
        DB::statement("ALTER TABLE examenes MODIFY COLUMN estado ENUM('0', '1', '2') COMMENT '0=Borrador, 1=Publicado, 2=Finalizado'");
    }

    /**
     * Reverse the migrations.
     * Revertir el ENUM a su estado original sin '2'
     */
    public function down(): void
    {
        // Verificar si hay registros con estado '2' antes de revertir
        $examenesConEstado2 = DB::table('examenes')->where('estado', '2')->count();

        if ($examenesConEstado2 > 0) {
            throw new \Exception("No se puede revertir la migración porque hay {$examenesConEstado2} examen(es) con estado '2' (Finalizado). Por favor, cambie el estado de estos exámenes antes de revertir.");
        }

        // Revertir el ENUM a su estado original
        DB::statement("ALTER TABLE examenes MODIFY COLUMN estado ENUM('0', '1') COMMENT '0=Borrador, 1=Publicado'");
    }
};
