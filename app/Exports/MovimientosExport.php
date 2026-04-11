<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class MovimientosExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $modelClass;
    protected $filters;
    protected $title;

    public function __construct($modelClass, $filters = [], $title = 'Movimientos')
    {
        $this->modelClass = $modelClass;
        $this->filters = $filters;
        $this->title = $title;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function collection()
    {
        $query = $this->modelClass::with(['solicitante', 'aprobador']);

        // Cargar relación del equipo base según el modelo
        $base = class_basename($this->modelClass);
        if ($base === 'MovimientoComputador') $query->with('computador');
        if ($base === 'MovimientoDispositivo') $query->with('dispositivo');
        if ($base === 'MovimientoInsumo') $query->with('insumo');

        if (!empty($this->filters['tipo_operacion'])) {
            if ($this->filters['tipo_operacion'] !== 'todos') {
                $query->where('tipo_operacion', $this->filters['tipo_operacion']);
            }
        }

        if (!empty($this->filters['estado_workflow'])) {
            if ($this->filters['estado_workflow'] !== 'todos') {
                $query->where('estado_workflow', $this->filters['estado_workflow']);
            }
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Referencia / Equipo',
            'Tipo Operación',
            'Cantidad (Insumos)',
            'Estado Workflow',
            'Solicitante',
            'Aprobador / Revisor',
            'Justificación',
            'Detalle de Cambios',
            'Motivo Rechazo (si aplica)',
            'Fecha Solicitud',
            'Fecha Ejecución',
        ];
    }

    public function map($item): array
    {
        $base = class_basename($this->modelClass);
        $referencia = 'Desconocido';
        if ($base === 'MovimientoComputador') $referencia = 'PC BN: ' . ($item->computador->bien_nacional ?? 'N/A');
        if ($base === 'MovimientoDispositivo') $referencia = 'DISP BN: ' . ($item->dispositivo->bien_nacional ?? 'N/A');
        if ($base === 'MovimientoInsumo') $referencia = 'INS BN: ' . ($item->insumo->bien_nacional ?? 'N/A');

        // Formatear cambios
        $detalleCambios = '';
        if ($item->payload_nuevo) {
            foreach ($item->payload_nuevo as $key => $value) {
                $old = $item->payload_anterior[$key] ?? 'N/A';
                if ($old != $value) {
                    $detalleCambios .= strtoupper($key) . ": $old -> $value\n";
                }
            }
        }

        return [
            $item->id,
            $referencia,
            $item->tipo_operacion === 'toggle_activo' ? 'Cambio de Estado' : strtoupper(str_replace('_', ' ', $item->tipo_operacion)),
            $item->cantidad_movida ?? '—',
            strtoupper($item->estado_workflow),
            $item->solicitante->name ?? 'N/A',
            $item->aprobador->name ?? 'Pendiente / N/A',
            $item->justificacion,
            $detalleCambios ?: 'Sin cambios detectados o creación',
            $item->motivo_rechazo ?? 'N/A',
            $item->created_at->format('d/m/Y H:i'),
            $item->aprobado_at ? $item->aprobado_at->format('d/m/Y H:i') : ($item->estado_workflow === 'ejecutado_directo' ? $item->created_at->format('d/m/Y H:i') : 'N/A'),
        ];
    }
}
