<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\RecordSignature;

class Dispositivo extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, RecordSignature;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $table = 'dispositivos';

    protected $fillable = [
        'bien_nacional',
        'serial',
        'tipo_dispositivo_id',
        'marca_id',
        'nombre',
        'ip',
        'estado',
        'departamento_id',
        'trabajador_id',
        'computador_id',
        'notas',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones
    public function tipoDispositivo()
    {
        return $this->belongsTo(TipoDispositivo::class, 'tipo_dispositivo_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }

    public function computador()
    {
        return $this->belongsTo(Computador::class, 'computador_id');
    }

    public function puertos()
    {
        return $this->belongsToMany(Puerto::class, 'dispositivo_puerto');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoDispositivo::class, 'dispositivo_id');
    }

    public function incidencias()
    {
        return $this->morphMany(Incidencia::class, 'modelo');
    }
}
