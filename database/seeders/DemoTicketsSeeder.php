<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\EspecialidadTecnica;
use App\Models\Trabajador;
use App\Models\Departamento;
use App\Models\Problema;
use App\Models\Incidencia;
use App\Models\Computador;

class DemoTicketsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener Especialidades
        $espSoporte = EspecialidadTecnica::where('nombre', 'Soporte Técnico Hardware/Software')->first();
        $espRedes = EspecialidadTecnica::where('nombre', 'Redes e Infraestructura')->first();

        if (!$espSoporte || !$espRedes) return;

        // 2. Crear Técnicos Resolutores
        $tecnicoRedes = User::firstOrCreate(
            ['email' => 'redes@sigeinv.test'],
            [
                'name' => 'Técnico de Redes (Demo)',
                'username' => 'tecnico_redes',
                'password' => Hash::make('password'),
                'activo' => true,
                'disponible_asignacion' => true,
                'especialidad_id' => $espRedes->id,
            ]
        );
        $tecnicoRedes->assignRole('resolutor-incidencia');

        $tecnicoSoporte = User::firstOrCreate(
            ['email' => 'soporte@sigeinv.test'],
            [
                'name' => 'Técnico de Soporte (Demo)',
                'username' => 'tecnico_soporte',
                'password' => Hash::make('password'),
                'activo' => true,
                'disponible_asignacion' => true,
                'especialidad_id' => $espSoporte->id,
            ]
        );
        $tecnicoSoporte->assignRole('resolutor-incidencia');

        // 3. Crear Usuario Estándar atado a un Trabajador (si existe alguno)
        $trabajador = Trabajador::first();
        $departamento = $trabajador ? $trabajador->departamento_id : Departamento::first()->id ?? null;

        if ($trabajador) {
            $usuarioEstandar = User::firstOrCreate(
                ['email' => 'usuario@sigeinv.test'],
                [
                    'name' => 'Usuario Estándar (Demo)',
                    'username' => 'usuario',
                    'password' => Hash::make('password'),
                    'activo' => true,
                    'trabajador_id' => $trabajador->id,
                ]
            );
            $usuarioEstandar->assignRole('trabajador');
        }

        // 4. Sembrar algunas Incidencias falsas
        if ($departamento) {
            $comp = Computador::first();
            $probSoporte = Problema::where('especialidad_id', $espSoporte->id)->first();
            $probRedes = Problema::where('especialidad_id', $espRedes->id)->first();

            // Incidencia 1: Abierta, asignada a Soporte
            if ($probSoporte && $comp) {
                Incidencia::create([
                    'problema_id' => $probSoporte->id,
                    'departamento_id' => $departamento,
                    'trabajador_id' => $trabajador->id ?? null,
                    'user_id' => $tecnicoSoporte->id,
                    'modelo_id' => $comp->id,
                    'modelo_type' => Computador::class,
                    'descripcion' => 'El equipo no enciende desde la tormenta de anoche.',
                    'solventado' => false,
                    'cerrado' => false,
                ]);
            }

            // Incidencia 2: Cerrada, de Redes
            if ($probRedes) {
                Incidencia::create([
                    'problema_id' => $probRedes->id,
                    'departamento_id' => $departamento,
                    'trabajador_id' => $trabajador->id ?? null,
                    'user_id' => $tecnicoRedes->id,
                    'descripcion' => 'Punto de red en oficina principal no da acceso a internet.',
                    'nota_resolucion' => 'Se reemplazó el cable patch cord de la roseta al switch. Puerto 14.',
                    'solventado' => true,
                    'cerrado' => true,
                ]);
            }

            // Incidencia 3: Pendiente de asignación (Sin Técnico)
            if ($probSoporte) {
                Incidencia::create([
                    'problema_id' => $probSoporte->id,
                    'departamento_id' => $departamento,
                    'trabajador_id' => $trabajador->id ?? null,
                    'user_id' => null, // Nadie la ha tomado!
                    'descripcion' => 'Se requiere instalación de software de diseño.',
                    'solventado' => false,
                    'cerrado' => false,
                ]);
            }
        }
    }
}
