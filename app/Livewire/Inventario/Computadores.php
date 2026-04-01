<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Computador;
use App\Models\Marca;
use App\Models\TipoDispositivo;
use App\Models\SistemaOperativo;
use App\Models\Procesador;
use App\Models\Gpu;
use App\Models\Puerto;

class Computadores extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    // Búsqueda y Ordenamiento
    public $search = '';
    public $sortField = 'id';
    public $sortAsc = false;
    public $tituloModal = 'Nuevo Computador';
    public $computador_detalle = null;

    // Campos del Formulario Principal
    public $computador_id, $bien_nacional, $serial, $nombre_equipo;
    public $marca_id, $tipo_dispositivo_id, $sistema_operativo_id, $procesador_id, $gpu_id;
    public $memoria_ram, $tipo_memoria, $almacenamiento, $tipo_almacenamiento, $observaciones;
    public $activo = true;
    
    // Array para los checkboxes de la tabla pivote
    public $puertos_seleccionados = [];

    // --- VARIABLES PARA CREACIÓN RÁPIDA (EN LÍNEA) ---
    public $creando_marca = false;
    public $nueva_marca = '';

    public $creando_tipo = false;
    public $nuevo_tipo = '';

    public $creando_so = false;
    public $nuevo_so = '';

        // --- VARIABLES PARA CREACIÓN RÁPIDA DE PROCESADOR Y GPU ---
    public $proc_marca_id, $proc_modelo, $proc_generacion, $proc_frecuencia_base, $proc_frecuencia_maxima, $proc_nucleos, $proc_hilos;
    public $gr_marca_id, $gr_modelo, $gr_memoria, $gr_tipo_memoria, $gr_frecuencia, $gr_bus;
    public $gr_puertos_seleccionados = [];

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
        }
        $this->sortField = $field;
    }

    public function render()
    {
        // Cargamos todos los catálogos activos para los Selects y Checkboxes
        $marcas = Marca::where('activo', true)->orderBy('nombre')->get();
        $tipos = TipoDispositivo::where('activo', true)->orderBy('nombre')->get();
        $sistemas = SistemaOperativo::where('activo', true)->orderBy('nombre')->get();
        $procesadores = Procesador::where('activo', true)->orderBy('modelo')->get();
        $gpus = Gpu::where('activo', true)->orderBy('modelo')->get();
        $puertos = Puerto::where('activo', true)->orderBy('nombre')->get();

        // Buscador Resiliente (V2.5) con relaciones
        $computadores = Computador::with(['marca', 'tipoDispositivo', 'sistemaOperativo', 'procesador'])
            ->where(function ($query) {
                $query->where('bien_nacional', 'like', '%' . $this->search . '%')
                      ->orWhere('serial', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre_equipo', 'like', '%' . $this->search . '%')
                      ->orWhereHas('marca', function ($q) { $q->where('nombre', 'like', '%' . $this->search . '%'); })
                      ->orWhereHas('tipoDispositivo', function ($q) { $q->where('nombre', 'like', '%' . $this->search . '%'); })
                      ->orWhereHas('sistemaOperativo', function ($q) { $q->where('nombre', 'like', '%' . $this->search . '%'); });
            })
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        return view('livewire.inventario.computadores', compact(
            'computadores', 'marcas', 'tipos', 'sistemas', 'procesadores', 'gpus', 'puertos'
        ));
    }

    public function crear()
    {
        $this->resetCampos();
        $this->tituloModal = 'Nuevo Computador';
    }

    public function ver($id)
    {
        // Cargamos el computador con todas sus relaciones, incluyendo la pivote (puertos)
        $this->computador_detalle = Computador::with(['marca', 'tipoDispositivo', 'sistemaOperativo', 'procesador', 'gpu', 'puertos'])->findOrFail($id);
    }

    public function editar($id)
    {
        $this->resetCampos();
        $this->tituloModal = 'Editar Computador';
        $computador = Computador::findOrFail($id);
        
        $this->computador_id = $computador->id;
        $this->bien_nacional = $computador->bien_nacional;
        $this->serial = $computador->serial;
        $this->nombre_equipo = $computador->nombre_equipo;
        $this->marca_id = $computador->marca_id;
        $this->tipo_dispositivo_id = $computador->tipo_dispositivo_id;
        $this->sistema_operativo_id = $computador->sistema_operativo_id;
        $this->procesador_id = $computador->procesador_id;
        $this->gpu_id = $computador->gpu_id;
        $this->memoria_ram = $computador->memoria_ram;
        $this->tipo_memoria = $computador->tipo_memoria;
        $this->almacenamiento = $computador->almacenamiento;
        $this->tipo_almacenamiento = $computador->tipo_almacenamiento;
        $this->observaciones = $computador->observaciones;
        $this->activo = $computador->activo;

        // Extraemos solo los IDs de los puertos asociados a este computador para llenar los checkboxes
        $this->puertos_seleccionados = $computador->puertos->pluck('id')->toArray();
    }

    public function guardar()
    {
        $this->validate([
            'marca_id' => 'required|exists:marcas,id',
            'tipo_dispositivo_id' => 'required|exists:tipo_dispositivos,id',
            // Corregimos el nombre de la tabla en la validación
            'sistema_operativo_id' => 'required|exists:sistemas_operativos,id',
            'procesador_id' => 'required|exists:procesadores,id',
            'bien_nacional' => 'nullable|unique:computadores,bien_nacional,' . $this->computador_id,
            'serial' => 'nullable|unique:computadores,serial,' . $this->computador_id,
            'memoria_ram' => 'required|numeric|min:1',
            'tipo_memoria' => 'required|string',
            'almacenamiento' => 'required|numeric|min:1',
            'tipo_almacenamiento' => 'required|string',
            'puertos_seleccionados' => 'array'
        ]);

        $computador = Computador::updateOrCreate(
            ['id' => $this->computador_id],
            [
                'bien_nacional' => $this->bien_nacional,
                'serial' => $this->serial,
                'nombre_equipo' => $this->nombre_equipo,
                'marca_id' => $this->marca_id,
                'tipo_dispositivo_id' => $this->tipo_dispositivo_id,
                // Mapeamos tu variable de Livewire al nombre de la columna en la BD
                'sistemas_operativo_id' => $this->sistema_operativo_id, 
                'procesador_id' => $this->procesador_id,
                'gpu_id' => empty($this->gpu_id) ? null : $this->gpu_id,
                'memoria_ram' => $this->memoria_ram,
                'tipo_memoria' => strtoupper($this->tipo_memoria),
                'almacenamiento' => $this->almacenamiento,
                'tipo_almacenamiento' => strtoupper($this->tipo_almacenamiento),
                'observaciones' => $this->observaciones,
                'activo' => $this->activo,
            ]
        );

        $computador->puertos()->sync($this->puertos_seleccionados);

        $this->resetCampos();
        $this->dispatch('cerrar-modal'); 
        $this->dispatch('toast', mensaje: 'Computador guardado exitosamente', tipo: 'success');
    }

    public function toggleActivo($id)
    {
        $computador = Computador::findOrFail($id);
        $computador->activo = !$computador->activo;
        $computador->save();
        $this->dispatch('toast', mensaje: 'Estado actualizado', tipo: 'success');
    }

    public function eliminar($id)
    {
        $computador = Computador::findOrFail($id);
        
        // Eliminación Segura: Aquí verificaremos si el computador está asignado a un trabajador (Próximo módulo)
        // if ($computador->asignaciones()->exists()) {
        //     $this->dispatch('toast', mensaje: 'No se puede eliminar: El equipo está asignado a un trabajador.', tipo: 'error');
        //     return;
        // }

        $computador->delete();
        $this->dispatch('toast', mensaje: 'Computador eliminado (SoftDelete)', tipo: 'success');
    }

    // --- MÉTODOS DE CREACIÓN RÁPIDA ---
    
    public function guardarMarcaRapida() {
        $this->validate(['nueva_marca' => 'required|unique:marcas,nombre|min:2']);
        $obj = Marca::create(['nombre' => $this->nueva_marca, 'activo' => true]);
        $this->marca_id = $obj->id;
        $this->creando_marca = false; $this->nueva_marca = '';
        $this->dispatch('toast', mensaje: 'Marca añadida', tipo: 'success');
    }

    public function guardarTipoRapido() {
        $this->validate(['nuevo_tipo' => 'required|unique:tipo_dispositivos,nombre|min:2']);
        $obj = TipoDispositivo::create(['nombre' => $this->nuevo_tipo, 'activo' => true]);
        $this->tipo_dispositivo_id = $obj->id;
        $this->creando_tipo = false; $this->nuevo_tipo = '';
        $this->dispatch('toast', mensaje: 'Tipo añadido', tipo: 'success');
    }

    public function guardarSORapido() {
        $this->validate(['nuevo_so' => 'required|unique:sistema_operativos,nombre|min:2']);
        $obj = SistemaOperativo::create(['nombre' => $this->nuevo_so, 'activo' => true]);
        $this->sistema_operativo_id = $obj->id;
        $this->creando_so = false; $this->nuevo_so = '';
        $this->dispatch('toast', mensaje: 'Sistema Operativo añadido', tipo: 'success');
    }



    // --- MÉTODOS ---

    public function guardarProcesadorRapido()
    {
        // 1. Relajamos la validación: Solo marca y modelo son obligatorios
        $this->validate([
            'proc_marca_id' => 'required|exists:marcas,id',
            'proc_modelo' => 'required|string',
            'proc_nucleos' => 'nullable|numeric|min:1',
            'proc_hilos' => 'nullable|numeric|min:1',
            'proc_frecuencia_base' => 'nullable|numeric|min:1',
            'proc_frecuencia_maxima' => 'nullable|numeric|min:1',
        ]);

        // 2. Guardamos. Los valores vacíos se irán como NULL a la base de datos
        $proc = Procesador::create([
            'marca_id' => $this->proc_marca_id,
            'modelo' => $this->proc_modelo,
            'generacion' => $this->proc_generacion,
            'nucleos' => $this->proc_nucleos,
            'hilos' => $this->proc_hilos,
            'frecuencia_base' => $this->proc_frecuencia_base,
            'frecuencia_maxima' => $this->proc_frecuencia_maxima,
            'activo' => true
        ]);

        $this->procesador_id = $proc->id;
        $this->reset(['proc_marca_id', 'proc_modelo', 'proc_generacion', 'proc_nucleos', 'proc_hilos', 'proc_frecuencia_base', 'proc_frecuencia_maxima']);
        
        $this->dispatch('cerrar-modal', id: 'modalProcesadorRapido');
        $this->dispatch('abrir-modal', id: 'modalComputador');
        $this->dispatch('toast', mensaje: 'Procesador añadido exitosamente', tipo: 'success');
    }

    public function guardarGpuRapida()
    {
        // 1. Relajamos la validación: Solo marca y modelo son obligatorios
        $this->validate([
            'gr_marca_id' => 'required|exists:marcas,id',
            'gr_modelo' => 'required|string',
            'gr_memoria' => 'nullable|numeric|min:1',
            'gr_tipo_memoria' => 'nullable|string',
            'gr_frecuencia' => 'nullable|numeric|min:1',
            'gr_bus' => 'nullable|numeric|min:1',
            'gr_puertos_seleccionados' => 'array', // Validamos el array de puertos
        ]);

        // 2. Guardamos. Validamos el strtoupper para que no falle si el campo viene vacío (null)
        $gpu = Gpu::create([
            'marca_id' => $this->gr_marca_id,
            'modelo' => $this->gr_modelo,
            'memoria' => $this->gr_memoria,
            'tipo_memoria' => $this->gr_tipo_memoria ? strtoupper($this->gr_tipo_memoria) : null,
            'frecuencia' => $this->gr_frecuencia,
            'bus' => $this->gr_bus,
            'activo' => true
        ]);

        // 3. Sincronizamos la tabla pivote de la GPU
        if (!empty($this->gr_puertos_seleccionados)) {
            $gpu->puertos()->sync($this->gr_puertos_seleccionados);
        }

        $this->gpu_id = $gpu->id;
        
        // Limpiamos las variables incluyendo los puertos
        $this->reset(['gr_marca_id', 'gr_modelo', 'gr_memoria', 'gr_tipo_memoria', 'gr_frecuencia', 'gr_bus', 'gr_puertos_seleccionados']);
        
        $this->dispatch('cerrar-modal', id: 'modalGpuRapida');
        $this->dispatch('abrir-modal', id: 'modalComputador');
        $this->dispatch('toast', mensaje: 'Tarjeta de Video añadida exitosamente', tipo: 'success');
    }

    public function resetCampos()
    {
        $this->reset([
            'computador_id', 'bien_nacional', 'serial', 'nombre_equipo', 
            'marca_id', 'tipo_dispositivo_id', 'sistema_operativo_id', 'procesador_id', 'gpu_id', 
            'memoria_ram', 'tipo_memoria', 'almacenamiento', 'tipo_almacenamiento', 'observaciones',
            'computador_detalle', 'puertos_seleccionados',
            'creando_marca', 'nueva_marca', 'creando_tipo', 'nuevo_tipo', 'creando_so', 'nuevo_so'
        ]);
        $this->activo = true;
        $this->resetValidation();
    }
}