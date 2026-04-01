<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use App\Models\Computador;
use App\Models\ComputadorDisco;
use App\Models\ComputadorRam;
use App\Models\Marca;
use App\Models\TipoDispositivo;
use App\Models\SistemaOperativo;
use App\Models\Procesador;
use App\Models\Gpu;
use App\Models\Trabajador;
use App\Models\Puerto;
use App\Models\Departamento;

class Computadores extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Campos principales
    public $computador_id, $bien_nacional, $serial, $marca_id, $tipo_dispositivo_id, $sistema_operativo_id;
    public $procesador_id, $gpu_id, $departamento_id, $trabajador_id, $tipo_ram, $mac, $ip, $tipo_conexion;
    public $estado_fisico = 'operativo';
    public $observaciones;
    public bool $activo = true;

    // Relaciones Multiples (Arrays Dinámicos)
    public $discos = [];
    public $rams = [];
    public $puertos_seleccionados = [];

    // Creación Rápida On The Fly (Select vs Input)
    public $creando_marca = false, $nueva_marca;
    public $creando_tipo = false, $nuevo_tipo;
    public $creando_so = false, $nuevo_so;
    public $creando_procesador = false, $nuevo_procesador_modelo, $nuevo_procesador_marca_id;
    public $creando_gpu = false, $nueva_gpu_modelo, $nueva_gpu_marca_id;
    // Variables para el Modal Completo de Trabajador
    public $nuevo_trab_nombres, $nuevo_trab_apellidos, $nuevo_trab_cedula, $nuevo_trab_departamento_id; 
    // (Agrega aquí otras variables si tu tabla trabajadores pide correo, cédula, etc.)

    public $computador_detalle;
    public $tituloModal = 'Nuevo Computador';
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

    public function mount()
    {
        // Inicializamos con un disco y una ram por defecto
        $this->addDisco();
        $this->addRam();
    }

    // --- MÉTODOS PARA FORMULARIOS DINÁMICOS ---
    public function addDisco()
    {
        $this->discos[] = ['capacidad' => '', 'tipo' => ''];
    }

    public function removeDisco($index)
    {
        unset($this->discos[$index]);
        $this->discos = array_values($this->discos); // Reindexar
    }

    public function addRam()
    {
        if(count($this->rams) < 6) {
            $this->rams[] = ['capacidad' => '', 'slot' => count($this->rams) + 1];
        } else {
            $this->dispatch('mostrar-toast', mensaje: 'Máximo 6 slots de RAM permitidos.', tipo: 'warning');
        }
    }

    public function removeRam($index)
    {
        unset($this->rams[$index]);
        $this->rams = array_values($this->rams);
        // Reasignar números de slot lógicos
        foreach($this->rams as $i => $ram) {
            $this->rams[$i]['slot'] = $i + 1;
        }
    }
    // ------------------------------------------
    // Este método se ejecuta AUTOMÁTICAMENTE cuando $departamento_id cambia en la vista
    public function updatedDepartamentoId($value)
    {
        $this->trabajador_id = null; // Reseteamos al trabajador para forzar la actualización
    }
    
    public function render()
    {
        $computadores = Computador::with(['marca', 'tipoDispositivo', 'trabajador', 'discos', 'rams'])
            ->where(function ($query) {
                $query->where('bien_nacional', 'like', '%' . $this->search . '%')
                      ->orWhere('serial', 'like', '%' . $this->search . '%')
                      ->orWhere('ip', 'like', '%' . $this->search . '%')
                      
                      // Búsqueda en relación Marca
                      ->orWhereHas('marca', function($q) {
                          $q->where('nombre', 'like', '%' . $this->search . '%');
                      })
                      
                      // Búsqueda en relación Trabajador
                      ->orWhereHas('trabajador', function($q) {
                          // OJO AQUÍ: Si en tu tabla trabajadores la columna se llama "nombres" en plural, 
                          // debes cambiar 'nombre' por 'nombres' en la línea de abajo.
                          $q->where('nombres', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        // Catálogos para los selects
        $marcas = Marca::where('activo', true)->orderBy('nombre')->get();
        $tipos = TipoDispositivo::where('activo', true)->orderBy('nombre')->get();
        $sistemas = SistemaOperativo::where('activo', true)->orderBy('nombre')->get();
        $procesadores = Procesador::with('marca')->where('activo', true)->orderBy('modelo')->get();
        $gpus = Gpu::with('marca')->where('activo', true)->orderBy('modelo')->get();
        $trabajadores = Trabajador::where('activo', true)->orderBy('nombres')->get();
        $puertos = Puerto::where('activo', true)->orderBy('nombre')->get();
        $departamentos = Departamento::where('activo', true)->orderBy('nombre')->get();

        // Filtramos trabajadores por departamento si hay uno seleccionado
        $trabajadores = Trabajador::where('activo', true)
            ->when($this->departamento_id, function($query) {
                return $query->where('departamento_id', $this->departamento_id);
            })
            ->orderBy('nombres')
            ->get();
            
        return view('livewire.inventario.computadores', compact(
            'computadores', 'marcas', 'tipos', 'sistemas', 'procesadores', 'gpus', 'trabajadores', 'puertos', 'departamentos'
        ));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-computadores'), 403);
        $this->resetCampos();
        $this->tituloModal = 'Nuevo Computador';
        $this->dispatch('abrir-modal', id: 'modalComputador');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->computador_id ? 'editar-computadores' : 'crear-computadores'), 403);

        // Validación base
        $this->validate([
            'bien_nacional' => 'nullable|string|unique:computadores,bien_nacional,' . $this->computador_id,
            'serial' => 'nullable|string|unique:computadores,serial,' . $this->computador_id,
            'ip' => 'nullable|ipv4',
            'mac' => 'nullable|string|unique:computadores,mac,' . $this->computador_id,
            'estado_fisico' => 'required|string',
            'tipo_ram' => 'required|string',
        ]);

        // Procesar Creación Rápida "On The Fly"
        if ($this->creando_marca && !empty($this->nueva_marca)) {
            $marca = Marca::firstOrCreate(['nombre' => $this->nueva_marca], ['activo' => true]);
            $this->marca_id = $marca->id;
        }
        if ($this->creando_tipo && !empty($this->nuevo_tipo)) {
            $tipo = TipoDispositivo::firstOrCreate(['nombre' => $this->nuevo_tipo], ['activo' => true]);
            $this->tipo_dispositivo_id = $tipo->id;
        }
        if ($this->creando_so && !empty($this->nuevo_so)) {
            $so = SistemaOperativo::firstOrCreate(['nombre' => $this->nuevo_so], ['activo' => true]);
            $this->sistema_operativo_id = $so->id;
        }
        if ($this->creando_procesador && !empty($this->nuevo_procesador_modelo) && !empty($this->nuevo_procesador_marca_id)) {
            $proc = Procesador::firstOrCreate([
                'modelo' => $this->nuevo_procesador_modelo,
                'marca_id' => $this->nuevo_procesador_marca_id
            ], ['activo' => true]);
            $this->procesador_id = $proc->id;
        }

        if ($this->creando_gpu && !empty($this->nueva_gpu_modelo) && !empty($this->nueva_gpu_marca_id)) {
            $gpu = Gpu::firstOrCreate([
                'modelo' => $this->nueva_gpu_modelo,
                'marca_id' => $this->nueva_gpu_marca_id
            ], ['activo' => true]);
            $this->gpu_id = $gpu->id;
        }


        // 1. Guardar el Computador
        $computador = Computador::updateOrCreate(
            ['id' => $this->computador_id],
            [
                'bien_nacional' => $this->bien_nacional,
                'serial' => $this->serial,
                'marca_id' => $this->marca_id,
                'tipo_dispositivo_id' => $this->tipo_dispositivo_id,
                'sistema_operativo_id' => $this->sistema_operativo_id,
                'procesador_id' => $this->procesador_id,
                'gpu_id' => $this->gpu_id ?: null,
                'departamento_id' => $this->departamento_id ?: null,
                'trabajador_id' => $this->trabajador_id ?: null,
                'tipo_ram' => $this->tipo_ram,
                'mac' => $this->mac,
                'ip' => $this->ip,
                'tipo_conexion' => $this->tipo_conexion ?: null,
                'estado_fisico' => $this->estado_fisico,
                'observaciones' => $this->observaciones,
                'activo' => $this->activo ? 1 : 0
            ]
        );

        // 2. Sincronizar Puertos (Tabla Pivote)
        $computador->puertos()->sync($this->puertos_seleccionados);

        // 3. Procesar Discos
        // Borramos los anteriores (SoftDeletes) y creamos los nuevos para mantener historial limpio
        if($this->computador_id) { ComputadorDisco::where('computador_id', $computador->id)->delete(); }
        foreach ($this->discos as $disco) {
            if (!empty($disco['capacidad']) && !empty($disco['tipo'])) {
                $computador->discos()->create([
                    'capacidad' => $disco['capacidad'] . 'GB', // Forzamos el sufijo
                    'tipo' => $disco['tipo']
                ]);
            }
        }

        // 4. Procesar RAMs
        if($this->computador_id) { ComputadorRam::where('computador_id', $computador->id)->delete(); }
        foreach ($this->rams as $index => $ram) {
            if (!empty($ram['capacidad'])) {
                $computador->rams()->create([
                    'capacidad' => $ram['capacidad'] . 'GB',
                    'slot' => $index + 1
                ]);
            }
        }

        $this->dispatch('cerrar-modal', id: 'modalComputador');
        $this->dispatch('mostrar-toast', mensaje: $this->computador_id ? 'Computador actualizado.' : 'Computador registrado.');
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-computadores'), 403);
        $this->resetValidation();
        $computador = Computador::with(['puertos', 'discos', 'rams'])->findOrFail($id);
        
        $this->computador_id = $computador->id;
        $this->bien_nacional = $computador->bien_nacional;
        $this->serial = $computador->serial;
        $this->marca_id = $computador->marca_id;
        $this->tipo_dispositivo_id = $computador->tipo_dispositivo_id;
        $this->sistema_operativo_id = $computador->sistema_operativo_id;
        $this->procesador_id = $computador->procesador_id;
        $this->gpu_id = $computador->gpu_id;
        $this->departamento_id = $computador->departamento_id;
        $this->trabajador_id = $computador->trabajador_id;
        $this->tipo_ram = $computador->tipo_ram;
        $this->mac = $computador->mac;
        $this->ip = $computador->ip;
        $this->tipo_conexion = $computador->tipo_conexion;
        $this->estado_fisico = $computador->estado_fisico;
        $this->observaciones = $computador->observaciones;
        $this->activo = (bool) $computador->activo; 

        // Recuperar Puertos
        $this->puertos_seleccionados = $computador->puertos->pluck('id')->toArray();

        // Recuperar Discos (Limpiando el 'GB' para el input numérico)
        $this->discos = [];
        foreach($computador->discos as $disco) {
            $this->discos[] = [
                'capacidad' => str_replace('GB', '', $disco->capacidad),
                'tipo' => $disco->tipo
            ];
        }
        if(count($this->discos) === 0) $this->addDisco();

        // Recuperar RAM
        $this->rams = [];
        foreach($computador->rams as $ram) {
            $this->rams[] = [
                'capacidad' => str_replace('GB', '', $ram->capacidad),
                'slot' => $ram->slot
            ];
        }
        if(count($this->rams) === 0) $this->addRam();
        
        $this->tituloModal = 'Editar Computador';
        $this->dispatch('abrir-modal', id: 'modalComputador');
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-computadores'), 403);
        $this->computador_detalle = Computador::with(['marca', 'tipoDispositivo', 'sistemaOperativo', 'procesador', 'gpu', 'trabajador', 'discos', 'rams', 'puertos'])->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-computadores'), 403);
        Computador::findOrFail($id)->delete(); // Hará SoftDelete en cascada si está configurado en DB, o individual.
        $this->dispatch('mostrar-toast', mensaje: 'Computador eliminado (Baja).');
    }

    public function resetCampos()
    {
        $this->reset([
            'computador_id', 'bien_nacional', 'serial', 'marca_id', 'tipo_dispositivo_id', 
            'sistema_operativo_id', 'procesador_id', 'gpu_id', 'departamento_id', 'trabajador_id', 'tipo_ram', 
            'mac', 'ip', 'tipo_conexion', 'estado_fisico', 'observaciones', 'computador_detalle',
            'nueva_marca', 'nuevo_tipo', 'nuevo_so'
        ]);
        
        $this->creando_marca = false;
        $this->creando_tipo = false;
        $this->creando_so = false;
        $this->creando_procesador = false;
        $this->creando_gpu = false;
        $this->creando_trabajador = false;
        $this->activo = true;
        
        $this->discos = [];
        $this->rams = [];
        $this->addDisco();
        $this->addRam();
        
        $this->puertos_seleccionados = [];
        $this->resetValidation();
    }

    // --- MÉTODOS PARA EL MODAL DE TRABAJADOR ---

    public function abrirModalTrabajador()
    {
        // Ocultamos el modal principal y abrimos el secundario
        $this->dispatch('cerrar-modal', id: 'modalComputador');
        $this->dispatch('abrir-modal', id: 'modalTrabajador');
    }

    public function cancelarModalTrabajador()
    {
        $this->reset([
            'nuevo_trab_nombres', 
            'nuevo_trab_apellidos', 
            'nuevo_trab_cedula', 
            'nuevo_trab_departamento_id'
        ]);
        
        $this->dispatch('cerrar-modal', id: 'modalTrabajador');
        $this->dispatch('abrir-modal', id: 'modalComputador');
    }

    public function guardarTrabajadorRapido()
    {
        $this->validate([
            'nuevo_trab_nombres' => 'required|string|max:255',
            'nuevo_trab_apellidos' => 'required|string|max:255',
            'nuevo_trab_cedula' => 'nullable|string|unique:trabajadores,cedula', // Ya no es required
            'nuevo_trab_departamento_id' => 'required|exists:departamentos,id',
        ]);

        // Al crear el trabajador, el Observer se dispara automáticamente y crea el usuario
        $trab = Trabajador::create([
            'nombres' => $this->nuevo_trab_nombres,
            'apellidos' => $this->nuevo_trab_apellidos,
            'cedula' => $this->nuevo_trab_cedula,
            'departamento_id' => $this->nuevo_trab_departamento_id,
            'activo' => true
        ]);

        $this->trabajador_id = $trab->id;

        $this->reset(['nuevo_trab_nombres', 'nuevo_trab_apellidos', 'nuevo_trab_cedula', 'nuevo_trab_departamento_id']);
        $this->dispatch('cerrar-modal', id: 'modalTrabajador');
        $this->dispatch('abrir-modal', id: 'modalComputador');
        $this->dispatch('mostrar-toast', mensaje: 'Trabajador y cuenta de usuario creados.');
    }
}