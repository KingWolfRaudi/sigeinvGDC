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

        // 1. Crear Computadores Semilla
        $comp1 = Computador::create([
            'bien_nacional' => 'BN-2026-0001',
            'serial' => 'DELL-OPT-90204',
            'marca_id' => $marcaDell,
            'tipo_dispositivo_id' => $tipoDesktop,
            'sistema_operativo_id' => $osWindows10,
            'procesador_id' => $procI5,
            'gpu_id' => $gpuDedicada,
            'departamento_id' => $deptoAdmin,
            'tipo_ram' => 'DDR4',
            'mac' => '00:1B:44:11:3A:B7',
            'ip' => '192.168.1.50',
            'tipo_conexion' => 'Ethernet',
            'unidad_dvd' => false,
            'fuente_poder' => true,
            'estado_fisico' => 'operativo',
            'observaciones' => 'Equipo principal de administración',
            'activo' => true
        ]);

        if ($puertoUSB && $puertoRJ45) {
            $comp1->puertos()->sync([$puertoUSB, $puertoRJ45]);
        }

        $comp2 = Computador::create([
            'bien_nacional' => 'BN-2026-0002',
            'serial' => 'HP-PAV-LT87',
            'marca_id' => $marcaHP,
            'tipo_dispositivo_id' => $tipoLaptop,
            'sistema_operativo_id' => $osWindows11,
            'procesador_id' => $procRyzen5,
            'gpu_id' => $gpuBasica,
            'departamento_id' => $deptoTI,
            'tipo_ram' => 'DDR5',
            'mac' => '00:1A:2B:3C:4D:5E',
            'ip' => '192.168.1.100',
            'tipo_conexion' => 'Wi-Fi',
            'unidad_dvd' => false,
            'fuente_poder' => false,
            'estado_fisico' => 'operativo',
            'observaciones' => 'Laptop asignada al departamento de TI',
            'activo' => true
        ]);

        if ($puertoUSB && $puertoHDMI) {
            $comp2->puertos()->sync([$puertoUSB, $puertoHDMI]);
        }

        // 2. Crear Dispositivos Semilla
        $disp1 = Dispositivo::create([
            'codigo' => 'DISP-IMP-001',
            'serial' => 'EPS-L4260-X1',
            'tipo_dispositivo_id' => $tipoImpresora,
            'marca_id' => $marcaEpson,
            'nombre' => 'EcoTank L4260',
            'ip' => '192.168.1.200',
            'estado' => 'operativo',
            'departamento_id' => $deptoAdmin,
            'computador_id' => null, // Impresora de red compartida
            'notas' => 'Cargada con tinta original',
            'activo' => true
        ]);

        // Sincronizar puerto para el dispositivo
        if ($puertoRJ45) {
            $disp1->puertos()->sync([$puertoRJ45]);
        }

        $disp2 = Dispositivo::create([
            'codigo' => 'DISP-RTR-002',
            'serial' => 'CISCO-RV340',
            'tipo_dispositivo_id' => $tipoRouter,
            'marca_id' => $marcaCisco,
            'nombre' => 'RV340 Dual WAN',
            'ip' => '192.168.1.1',
            'estado' => 'operativo',
            'departamento_id' => $deptoTI,
            'computador_id' => $comp2->id, // Conectado para monitoreo
            'notas' => 'Router principal del edificio',
            'activo' => true
        ]);

        if ($puertoRJ45) {
            $disp2->puertos()->sync([$puertoRJ45]);
        }

        // 3. Crear Insumos / Herramientas Semilla
        $catToner = CategoriaInsumo::where('nombre', 'Suministro/Impresión (Tóner, Tinta)')->first()->id ?? 1;
        $catCable = CategoriaInsumo::where('nombre', 'Cableado y Conectividad (Bobinas, Plugs RJ45)')->first()->id ?? 1;
        $catHerramienta = CategoriaInsumo::where('nombre', 'Herramienta Fija (Crimpadora, Tester, Pinzas)')->first()->id ?? 1;
        $catRepuesto = CategoriaInsumo::where('nombre', 'Repuesto Computacional (RAM, SSD, Placa)')->first()->id ?? 1;
        
        $marcaKingston = Marca::firstOrCreate(['nombre' => 'Kingston'], ['activo' => true])->id;
        $marcaTruper = Marca::firstOrCreate(['nombre' => 'Truper'], ['activo' => true])->id;
        $marcaCommScope = Marca::firstOrCreate(['nombre' => 'CommScope'], ['activo' => true])->id;

        Insumo::create([
            'bien_nacional' => null,
            'serial' => null,
            'nombre' => 'Bobina UTP Cat 6',
            'descripcion' => 'Cable multifilar interior estándar azul.',
            'marca_id' => $marcaCommScope,
            'categoria_insumo_id' => $catCable,
            'unidad_medida' => 'metros',
            'medida_actual' => 120.50,
            'medida_minima' => 50.00,
            'reutilizable' => false,
            'instalable_en_equipo' => false,
            'estado_fisico' => 'operativo',
            'activo' => true
        ]);

        Insumo::create([
            'bien_nacional' => 'BN-2026-HR01',
            'serial' => 'TRP-CRM-08',
            'nombre' => 'Crimpadora RJ45 Profesional',
            'descripcion' => 'Crimpadora resistente para cableado estructurado.',
            'marca_id' => $marcaTruper,
            'categoria_insumo_id' => $catHerramienta,
            'unidad_medida' => 'unidad',
            'medida_actual' => 2.00,
            'medida_minima' => 1.00,
            'reutilizable' => true,
            'instalable_en_equipo' => false,
            'estado_fisico' => 'operativo',
            'activo' => true
        ]);

        Insumo::create([
            'bien_nacional' => 'BN-2026-RM32',
            'serial' => 'KVR32S22S8/8',
            'nombre' => 'Memoria RAM 8GB DDR4 3200MHz',
            'descripcion' => 'Módulo SO-DIMM para Laptop.',
            'marca_id' => $marcaKingston,
            'categoria_insumo_id' => $catRepuesto,
            'unidad_medida' => 'unidad',
            'medida_actual' => 5.00,
            'medida_minima' => 2.00,
            'reutilizable' => false,
            'instalable_en_equipo' => true,
            'estado_fisico' => 'operativo',
            'activo' => true
        ]);
        
        Insumo::create([
            'bien_nacional' => null,
            'serial' => null,
            'nombre' => 'Tóner Negro',
            'descripcion' => 'Genérico, rinde 2000 páginas.',
            'marca_id' => $marcaEpson,
            'categoria_insumo_id' => $catToner,
            'unidad_medida' => 'unidad',
            'medida_actual' => 1.00, 
            'medida_minima' => 2.00, // Stock critico forzado
            'reutilizable' => false,
            'instalable_en_equipo' => false,
            'estado_fisico' => 'operativo',
            'activo' => true
        ]);
    }
}
