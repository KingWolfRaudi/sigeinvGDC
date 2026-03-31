<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Marca;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
class Marcas extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $marca_id, $nombre, $marca_detalle;
    public bool $activo = true;
    public $tituloModal = 'Nueva Marca';

    // 1. Variables para Búsqueda y Filtros
    public $search = '';
    public $sortField = 'id';
    public $sortAsc = false; // false = descendente (más nuevos primero)

    // 2. Resetear la paginación cuando el usuario escribe en el buscador
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // 3. Método para ordenar al hacer clic en las columnas
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc; // Invierte el orden si es la misma columna
        } else {
            $this->sortAsc = true;
            $this->sortField = $field;
        }
    }

    public function render()
    {
        // 4. Aplicamos la búsqueda y el orden dinámico
        $marcas = Marca::where('nombre', 'like', '%' . $this->search . '%')
                       ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                       ->paginate(10);
                       
        return view('livewire.catalogos.marcas', compact('marcas'));
    }

    public function crear()
    {
        // PROTECCIÓN: Si no tiene permiso, lanza error 403 (Prohibido)
        abort_if(Gate::denies('crear-marcas'), 403, 'No tienes permiso para esto.');

        $this->resetCampos();
        $this->tituloModal = 'Nueva Marca';
        $this->dispatch('abrir-modal', id: 'modalMarca');
    }

    public function guardar()
    {
        // El guardado comparte permiso con crear/editar
        $this->validate([
            'nombre' => 'required|min:2|unique:marcas,nombre,' . $this->marca_id,
        ]);

        Marca::updateOrCreate(
            ['id' => $this->marca_id],
            [
                'nombre' => $this->nombre,
                'activo' => $this->activo ? 1 : 0
            ]
        );

        $this->dispatch('cerrar-modal', id: 'modalMarca');
        $this->dispatch('mostrar-toast', mensaje: $this->marca_id ? 'Marca actualizada.' : 'Marca creada.');
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-marcas'), 403);

        $this->resetValidation();
        $marca = Marca::findOrFail($id);
        $this->marca_id = $marca->id;
        $this->nombre = $marca->nombre;
        $this->activo = (bool) $marca->activo; 
        
        $this->tituloModal = 'Editar Marca';
        $this->dispatch('abrir-modal', id: 'modalMarca');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-marcas'), 403, 'No tienes permiso para cambiar el estatus.');

        $marca = Marca::findOrFail($id);
        $marca->activo = !$marca->activo;
        $marca->save();
        $this->dispatch('mostrar-toast', mensaje: "Estado cambiado.");
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-marcas'), 403);
        $this->marca_detalle = Marca::findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function eliminar($id)
    {
        $marca = Marca::findOrFail($id);

        // 1. Verificamos si tiene procesadores asociados
        if ($marca->procesadores()->exists()) {
            $this->dispatch('toast', mensaje: 'No se puede eliminar: Tiene procesadores asociados.', tipo: 'error');
            return; // Cortamos la ejecución aquí
        }

        // 2. Verificamos si tiene GPUs asociadas
        if ($marca->gpus()->exists()) {
            $this->dispatch('toast', mensaje: 'No se puede eliminar: Tiene tarjetas de video (GPUs) asociadas.', tipo: 'error');
            return;
        }

        // 3. Si pasa las validaciones, procedemos con el SoftDelete
        $marca->delete();
        $this->dispatch('toast', mensaje: 'Marca eliminada correctamente.', tipo: 'success');
    }

    public function resetCampos()
    {
        $this->reset(['marca_id', 'nombre', 'marca_detalle']);
        $this->activo = true;
        $this->resetValidation();
    }
}