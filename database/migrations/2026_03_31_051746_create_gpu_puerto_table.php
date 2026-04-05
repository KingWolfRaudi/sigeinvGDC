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
        Schema::create('gpu_puerto', function (Blueprint $table) {
            $table->id();
            // cascadeOnDelete significa que si borras la GPU, se borra su registro de puertos aquí automáticamente
            $table->foreignId('gpu_id')->constrained('gpus')->cascadeOnDelete();
            $table->foreignId('puerto_id')->constrained('puertos')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gpu_puerto');
    }
};
