<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\RecordSignature;

class Incidencia extends Model
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
        'problema_id',
        'departamento_id',
        'dependencia_id',
        'trabajador_id',
        'user_id',
        'modelo_id',
        'modelo_type',
        'descripcion',
        'nota_resolucion',
        'amerita_movimiento',
        'solventado',
        'cerrado'
    ];

    protected $casts = [
        'solventado' => 'boolean',
        'cerrado' => 'boolean',
        'amerita_movimiento' => 'boolean',
    ];

    // Relaciones
    public function problema()
    {
        return $this->belongsTo(Problema::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class);
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación Polimórfica para el Activo Fijo
    public function modelo()
    {
        return $this->morphTo();
    }

    // Relaciones con movimientos generados
    public function movimientoComputador()
    {
        return $this->hasOne(MovimientoComputador::class, 'incidencia_id');
    }

    public function movimientoDispositivo()
    {
        return $this->hasOne(MovimientoDispositivo::class, 'incidencia_id');
    }

    public function movimientoInsumo()
    {
        return $this->hasOne(MovimientoInsumo::class, 'incidencia_id');
    }
}
