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
        Schema::create('software', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_programa', 35);
            $table->enum('arquitectura_programa', ['32bits', '64bits'])->nullable();
            $table->enum('tipo_licencia', ['Libre', 'Privativo']);
            $table->string('serial', 50)->nullable();
            $table->string('descripcion_programa', 250)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software');
    }
};
