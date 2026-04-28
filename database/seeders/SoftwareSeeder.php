<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Software;

class SoftwareSeeder extends Seeder
{
    public function run(): void
    {
        $softwareList = [
            ['nombre_programa' => 'MS Office 2021 Pro Plus', 'tipo_licencia' => 'Privativo', 'serial' => 'XXXXX-XXXXX-XXXXX-XXXXX-XXXXX', 'arquitectura_programa' => '64bits'],
            ['nombre_programa' => 'Adobe Acrobat Pro DC', 'tipo_licencia' => 'Privativo', 'serial' => '1234-5678-9012-3456', 'arquitectura_programa' => '64bits'],
            ['nombre_programa' => 'VLC Media Player', 'tipo_licencia' => 'Libre', 'serial' => null, 'arquitectura_programa' => 'Universal'],
            ['nombre_programa' => 'Google Chrome Enterprise', 'tipo_licencia' => 'Libre', 'serial' => null, 'arquitectura_programa' => '64bits'],
            ['nombre_programa' => 'Mozilla Firefox', 'tipo_licencia' => 'Libre', 'serial' => null, 'arquitectura_programa' => 'Universal'],
            ['nombre_programa' => '7-Zip', 'tipo_licencia' => 'Libre', 'serial' => null, 'arquitectura_programa' => '64bits'],
            ['nombre_programa' => 'AutoCAD 2024', 'tipo_licencia' => 'Privativo', 'serial' => '999-99999999', 'arquitectura_programa' => '64bits'],
            ['nombre_programa' => 'Kaspersky Endpoint Security', 'tipo_licencia' => 'Privativo', 'serial' => 'KASP-123-ABC', 'arquitectura_programa' => 'Universal'],
            ['nombre_programa' => 'WinRAR Professional', 'tipo_licencia' => 'Privativo', 'serial' => 'WIN-RAR-PRO-LICENSE', 'arquitectura_programa' => 'Universal'],
            ['nombre_programa' => 'Slack Desktop', 'tipo_licencia' => 'Libre', 'serial' => null, 'arquitectura_programa' => '64bits'],
            ['nombre_programa' => 'Zoom Meetings', 'tipo_licencia' => 'Libre', 'serial' => null, 'arquitectura_programa' => '64bits'],
            ['nombre_programa' => 'LibreOffice Suite', 'tipo_licencia' => 'Libre', 'serial' => null, 'arquitectura_programa' => '64bits'],
        ];

        foreach ($softwareList as $soft) {
            Software::firstOrCreate(
                ['nombre_programa' => $soft['nombre_programa']],
                [
                    'tipo_licencia' => $soft['tipo_licencia'],
                    'serial' => $soft['serial'],
                    'arquitectura_programa' => $soft['arquitectura_programa'],
                    'descripcion_programa' => 'Software de prueba para el inventario corporativo.',
                    'activo' => true
                ]
            );
        }
    }
}
