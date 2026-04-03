<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudPerfil extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_perfil';

    protected $fillable = [
        'user_id', 'tipo', 'valor_nuevo', 'estado', 
        'motivo_rechazo', 'revisado_por'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    /**
     * Valida si el usuario puede realizar una solicitud de este tipo.
     * Debe haber pasado 180 días desde la última solicitud APROBADA del mismo tipo.
     */
    public static function canRequest($userId, $tipo)
    {
        // Verificar si hay una pendiente (Solo una activa a la vez)
        $hasPending = self::where('user_id', $userId)
            ->where('tipo', $tipo)
            ->where('estado', 'pendiente')
            ->exists();

        if ($hasPending) return false;

        // Verificar la regla de los 180 días (Solo para aprobadas)
        $lastApproved = self::where('user_id', $userId)
            ->where('tipo', $tipo)
            ->where('estado', 'aprobado')
            ->latest()
            ->first();

        if (!$lastApproved) return true;

        return $lastApproved->created_at->addDays(180)->isPast();
    }

    /**
     * Calcula cuántos días faltan para poder solicitar de nuevo.
     */
    public static function daysRemaining($userId, $tipo)
    {
        $lastApproved = self::where('user_id', $userId)
            ->where('tipo', $tipo)
            ->where('estado', 'aprobado')
            ->latest()
            ->first();

        if (!$lastApproved) return 0;

        $nextDate = $lastApproved->created_at->addDays(180);
        
        if ($nextDate->isPast()) return 0;

        return now()->diffInDays($nextDate);
    }
}
