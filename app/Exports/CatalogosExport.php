<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\Schema;

class CatalogosExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $modelClass;
    protected $filters;
    protected $customTitle;

    public function __construct($modelClass, $filters = [], $title = 'Catalogo')
    {
        $this->modelClass = $modelClass;
        $this->filters = $filters;
        $this->customTitle = $title;
    }

    public function title(): string
    {
        return $this->customTitle;
    }

    public function collection()
    {
        $query = $this->modelClass::query();

        // Si es Procesador o Gpu, cargar marca
        if (in_array(class_basename($this->modelClass), ['Procesador', 'Gpu'])) {
            $query->with('marca');
        }
        
        // Si es Trabajador, cargar departamento
        if (class_basename($this->modelClass) === 'Trabajador') {
            $query->with('departamento');
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                // Intentar buscar por nombre, modelo o nombres/apellidos
                $q->where('id', 'like', "%$search%");
                if (Schema::hasColumn($q->getModel()->getTable(), 'nombre')) {
                    $q->orWhere('nombre', 'like', "%$search%");
                }
                if (Schema::hasColumn($q->getModel()->getTable(), 'modelo')) {
                    $q->orWhere('modelo', 'like', "%$search%");
                }
                if (Schema::hasColumn($q->getModel()->getTable(), 'nombres')) {
                    $q->orWhere('nombres', 'like', "%$search%")->orWhere('apellidos', 'like', "%$search%");
                }
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
        $base = class_basename($this->modelClass);
        if ($base === 'Trabajador') {
            return ['ID', 'Nombres', 'Apellidos', 'Cédula', 'Cargo', 'Departamento', 'Estatus', 'Creado Por', 'Modificado Por', 'Fecha Reg.', 'Última Modif.'];
        }
        if (in_array($base, ['Procesador', 'Gpu'])) {
            return ['ID', 'Modelo', 'Marca', 'Estatus', 'Creado Por', 'Modificado Por', 'Fecha Reg.', 'Última Modif.'];
        }
        return [
            'ID',
            'Nombre / Identificador',
            'Estatus',
            'Creado Por',
            'Modificado Por',
            'Fecha Registro',
            'Última Modif.',
        ];
    }

    public function map($item): array
    {
        $base = class_basename($this->modelClass);
        
        if ($base === 'Trabajador') {
            return [
                $item->id,
                $item->nombres,
                $item->apellidos,
                $item->cedula ?? 'N/A',
                $item->cargo ?? 'N/A',
                $item->departamento->nombre ?? 'N/A',
                $item->activo ? 'ACTIVO' : 'INACTIVO',
                $item->creator->name ?? 'Sistema',
                $item->updater->name ?? 'N/A',
                $item->created_at->format('d/m/Y H:i'),
                $item->updated_at->format('d/m/Y H:i'),
            ];
        }

        if (in_array($base, ['Procesador', 'Gpu'])) {
            return [
                $item->id,
                $item->modelo,
                $item->marca->nombre ?? 'N/A',
                $item->activo ? 'ACTIVO' : 'INACTIVO',
                $item->creator->name ?? 'Sistema',
                $item->updater->name ?? 'N/A',
                $item->created_at->format('d/m/Y H:i'),
                $item->updated_at->format('d/m/Y H:i'),
            ];
        }

        return [
            $item->id,
            $item->nombre ?? $item->modelo ?? 'N/A',
            $item->activo ? 'ACTIVO' : 'INACTIVO',
            $item->creator->name ?? 'Sistema',
            $item->updater->name ?? 'N/A',
            $item->created_at->format('d/m/Y H:i'),
            $item->updated_at->format('d/m/Y H:i'),
        ];
    }
}
