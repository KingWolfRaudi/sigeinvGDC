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
        $tables = [
            'trabajadores',
            'computadores',
            'dispositivos',
            'insumos',
            'incidencias'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Agregar la columna antes de 'activo' si existe, o al final
                // Para simplificar, la agregamos normalmente.
                $table->foreignId('dependencia_id')->nullable()->constrained('dependencias')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'trabajadores',
            'computadores',
            'dispositivos',
            'insumos',
            'incidencias'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['dependencia_id']);
                $table->dropColumn('dependencia_id');
            });
        }
    }
};
