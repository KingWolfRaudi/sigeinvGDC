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
        Schema::create('movimientos_computador', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('computador_id');
            $table->foreign('computador_id')->references('id')->on('computadores')->onDelete('restrict');

            $table->enum('tipo_operacion', [
                'cambio_departamento',
                'reasignacion_trabajador',
                'cambio_estado',
                'actualizacion_datos',
                'baja',
                'toggle_activo',
            ]);

            $table->json('payload_anterior')->nullable(); // Snapshot antes del cambio
            $table->json('payload_nuevo');                // Datos propuestos

            $table->enum('estado_workflow', [
                'borrador',
                'pendiente',
                'aprobado',
                'rechazado',
                'ejecutado_directo',
            ])->default('borrador');

            $table->text('justificacion');
            $table->text('motivo_rechazo')->nullable();
            
            // Refactorizacion Incidencias V2
            $table->foreignId('incidencia_id')->nullable()->constrained('incidencias')->onDelete('set null');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('movimiento_computadors');
    }
};
