<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\RecordSignature;

class ComputadorDisco extends Model
{
    use HasFactory, SoftDeletes, RecordSignature;

    protected $table = 'computador_discos';
    protected $fillable = ['computador_id', 'capacidad', 'tipo'];

    public function computador() { return $this->belongsTo(Computador::class); }
}