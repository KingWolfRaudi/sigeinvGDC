<?php

namespace App\Models;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EspecialidadTecnica extends Model
{
    use HasFactory, RecordSignature, SoftDeletes;

    protected $table = 'especialidades_tecnicas';

    protected $fillable = ['nombre', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function problemas()
    {
        return $this->hasMany(Problema::class, 'especialidad_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'especialidad_id');
    }
}
