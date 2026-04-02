<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\TipoDispositivo;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;

class TiposDispositivo extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $tipo_id, $nombre, $tipo_detalle;
    public bool $activo = true;
    public $tituloModal = 'Nuevo Tipo de Dispositivo';

    // Variables para Búsqueda y Filtros
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
        $query = TipoDispositivo::query();

        // 2. LÓGICA DE ESTADOS Y VISIBILIDAD (Data Scoping)
        if (\Illuminate\Support\Facades\Gate::allows('ver-estado-tipos-dispositivo')) {
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
        $tipos = $query->where('nombre', 'like', '%' . $this->search . '%')
                       ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                       ->paginate(10);
                       
        return view('livewire.catalogos.tipos-dispositivo', compact('tipos'));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-tipos-dispositivo'), 403);

        $this->resetCampos();
        $this->tituloModal = 'Nuevo Tipo de Dispositivo';
        $this->dispatch('abrir-modal', id: 'modalTipo');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->tipo_id ? 'editar-tipos-dispositivo' : 'crear-tipos-dispositivo'), 403);

        $this->validate([
            'nombre' => 'required|min:2|unique:tipo_dispositivos,nombre,' . $this->tipo_id,
        ]);

        TipoDispositivo::updateOrCreate(
            ['id' => $this->tipo_id],
            [
                'nombre' => $this->nombre,
                'activo' => $this->activo ? 1 : 0
            ]
        );

        $this->dispatch('cerrar-modal', id: 'modalTipo');
        $this->dispatch('mostrar-toast', mensaje: $this->tipo_id ? 'Tipo de dispositivo actualizado exitosamente.' : 'Tipo de dispositivo creado exitosamente.', tipo:'success');
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-tipos-dispositivo'), 403);

        $this->resetValidation();
        $tipo = TipoDispositivo::findOrFail($id);
        $this->tipo_id = $tipo->id;
        $this->nombre = $tipo->nombre;
        $this->activo = (bool) $tipo->activo; 
        
        $this->tituloModal = 'Editar Tipo de Dispositivo';
        $this->dispatch('abrir-modal', id: 'modalTipo');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-tipos-dispositivo'), 403);

        $tipo = TipoDispositivo::findOrFail($id);
        $tipo->activo = !$tipo->activo;
        $tipo->save();
        $this->dispatch('mostrar-toast', mensaje: "Estado cambiado.", tipo:'success');
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-tipos-dispositivo'), 403);
        
        $this->tipo_detalle = TipoDispositivo::findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-tipos-dispositivo'), 403);

        TipoDispositivo::findOrFail($id)->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Tipo de dispositivo eliminado.', tipo:'success');
    }

    public function resetCampos()
    {
        $this->reset(['tipo_id', 'nombre', 'tipo_detalle']);
        $this->activo = true;
        $this->resetValidation();
    }
}