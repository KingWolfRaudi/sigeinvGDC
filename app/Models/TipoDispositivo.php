<?php

namespace App\Models;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // 1. Importar SoftDeletes
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoDispositivo extends Model
{
    // 2. Añadir SoftDeletes al lado de HasFactory
    use HasFactory, RecordSignature, SoftDeletes;

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
