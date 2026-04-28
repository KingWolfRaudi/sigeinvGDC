<?php

namespace App\Exports;

use App\Models\Dispositivo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DispositivosExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Dispositivos Periféricos';
    }

    public function collection()
    {
        $query = Dispositivo::with(['marca', 'tipoDispositivo', 'departamento', 'dependencia', 'trabajador', 'computador', 'puertos']);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('bien_nacional', 'like', "%$search%")
                  ->orWhere('serial', 'like', "%$search%")
                  ->orWhere('nombre', 'like', "%$search%")
                  ->orWhere('ip', 'like', "%$search%")
                  ->orWhereHas('marca', fn($sub) => $sub->where('nombre', 'like', "%$search%"));
            });
        }

        if (isset($this->filters['estado'])) {
            if ($this->filters['estado'] === 'activos') $query->where('activo', true);
            if ($this->filters['estado'] === 'inactivos') $query->where('activo', false);
        }

        if (!empty($this->filters['departamento_id'])) {
            $query->where('departamento_id', $this->filters['departamento_id']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Bien Nacional',
            'Serial / S/N',
            'Dispositivo (Modelo)',
            'Marca',
            'Tipo',
            'Red (IP)',
            'Departamento',
            'Dependencia',
            'Responsable',
            'Conectado a PC',
            'Condición',
            'Puertos',
            'Notas',
            'Estado',
            'Creado Por',
            'Última Modif. Por',
            'Fecha Registro',
            'Última Modificación',
        ];
    }

    public function map($item): array
    {
        $puertos = $item->puertos->pluck('nombre')->implode(', ');

        return [
            $item->id,
            $item->bien_nacional ?? 'N/A',
            $item->serial ?? 'N/A',
            $item->nombre,
            $item->marca->nombre ?? 'N/A',
            $item->tipoDispositivo->nombre ?? 'N/A',
            $item->ip ?? 'Directo / N/A',
            $item->departamento->nombre ?? 'STOCK / ALMACÉN',
            $item->dependencia->nombre ?? 'N/A',
            ($item->trabajador ? ($item->trabajador->nombres . ' ' . $item->trabajador->apellidos) : 'No asignado'),
            $item->computador ? ('BN: ' . $item->computador->bien_nacional) : 'Libre / En Red',
            strtoupper(str_replace('_', ' ', $item->estado)),
            $puertos ?: 'Estandard',
            $item->notas,
            $item->activo ? 'ACTIVO' : 'INACTIVO',
            $item->creator->name ?? 'Sistema',
            $item->updater->name ?? 'N/A',
            $item->created_at->format('d/m/Y H:i'),
            $item->updated_at->format('d/m/Y H:i'),
        ];
    }
}
