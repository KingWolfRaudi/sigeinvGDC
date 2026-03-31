<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Procesador;
use App\Models\Marca;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;

class Procesadores extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Variables del formulario
    public $procesador_id, $marca_id, $modelo, $generacion;
    public $frecuencia_base, $frecuencia_maxima, $nucleos, $hilos;
    public $procesador_detalle;
    public bool $activo = true;
    public $tituloModal = 'Nuevo Procesador';
    // Variables para creación rápida de Marca
    public $nueva_marca = '';
    public $creando_marca = false;

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
        $procesadores = Procesador::with('marca')
            ->where(function ($query) {
                // Agrupamos todas las condiciones de búsqueda
                $query->where('modelo', 'like', '%' . $this->search . '%')
                      ->orWhere('generacion', 'like', '%' . $this->search . '%')
                      ->orWhere('frecuencia_base', 'like', '%' . $this->search . '%')
                      ->orWhere('frecuencia_maxima', 'like', '%' . $this->search . '%')
                      ->orWhere('nucleos', 'like', '%' . $this->search . '%')
                      ->orWhere('hilos', 'like', '%' . $this->search . '%')
                      
                      // Búsqueda en la tabla relacionada (Marca)
                      ->orWhereHas('marca', function($q) {
                          $q->where('nombre', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        // Cargamos solo las marcas activas para el formulario
        $marcas = Marca::where('activo', true)->orderBy('nombre')->get();
                       
        return view('livewire.catalogos.procesadores', compact('procesadores', 'marcas'));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-procesadores'), 403);
        $this->resetCampos();
        $this->tituloModal = 'Nuevo Procesador';
        $this->dispatch('abrir-modal', id: 'modalProcesador');
        $this->creando_marca = false;
        $this->nueva_marca = '';
    }

    public function guardarMarca()
    {
        $this->validate([
            'nueva_marca' => 'required|string|unique:marcas,nombre|min:2'
        ]);

        // Guardamos la marca tal cual la escribe el usuario (sin strtoupper)
        $marca = \App\Models\Marca::create([
            'nombre' => $this->nueva_marca,
            'activo' => true
        ]);

        // Asignamos el ID, cerramos el modo de creación rápida y limpiamos el input
        $this->marca_id = $marca->id;

        $this->dispatch('toast', mensaje: 'Marca creada y seleccionada', tipo: 'success');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->procesador_id ? 'editar-procesadores' : 'crear-procesadores'), 403);

        $this->validate([
            'marca_id' => 'required|exists:marcas,id',
            'modelo' => 'required|string|max:255',
            'generacion' => 'nullable|string|max:255',
            'frecuencia_base' => 'nullable|string|max:50',
            'frecuencia_maxima' => 'nullable|string|max:50',
            'nucleos' => 'nullable|integer|min:1',
            'hilos' => 'nullable|integer|min:1',
        ]);

        Procesador::updateOrCreate(
            ['id' => $this->procesador_id],
            [
                'marca_id' => $this->marca_id,
                'modelo' => $this->modelo,
                'generacion' => $this->generacion,
                'frecuencia_base' => $this->frecuencia_base,
                'frecuencia_maxima' => $this->frecuencia_maxima,
                'nucleos' => $this->nucleos,
                'hilos' => $this->hilos,
                'activo' => $this->activo ? 1 : 0
            ]
        );

        $this->dispatch('cerrar-modal', id: 'modalProcesador');
        $this->dispatch('mostrar-toast', mensaje: $this->procesador_id ? 'Procesador actualizado.' : 'Procesador creado.');
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-procesadores'), 403);
        $this->resetValidation();
        $procesador = Procesador::findOrFail($id);
        
        $this->procesador_id = $procesador->id;
        $this->marca_id = $procesador->marca_id;
        $this->modelo = $procesador->modelo;
        $this->generacion = $procesador->generacion;
        $this->frecuencia_base = $procesador->frecuencia_base;
        $this->frecuencia_maxima = $procesador->frecuencia_maxima;
        $this->nucleos = $procesador->nucleos;
        $this->hilos = $procesador->hilos;
        $this->activo = (bool) $procesador->activo; 
        
        $this->tituloModal = 'Editar Procesador';
        $this->dispatch('abrir-modal', id: 'modalProcesador');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-procesadores'), 403);
        $procesador = Procesador::findOrFail($id);
        $procesador->activo = !$procesador->activo;
        $procesador->save();
        $this->dispatch('mostrar-toast', mensaje: "Estado cambiado.");
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-procesadores'), 403);
        $this->procesador_detalle = Procesador::with('marca')->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-procesadores'), 403);
        // Validar si está en uso por una computadora antes de eliminar
        // (Esto lo agregaremos más adelante cuando hagamos la tabla computadoras)
        Procesador::findOrFail($id)->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Procesador eliminado.');
    }

    public function resetCampos()
    {
        $this->reset([
            'procesador_id', 'marca_id', 'modelo', 'generacion', 
            'frecuencia_base', 'frecuencia_maxima', 'nucleos', 'hilos', 'procesador_detalle'
        ]);
        $this->creando_marca = false;
        $this->nueva_marca = '';
        $this->activo = true;
        $this->resetValidation();
    }
}