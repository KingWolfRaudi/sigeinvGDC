<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\RecordSignature;

class MovimientoDispositivo extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, RecordSignature;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $table = 'movimientos_dispositivo';

    protected $fillable = [
        'dispositivo_id',
        'tipo_operacion',
        'payload_anterior',
        'payload_nuevo',
        'estado_workflow',
        'justificacion',
        'motivo_rechazo',
        'solicitante_id',
        'aprobador_id',
        'aprobado_at',
    ];

    protected $casts = [
        'payload_anterior' => 'array',
        'payload_nuevo'    => 'array',
        'aprobado_at'      => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────

    public function dispositivo()
    {
        return $this->belongsTo(Dispositivo::class);
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobador_id');
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
