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
        Schema::create('opciones', function (Blueprint $table) {
            $table->increments('idOpciones');
            $table->unsignedInteger('idPregunta')->index('idpregunta');
            $table->string('texto_respuesta');
            $table->boolean('esCorrecta')->default(false)->comment('Si la respuesta es correcta');
        });
        DB::statement('ALTER TABLE opciones ENGINE = InnoDB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opciones');
    }
};
