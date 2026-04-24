<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RecordSignature;

class Dependencia extends Model
{
    use HasFactory, SoftDeletes, RecordSignature;

    protected $table = 'dependencias';

    protected $fillable = [
        'nombre',
        'departamento_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

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

    public function insumos()
    {
        return $this->hasMany(Insumo::class);
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }
}
