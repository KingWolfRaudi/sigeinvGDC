<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Cuentas de Usuario';
    }

    public function collection()
    {
        $query = User::with(['roles', 'trabajador']);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
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
            'Nombre Completo',
            'Usuario (Login)',
            'Email',
            'Roles Asignados',
            'Trabajador Asociado',
            'Estado de Cuenta',
            'Fecha Registro',
            'Última Modificación',
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->name,
            $item->username,
            $item->email,
            $item->getRoleNames()->implode(', '),
            $item->trabajador ? ($item->trabajador->nombres . ' ' . $item->trabajador->apellidos) : 'Administrador Manual',
            $item->activo ? 'ACTIVO' : 'INACTIVO',
            $item->created_at->format('d/m/Y H:i'),
            $item->updated_at->format('d/m/Y H:i'),
        ];
    }
}
