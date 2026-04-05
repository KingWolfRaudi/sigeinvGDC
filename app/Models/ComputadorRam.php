<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\RecordSignature;

class ComputadorRam extends Model
{
    use HasFactory, SoftDeletes, RecordSignature;

    protected $table = 'computador_rams';
    protected $fillable = ['computador_id', 'capacidad', 'slot'];

    public function computador() { return $this->belongsTo(Computador::class); }
}