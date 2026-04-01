<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('computadores', function (Blueprint $table) {
            // Agregamos el departamento después del gpu_id para mantener orden
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('restrict')->after('gpu_id');
        });
    }

    public function down(): void
    {
        Schema::table('computadores', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropColumn('departamento_id');
        });
    }
};