<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Problema;
use App\Models\Configuracion;

class IncidenciasSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tipos de Problemas Iniciales
        $problemas = [
            'Falla de Hardware',
            'Error de Software / Sistema',
            'Problema de Red / Internet',
            'Solicitud de Mantenimiento',
            'Reclamo de Garantía',
            'Otro'
        ];

        foreach ($problemas as $p) {
            Problema::firstOrCreate(['nombre' => $p], ['activo' => true]);
        }

        // 2. Configuración Inicial (Roles Técnicos)
        // Por defecto, permitiremos Administrador y Personal TI
        Configuracion::firstOrCreate(
            ['clave' => 'incidencias_roles_tecnicos'],
            [
                'valor' => json_encode(['administrador', 'personal-ti']),
                'grupo' => 'incidencias'
            ]
        );

        Configuracion::firstOrCreate(
            ['clave' => 'incidencias_cierre_irreversible'],
            ['valor' => '0', 'grupo' => 'incidencias']
        );

        Configuracion::firstOrCreate(
            ['clave' => 'incidencias_activo_obligatorio'],
            ['valor' => '0', 'grupo' => 'incidencias']
        );

        // 3. Configuración de Perfil (Solicitudes)
        $perfilConfigs = [
            'perfil_solicitar_nombre' => '1',
            'perfil_solicitar_username' => '1',
            'perfil_solicitar_email' => '1',
            'perfil_solicitar_password' => '1',
        ];

        foreach ($perfilConfigs as $clave => $valor) {
            Configuracion::firstOrCreate(
                ['clave' => $clave],
                ['valor' => $valor, 'grupo' => 'perfil']
            );
        }
    }
}
