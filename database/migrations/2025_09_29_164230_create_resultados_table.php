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
        Schema::create('resultados', function (Blueprint $table) {
            $table->increments('idResultado');
            $table->unsignedInteger('idExamen');
            $table->unsignedInteger('idUsuario')->index('fk_resultado_docente');
            $table->integer('intento')->nullable()->default(1);
            $table->decimal('puntaje', 5, 2);
            $table->boolean('aprobado')->nullable()->storedAs('(`puntaje` >= 11.00)');
            $table->timestamp('fechaRealizacion')->nullable()->useCurrent();
            $table->integer('tiempo_usado')->nullable()->comment('en segundo');
            $table->enum('estado', ['0', '1', '2'])->nullable()->default('1')->comment('0:iniciado,1:finalizado,2:anulado');
            $table->string('ip_address', 45)->nullable();
            $table->string('dispositivo', 100)->nullable();

            $table->unique(['idExamen', 'idUsuario', 'intento'], 'uq_examen_usuario_intento');
        });
        DB::statement('ALTER TABLE resultados ENGINE = InnoDB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultados');
    }
};
