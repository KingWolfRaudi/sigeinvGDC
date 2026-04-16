<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Computador;
use App\Models\Dispositivo;
use App\Models\Marca;
use App\Models\TipoDispositivo;
use App\Models\SistemaOperativo;
use App\Models\Procesador;
use App\Models\Gpu;
use App\Models\Departamento;
use App\Models\Puerto;
use App\Models\Insumo;
use App\Models\CategoriaInsumo;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        // Elementos base
        $marcaDell = Marca::where('nombre', 'Dell')->first()->id ?? 1;
        $marcaHP = Marca::where('nombre', 'HP')->first()->id ?? 1;
        $marcaEpson = Marca::where('nombre', 'Epson')->first()->id ?? 1;
        $marcaCisco = Marca::where('nombre', 'Cisco')->first()->id ?? 1;
        
        $tipoDesktop = TipoDispositivo::where('nombre', 'Computadora de Escritorio')->first()->id ?? 1;
        $tipoLaptop = TipoDispositivo::where('nombre', 'Laptop')->first()->id ?? 1;
        $tipoImpresora = TipoDispositivo::where('nombre', 'Impresora')->first()->id ?? 1;
        $tipoRouter = TipoDispositivo::where('nombre', 'Router')->first()->id ?? 1;

        $osWindows10 = SistemaOperativo::where('nombre', 'Windows 10')->first()->id ?? 1;
        $osWindows11 = SistemaOperativo::where('nombre', 'Windows 11')->first()->id ?? 1;

        $procI5 = Procesador::where('modelo', 'Core i5')->first()->id ?? 1;
        $procRyzen5 = Procesador::where('modelo', 'Ryzen 5')->first()->id ?? 1;

        $gpuBasica = Gpu::where('modelo', 'Gráficos UHD Integrados')->first()->id ?? null;
        $gpuDedicada = Gpu::where('modelo', 'GeForce GTX 1650')->first()->id ?? null;

        $deptoTI = Departamento::where('nombre', 'Tecnología de la Información (TI)')->first()->id ?? 1;
        $deptoAdmin = Departamento::where('nombre', 'Administración')->first()->id ?? 1;

        // Recuperar algunos puertos
        $puertoUSB = Puerto::where('nombre', 'USB-A 3.0')->first()->id ?? null;
        $puertoRJ45 = Puerto::where('nombre', 'Ethernet (RJ45)')->first()->id ?? null;
        $puertoHDMI = Puerto::where('nombre', 'HDMI')->first()->id ?? null;

        // 1. Crear 11 Computadores
        for ($i = 1; $i <= 11; $i++) {
            $comp = Computador::create([
                'nombre_equipo' => 'PC-EQUIPO-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'bien_nacional' => 'BN-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'serial' => strtoupper(substr(md5(uniqid()), 0, 12)),
                'marca_id' => ($i % 2 == 0) ? $marcaDell : $marcaHP,
                'tipo_computador' => ($i % 3 == 0) ? 'Laptop' : 'Computadora de escritorio',
                'sistema_operativo_id' => ($i % 2 == 0) ? $osWindows10 : $osWindows11,
                'procesador_id' => ($i % 2 == 0) ? $procI5 : $procRyzen5,
                'gpu_id' => ($i % 4 == 0) ? $gpuDedicada : $gpuBasica,
                'departamento_id' => ($i % 3 == 0) ? $deptoTI : $deptoAdmin,
                'tipo_ram' => ($i % 2 == 0) ? 'DDR4' : 'DDR5',
                'mac' => implode(':', str_split(strtoupper(substr(md5(uniqid()), 0, 12)), 2)),
                'ip' => '192.168.1.' . (50 + $i),
                'tipo_conexion' => ($i % 3 == 0) ? 'Wi-Fi' : 'Ethernet',
                'unidad_dvd' => false,
                'fuente_poder' => true,
                'estado_fisico' => 'operativo',
                'observaciones' => 'Equipo de prueba #' . $i,
                'activo' => true
            ]);

            if ($puertoUSB && $puertoRJ45) {
                $comp->puertos()->sync([$puertoUSB, $puertoRJ45]);
            }
        }

        // 2. Crear 11 Dispositivos
        for ($i = 1; $i <= 11; $i++) {
            Dispositivo::create([
                'bien_nacional' => 'BN-DISP-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'serial' => strtoupper(substr(md5(uniqid()), 0, 10)),
                'tipo_dispositivo_id' => ($i % 2 == 0) ? $tipoImpresora : $tipoRouter,
                'marca_id' => ($i % 2 == 0) ? $marcaEpson : $marcaCisco,
                'nombre' => (($i % 2 == 0) ? 'Impresora L-Series ' : 'Router Multi-Port ') . $i,
                'ip' => '192.168.1.' . (200 + $i),
                'estado' => 'operativo',
                'departamento_id' => ($i % 2 == 0) ? $deptoAdmin : $deptoTI,
                'computador_id' => null,
                'notas' => 'Dispositivo de prueba #' . $i,
                'activo' => true
            ]);
        }

        // 3. Crear 11 Insumos / Herramientas
        $catToner = CategoriaInsumo::where('nombre', 'Suministro/Impresión (Tóner, Tinta)')->first()->id ?? 1;
        $catCable = CategoriaInsumo::where('nombre', 'Cableado y Conectividad (Bobinas, Plugs RJ45)')->first()->id ?? 1;
        $catHerramienta = CategoriaInsumo::where('nombre', 'Herramienta Fija (Crimpadora, Tester, Pinzas)')->first()->id ?? 1;
        $catRepuesto = CategoriaInsumo::where('nombre', 'Repuesto Computacional (RAM, SSD, Placa)')->first()->id ?? 1;
        
        $marcaKingston = Marca::where('nombre', 'Kingston')->first()->id ?? 1;
        $marcaTruper = Marca::where('nombre', 'Truper')->first()->id ?? 1;
        $marcaCommScope = Marca::where('nombre', 'CommScope')->first()->id ?? 1;

        $insumosBase = [
            ['nombre' => 'Bobina UTP Cat 6', 'cat' => $catCable, 'marca' => $marcaCommScope, 'medida' => 'metros'],
            ['nombre' => 'Crimpadora RJ45', 'cat' => $catHerramienta, 'marca' => $marcaTruper, 'medida' => 'unidad'],
            ['nombre' => 'RAM 8GB DDR4', 'cat' => $catRepuesto, 'marca' => $marcaKingston, 'medida' => 'unidad'],
            ['nombre' => 'Tóner Negro', 'cat' => $catToner, 'marca' => $marcaEpson, 'medida' => 'unidad'],
        ];

        for ($i = 1; $i <= 11; $i++) {
            $base = $insumosBase[($i - 1) % count($insumosBase)];
            Insumo::create([
                'bien_nacional' => ($i % 2 == 0) ? 'BN-2026-INS-' . $i : null,
                'serial' => ($i % 2 == 0) ? 'SER-' . $i . '-ABC' : null,
                'nombre' => $base['nombre'] . ' Model ' . $i,
                'descripcion' => 'Insumo de prueba para el almacén.',
                'marca_id' => $base['marca'],
                'categoria_insumo_id' => $base['cat'],
                'unidad_medida' => $base['medida'],
                'medida_actual' => rand(10, 100),
                'medida_minima' => 5,
                'reutilizable' => ($i % 4 == 0),
                'instalable_en_equipo' => ($i % 3 == 0),
                'estado_fisico' => 'operativo',
                'activo' => true
            ]);
        }
    }
}
