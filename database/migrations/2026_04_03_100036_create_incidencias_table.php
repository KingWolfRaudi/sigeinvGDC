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
            $table->foreignId('user_id')->nullable()->constrained('users'); // Técnico resolutor (nullable para auto-asignación)
            
            // Relación Polimórfica para el Activo Fijo (Computador, Dispositivo, Insumo)
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->string('modelo_type')->nullable();
            
            $table->text('descripcion');
            $table->string('nota_resolucion', 500)->nullable();
            
            $table->boolean('amerita_movimiento')->default(false);
            $table->boolean('solventado')->default(false);
            $table->boolean('cerrado')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
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
