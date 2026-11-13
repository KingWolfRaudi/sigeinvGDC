<?php

namespace App\Models;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // 1. Importar SoftDeletes
use Illuminate\Database\Eloquent\SoftDeletes;

class SistemaOperativo extends Model
{
    // 2. Añadir SoftDeletes al lado de HasFactory
    use HasFactory, RecordSignature, SoftDeletes;

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
