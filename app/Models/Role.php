<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Agregamos 'descripcion' a los campos que se pueden llenar masivamente
    protected $fillable = [
        'name',
        'guard_name',
        'descripcion',
        'updated_at',
        'created_at'
    ];
}