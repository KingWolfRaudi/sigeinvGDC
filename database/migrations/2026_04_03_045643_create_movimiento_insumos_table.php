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
        Schema::create('movimientos_insumo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('restrict');

            $table->enum('tipo_operacion', [
                'salida_consumo',
                'entrada_stock',
                'prestamo',
                'devolucion',
                'actualizacion_datos',
                'baja',
                'toggle_activo',
            ]);

            // Para stock: cuánto entra/sale/se presta
            $table->decimal('cantidad_movida', 8, 2)->nullable();

            $table->json('payload_anterior')->nullable();
            $table->json('payload_nuevo');

            $table->enum('estado_workflow', [
                'borrador',
                'pendiente',
                'aprobado',
                'rechazado',
                'ejecutado_directo',
            ])->default('borrador');

            $table->text('justificacion');
            $table->text('motivo_rechazo')->nullable();

            $table->foreignId('solicitante_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('aprobador_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('aprobado_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_insumos');
    }
};
