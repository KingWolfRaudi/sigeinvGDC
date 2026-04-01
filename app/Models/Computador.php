<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Computador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'computadores';

    // Asegúrate de que los nombres en este array coincidan EXACTAMENTE 
    // con cómo se llaman las columnas en tu base de datos (phpMyAdmin/DBeaver)
    protected $fillable = [
        'bien_nacional',
        'serial',
        'nombre_equipo',
        'marca_id',
        'tipo_dispositivo_id',
        'sistemas_operativo_id', // <--- Ajustado al nombre exacto de tu BD
        'procesador_id',
        'gpu_id',
        'memoria_ram',
        'tipo_memoria',
        'almacenamiento',
        'tipo_almacenamiento',
        'observaciones',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // --- Relaciones (Padres) EXPLICITAS ---
    
    public function marca() { 
        // El segundo parámetro fuerza el nombre de la columna en la tabla computadores
        return $this->belongsTo(Marca::class, 'marca_id'); 
    }
    
    public function tipoDispositivo() { 
        // Este estaba bien
        return $this->belongsTo(TipoDispositivo::class, 'tipo_dispositivo_id'); 
    }
    
    public function sistemaOperativo() { 
        // Forzamos a que busque la columna con el nombre exacto de tu BD
        return $this->belongsTo(SistemaOperativo::class, 'sistemas_operativo_id'); 
    }
    
    public function procesador() { 
        return $this->belongsTo(Procesador::class, 'procesador_id'); 
    }
    
    public function gpu() { 
        return $this->belongsTo(Gpu::class, 'gpu_id'); 
    }

    // --- Relación Many-to-Many con Puertos ---
    public function puertos()
    {
        return $this->belongsToMany(Puerto::class, 'computador_puerto');
    }
}