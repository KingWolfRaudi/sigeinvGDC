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
        Schema::create('especialidades_tecnicas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            
            // Auditoría y Base
            // Nota: Aquí validamos references en string crudo u omitimos foreign si users se crea despues.
            // Puesto que esta tabla se crea ANTES que users, NO PODEMOS poner constrained('users') aqui.
            // Para simplificar y seguir el flujo, añadimos los campos y luego en la migracion de users añadimos la foreign si es estrictamente necesaria. O prescindimos de foreign keys para auditoría aquí.
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidades_tecnicas');
    }
};
