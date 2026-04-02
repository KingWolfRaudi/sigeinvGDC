<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('computadores', function (Blueprint $table) {
            $table->boolean('unidad_dvd')->default(true)->after('gpu_id');
            $table->boolean('fuente_poder')->default(true)->after('unidad_dvd');
        });
    }

    public function down(): void
    {
        Schema::table('computadores', function (Blueprint $table) {
            $table->dropColumn(['unidad_dvd', 'fuente_poder']);
        });
    }
};