<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Marca;
use Livewire\WithPagination;

class Marcas extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $marca_id, $nombre;
    public bool $activo = true; // Forzamos que sea booleano
    public $tituloModal = 'Nueva Marca';
    
    // Propiedad para cargar los datos del modal de Detalle
    public $marca_detalle;

    public function render()
    {
        $marcas = Marca::orderBy('id', 'desc')->paginate(10);
        return view('livewire.catalogos.marcas', compact('marcas'));
    }

    public function crear()
    {
        $this->resetCampos();
        $this->tituloModal = 'Nueva Marca';
        // Le decimos a JS que abra el modal DESPUÉS de limpiar los campos
        $this->dispatch('abrir-modal', id: 'modalMarca');
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => 'required|min:2|unique:marcas,nombre,' . $this->marca_id,
        ]);

        Marca::updateOrCreate(
            ['id' => $this->marca_id],
            [
                'nombre' => $this->nombre,
                'activo' => $this->activo ? 1 : 0 // Guardamos 1 o 0 en la BD
            ]
        );

        $this->dispatch('cerrar-modal', id: 'modalMarca');
        $this->dispatch('mostrar-toast', mensaje: $this->marca_id ? 'Marca actualizada exitosamente.' : 'Marca creada exitosamente.');
        
        $this->resetCampos();
    }

    public function editar($id)
    {
        $this->resetValidation();
        $marca = Marca::findOrFail($id);
        $this->marca_id = $marca->id;
        $this->nombre = $marca->nombre;
        
        // Convertimos el 1 o 0 de la BD a true/false para el checkbox
        $this->activo = (bool) $marca->activo; 
        
        $this->tituloModal = 'Editar Marca';
        
        // Abrimos el modal SOLO cuando los datos ya están cargados
        $this->dispatch('abrir-modal', id: 'modalMarca');
    }

    public function toggleActivo($id)
    {
        // Buscamos la marca
        $marca = Marca::findOrFail($id);
        
        // Invertimos su estado actual (si era true pasa a false, y viceversa)
        $marca->activo = !$marca->activo;
        $marca->save();

        // Preparamos el mensaje según el nuevo estado
        $estado = $marca->activo ? 'activada' : 'desactivada';
        
        // Mostramos el Toast de éxito
        $this->dispatch('mostrar-toast', mensaje: "Marca {$estado} exitosamente.");
    }

    public function ver($id)
    {
        // Buscamos la marca completa y abrimos el modal de detalle
        $this->marca_detalle = Marca::findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function eliminar($id)
    {
        Marca::findOrFail($id)->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Marca eliminada exitosamente.');
    }

    public function resetCampos()
    {
        $this->reset(['marca_id', 'nombre', 'marca_detalle']);
        $this->activo = true;
        $this->resetValidation();
    }
}