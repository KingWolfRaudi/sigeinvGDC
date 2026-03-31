<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Importar SoftDeletes

class Puerto extends Model
{
    // 2. Añadir SoftDeletes al lado de HasFactory
    use HasFactory, SoftDeletes;

    protected $table = 'puertos';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    // 3. Aprovechamos de aplicar el casteo obligatorio
    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación Many-to-Many con GPUs
    public function gpus()
    {
        return $this->belongsToMany(Gpu::class, 'gpu_puerto');
    }
}