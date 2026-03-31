<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Importar SoftDeletes

class SistemaOperativo extends Model
{
    // 2. Añadir SoftDeletes al lado de HasFactory
    use HasFactory, SoftDeletes; 

    // Forzamos el nombre correcto de la tabla
    protected $table = 'sistemas_operativos';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    // 3. Aprovechamos de aplicar el casteo obligatorio
    protected $casts = [
        'activo' => 'boolean',
    ];
}