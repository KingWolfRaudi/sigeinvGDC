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
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            // Identificadores (similares a dispositivos/computadores)
            $table->string('bien_nacional')->nullable()->unique();
            $table->string('serial')->nullable()->unique();
            
            // Atributos básicos
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            
            // Relaciones foráneas
            $table->foreignId('marca_id')->constrained('marcas')->onDelete('restrict');
            $table->foreignId('categoria_insumo_id')->constrained('categoria_insumos')->onDelete('restrict');
            
            // Manejo de cantidades (Individual = 1 unidad. Bobinas = X metros)
            $table->enum('unidad_medida', ['unidad', 'metros', 'litros', 'cajas', 'pares'])->default('unidad');
            $table->decimal('medida_actual', 8, 2)->default(1.00); 
            $table->decimal('medida_minima', 8, 2)->default(1.00);
            
            // Estado y comportamiento
            $table->boolean('reutilizable')->default(false); 
            $table->boolean('instalable_en_equipo')->default(false);
            $table->enum('estado_fisico', ['operativo', 'danado', 'indeterminado', 'en_reparacion', 'baja'])->default('operativo');
            
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
        Schema::dropIfExists('insumos');
    }
};
