<?php

namespace App\Models;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MovimientoInsumo extends Model
{
    use HasFactory, LogsActivity, RecordSignature, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $table = 'movimientos_insumo';

    protected $fillable = [
        'insumo_id',
        'tipo_operacion',
        'cantidad_movida',
        'payload_anterior',
        'payload_nuevo',
        'estado_workflow',
        'justificacion',
        'motivo_rechazo',
        'solicitante_id',
        'aprobador_id',
        'aprobado_at',
        'incidencia_id',
    ];

    protected $casts = [
        'payload_anterior' => 'array',
        'payload_nuevo' => 'array',
        'aprobado_at' => 'datetime',
        'cantidad_movida' => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobador_id');
    }

    public function incidencia()
    {
        return $this->belongsTo(Incidencia::class);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeBorradores($query)
    {
        return $query->where('estado_workflow', 'borrador');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado_workflow', 'pendiente');
    }

    public function scopeResueltos($query)
    {
        return $query->whereIn('estado_workflow', ['aprobado', 'rechazado', 'ejecutado_directo']);
    }
}
