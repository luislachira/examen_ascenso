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
        Schema::create('examenpregunta', function (Blueprint $table) {
            $table->integer('idExamenPregunta', true);
            $table->integer('idExamen')->index('idexamen');
            $table->integer('idPregunta')->index('idpregunta');
        });
        DB::statement('ALTER TABLE examenpregunta ENGINE = InnoDB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examenpregunta');
    }
};
