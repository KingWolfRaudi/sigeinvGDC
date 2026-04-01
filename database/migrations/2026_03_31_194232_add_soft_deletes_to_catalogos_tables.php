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
        // Añadimos softDeletes() a todas las tablas de nuestros catálogos
        Schema::table('marcas', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('tipo_dispositivos', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('sistemas_operativos', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('puertos', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('departamentos', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('procesadores', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('gpus', function (Blueprint $table) { $table->softDeletes(); });
        //Schema::table('trabajadores', function (Blueprint $table) { $table->softDeletes(); });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Permitimos revertir (rollback) eliminando la columna
        Schema::table('marcas', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('tipo_dispositivos', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('sistemas_operativos', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('puertos', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('departamentos', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('procesadores', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('gpus', function (Blueprint $table) { $table->dropSoftDeletes(); });
        //Schema::table('trabajadores', function (Blueprint $table) { $table->dropSoftDeletes(); });
    }
};