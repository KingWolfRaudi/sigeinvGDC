<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Livewire\Attributes\Layout;

class Auditoria extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Filtros
    public $searchUser = '';
    public $searchModule = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $sortField = 'created_at';
    public $sortAsc = false;

    // Detalle
    public $selectedLog = null;

    // Reporte Masivo
    public $showReportModal = false;
    public $reportSelections = [
        'computadores' => ['selected' => false, 'full_inventory' => true, 'filters' => ['estado' => '', 'departamento_id' => '']],
        'dispositivos' => ['selected' => false, 'full_inventory' => true, 'filters' => ['estado' => '', 'departamento_id' => '']],
        'insumos' => ['selected' => false, 'full_inventory' => true, 'filters' => ['estado' => '']],
        'marcas' => ['selected' => false, 'full_inventory' => true, 'filters' => ['estado' => '']],
        'tipos-dispositivo' => ['selected' => false, 'full_inventory' => true, 'filters' => ['estado' => '']],
        'sistemas-operativos' => ['selected' => false, 'full_inventory' => true, 'filters' => ['estado' => '']],
        'departamentos' => ['selected' => false, 'full_inventory' => true, 'filters' => ['estado' => '']],
        'trabajadores' => ['selected' => false, 'full_inventory' => true, 'filters' => ['estado' => '']],
    ];

    public function updated($propertyName)
    {
        $this->resetPage();
    }

    public function abrirReporte()
    {
        $this->showReportModal = true;
        $this->dispatch('abrir-modal', id: 'modalReporteMasivo');
    }

    public function generarReporteMasivo()
    {
        $this->authorize('reportes-masivos-filtros');

        $finalSelections = [];
        foreach ($this->reportSelections as $module => $data) {
            if ($data['selected']) {
                $finalSelections[] = [
                    'module' => $module,
                    'full_inventory' => $data['full_inventory'] ?? false, // <-- Agregado
                    'filters' => $data['filters']
                ];
            }
        }

        if (empty($finalSelections)) {
            $this->dispatch('notificar', icon: 'error', title: 'Selección vacía', text: 'Debe seleccionar al menos un módulo.');
            return;
        }

        // Redirigir a una ruta POST que genere el reporte masivo
        // (En Livewire, para descargas complejas, a veces es mejor usar un form oculto o redirigir con params si son pocos)
        // Usaremos una técnica de despacho para que el frontend haga un submit de un form oculto
        $this->dispatch('descargar-masivo', selections: $finalSelections);
    }

    public function verDetalle($id)
    {
        $this->selectedLog = Activity::findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalleLog');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
            $this->sortField = $field;
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Activity::with('causer')->latest();

        if ($this->searchUser) {
            $query->whereHas('causer', function($q) {
                $q->where('name', 'like', '%' . $this->searchUser . '%')
                  ->orWhere('email', 'like', '%' . $this->searchUser . '%');
            });
        }

        if ($this->searchModule) {
            $query->where('subject_type', 'like', '%' . $this->searchModule . '%')
                  ->orWhere('description', 'like', '%' . $this->searchModule . '%');
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $logs = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                      ->paginate(15);

        return view('livewire.admin.auditoria', [
            'logs' => $logs,
            'departamentos' => \App\Models\Departamento::activos()->get()
        ]);
    }
}
