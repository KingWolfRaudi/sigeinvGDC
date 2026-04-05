<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Importar SoftDeletes

use App\Traits\RecordSignature;

class Procesador extends Model
{
    // 2. Añadir SoftDeletes al lado de HasFactory
    use HasFactory, SoftDeletes, RecordSignature; 

    protected $table = 'procesadores';

    protected $fillable = [
        'marca_id',
        'modelo',
        'generacion',
        'frecuencia_base',
        'frecuencia_maxima',
        'nucleos',
        'hilos',
        'activo',
    ];

    // 3. Aprovechamos de aplicar el casteo obligatorio
    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación: Un procesador pertenece a una marca
    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }
}