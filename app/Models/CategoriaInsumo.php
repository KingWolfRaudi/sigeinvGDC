<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaInsumo extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['nombre', 'activo'];
    
    public function insumos() { return $this->hasMany(Insumo::class); }
}
