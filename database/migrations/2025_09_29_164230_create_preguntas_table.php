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
        Schema::create('preguntas', function (Blueprint $table) {
            $table->increments('idPregunta');
            $table->unsignedInteger('idCategoria')->index('idcategoria');
            $table->text('enunciado');
            $table->enum('tipoPregunta', ['0', '1'])->default('0')->comment('0:una respuesta,1:multiple respuesta');
            $table->string('adminRegistro', 250)->comment('nombre del administrador del registro');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE preguntas ENGINE = InnoDB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preguntas');
    }
};
