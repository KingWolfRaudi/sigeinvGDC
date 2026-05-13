<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Incidencia;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;

class AuditoriaTecnicos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $tecnicos = [];
    public $filtro_tecnico = '';
    public $dateFrom = '';
    public $dateTo = '';


    // KPIs
    public $kpis = [
        'asignados' => 0,
        'resueltos' => 0,
        'cerrados' => 0,
        'abiertos' => 0,
        'tasa_resolucion' => 0,
        'tiempo_promedio' => '0h 0m',
    ];

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->tecnicos = User::role('resolutor-incidencia')->orderBy('name')->get();
        $this->calcularKpis();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['filtro_tecnico', 'dateFrom', 'dateTo'])) {
            $this->calcularKpis();
            $this->resetPage();
        }
    }

    public function calcularKpis()
    {
        $queryIncidencias = Incidencia::query();
        $queryActividad = Activity::query();

        // Aplicar filtros de fecha
        if ($this->dateFrom) {
            $queryIncidencias->whereDate('created_at', '>=', $this->dateFrom);
            $queryActividad->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $queryIncidencias->whereDate('created_at', '<=', $this->dateTo);
            $queryActividad->whereDate('created_at', '<=', $this->dateTo);
        }

        // Aplicar filtro de técnico (si está vacío, considera a todos los técnicos)
        if ($this->filtro_tecnico) {
            $queryIncidencias->where('user_id', $this->filtro_tecnico);
            $queryActividad->where('causer_id', $this->filtro_tecnico)->where('causer_type', User::class);
        } else {
            $tecnicoIds = $this->tecnicos->pluck('id')->toArray();
            $queryIncidencias->whereIn('user_id', $tecnicoIds);
            $queryActividad->whereIn('causer_id', $tecnicoIds)->where('causer_type', User::class);
        }

        // Clonar consultas para KPIs
        $qAsignados = clone $queryIncidencias;
        $qResueltos = clone $queryIncidencias;
        $qCerrados = clone $queryIncidencias;
        $qAbiertos = clone $queryIncidencias;

        $this->kpis['asignados'] = $qAsignados->count();
        $this->kpis['resueltos'] = $qResueltos->where('solventado', true)->count();
        $this->kpis['cerrados'] = $qCerrados->where('cerrado', true)->count();
        $this->kpis['abiertos'] = $qAbiertos->where('solventado', false)->where('cerrado', false)->count();

        // Tasa de resolución
        if ($this->kpis['asignados'] > 0) {
            $this->kpis['tasa_resolucion'] = round(($this->kpis['resueltos'] / $this->kpis['asignados']) * 100, 1);
        } else {
            $this->kpis['tasa_resolucion'] = 0;
        }

        // Tiempo Promedio de Resolución
        $resueltosList = (clone $queryIncidencias)->where('solventado', true)->get();
        if ($resueltosList->count() > 0) {
            $totalMinutos = 0;
            foreach ($resueltosList as $inc) {
                // Asumimos que updated_at contiene la fecha de resolución si está solventado
                $totalMinutos += $inc->created_at->diffInMinutes($inc->updated_at);
            }
            $promedioMinutos = $totalMinutos / $resueltosList->count();
            $horas = floor($promedioMinutos / 60);
            $minutos = round($promedioMinutos % 60);
            $this->kpis['tiempo_promedio'] = "{$horas}h {$minutos}m";
        } else {
            $this->kpis['tiempo_promedio'] = '0h 0m';
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Activity::with('causer')->latest();

        // Filtros de fecha
        if ($this->dateFrom) $query->whereDate('created_at', '>=', $this->dateFrom);
        if ($this->dateTo) $query->whereDate('created_at', '<=', $this->dateTo);

        $query->where('subject_type', Incidencia::class);

        // Filtro de técnico
        if ($this->filtro_tecnico) {
            $ticketIds = Incidencia::where('user_id', $this->filtro_tecnico)->pluck('id')->toArray();
            $query->whereIn('subject_id', $ticketIds);
        } else {
            $tecnicoIds = $this->tecnicos->pluck('id')->toArray();
            $ticketIds = Incidencia::whereIn('user_id', $tecnicoIds)->pluck('id')->toArray();
            $query->whereIn('subject_id', $ticketIds);
        }

        $actividades = $query->paginate(15);

        return view('livewire.admin.auditoria-tecnicos', compact('actividades'));
    }
}
