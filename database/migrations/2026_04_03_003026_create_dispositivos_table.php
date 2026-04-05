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
        Schema::create('dispositivos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable()->unique();
            $table->string('serial')->nullable()->unique();
            $table->foreignId('tipo_dispositivo_id')->constrained('tipo_dispositivos')->onDelete('restrict');
            $table->foreignId('marca_id')->constrained('marcas')->onDelete('restrict');
            $table->string('nombre'); // Hace referencia al modelo específico
            $table->string('ip')->nullable();
            $table->enum('estado', ['operativo', 'dañado', 'indeterminado', 'en_reparacion', 'baja'])->default('operativo');
            $table->foreignId('departamento_id')->constrained('departamentos')->onDelete('restrict');
            $table->foreignId('trabajador_id')->nullable()->constrained('trabajadores')->onDelete('restrict');
            $table->foreignId('computador_id')->nullable()->constrained('computadores')->onDelete('restrict');
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
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
        Schema::dropIfExists('dispositivos');
    }
};
