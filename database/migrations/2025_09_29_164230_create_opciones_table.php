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
        Schema::create('opciones', function (Blueprint $table) {
            $table->integer('idRespuesta', true);
            $table->integer('idPregunta')->index('idpregunta');
            $table->string('texto_respuesta');
            $table->boolean('esCorrecta')->default(false)->comment('Si la respuesta es correcta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opciones');
    }
};
