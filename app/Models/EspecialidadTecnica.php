<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\RecordSignature;

class EspecialidadTecnica extends Model
{
    use HasFactory, SoftDeletes, RecordSignature;

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
