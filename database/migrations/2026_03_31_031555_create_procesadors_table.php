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
        Schema::create('procesadores', function (Blueprint $table) {
            $table->id();
            // Llave foránea hacia la tabla marcas
            $table->foreignId('marca_id')->constrained('marcas')->onDelete('restrict');
            
            $table->string('modelo');
            $table->string('generacion')->nullable();
            $table->string('frecuencia_base')->nullable();
            $table->string('frecuencia_maxima')->nullable();
            $table->integer('nucleos')->nullable();
            $table->integer('hilos')->nullable();
            
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesadors');
    }
};
