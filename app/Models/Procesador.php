<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procesador extends Model
{
    use HasFactory;

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

    // Relación: Un procesador pertenece a una marca
    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }
}