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
            
            // Datos de Identificación
            $table->string('bien_nacional')->unique()->nullable();
            $table->string('serial')->unique()->nullable();
            $table->string('nombre_equipo')->nullable(); // Ej: PC-RRHH-01
            
            // Llaves foráneas a Catálogos Maestros (Integridad Estricta)
            $table->foreignId('marca_id')->constrained('marcas')->onDelete('restrict');
            $table->foreignId('tipo_dispositivo_id')->constrained('tipo_dispositivos')->onDelete('restrict');
            $table->foreignId('sistemas_operativo_id')->constrained('sistemas_operativos')->onDelete('restrict');
            $table->foreignId('procesadores_id')->constrained('procesadores')->onDelete('restrict');
            
            // La GPU puede ser nula (muchas PCs usan gráficas integradas en el procesador)
            $table->foreignId('gpu_id')->nullable()->constrained('gpus')->onDelete('restrict');
            
            // Especificaciones Técnicas
            $table->integer('memoria_ram'); // Se guardará el número, en vista se añade "GB"
            $table->string('tipo_memoria'); // Ej: DDR4, DDR5 (Forzado a Mayúsculas)
            $table->integer('almacenamiento');
            $table->string('tipo_almacenamiento'); // Ej: SSD, HDD, NVMe (Forzado a Mayúsculas)
            
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            
            $table->timestamps();
            $table->softDeletes(); // Obligatorio según V2.5
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computadores');
    }
};