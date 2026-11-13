<?php

namespace App\Models;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // No olvides el SoftDeletes
use Illuminate\Database\Eloquent\SoftDeletes;

class Departamento extends Model
{
    use HasFactory, RecordSignature, SoftDeletes;

    protected $fillable = ['nombre', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Relación: Un departamento tiene muchas dependencias
    public function dependencias()
    {
        return $this->hasMany(Dependencia::class);
    }

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
