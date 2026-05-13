<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\Layout;

class AuditoriaUsuarios extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $sortField = 'name';
    public $sortAsc = true;
    public $filtro_estado = 'todos';

    public $selectedUser = null;
    public $dateFrom = '';
    public $dateTo = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
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

    public function verDetalle($id)
    {
        $this->selectedUser = User::with('roles')->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalAuditoriaUsuario');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = User::with('roles');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('username', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filtro_estado === 'activos') {
            $query->where('activo', true);
        } elseif ($this->filtro_estado === 'inactivos') {
            $query->where('activo', false);
        }

        $usuarios = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                          ->paginate(10);

        // Si hay un usuario seleccionado, cargamos su historial de actividad
        $actividades = collect();
        if ($this->selectedUser) {
            $actQuery = Activity::where('causer_id', $this->selectedUser->id)
                                ->where('causer_type', User::class)
                                ->latest();
            
            if ($this->dateFrom) $actQuery->whereDate('created_at', '>=', $this->dateFrom);
            if ($this->dateTo) $actQuery->whereDate('created_at', '<=', $this->dateTo);

            $actividades = $actQuery->take(100)->get();
        }

        return view('livewire.admin.auditoria-usuarios', compact('usuarios', 'actividades'));
    }
}
