<?php

namespace App\Models;

use App\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory, RecordSignature;

    protected $table = 'configuracions';

    protected $fillable = ['clave', 'valor', 'grupo'];
}
