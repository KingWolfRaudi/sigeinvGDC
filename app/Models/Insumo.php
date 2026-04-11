<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\RecordSignature;

class Insumo extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, RecordSignature;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    
    protected $fillable = [
        'bien_nacional', 'serial', 'nombre', 'descripcion', 
        'marca_id', 'categoria_insumo_id', 'unidad_medida', 
        'medida_actual', 'medida_minima', 'reutilizable', 
        'instalable_en_equipo', 'estado_fisico', 'activo',
        'departamento_id', 'trabajador_id', 'dispositivo_id', 'computador_id'
    ];
    
    public function marca() { return $this->belongsTo(Marca::class); }
    public function categoriaInsumo() { return $this->belongsTo(CategoriaInsumo::class); }
    public function departamento() { return $this->belongsTo(Departamento::class); }
    public function trabajador() { return $this->belongsTo(Trabajador::class); }
    public function dispositivo() { return $this->belongsTo(Dispositivo::class); }
    public function computador() { return $this->belongsTo(Computador::class); }

    // Trazabilidad de Movimientos
    public function movimientos() { return $this->hasMany(MovimientoInsumo::class, 'insumo_id'); }

    public function incidencias()
    {
        return $this->morphMany(Incidencia::class, 'modelo');
    }
}
