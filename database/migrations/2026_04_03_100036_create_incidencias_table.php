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
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problema_id')->constrained('problemas');
            $table->foreignId('departamento_id')->constrained('departamentos');
            $table->foreignId('trabajador_id')->nullable()->constrained('trabajadores'); // El solicitante
            $table->foreignId('user_id')->constrained('users'); // Técnico resolutor
            
            // Relación Polimórfica para el Activo Fijo (Computador, Dispositivo, Insumo)
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->string('modelo_type')->nullable();
            
            $table->text('descripcion');
            $table->text('notas')->nullable();
            
            $table->boolean('solventado')->default(false);
            $table->boolean('cerrado')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
