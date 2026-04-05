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
        Schema::create('computador_discos', function (Blueprint $table) {
            $table->id();
            // Cascade: si borramos (o soft-borramos) el PC, sus discos se van con él
            $table->foreignId('computador_id')->constrained('computadores')->cascadeOnDelete();
            $table->string('capacidad'); // Ej: 500GB
            $table->enum('tipo', ['SSD', 'NVME', 'M.2', 'HDD', 'No Posee']);
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
        Schema::dropIfExists('computador_discos');
    }
};
