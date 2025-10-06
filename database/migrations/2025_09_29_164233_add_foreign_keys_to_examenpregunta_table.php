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
        Schema::table('examen_pregunta', function (Blueprint $table) {
            $table->foreign(['idExamen'], 'fk_examenpregunta_examen')->references(['idExamen'])->on('examenes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['idPregunta'], 'fk_examenpregunta_pregunta')->references(['idPregunta'])->on('preguntas')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examenpregunta', function (Blueprint $table) {
            $table->dropForeign('fk_examenpregunta_examen');
            $table->dropForeign('fk_examenpregunta_pregunta');
        });
    }
};
