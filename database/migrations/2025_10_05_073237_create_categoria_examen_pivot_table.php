<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categoria_examen', function (Blueprint $table) {
            // Claves foráneas UNSIGNED para que coincidan con las claves primarias
            $table->unsignedInteger('idExamen');
            $table->unsignedInteger('idCategoria');

            // La regla: cuántas preguntas tomar de esta categoría para este examen
            $table->unsignedInteger('numero_preguntas');

            // Definimos una clave primaria compuesta para evitar duplicados
            $table->primary(['idExamen', 'idCategoria']);
        });
        DB::statement('ALTER TABLE categoria_examen ENGINE = InnoDB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_examen');
    }
};
