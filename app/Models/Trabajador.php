<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trabajador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trabajadores';

    // 1. Agrega 'user_id' aquí:
    protected $fillable = [
        'nombres', 'apellidos', 'cedula', 'cargo', 
        'departamento_id', 'user_id', 'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    // 2. Agrega la relación si no la tenías:
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}