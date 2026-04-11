<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computadores', function (Blueprint $table) {
            $table->id();
            
            // Identificadores (Únicos y obligatorios)
            $table->string('bien_nacional')->unique();
            $table->string('serial')->unique();
            $table->string('nombre_equipo', 15); // Campo obligatorio, máx 15 chars
            
            // Relaciones Foráneas (con restrict según V2.5)
            $table->foreignId('marca_id')->constrained('marcas')->onDelete('restrict');
            $table->string('tipo_computador'); // Computador de escritorio, Laptop, Mini Laptop
            $table->foreignId('sistema_operativo_id')->constrained('sistemas_operativos')->onDelete('restrict');
            $table->foreignId('procesador_id')->constrained('procesadores')->onDelete('restrict');
            $table->foreignId('gpu_id')->nullable()->constrained('gpus')->onDelete('restrict');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('restrict');
            $table->foreignId('trabajador_id')->nullable()->constrained('trabajadores')->onDelete('restrict');
            
            // Especificaciones
            $table->enum('tipo_ram', ['DDR2', 'DDR3', 'DDR4', 'DDR5', 'DDR6']);
            $table->string('mac')->nullable()->unique();
            $table->string('ip')->nullable();
            $table->enum('tipo_conexion', ['Ethernet', 'Wi-Fi', 'Ambas'])->nullable();
            $table->boolean('unidad_dvd')->default(true);
            $table->boolean('fuente_poder')->default(true);
            $table->enum('estado_fisico', ['operativo', 'danado', 'indeterminado', 'en_reparacion', 'baja'])->default('operativo');
            $table->text('observaciones')->nullable();
            
            // Estándares V2.5
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes(); // ¡Obligatorio V2.5!
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computadores');
    }
};