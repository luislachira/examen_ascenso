<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void
    {
        // Evitar duplicados si se corre múltiples veces
        $exists = DB::table('usuarios')->where('correo', 'admin@example.com')->exists();
        if (!$exists) {
            DB::table('usuarios')->insert([
                'dni' => '54433321',
                'nombre' => 'Luis',
                'apellidos' => 'Lachira Nima',
                'correo' => 'luislachiraofi1@gmail.com',
                'password' => Hash::make('Forgotme1'),
                'rol' => '0', // Administrador
                'estado' => '1', // Activo
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('usuarios')->where('correo', 'luislachiraofi1@gmail.com')->delete();
    }
};
