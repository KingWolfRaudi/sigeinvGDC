<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computador_puerto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computador_id')->constrained('computadores')->cascadeOnDelete();
            $table->foreignId('puerto_id')->constrained('puertos')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            // Las tablas pivote puras generalmente no necesitan SoftDeletes, pero lo mantenemos estándar si la migras
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computador_puerto');
    }
};