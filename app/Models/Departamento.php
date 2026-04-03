<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // No olvides el SoftDeletes

class Departamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nombre', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación: Un departamento tiene muchos trabajadores
    public function trabajadores()
    {
        return $this->hasMany(Trabajador::class);
    }

    public function computadores()
    {
        return $this->hasMany(Computador::class);
    }

    public function dispositivos()
    {
        return $this->hasMany(Dispositivo::class);
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }
}