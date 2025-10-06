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
        Schema::create('examen_pregunta', function (Blueprint $table) {
            $table->unsignedInteger('idExamen');
            $table->unsignedInteger('idPregunta');
            // Clave primaria compuesta
            $table->primary(['idExamen', 'idPregunta']);
        });
        DB::statement('ALTER TABLE examen_pregunta ENGINE = InnoDB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examen_pregunta');
    }
};
