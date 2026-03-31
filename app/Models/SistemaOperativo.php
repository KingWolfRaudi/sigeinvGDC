<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SistemaOperativo extends Model
{
    use HasFactory;

    // Forzamos el nombre correcto de la tabla
    protected $table = 'sistemas_operativos';

    protected $fillable = [
        'nombre',
        'activo',
    ];
}