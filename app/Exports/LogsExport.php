<?php

namespace App\Exports;

use Spatie\Activitylog\Models\Activity;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LogsExport implements FromQuery, WithHeadings, WithMapping, WithCustomStartCell, WithStyles, WithEvents
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Activity::with('causer')->latest();

        if (!empty($this->filters['searchUser'])) {
            $query->whereHas('causer', function($q) {
                $q->where('name', 'like', '%' . $this->filters['searchUser'] . '%')
                  ->orWhere('email', 'like', '%' . $this->filters['searchUser'] . '%');
            });
        }

        if (!empty($this->filters['searchModule'])) {
            $query->where(function($q) {
                $q->where('subject_type', 'like', '%' . $this->filters['searchModule'] . '%')
                  ->orWhere('description', 'like', '%' . $this->filters['searchModule'] . '%');
            });
        }

        if (!empty($this->filters['dateFrom'])) {
            $query->whereDate('created_at', '>=', $this->filters['dateFrom']);
        }

        if (!empty($this->filters['dateTo'])) {
            $query->whereDate('created_at', '<=', $this->filters['dateTo']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Responsable',
            'Email',
            'Acción',
            'Módulo Afectado',
            'ID Registro Afectado',
            'Fecha',
            'Hora',
        ];
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->causer->name ?? 'Sistema',
            $log->causer->email ?? 'Automático',
            ucfirst($log->description),
            class_basename($log->subject_type),
            $log->subject_id ?? 'N/A',
            $log->created_at->format('d/m/Y'),
            $log->created_at->format('h:i:s A'),
        ];
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF343A40'], // Dark Gray
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Título Principal
                $sheet->setCellValue('A1', 'Reporte de Auditoría de Logs');
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF0D6EFD']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ]);

                // Subtítulo con filtros
                $info = "Fecha de Emisión: " . now()->format('d/m/Y h:i A');
                if (!empty($this->filters['dateFrom'])) {
                    $info .= " | Desde: " . $this->filters['dateFrom'];
                }
                if (!empty($this->filters['dateTo'])) {
                    $info .= " | Hasta: " . $this->filters['dateTo'];
                }
                if (!empty($this->filters['searchUser'])) {
                    $info .= " | Usuario: " . $this->filters['searchUser'];
                }
                if (!empty($this->filters['searchModule'])) {
                    $info .= " | Módulo/Acción: " . $this->filters['searchModule'];
                }

                $sheet->setCellValue('A2', $info);
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF6C757D']],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                ]);

                // Auto-size columns
                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
