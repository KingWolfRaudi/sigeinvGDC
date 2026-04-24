<?php

namespace App\Livewire\Asignaciones;

use Livewire\Component;
use App\Models\Dependencia;
use App\Models\Departamento;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;

class Dependencias extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $dependencia_id, $nombre, $departamento_id, $dependencia_detalle;
    public bool $activo = true;
    public $tituloModal = 'Nueva Dependencia';

    // Variables para Reutilización y Anidación
    public $presetFiltro = [];
    public $ocultarTitulos = false;

    public function mount($presetFiltro = [], $ocultarTitulos = false)
    {
        $this->presetFiltro = $presetFiltro;
        $this->ocultarTitulos = $ocultarTitulos;
    }

    public $search = '';
    public $sortField = 'nombre';
    public $sortAsc = true;
    public $filtro_estado = 'todos';

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

    public function render()
    {
        $query = Dependencia::query()->with('departamento');

        if (\Illuminate\Support\Facades\Gate::allows('ver-estado-departamentos')) {
            if ($this->filtro_estado === 'activos') {
                $query->where('activo', true);
            } elseif ($this->filtro_estado === 'inactivos') {
                $query->where('activo', false);
            }
        } else {
            $query->where('activo', true);
        }

        if (!empty($this->presetFiltro)) {
            foreach($this->presetFiltro as $col => $val) {
                if ($val !== null) {
                    $query->where($col, $val);
                }
            }
        }

        $query->where(function($q) {
            $search = '%' . $this->search . '%';
            $q->where('nombre', 'like', $search)
              ->orWhereHas('departamento', fn($d) => $d->where('nombre', 'like', $search));
        });

        $dependencias = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                              ->paginate(10);
                              
        $departamentos = Departamento::activos()->orderBy('nombre')->get();
                       
        return view('livewire.asignaciones.dependencias', compact('dependencias', 'departamentos'));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-departamentos'), 403);

        $this->resetCampos();
        $this->tituloModal = 'Nueva Dependencia';
        $this->dispatch('abrir-modal', id: 'modalDependencia');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->dependencia_id ? 'editar-departamentos' : 'crear-departamentos'), 403);

        $this->validate([
            'nombre' => 'required|min:2|unique:dependencias,nombre,' . $this->dependencia_id,
            'departamento_id' => 'required|exists:departamentos,id'
        ]);

        Dependencia::updateOrCreate(
            ['id' => $this->dependencia_id],
            [
                'nombre' => $this->nombre,
                'departamento_id' => $this->departamento_id,
                'activo' => $this->activo ? 1 : 0
            ]
        );

        $this->dispatch('cerrar-modal', id: 'modalDependencia');
        $this->dispatch('mostrar-toast', mensaje: $this->dependencia_id ? 'Dependencia actualizada.' : 'Dependencia creada.', tipo:'success');
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-departamentos'), 403);

        $this->resetValidation();
        $dependencia = Dependencia::findOrFail($id);
        $this->dependencia_id = $dependencia->id;
        $this->nombre = $dependencia->nombre;
        $this->departamento_id = $dependencia->departamento_id;
        $this->activo = (bool) $dependencia->activo; 
        
        $this->tituloModal = 'Editar Dependencia';
        $this->dispatch('abrir-modal', id: 'modalDependencia');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-departamentos'), 403);

        $dependencia = Dependencia::findOrFail($id);
        $dependencia->activo = !$dependencia->activo;
        $dependencia->save();
        $this->dispatch('mostrar-toast', mensaje: "Estado cambiado.", tipo:'success');
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-departamentos'), 403);
        
        $this->dependencia_detalle = Dependencia::with('departamento')->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalleDependencia');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-departamentos'), 403);

        $dependencia = Dependencia::findOrFail($id);

        if ($dependencia->trabajadores()->exists() || $dependencia->computadores()->exists() || $dependencia->dispositivos()->exists() || $dependencia->insumos()->exists() || $dependencia->incidencias()->exists()) {
            $this->dispatch('mostrar-toast', mensaje: 'No se puede eliminar: Hay registros asignados a esta dependencia.', tipo: 'error');
            return;
        }

        $dependencia->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Dependencia eliminada correctamente.', tipo: 'success');
    }

    public function resetCampos()
    {
        $this->reset(['dependencia_id', 'nombre', 'departamento_id', 'dependencia_detalle']);
        $this->activo = true;
        $this->resetValidation();
    }
}
