<?php

namespace App\Exports;

use App\Models\Incidencia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class IncidenciasExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Reporte de Incidencias';
    }

    public function collection()
    {
        $query = Incidencia::with(['trabajador', 'tecnico', 'problema', 'departamento', 'dependencia', 'modelo', 'activities.causer']);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('descripcion', 'like', "%$search%")
                  ->orWhereHas('trabajador', fn($q2) => $q2->where('nombres', 'like', "%$search%")->orWhere('apellidos', 'like', "%$search%"))
                  ->orWhereHas('problema', fn($q2) => $q2->where('nombre', 'like', "%$search%"));
            });
        }

        if (!empty($this->filters['departamento_id'])) {
            $query->where('departamento_id', $this->filters['departamento_id']);
        }

        if (!empty($this->filters['estado'])) {
            if ($this->filters['estado'] === 'solventado') $query->where('solventado', true);
            if ($this->filters['estado'] === 'cerrado') $query->where('cerrado', true);
            if ($this->filters['estado'] === 'abierto') $query->where('cerrado', false);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Folio',
            'Fecha Reporte',
            'Departamento',
            'Dependencia',
            'Solicitante (Trabajador)',
            'Tipo de Problema',
            'Activo Relacionado',
            'Descripción de Falla',
            'Técnico Asignado',
            'Notas de Resolución',
            '¿Solventado?',
            '¿Cerrado?',
            'Historial de Comentarios / Cambios',
            'Última Actualización',
        ];
    }

    public function map($item): array
    {
        $activo = 'Ninguno';
        if ($item->modelo) {
            $activo = '[' . ($item->modelo->bien_nacional ?? 'S/BN') . '] ' . ($item->modelo->nombre ?? $item->modelo->marca->nombre ?? 'Activo');
        }

        // Formatear historial de actividades como "comentarios"
        $historial = $item->activities->map(function($activity) {
            $fecha = $activity->created_at->format('d/m/Y H:i');
            $usuario = $activity->causer->name ?? 'Sistema';
            $msg = $activity->description;
            
            // Si hay notas en los cambios, incluirlas
            if (isset($activity->properties['attributes']['notas'])) {
                $msg .= " - Nota: " . $activity->properties['attributes']['notas'];
            }
            
            return "[$fecha] $usuario: $msg";
        })->implode("\n");

        return [
            '#' . str_pad($item->id, 5, '0', STR_PAD_LEFT),
            $item->created_at->format('d/m/Y H:i'),
            $item->departamento->nombre ?? 'N/A',
            $item->dependencia->nombre ?? 'N/A',
            ($item->trabajador->nombres ?? '') . ' ' . ($item->trabajador->apellidos ?? ''),
            $item->problema->nombre ?? 'N/A',
            $activo,
            $item->descripcion,
            $item->tecnico->name ?? 'No asignado',
            $item->notas ?? 'Sin notas',
            $item->solventado ? 'SÍ' : 'NO',
            $item->cerrado ? 'SÍ' : 'NO',
            $historial ?: 'Sin registros adicionales',
            $item->updated_at->format('d/m/Y H:i'),
        ];
    }
}
