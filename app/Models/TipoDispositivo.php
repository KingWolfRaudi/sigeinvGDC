<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDispositivo extends Model
{
    use HasFactory;

    // Forzamos el nombre de la tabla en español
    protected $table = 'tipo_dispositivos';

    protected $fillable = [
        'nombre',
        'activo',
    ];
}