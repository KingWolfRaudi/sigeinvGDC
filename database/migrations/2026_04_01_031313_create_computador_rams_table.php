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
        Schema::create('computador_rams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('computador_id')->constrained('computadores')->cascadeOnDelete();
            $table->string('capacidad'); // Ej: 8GB
            $table->integer('slot'); // Ej: 1, 2, 3, 4
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('computador_rams');
    }
};
