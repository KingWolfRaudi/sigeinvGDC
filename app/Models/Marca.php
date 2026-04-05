<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\RecordSignature;

class Marca extends Model
{
    use HasFactory, SoftDeletes, RecordSignature;

    protected $table = 'marcas';
    protected $fillable = ['nombre', 'activo'];
    protected $casts = [
        'activo' => 'boolean',
    ];

    // Definimos qué elementos dependen de esta marca
    public function procesadores()
    {
        return $this->hasMany(Procesador::class);
    }

    public function gpus()
    {
        return $this->hasMany(Gpu::class);
    }

    public function insumos()
    {
        return $this->hasMany(Insumo::class);
    }

    // A futuro agregaremos: computadores(), dispositivos(), etc.
}