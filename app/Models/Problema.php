<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\RecordSignature;

class Problema extends Model
{
    use HasFactory, SoftDeletes, RecordSignature;

    protected $fillable = ['nombre', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }
}
