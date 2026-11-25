<?php

namespace App\Exports;

use App\Models\Departamento;
use App\Models\Gpu;
use App\Models\Marca;
use App\Models\Procesador;
use App\Models\Puerto;
use App\Models\SistemaOperativo;
use App\Models\TipoDispositivo;
use App\Models\Trabajador;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MassiveReportExport implements WithMultipleSheets
{
    protected $selections;

    /**
     * @param  array  $selections  Array of ['module' => '...', 'filters' => [...]]
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
                    $sheets[] = new CatalogosExport(Marca::class, $filters, 'Marcas');
                    break;
                case 'tipos-dispositivo':
                    $sheets[] = new CatalogosExport(TipoDispositivo::class, $filters, 'Tipos de Dispositivo');
                    break;
                case 'sistemas-operativos':
                    $sheets[] = new CatalogosExport(SistemaOperativo::class, $filters, 'Sistemas Operativos');
                    break;
                case 'departamentos':
                    $sheets[] = new CatalogosExport(Departamento::class, $filters, 'Departamentos');
                    break;
                case 'trabajadores':
                    $sheets[] = new CatalogosExport(Trabajador::class, $filters, 'Trabajadores');
                    break;
                case 'procesadores':
                    $sheets[] = new CatalogosExport(Procesador::class, $filters, 'Procesadores');
                    break;
                case 'gpus':
                    $sheets[] = new CatalogosExport(Gpu::class, $filters, 'GPUs');
                    break;
                case 'puertos':
                    $sheets[] = new CatalogosExport(Puerto::class, $filters, 'Puertos');
                    break;
                default:
                    // Estado no cubierto: falla explícito, nunca hoja vacía en silencio
                    throw new \InvalidArgumentException("Módulo de reporte no soportado: {$module}");
            }
        }

        return $sheets;
    }
}
