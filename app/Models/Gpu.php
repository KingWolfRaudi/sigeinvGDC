<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gpu extends Model
{
    use HasFactory;

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