<?php

namespace App\Models;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // 1. Importar SoftDeletes
use Illuminate\Database\Eloquent\SoftDeletes;

class Gpu extends Model
{
    // 2. Añadir SoftDeletes al lado de HasFactory
    use HasFactory, RecordSignature, SoftDeletes;

    protected $table = 'gpus';

    // Quitamos 'puertos' del fillable
    protected $fillable = [
        'marca_id', 'modelo', 'memoria', 'tipo_memoria', 'bus', 'frecuencia', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        // Quitamos el cast a array
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    // NUEVA RELACIÓN: Muchos a Muchos con Puertos
    public function puertos()
    {
        return $this->belongsToMany(Puerto::class, 'gpu_puerto');
    }
}
