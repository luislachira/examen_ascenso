<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Eliminar tablas legadas del SQL antiguo si existen
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $legacyTables = [
            'categoria_examen',
            'categorias',
            'examen_pregunta',
            'examen_usuario',
            'opciones',
            'respuestas_usuarios',
            'resultados',
            // Estas dos pueden existir con esquemas incompatibles
            'preguntas',
            'examenes',
        ];

        foreach ($legacyTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        // No se re-crean tablas legadas
    }
};


