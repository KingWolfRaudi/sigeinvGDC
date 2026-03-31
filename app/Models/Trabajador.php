<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Importar SoftDeletes

class Trabajador extends Model
{
    // 2. Añadir SoftDeletes al lado de HasFactory
    use HasFactory, SoftDeletes; 

    // Forzamos el nombre de la tabla en español
    protected $table = 'trabajadores';

    protected $fillable = [
        'nombres',
        'apellidos',
        'cedula',
        'cargo',
        'departamento_id',
        'activo',
    ];

    // Casteo de datos obligatorio
    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación: Un trabajador pertenece a un departamento
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    // Relación: Un trabajador puede estar vinculado a un usuario del sistema (Observer)
    public function user()
    {
        return $this->hasOne(User::class);
    }
}