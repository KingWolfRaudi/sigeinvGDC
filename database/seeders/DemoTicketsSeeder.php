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
        // 2. Crear 11 Trabajadores y Usuarios
        $deptos = Departamento::where('activo', true)->take(11)->get();
        if ($deptos->isEmpty()) return;

        for ($i = 1; $i <= 11; $i++) {
            $deptoId = $deptos[($i - 1) % $deptos->count()]->id;
            
            $trab = Trabajador::create([
                'nombres' => 'Trabajador ' . $i,
                'apellidos' => 'Demo ' . $i,
                'cedula' => 'V-' . (25000000 + $i),
                'cargo' => 'Cargo Demo ' . $i,
                'departamento_id' => $deptoId,
                'activo' => true
            ]);

            // Crear un usuario para algunos trabajadores
            if ($i <= 11) {
                $user = User::create([
                    'name' => $trab->nombres . ' ' . $trab->apellidos,
                    'username' => 'user' . $i,
                    'email' => 'trabajador' . $i . '@sigeinv.test',
                    'password' => Hash::make('password'),
                    'activo' => true,
                    'trabajador_id' => $trab->id,
                ]);
                $user->assignRole('trabajador');
            }
        }

        // Crear 11 Técnicos Resolutores (reutilizando especialidades)
        $especialidades = EspecialidadTecnica::all();
        for ($i = 1; $i <= 11; $i++) {
            $esp = $especialidades[($i - 1) % $especialidades->count()];
            $tec = User::create([
                'name' => 'Técnico ' . $i . ' (' . $esp->nombre . ')',
                'username' => 'tecnico' . $i,
                'email' => 'tecnico' . $i . '@sigeinv.test',
                'password' => Hash::make('password'),
                'activo' => true,
                'disponible_asignacion' => true,
                'especialidad_id' => $esp->id,
            ]);
            $tec->assignRole('resolutor-incidencia');
        }

        // 3. Crear 11 Incidencias
        $problemas = Problema::all();
        $tecnicos = User::role('resolutor-incidencia')->get();
        $usuarios = User::whereNotNull('trabajador_id')->role('trabajador')->get();
        $computadores = Computador::all();
        $fallbackDepto = Departamento::first()->id ?? 1;

        for ($i = 1; $i <= 11; $i++) {
            $userSolicitante = $usuarios[($i - 1) % $usuarios->count()];
            $prob = $problemas[($i - 1) % $problemas->count()];
            $tec = $tecnicos[($i - 1) % $tecnicos->count()];
            $comp = $computadores[($i - 1) % $computadores->count()];

            Incidencia::create([
                'problema_id' => $prob->id,
                'departamento_id' => $userSolicitante->trabajador?->departamento_id ?? $fallbackDepto,
                'dependencia_id' => $userSolicitante->trabajador?->dependencia_id ?? null,
                'trabajador_id' => $userSolicitante->trabajador_id,
                'user_id' => ($i % 3 != 0) ? $tec->id : null, 
                'modelo_id' => ($i % 2 == 0) ? $comp->id : null,
                'modelo_type' => ($i % 2 == 0) ? Computador::class : null,
                'descripcion' => 'Descripción de la incidencia demo #' . $i . ': Fallo reportado por el usuario.',
                'nota_resolucion' => ($i % 2 == 0) ? 'Resolución técnica aplicada para el ticket #' . $i : null,
                'solventado' => ($i % 2 == 0),
                'cerrado' => ($i % 4 == 0),
            ]);
        }
    }
}
