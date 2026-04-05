<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // <-- Importar trait de Roles
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Importar SoftDeletes
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use App\Traits\RecordSignature;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes, Notifiable, HasRoles, LogsActivity, RecordSignature;

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
    ];

    public function trabajador()
    {
        return $this->hasOne(Trabajador::class);
    }

    public function solicitudesPerfil()
    {
        return $this->hasMany(SolicitudPerfil::class);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean', // <-- Casteo a booleano
    ];
}