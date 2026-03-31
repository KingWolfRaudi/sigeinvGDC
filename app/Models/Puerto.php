<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puerto extends Model
{
    use HasFactory;

    protected $table = 'puertos';

    protected $fillable = [
        'nombre',
        'activo',
    ];
}