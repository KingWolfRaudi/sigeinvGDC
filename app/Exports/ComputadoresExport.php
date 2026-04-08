<?php

namespace App\Exports;

use App\Models\Computador;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ComputadoresExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function title(): string
    {
        return 'Computadores';
    }
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Computador::with([
            'marca', 
            'departamento', 
            'trabajador', 
            'procesador.marca', 
            'gpu.marca', 
            'sistemaOperativo', 
            'discos', 
            'rams', 
            'puertos'
        ]);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('bien_nacional', 'like', "%$search%")
                  ->orWhere('serial', 'like', "%$search%")
                  ->orWhere('nombre_equipo', 'like', "%$search%")
                  ->orWhere('tipo_computador', 'like', "%$search%")
                  ->orWhere('ip', 'like', "%$search%")
                  ->orWhereHas('marca', fn($sub) => $sub->where('nombre', 'like', "%$search%"))
                  ->orWhereHas('trabajador', fn($sub) => $sub->where('nombres', 'like', "%$search%")->orWhere('apellidos', 'like', "%$search%"));
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
            'Equipo',
            'Bien Nacional',
            'Serial',
            'Marca',
            'Tipo Equipo',
            'Procesador',
            'Memoria RAM',
            'Almacenamiento',
            'Tarjeta de Video (GPU)',
            'Sist. Operativo',
            'Red (IP)',
            'MAC Address',
            'Conexión',
            'Estado Físico',
            'Departamento',
            'Responsable',
            'DVD',
            'Fuente',
            'Puertos',
            'Observaciones',
            'Creado Por',
            'Última Modif. Por',
            'Fecha Registro',
            'Última Modificación'
        ];
    }

    public function map($pc): array
    {
        // Calcular RAM total
        $totalRam = $pc->rams->sum(function($r) {
            return (int) str_replace('GB', '', $r->capacidad);
        });

        // Calcular Almacenamiento total y detallado
        $storageInfo = $pc->discos->map(function($d) {
            return $d->capacidad . ' (' . $d->tipo . ')';
        })->implode(' + ');

        // CPU y GPU
        $cpu = ($pc->procesador->marca->nombre ?? '') . ' ' . ($pc->procesador->modelo ?? 'N/A');
        $gpu = ($pc->gpu->marca->nombre ?? '') . ' ' . ($pc->gpu->modelo ?? 'Integrada/NA');

        // Puertos
        $puertos = $pc->puertos->pluck('nombre')->implode(', ');

        return [
            $pc->id,
            $pc->nombre_equipo,
            $pc->bien_nacional ?? 'S/P',
            $pc->serial ?? 'S/S',
            $pc->marca->nombre ?? 'Generico',
            $pc->tipo_computador ?? 'N/A',
            compact_string($cpu),
            $totalRam > 0 ? $totalRam . ' GB (' . $pc->tipo_ram . ')' : 'N/A',
            $storageInfo ?: 'N/A',
            compact_string($gpu),
            $pc->sistemaOperativo->nombre ?? 'N/A',
            $pc->ip ?? 'Dinamica/NA',
            $pc->mac ?? 'N/A',
            $pc->tipo_conexion,
            strtoupper($pc->estado_fisico),
            $pc->departamento->nombre ?? 'STOCK / ALMACEN',
            $pc->trabajador ? ($pc->trabajador->nombres . ' ' . $pc->trabajador->apellidos) : 'Sin Asignar',
            $pc->unidad_dvd ? 'SI' : 'NO',
            $pc->fuente_poder ? 'SI' : 'NO',
            $puertos ?: 'Estandard',
            $pc->observaciones,
            $pc->creator->name ?? 'Sistema',
            $pc->updater->name ?? 'N/A',
            $pc->created_at->format('d/m/Y H:i'),
            $pc->updated_at->format('d/m/Y H:i'),
        ];
    }
}

/** Helper para limpiar strings de CPU/GPU si vienen vacios */
function compact_string($str) {
    $trimmed = trim($str);
    return empty($trimmed) ? 'N/A' : $trimmed;
}
