<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('cedula')->nullable()->unique();
            $table->string('cargo')->nullable();
            
            $table->foreignId('departamento_id')->constrained('departamentos')->onDelete('restrict');
            
            // AGREGAR ESTA LÍNEA (El vínculo con la cuenta de usuario)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('restrict');
            
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    

    public function down(): void
    {
        Schema::dropIfExists('trabajadores');
    }
};