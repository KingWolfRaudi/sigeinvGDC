<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Insumo extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'bien_nacional', 'serial', 'nombre', 'descripcion', 
        'marca_id', 'categoria_insumo_id', 'unidad_medida', 
        'medida_actual', 'medida_minima', 'reutilizable', 
        'instalable_en_equipo', 'estado_fisico', 'activo'
    ];
    
    public function marca() { return $this->belongsTo(Marca::class); }
    public function categoriaInsumo() { return $this->belongsTo(CategoriaInsumo::class); }

    // Trazabilidad de Movimientos
    public function movimientos() { return $this->hasMany(MovimientoInsumo::class, 'insumo_id'); }

    public function incidencias()
    {
        return $this->morphMany(Incidencia::class, 'modelo');
    }
}
