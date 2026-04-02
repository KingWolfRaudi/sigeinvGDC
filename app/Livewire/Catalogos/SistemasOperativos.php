<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\SistemaOperativo;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;

class SistemasOperativos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $sistema_id, $nombre, $sistema_detalle;
    public bool $activo = true;
    public $tituloModal = 'Nuevo Sistema Operativo';

    public $search = '';
    public $sortField = 'id';
    public $sortAsc = false;
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
        $query = SistemaOperativo::query();

        // 2. LÓGICA DE ESTADOS Y VISIBILIDAD (Data Scoping)
        if (\Illuminate\Support\Facades\Gate::allows('ver-estado-sistemas-operativos')) {
            if ($this->filtro_estado === 'activos') {
                $query->where('activo', true);
            } elseif ($this->filtro_estado === 'inactivos') {
                $query->where('activo', false);
            }
        } else {
            // Usuario sin permisos solo ve activos
            $query->where('activo', true);
        }

        // 3. Búsqueda y Paginación
        $sistemas = $query->where('nombre', 'like', '%' . $this->search . '%')
                          ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                          ->paginate(10);
                       
        return view('livewire.catalogos.sistemas-operativos', compact('sistemas'));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-sistemas-operativos'), 403);

        $this->resetCampos();
        $this->tituloModal = 'Nuevo Sistema Operativo';
        $this->dispatch('abrir-modal', id: 'modalSistema');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->sistema_id ? 'editar-sistemas-operativos' : 'crear-sistemas-operativos'), 403);

        $this->validate([
            'nombre' => 'required|min:2|unique:sistemas_operativos,nombre,' . $this->sistema_id,
        ]);

        SistemaOperativo::updateOrCreate(
            ['id' => $this->sistema_id],
            [
                'nombre' => $this->nombre,
                'activo' => $this->activo ? 1 : 0
            ]
        );

        $this->dispatch('cerrar-modal', id: 'modalSistema');
        $this->dispatch('mostrar-toast', mensaje: $this->sistema_id ? 'Sistema Operativo actualizado.' : 'Sistema Operativo creado.', tipo:'success');
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-sistemas-operativos'), 403);

        $this->resetValidation();
        $sistema = SistemaOperativo::findOrFail($id);
        $this->sistema_id = $sistema->id;
        $this->nombre = $sistema->nombre;
        $this->activo = (bool) $sistema->activo; 
        
        $this->tituloModal = 'Editar Sistema Operativo';
        $this->dispatch('abrir-modal', id: 'modalSistema');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-sistemas-operativos'), 403);

        $sistema = SistemaOperativo::findOrFail($id);
        $sistema->activo = !$sistema->activo;
        $sistema->save();
        $this->dispatch('mostrar-toast', mensaje: "Estado cambiado.", tipo:'success');
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-sistemas-operativos'), 403);
        
        $this->sistema_detalle = SistemaOperativo::findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-sistemas-operativos'), 403);

        SistemaOperativo::findOrFail($id)->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Sistema Operativo eliminado.', tipo:'success');
    }

    public function resetCampos()
    {
        $this->reset(['sistema_id', 'nombre', 'sistema_detalle']);
        $this->activo = true;
        $this->resetValidation();
    }
}