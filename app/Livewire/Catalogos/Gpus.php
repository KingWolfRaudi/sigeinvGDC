<?php

namespace App\Livewire\Catalogos;

use Livewire\Component;
use App\Models\Gpu;
use App\Models\Marca;
use App\Models\Puerto;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;

class Gpus extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $gpu_id, $marca_id, $modelo, $memoria, $tipo_memoria, $bus, $frecuencia;
    public $puertos_seleccionados = []; // Arreglo para los checkboxes
    public $gpu_detalle;
    public bool $activo = true;
    public $tituloModal = 'Nueva GPU';
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
        // Agregamos 'puertos' al with() para evitar problemas N+1
        $gpus = Gpu::with(['marca', 'puertos'])
            ->where(function ($query) {
                $query->where('modelo', 'like', '%' . $this->search . '%')
                      ->orWhere('memoria', 'like', '%' . $this->search . '%')
                      ->orWhere('tipo_memoria', 'like', '%' . $this->search . '%')
                      ->orWhere('bus', 'like', '%' . $this->search . '%')
                      ->orWhere('frecuencia', 'like', '%' . $this->search . '%')
                      
                      // Búsqueda en Marca
                      ->orWhereHas('marca', function($q) {
                          $q->where('nombre', 'like', '%' . $this->search . '%');
                      })
                      // NUEVO: Búsqueda profunda en Puertos (¡Adiós problemas de minúsculas/mayúsculas!)
                      ->orWhereHas('puertos', function($q) {
                          $q->where('nombre', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        $marcas = Marca::where('activo', true)->orderBy('nombre')->get();
        $lista_puertos = Puerto::where('activo', true)->orderBy('nombre')->get();
                       
        return view('livewire.catalogos.gpus', compact('gpus', 'marcas', 'lista_puertos'));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-gpus'), 403);
        $this->resetCampos();
        $this->tituloModal = 'Nueva GPU';
        $this->dispatch('abrir-modal', id: 'modalGpu');
    }

    public function guardarMarca()
    {
        $this->validate([
            'nueva_marca' => 'required|string|unique:marcas,nombre|min:2'
        ]);

        // Guardamos la marca sin forzar mayúsculas (según nuestras convenciones V2.1)
        $marca = \App\Models\Marca::create([
            'nombre' => $this->nueva_marca,
            'activo' => true
        ]);

        $this->marca_id = $marca->id;
        $this->creando_marca = false;
        $this->nueva_marca = '';

        $this->dispatch('toast', mensaje: 'Marca creada y seleccionada', tipo: 'success');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->gpu_id ? 'editar-gpus' : 'crear-gpus'), 403);

        $this->validate([
            'marca_id' => 'required|exists:marcas,id',
            'modelo' => 'required|string|max:255',
            'memoria' => 'nullable|integer|min:1', 
            'tipo_memoria' => 'nullable|string|max:100',
            'bus' => 'nullable|string|in:32-bit,64-bit,128-bit,192-bit,256-bit,320-bit,384-bit,512-bit',
            'frecuencia' => 'nullable|integer|min:1', 
            'puertos_seleccionados' => 'nullable|array',
        ]);

        $memoria_formateada = $this->memoria ? $this->memoria . 'GB' : null;
        $frecuencia_formateada = $this->frecuencia ? $this->frecuencia . 'MHz' : null;
        $tipo_memoria_formateado = $this->tipo_memoria ? strtoupper($this->tipo_memoria) : null;

        // 1. Guardamos la GPU (Ya no le pasamos 'puertos' aquí)
        $gpu = Gpu::updateOrCreate(
            ['id' => $this->gpu_id],
            [
                'marca_id' => $this->marca_id,
                'modelo' => $this->modelo,
                'memoria' => $memoria_formateada,
                'tipo_memoria' => $tipo_memoria_formateado,
                'bus' => $this->bus,
                'frecuencia' => $frecuencia_formateada,
                'activo' => $this->activo ? 1 : 0
            ]
        );

        // 2. Magia de Laravel: Sincroniza los IDs de los puertos en la tabla pivote
        $gpu->puertos()->sync($this->puertos_seleccionados);

        $this->dispatch('cerrar-modal', id: 'modalGpu');
        $this->dispatch('mostrar-toast', mensaje: $this->gpu_id ? 'GPU actualizada.' : 'GPU creada.');
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-gpus'), 403);
        $this->resetValidation();
        $gpu = Gpu::with('puertos')->findOrFail($id); // Cargamos con sus puertos
        
        $this->gpu_id = $gpu->id;
        $this->marca_id = $gpu->marca_id;
        $this->modelo = $gpu->modelo;
        $this->memoria = $gpu->memoria ? str_replace('GB', '', $gpu->memoria) : null;
        $this->frecuencia = $gpu->frecuencia ? str_replace('MHz', '', $gpu->frecuencia) : null;
        $this->tipo_memoria = $gpu->tipo_memoria;
        $this->bus = $gpu->bus;
        
        // Pluck extrae solo los IDs de la relación y los convierte en un arreglo [1, 3, 5]
        $this->puertos_seleccionados = $gpu->puertos->pluck('id')->toArray(); 
        
        $this->activo = (bool) $gpu->activo; 
        
        $this->tituloModal = 'Editar GPU';
        $this->dispatch('abrir-modal', id: 'modalGpu');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-gpus'), 403);
        $gpu = Gpu::findOrFail($id);
        $gpu->activo = !$gpu->activo;
        $gpu->save();
        $this->dispatch('mostrar-toast', mensaje: "Estado cambiado.");
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-gpus'), 403);
        $this->gpu_detalle = Gpu::with('marca')->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-gpus'), 403);
        Gpu::findOrFail($id)->delete();
        $this->dispatch('mostrar-toast', mensaje: 'GPU eliminada.');
    }

    public function resetCampos()
    {
        $this->reset([
            'gpu_id', 'marca_id', 'modelo', 'memoria', 'tipo_memoria', 
            'bus', 'frecuencia', 'gpu_detalle'
        ]);
        $this->puertos_seleccionados = [];
        // Variables para creación rápida de Marca
        
        $this->activo = true;
        $this->resetValidation();
    }
}