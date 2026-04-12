<?php

namespace App\Exports;

use App\Models\Insumo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class InsumosExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Almacén Insumos';
    }

    public function collection()
    {
        $query = Insumo::with(['marca', 'categoriaInsumo', 'departamento', 'trabajador', 'computador', 'dispositivo']);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                  ->orWhere('bien_nacional', 'like', "%$search%")
                  ->orWhere('serial', 'like', "%$search%")
                  ->orWhereHas('marca', fn($sub) => $sub->where('nombre', 'like', "%$search%"))
                  ->orWhereHas('categoriaInsumo', fn($sub) => $sub->where('nombre', 'like', "%$search%"));
            });
        }

        if (isset($this->filters['estado'])) {
            if ($this->filters['estado'] === 'activos') $query->where('activo', true);
            if ($this->filters['estado'] === 'inactivos') $query->where('activo', false);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Bien Nacional',
            'Serial / S/N',
            'Insumo / Modelo',
            'Categoría',
            'Marca',
            'Descripción',
            'Stock Actual',
            'U. Medida',
            '¿Reutilizable?',
            'Instalable en PC',
            'Stock Mínimo (Alerta)',
            'Condición Física',
            'Ubicación (Dpto)',
            'Responsable',
            'Asociado a PC',
            'Asociado a Dispositivo',
            'Estado',
            'Creado Por',
            'Última Modif. Por',
            'Fecha Registro',
            'Última Modificación',
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->bien_nacional ?? 'S/P',
            $item->serial ?? 'S/S',
            $item->nombre,
            $item->categoriaInsumo->nombre ?? 'Sin Categoría',
            $item->marca->nombre ?? 'N/A',
            $item->descripcion ?? 'N/A',
            floatval($item->medida_actual),
            $item->unidad_medida,
            $item->reutilizable ? 'SI' : 'NO',
            $item->instalable_en_equipo ? 'SI' : 'NO',
            floatval($item->medida_minima),
            strtoupper(str_replace('_', ' ', $item->estado_fisico)),
            $item->departamento->nombre ?? 'STOCK / ALMACÉN',
            $item->trabajador ? ($item->trabajador->nombres . ' ' . $item->trabajador->apellidos) : 'No asignado',
            $item->computador ? ('BN: ' . $item->computador->bien_nacional) : 'N/A',
            $item->dispositivo ? ('BN: ' . $item->dispositivo->bien_nacional) : 'N/A',
            $item->activo ? 'ACTIVO' : 'INACTIVO',
            $item->creator->name ?? 'Sistema',
            $item->updater->name ?? 'N/A',
            $item->created_at->format('d/m/Y H:i'),
            $item->updated_at->format('d/m/Y H:i'),
        ];
    }
}
