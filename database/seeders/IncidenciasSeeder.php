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
        // 0. Especialidades Técnicas
        $espSoporte = \App\Models\EspecialidadTecnica::firstOrCreate(['nombre' => 'Soporte Técnico Hardware/Software']);
        $espRedes = \App\Models\EspecialidadTecnica::firstOrCreate(['nombre' => 'Redes e Infraestructura']);
        $espMantenimiento = \App\Models\EspecialidadTecnica::firstOrCreate(['nombre' => 'Mantenimiento General']);

        // 1. Tipos de Problemas Iniciales con su Especialidad
        $problemas = [
            ['nombre' => 'Falla de Hardware', 'especialidad_id' => $espSoporte->id],
            ['nombre' => 'Error de Software / Sistema', 'especialidad_id' => $espSoporte->id],
            ['nombre' => 'Problema de Red / Internet', 'especialidad_id' => $espRedes->id],
            ['nombre' => 'Mantenimiento Preventivo', 'especialidad_id' => $espMantenimiento->id],
            ['nombre' => 'Reclamo de Garantía', 'especialidad_id' => $espSoporte->id],
            ['nombre' => 'Cableado Estructurado', 'especialidad_id' => $espRedes->id],
        ];

        foreach ($problemas as $p) {
            Problema::firstOrCreate(['nombre' => $p['nombre']], ['activo' => true, 'especialidad_id' => $p['especialidad_id']]);
        }

        // 2. Configuración Inicial

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
