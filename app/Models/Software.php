<?php

namespace App\Models;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Software extends Model
{
    use HasFactory, LogsActivity, RecordSignature, SoftDeletes;

    protected $table = 'software';

    protected $fillable = [
        'nombre_programa',
        'arquitectura_programa',
        'tipo_licencia',
        'serial',
        'descripcion_programa',
        'activo',
    ];

    /**
     * Los atributos que deben castearse a tipos nativos.
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Configuración de ActivityLog (Auditoría)
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombre_programa',
                'arquitectura_programa',
                'tipo_licencia',
                'serial',
                'descripcion_programa',
                'activo',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
