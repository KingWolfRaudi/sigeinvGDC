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
        Schema::create('gpus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marca_id')->constrained('marcas')->onDelete('restrict');
            $table->string('modelo');
            $table->string('memoria')->nullable();
            $table->string('tipo_memoria')->nullable();
            $table->string('bus')->nullable();
            $table->string('frecuencia')->nullable();
            //$table->json('puertos')->nullable(); // Guardará un arreglo
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gpus');
    }
};
