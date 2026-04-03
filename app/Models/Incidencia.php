<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incidencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'problema_id',
        'departamento_id',
        'trabajador_id',
        'user_id',
        'modelo_id',
        'modelo_type',
        'descripcion',
        'notas',
        'solventado',
        'cerrado'
    ];

    protected $casts = [
        'solventado' => 'boolean',
        'cerrado' => 'boolean',
    ];

    // Relaciones
    public function problema()
    {
        return $this->belongsTo(Problema::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación Polimórfica para el Activo Fijo
    public function modelo()
    {
        return $this->morphTo();
    }
}
