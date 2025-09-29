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
        Schema::table('opciones', function (Blueprint $table) {
            $table->foreign(['idPregunta'], 'fk_respuesta_pregunta')->references(['idPregunta'])->on('preguntas')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opciones', function (Blueprint $table) {
            $table->dropForeign('fk_respuesta_pregunta');
        });
    }
};
