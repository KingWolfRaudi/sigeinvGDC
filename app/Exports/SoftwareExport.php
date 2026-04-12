<?php

namespace App\Exports;

use App\Models\Software;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class SoftwareExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Catálogo de Software';
    }

    public function collection()
    {
        $query = Software::query();

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre_programa', 'like', "%$search%")
                  ->orWhere('arquitectura_programa', 'like', "%$search%")
                  ->orWhere('tipo_licencia', 'like', "%$search%")
                  ->orWhere('serial', 'like', "%$search%")
                  ->orWhere('descripcion_programa', 'like', "%$search%");
            });
        }

        if (isset($this->filters['estado'])) {
            if ($this->filters['estado'] === 'activos') $query->where('activo', true);
            if ($this->filters['estado'] === 'inactivos') $query->where('activo', false);
        }

        return $query->orderBy('nombre_programa', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre del Programa',
            'Arquitectura',
            'Tipo de Licencia',
            'Serial / Clave',
            'Descripción',
            'Estado',
            'Fecha de Registro'
        ];
    }

    public function map($software): array
    {
        return [
            $software->id,
            $software->nombre_programa,
            $software->arquitectura_programa ?? 'No aplica',
            $software->tipo_licencia,
            $software->serial ?? 'N/A',
            $software->descripcion_programa ?? 'N/A',
            $software->activo ? 'Activo' : 'Inactivo',
            $software->created_at->format('d/m/Y H:i')
        ];
    }
}
