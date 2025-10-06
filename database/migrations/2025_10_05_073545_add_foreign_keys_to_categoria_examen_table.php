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
        Schema::table('categoria_examen', function (Blueprint $table) {
            // Relación con la tabla 'examenes'
            $table->foreign('idExamen', 'fk_cat_examen_examen') // Damos un nombre a la restricción
                ->references('idExamen')->on('examenes')
                ->onDelete('cascade');

            // Relación con la tabla 'categorias'
            $table->foreign('idCategoria', 'fk_cat_examen_categoria') // Damos un nombre a la restricción
                ->references('idCategoria')->on('categorias')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categoria_examen', function (Blueprint $table) {
            // Elimina las claves foráneas usando los nombres que les dimos
            $table->dropForeign('fk_cat_examen_examen');
            $table->dropForeign('fk_cat_examen_categoria');
        });
    }
};
