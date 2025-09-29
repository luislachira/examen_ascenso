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
        Schema::create('examenes', function (Blueprint $table) {
            $table->integer('idExamen', true);
            $table->integer('idUsuario')->index('idusuario');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->dateTime('fechaCreacion')->nullable()->useCurrent();
            $table->dateTime('fechaInicio')->nullable();
            $table->dateTime('fechaFin')->nullable();
            $table->time('duracionMin')->nullable();
            $table->enum('estado', ['0', '1', '2'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examenes');
    }
};
