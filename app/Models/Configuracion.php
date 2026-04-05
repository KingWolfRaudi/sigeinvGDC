<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\RecordSignature;

class Configuracion extends Model
{
    use HasFactory, RecordSignature;

    protected $table = 'configuracions';

    protected $fillable = ['clave', 'valor', 'grupo'];
}
