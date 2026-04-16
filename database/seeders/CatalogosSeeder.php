<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Marca; // <-- No olvides importar el modelo de Marca
use App\Models\TipoDispositivo;
use App\Models\SistemaOperativo;
use App\Models\Puerto;
use App\Models\Departamento;
use App\Models\Procesador;
use App\Models\Gpu;
use App\Models\CategoriaInsumo;

class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Marcas (NUEVO)
        $marcas = [
            'HP', 'Dell', 'Lenovo', 'Apple', 'Asus', 'Acer', 'MSI', // Computadoras
            'Intel', 'AMD', 'NVIDIA', // Procesadores y GPUs
            'Samsung', 'LG', 'AOC', 'ViewSonic', // Monitores
            'Kingston', 'Corsair', 'Crucial', 'Western Digital', 'Seagate', // Almacenamiento y RAM
            'Logitech', 'Genius', 'Microsoft', // Periféricos
            'Cisco', 'TP-Link', 'Ubiquiti', 'MikroTik', // Redes
            'Epson', 'Canon', 'Brother', 'Kyocera', // Impresoras
            'APC', 'Forza', // Energía/UPS
            'Genérica' // Siempre es bueno tener una opción genérica
        ];
        
        foreach ($marcas as $marca) {
            Marca::firstOrCreate(['nombre' => $marca], ['activo' => true]);
        }

        // 1. Tipos de Dispositivos (Tu código actual)
        $tipos = [
            'Laptop', 'Computadora de Escritorio', 'Monitor', 'Impresora', 
            'Teléfono Móvil', 'Tablet', 'Servidor', 'Router', 'Switch', 'UPS',
            'Cámara de Seguridad', 'Escáner', 'Proyector'
        ];
        foreach ($tipos as $tipo) {
            TipoDispositivo::firstOrCreate(['nombre' => $tipo], ['activo' => true]);
        }

        // 2. Sistemas Operativos (Tu código actual)
        $sistemas = [
            'Windows 10', 'Windows 11', 'Windows Server 2022', 
            'Ubuntu 22.04 LTS', 'Ubuntu 24.04 LTS', 
            'macOS Sonoma', 'macOS Ventura', 'Android 14', 'iOS 17', 
            'Debian 12', 'Red Hat Enterprise Linux 9', 'Sin Sistema Operativo'
        ];
        foreach ($sistemas as $sistema) {
            SistemaOperativo::firstOrCreate(['nombre' => $sistema], ['activo' => true]);
        }

        // 3. Puertos y Conexiones (Tu código actual)
        $puertos = [
            'USB-A 2.0', 'USB-A 3.0', 'USB-C', 'HDMI', 'DisplayPort', 
            'VGA', 'Ethernet (RJ45)', 'Jack Audio 3.5mm', 'Thunderbolt 4',
            'DVI', 'Serial (RS-232)', 'Paralelo (LPT)'
        ];
        foreach ($puertos as $puerto) {
            Puerto::firstOrCreate(['nombre' => $puerto], ['activo' => true]);
        }

        // 4. Departamentos (Tu código actual)
        $departamentos = [
            'Tecnología de la Información (TI)', 'Recursos Humanos', 
            'Contabilidad y Finanzas', 'Administración', 'Ventas', 
            'Marketing', 'Operaciones', 'Dirección General',
            'Servicios Generales', 'Seguridad Integral', 'Auditoría Interna',
            'Logística y Transporte'
        ];
        foreach ($departamentos as $departamento) {
            Departamento::firstOrCreate(['nombre' => $departamento], ['activo' => true]);
        }

        // 5. Procesadores
        $marcaIntel = Marca::where('nombre', 'Intel')->first()->id;
        $marcaAMD = Marca::where('nombre', 'AMD')->first()->id;

        $procesadores = [
            ['marca_id' => $marcaIntel, 'modelo' => 'Core i3', 'generacion' => '10 Gen', 'hilos' => 4],
            ['marca_id' => $marcaIntel, 'modelo' => 'Core i5', 'generacion' => '10 Gen', 'hilos' => 8],
            ['marca_id' => $marcaIntel, 'modelo' => 'Core i7', 'generacion' => '12 Gen', 'hilos' => 16],
            ['marca_id' => $marcaIntel, 'modelo' => 'Core i9', 'generacion' => '13 Gen', 'hilos' => 24],
            ['marca_id' => $marcaIntel, 'modelo' => 'Xeon Silver', 'generacion' => '3ra Gen', 'hilos' => 32],
            ['marca_id' => $marcaAMD, 'modelo' => 'Ryzen 3', 'generacion' => '3000 Series', 'hilos' => 8],
            ['marca_id' => $marcaAMD, 'modelo' => 'Ryzen 5', 'generacion' => '5000 Series', 'hilos' => 12],
            ['marca_id' => $marcaAMD, 'modelo' => 'Ryzen 7', 'generacion' => '7000 Series', 'hilos' => 16],
            ['marca_id' => $marcaAMD, 'modelo' => 'Ryzen 9', 'generacion' => '7000 Series', 'hilos' => 24],
            ['marca_id' => $marcaAMD, 'modelo' => 'EPYC', 'generacion' => 'Milan', 'hilos' => 64],
            ['marca_id' => $marcaApple ?? 4, 'modelo' => 'Apple M1', 'generacion' => 'N/A', 'hilos' => 8],
            ['marca_id' => $marcaApple ?? 4, 'modelo' => 'Apple M2 Pro', 'generacion' => 'N/A', 'hilos' => 12],
        ];
        
        foreach ($procesadores as $proc) {
            Procesador::firstOrCreate(
                ['modelo' => $proc['modelo']], 
                ['marca_id' => $proc['marca_id'], 'generacion' => $proc['generacion'], 'hilos' => $proc['hilos'], 'activo' => true]
            );
        }

        // 6. GPUs
        $marcaNvidia = Marca::where('nombre', 'NVIDIA')->first()->id;
        $marcaGenerica = Marca::where('nombre', 'Genérica')->first()->id;

        $gpus = [
            ['marca_id' => $marcaNvidia, 'modelo' => 'GeForce GTX 1650', 'memoria' => '4GB'],
            ['marca_id' => $marcaNvidia, 'modelo' => 'GeForce RTX 3060', 'memoria' => '12GB'],
            ['marca_id' => $marcaNvidia, 'modelo' => 'GeForce RTX 4070', 'memoria' => '12GB'],
            ['marca_id' => $marcaNvidia, 'modelo' => 'Quadro T1000', 'memoria' => '4GB'],
            ['marca_id' => $marcaAMD, 'modelo' => 'Radeon RX 6600', 'memoria' => '8GB'],
            ['marca_id' => $marcaAMD, 'modelo' => 'Radeon RX 7900 XTX', 'memoria' => '24GB'],
            ['marca_id' => $marcaAMD, 'modelo' => 'Radeon Pro W6400', 'memoria' => '4GB'],
            ['marca_id' => $marcaIntel, 'modelo' => 'Arc A770', 'memoria' => '16GB'],
            ['marca_id' => $marcaIntel, 'modelo' => 'Gráficos UHD Integrados', 'memoria' => 'N/A'],
            ['marca_id' => $marcaGenerica, 'modelo' => 'Tarjeta Genérica Básica', 'memoria' => '1GB'],
            ['marca_id' => $marcaGenerica, 'modelo' => 'Chipset SVGA Estándar', 'memoria' => '512MB'],
        ];
        
        foreach ($gpus as $gpu) {
            Gpu::firstOrCreate(
                ['modelo' => $gpu['modelo']], 
                ['marca_id' => $gpu['marca_id'], 'memoria' => $gpu['memoria'], 'activo' => true]
            );
        }

        // 7. Categorías de Insumos
        $categoriasInsumo = [
            'Suministro/Impresión (Tóner, Tinta)',
            'Repuesto Computacional (RAM, SSD, Placa)',
            'Cableado y Conectividad (Bobinas, Plugs RJ45)',
            'Herramienta Fija (Crimpadora, Tester, Pinzas)',
            'Periférico Genérico (Baterías, Cargadores)',
            'Accesorio Vario',
            'Limpieza Técnica (Aire comp., Alcohol Isop.)',
            'Material de Oficina Asociado',
            'Equipos de Medición',
            'Organización de Cableado (Racks, PDU)',
            'Adaptadores y Conversiones'
        ];
        foreach ($categoriasInsumo as $cat) {
            CategoriaInsumo::firstOrCreate(['nombre' => $cat], ['activo' => true]);
        }
    }
}