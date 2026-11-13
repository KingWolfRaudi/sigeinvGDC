<?php

namespace App\Models;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // 1. Importar SoftDeletes
use Illuminate\Database\Eloquent\SoftDeletes;

class Puerto extends Model
{
    // 2. Añadir SoftDeletes al lado de HasFactory
    use HasFactory, RecordSignature, SoftDeletes;

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
