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
        Schema::table('resultados', function (Blueprint $table) {
            $table->foreign(['idUsuario'], 'fk_resultado_docente')->references(['idUsuario'])->on('usuarios')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['idExamen'], 'fk_resultado_examen')->references(['idExamen'])->on('examenes')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resultados', function (Blueprint $table) {
            $table->dropForeign('fk_resultado_docente');
            $table->dropForeign('fk_resultado_examen');
        });
    }
};
