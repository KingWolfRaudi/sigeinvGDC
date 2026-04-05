<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MassiveReportExport implements WithMultipleSheets
{
    protected $selections;

    /**
     * @param array $selections Array of ['module' => '...', 'filters' => [...]]
     */
    public function __construct(array $selections)
    {
        $this->selections = $selections;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->selections as $selection) {
            $module = $selection['module'];
            $fullInventory = $selection['full_inventory'] ?? false;
            $filters = $fullInventory ? [] : ($selection['filters'] ?? []); // <-- Si es full, vaciar filtros

            switch ($module) {
                case 'computadores':
                    $sheets[] = new ComputadoresExport($filters);
                    break;
                case 'dispositivos':
                    $sheets[] = new DispositivosExport($filters);
                    break;
                case 'insumos':
                    $sheets[] = new InsumosExport($filters);
                    break;
                case 'marcas':
                    $sheets[] = new CatalogosExport(\App\Models\Marca::class, $filters, 'Marcas');
                    break;
                case 'tipos-dispositivo':
                    $sheets[] = new CatalogosExport(\App\Models\TipoDispositivo::class, $filters, 'Tipos de Dispositivo');
                    break;
                case 'sistemas-operativos':
                    $sheets[] = new CatalogosExport(\App\Models\SistemaOperativo::class, $filters, 'Sistemas Operativos');
                    break;
                case 'departamentos':
                    $sheets[] = new CatalogosExport(\App\Models\Departamento::class, $filters, 'Departamentos');
                    break;
                case 'trabajadores':
                    $sheets[] = new CatalogosExport(\App\Models\Trabajador::class, $filters, 'Trabajadores');
                    break;
                case 'procesadores':
                    $sheets[] = new CatalogosExport(\App\Models\Procesador::class, $filters, 'Procesadores');
                    break;
                case 'gpus':
                    $sheets[] = new CatalogosExport(\App\Models\Gpu::class, $filters, 'GPUs');
                    break;
                case 'puertos':
                    $sheets[] = new CatalogosExport(\App\Models\Puerto::class, $filters, 'Puertos');
                    break;
            }
        }

        return $sheets;
    }
}
