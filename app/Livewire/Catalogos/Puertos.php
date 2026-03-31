<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Puerto;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;

class Puertos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $puerto_id, $nombre, $puerto_detalle;
    public bool $activo = true;
    public $tituloModal = 'Nuevo Puerto';

    public $search = '';
    public $sortField = 'id';
    public $sortAsc = false;

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
        $puertos = Puerto::where('nombre', 'like', '%' . $this->search . '%')
                       ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                       ->paginate(10);
                       
        return view('livewire.catalogos.puertos', compact('puertos'));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-puertos'), 403);

        $this->resetCampos();
        $this->tituloModal = 'Nuevo Puerto';
        $this->dispatch('abrir-modal', id: 'modalPuerto');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->puerto_id ? 'editar-puertos' : 'crear-puertos'), 403);

        $this->validate([
            'nombre' => 'required|min:2|unique:puertos,nombre,' . $this->puerto_id,
        ]);

        Puerto::updateOrCreate(
            ['id' => $this->puerto_id],
            [
                'nombre' => $this->nombre,
                'activo' => $this->activo ? 1 : 0
            ]
        );

        $this->dispatch('cerrar-modal', id: 'modalPuerto');
        $this->dispatch('mostrar-toast', mensaje: $this->puerto_id ? 'Puerto actualizado.' : 'Puerto creado.');
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-puertos'), 403);

        $this->resetValidation();
        $puerto = Puerto::findOrFail($id);
        $this->puerto_id = $puerto->id;
        $this->nombre = $puerto->nombre;
        $this->activo = (bool) $puerto->activo; 
        
        $this->tituloModal = 'Editar Puerto';
        $this->dispatch('abrir-modal', id: 'modalPuerto');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-puertos'), 403);

        $puerto = Puerto::findOrFail($id);
        $puerto->activo = !$puerto->activo;
        $puerto->save();
        $this->dispatch('mostrar-toast', mensaje: "Estado cambiado.");
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-puertos'), 403);
        
        $this->puerto_detalle = Puerto::findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function eliminar($id)
    {
        $puerto = Puerto::findOrFail($id);

        // Protección de integridad en tabla pivote
        if ($puerto->gpus()->exists()) {
            $this->dispatch('toast', mensaje: 'No se puede eliminar: Existen Tarjetas de Video (GPUs) usando este puerto.', tipo: 'error');
            return;
        }

        $puerto->delete();
        $this->dispatch('toast', mensaje: 'Puerto de conexión eliminado.', tipo: 'success');
    }

    public function resetCampos()
    {
        $this->reset(['puerto_id', 'nombre', 'puerto_detalle']);
        $this->activo = true;
        $this->resetValidation();
    }
}