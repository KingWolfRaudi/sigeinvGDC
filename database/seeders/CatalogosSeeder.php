<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Marca; // <-- No olvides importar el modelo de Marca
use App\Models\TipoDispositivo;
use App\Models\SistemaOperativo;
use App\Models\Puerto;
use App\Models\Departamento;

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
            'Teléfono Móvil', 'Tablet', 'Servidor', 'Router', 'Switch', 'UPS'
        ];
        foreach ($tipos as $tipo) {
            TipoDispositivo::firstOrCreate(['nombre' => $tipo], ['activo' => true]);
        }

        // 2. Sistemas Operativos (Tu código actual)
        $sistemas = [
            'Windows 10', 'Windows 11', 'Windows Server 2022', 
            'Ubuntu 22.04 LTS', 'Ubuntu 24.04 LTS', 
            'macOS Sonoma', 'Android 14', 'iOS 17', 'Sin Sistema Operativo'
        ];
        foreach ($sistemas as $sistema) {
            SistemaOperativo::firstOrCreate(['nombre' => $sistema], ['activo' => true]);
        }

        // 3. Puertos y Conexiones (Tu código actual)
        $puertos = [
            'USB-A 2.0', 'USB-A 3.0', 'USB-C', 'HDMI', 'DisplayPort', 
            'VGA', 'Ethernet (RJ45)', 'Jack Audio 3.5mm', 'Thunderbolt 4'
        ];
        foreach ($puertos as $puerto) {
            Puerto::firstOrCreate(['nombre' => $puerto], ['activo' => true]);
        }

        // 4. Departamentos (Tu código actual)
        $departamentos = [
            'Tecnología de la Información (TI)', 'Recursos Humanos', 
            'Contabilidad y Finanzas', 'Administración', 'Ventas', 
            'Marketing', 'Operaciones', 'Dirección General'
        ];
        foreach ($departamentos as $departamento) {
            Departamento::firstOrCreate(['nombre' => $departamento], ['activo' => true]);
        }
    }
}