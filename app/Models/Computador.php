<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Computador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'computadores';

    protected $fillable = [
        'bien_nacional', 'serial', 'marca_id', 'tipo_dispositivo_id', 
        'sistema_operativo_id', 'procesador_id', 'gpu_id', 'unidad_dvd', 'fuente_poder', 'departamento_id', // <-- Agregados aquí
        'trabajador_id', 'tipo_ram', 'mac', 'ip', 'tipo_conexion', 'estado_fisico', 
        'observaciones', 'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'unidad_dvd' => 'boolean',
        'fuente_poder' => 'boolean',
    ];

    // Relaciones Simples (BelongsTo)
    public function marca() { return $this->belongsTo(Marca::class); }
    public function tipoDispositivo() { return $this->belongsTo(TipoDispositivo::class); }
    public function sistemaOperativo() { return $this->belongsTo(SistemaOperativo::class); }
    public function procesador() { return $this->belongsTo(Procesador::class); }
    public function gpu() { return $this->belongsTo(Gpu::class); }
    public function trabajador() { return $this->belongsTo(Trabajador::class); }
    public function departamento() { return $this->belongsTo(Departamento::class); }

    // Relaciones Múltiples (HasMany y BelongsToMany)
    public function discos() { return $this->hasMany(ComputadorDisco::class); }
    public function rams() { return $this->hasMany(ComputadorRam::class); }
    public function puertos() { return $this->belongsToMany(Puerto::class, 'computador_puerto'); }

    // Trazabilidad de Movimientos
    public function movimientos() { return $this->hasMany(MovimientoComputador::class, 'computador_id'); }

    /**
     * ACCESOR: Calcula el total de RAM limpiando el 'GB' para poder sumar
     */
    public function getTotalRamAttribute()
    {
        $total = $this->rams->sum(function ($ram) {
            return (int) str_replace('GB', '', $ram->capacidad);
        });
        
        return $total > 0 ? $total . 'GB' : '0GB';
    }

    /**
     * ACCESOR: Calcula el total de almacenamiento limpiando el 'GB' para poder sumar
     */
    public function getTotalAlmacenamientoAttribute()
    {
        $total = $this->discos->sum(function ($disco) {
            return (int) str_replace('GB', '', $disco->capacidad);
        });
        
        return $total > 0 ? $total . 'GB' : '0GB';
    }
}