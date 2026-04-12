<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\RecordSignature;

class Software extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, RecordSignature;

    protected $table = 'software';

    protected $fillable = [
        'nombre_programa',
        'arquitectura_programa',
        'tipo_licencia',
        'serial',
        'descripcion_programa',
        'activo'
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
                'activo'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
