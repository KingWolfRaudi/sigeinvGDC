<?php

namespace App\Livewire\Asignaciones;

use Livewire\Component;
use App\Models\Departamento;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;

class Departamentos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $departamento_id, $nombre, $departamento_detalle;
    public bool $activo = true;
    public $tituloModal = 'Nuevo Departamento';

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
        // 1. Iniciamos la consulta base
        $query = Departamento::query();

        // 2. LÓGICA DE ESTADOS Y VISIBILIDAD (Data Scoping)
        if (\Illuminate\Support\Facades\Gate::allows('ver-estado-departamentos')) {
            if ($this->filtro_estado === 'activos') {
                $query->where('activo', true);
            } elseif ($this->filtro_estado === 'inactivos') {
                $query->where('activo', false);
            }
        } else {
            // Usuario sin permisos solo ve activos
            $query->where('activo', true);
        }

        // 2.5 Filtros Prediseñados (Cuando el componente se renderiza dentro de un Asociaciones Dashboard)
        if (!empty($this->presetFiltro)) {
            foreach($this->presetFiltro as $col => $val) {
                if ($val !== null) {
                    $query->where($col, $val);
                }
            }
        }

        // 3. Búsqueda y Paginación (Deep Search)
        $query->where(function($q) {
            $search = '%' . $this->search . '%';
            
            $q->where('nombre', 'like', $search)
              ->orWhereHas('trabajadores', fn($t) => $t->where('nombres', 'like', $search)->orWhere('apellidos', 'like', $search)->orWhere('cedula', 'like', $search))
              ->orWhereHas('computadores', fn($c) => $c->where('bien_nacional', 'like', $search)->orWhere('serial', 'like', $search))
              ->orWhereHas('dispositivos', fn($d) => $d->where('codigo', 'like', $search)->orWhere('serial', 'like', $search));
        });

        $departamentos = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                               ->paginate(10);
                       
        return view('livewire.asignaciones.departamentos', compact('departamentos'));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-departamentos'), 403);

        $this->resetCampos();
        $this->tituloModal = 'Nuevo Departamento';
        $this->dispatch('abrir-modal', id: 'modalDepartamento');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->departamento_id ? 'editar-departamentos' : 'crear-departamentos'), 403);

        $this->validate([
            'nombre' => 'required|min:2|unique:departamentos,nombre,' . $this->departamento_id,
        ]);

        Departamento::updateOrCreate(
            ['id' => $this->departamento_id],
            [
                'nombre' => $this->nombre,
                'activo' => $this->activo ? 1 : 0
            ]
        );

        $this->dispatch('cerrar-modal', id: 'modalDepartamento');
        $this->dispatch('mostrar-toast', mensaje: $this->departamento_id ? 'Departamento actualizado.' : 'Departamento creado.', tipo:'success');
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-departamentos'), 403);

        $this->resetValidation();
        $departamento = Departamento::findOrFail($id);
        $this->departamento_id = $departamento->id;
        $this->nombre = $departamento->nombre;
        $this->activo = (bool) $departamento->activo; 
        
        $this->tituloModal = 'Editar Departamento';
        $this->dispatch('abrir-modal', id: 'modalDepartamento');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-departamentos'), 403);

        $departamento = Departamento::findOrFail($id);
        $departamento->activo = !$departamento->activo;
        $departamento->save();
        $this->dispatch('mostrar-toast', mensaje: "Estado cambiado.", tipo:'success');
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-departamentos'), 403);
        
        $this->departamento_detalle = Departamento::findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalleDepartamento');
    }

    public function eliminar($id)
    {
        $departamento = Departamento::findOrFail($id);

        // Protección de integridad
        if ($departamento->trabajadores()->exists()) {
            $this->dispatch('toast', mensaje: 'No se puede eliminar: Hay trabajadores asignados a este departamento.', tipo: 'error');
            return;
        }

        $departamento->delete();
        $this->dispatch('toast', mensaje: 'Departamento eliminado correctamente.', tipo: 'success');
    }

    public function resetCampos()
    {
        $this->reset(['departamento_id', 'nombre', 'departamento_detalle']);
        $this->activo = true;
        $this->resetValidation();
    }
}