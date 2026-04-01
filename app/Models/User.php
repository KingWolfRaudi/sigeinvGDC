<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // <-- Importar trait de Roles
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Importar SoftDeletes

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes, Notifiable, HasRoles; // <-- Agregar HasRoles

    protected $fillable = [
        'name',
        'email',
        'username', // <-- Agregado
        'password',
        'activo',   // <-- Agregado
    ];

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