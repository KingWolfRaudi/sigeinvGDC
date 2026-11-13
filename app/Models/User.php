<?php

namespace App\Models;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; // <-- Importar trait de Roles
use Laravel\Sanctum\HasApiTokens; // 1. Importar SoftDeletes
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable, RecordSignature, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    } // <-- Agregar HasRoles

    protected $fillable = [
        'name',
        'email',
        'username', // <-- Agregado
        'password',
        'avatar',   // <-- Agregado
        'activo',   // <-- Agregado
        'trabajador_id',
        'disponible_asignacion',
        'especialidad_id',
    ];

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function solicitudesPerfil()
    {
        return $this->hasMany(SolicitudPerfil::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(EspecialidadTecnica::class, 'especialidad_id');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean', // <-- Casteo a booleano
        'disponible_asignacion' => 'boolean',
    ];
}
