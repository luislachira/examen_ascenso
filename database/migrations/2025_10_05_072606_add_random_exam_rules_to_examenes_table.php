<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('examenes', function (Blueprint $table) {
            // Define si el examen es de preguntas fijas o seleccionadas al azar.
            $table->enum('tipo_seleccion', ['0', '1'])
                ->default('0')
                ->after('estado')
                ->comment('Define si las preguntas son fijas (0:manual) o dinámicas (1:aleatorio).');
            // Almacena el número total de preguntas para un examen de tipo aleatorio.
            $table->unsignedInteger('numero_preguntas_aleatorias')
                ->nullable()
                ->after('tipo_seleccion')
                ->comment('Número total de preguntas a seleccionar si el tipo es aleatorio.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examenes', function (Blueprint $table) {
            // Elimina las columnas en el orden inverso a su creación.
            $table->dropColumn('numero_preguntas_aleatorias');
            $table->dropColumn('tipo_seleccion');
        });
    }
};
