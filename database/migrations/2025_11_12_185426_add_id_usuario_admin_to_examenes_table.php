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
            $table->unsignedInteger('idUsuarioAdmin')->nullable()->after('fecha_finalizacion')
                ->comment('Usuario administrador que creó el examen');
            $table->foreign('idUsuarioAdmin')->references('idUsuario')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('examenes', function (Blueprint $table) {
            $table->dropForeign(['idUsuarioAdmin']);
            $table->dropColumn('idUsuarioAdmin');
        });
    }
};
