<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\RecordSignature;

class Trabajador extends Model
{
    use HasFactory, SoftDeletes, RecordSignature;

    protected $table = 'trabajadores';

    // 1. Agrega 'user_id' aquí:
    protected $fillable = [
        'nombres', 'apellidos', 'cedula', 'cargo', 
        'departamento_id', 'user_id', 'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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