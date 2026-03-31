<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Importar SoftDeletes

class TipoDispositivo extends Model
{
    // 2. Añadir SoftDeletes al lado de HasFactory
    use HasFactory, SoftDeletes; 

    // Forzamos el nombre de la tabla en español
    protected $table = 'tipo_dispositivos';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    // 3. Aprovechamos de aplicar el casteo obligatorio
    protected $casts = [
        'activo' => 'boolean',
    ];
}