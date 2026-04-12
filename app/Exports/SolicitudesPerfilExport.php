<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class SolicitudesPerfilExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function title(): string
    {
        return 'Solicitudes de Perfil';
    }

    public function collection()
    {
        // El query ya viene con filtros aplicados desde el componente
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Usuario',
            'Username',
            'Tipo de Cambio',
            'Valor Nuevo',
            'Estado',
            'Motivo Rechazo',
            'Fecha Solicitud',
            'Atendido Por',
            'Fecha Revisión'
        ];
    }

    public function map($sol): array
    {
        return [
            $sol->id,
            $sol->user->name ?? 'N/A',
            $sol->user->username ?? 'N/A',
            ucfirst($sol->tipo),
            $sol->tipo === 'password' ? '(Nueva Contraseña)' : $sol->valor_nuevo,
            ucfirst($sol->estado),
            $sol->motivo_rechazo ?? 'N/A',
            $sol->created_at->format('d/m/Y H:i A'),
            $sol->revisor->name ?? 'Pendiente',
            $sol->estado !== 'pendiente' ? $sol->updated_at->format('d/m/Y H:i A') : 'N/A',
        ];
    }
}
