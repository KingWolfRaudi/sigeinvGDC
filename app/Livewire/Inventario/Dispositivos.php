<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Dispositivo;
use App\Models\Marca;
use App\Models\TipoDispositivo;
use App\Models\Trabajador;
use App\Models\Puerto;
use App\Models\Departamento;
use App\Models\Computador;
use App\Models\MovimientoDispositivo;

class Dispositivos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Campos principales
    public $dispositivo_id, $codigo, $serial, $tipo_dispositivo_id, $marca_id;
    public $nombre, $ip, $estado = 'operativo', $departamento_id, $trabajador_id;
    public $computador_id, $notas;
    public bool $activo = true;

    // Workflow de Movimientos
    public $justificacion = '';
    public bool $es_edicion = false;

    // Relaciones Multiples (Arrays Dinámicos)
    public $puertos_seleccionados = [];

    // Creación Rápida On The Fly (Select vs Input)
    public $creando_marca = false, $nueva_marca;
    public $creando_tipo = false, $nuevo_tipo;
    // Variables para el Modal Completo de Trabajador
    public $nuevo_trab_nombres, $nuevo_trab_apellidos, $nuevo_trab_cedula, $nuevo_trab_departamento_id; 

    public $dispositivo_detalle;
    public $tituloModal = 'Nuevo Dispositivo';
    public $search = '';
    public $sortField = 'id';
    public $sortAsc = false;
    public $filtro_estado = 'todos'; // Por defecto muestra todos (para quien tenga permiso)

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
    }

    // Este método se ejecuta AUTOMÁTICAMENTE cuando $departamento_id cambia en la vista
    public function updatedDepartamentoId($value)
    {
        $this->trabajador_id = null; // Reseteamos al trabajador para forzar la actualización
    }
    
    public function render()
    {
        // 1. Iniciamos la consulta base
        $query = Dispositivo::with(['marca', 'tipoDispositivo', 'trabajador', 'departamento'])
            ->withCount(['movimientos as pendientes_count' => function ($q) {
                $q->where('estado_workflow', 'pendiente');
            }]);

        // 2. LÓGICA DE ESTADOS Y VISIBILIDAD
        if (\Illuminate\Support\Facades\Gate::allows('ver-estado-dispositivos')) {
            // Si TIENE permiso, aplicamos el filtro visual
            if ($this->filtro_estado === 'activos') {
                $query->where('activo', true);
            } elseif ($this->filtro_estado === 'inactivos') {
                $query->where('activo', false);
            }
        } else {
            // Si NO TIENE permiso, forzamos a que solo vea los activos
            $query->where('activo', true);
        }

        // 3. Búsqueda profunda (Deep Search)
        $query->where(function ($q) {
            $q->where('codigo', 'like', '%' . $this->search . '%')
              ->orWhere('serial', 'like', '%' . $this->search . '%')
              ->orWhere('nombre', 'like', '%' . $this->search . '%')
              ->orWhere('ip', 'like', '%' . $this->search . '%')
              // Búsqueda en relación Marca
              ->orWhereHas('marca', function($subQ) {
                  $subQ->where('nombre', 'like', '%' . $this->search . '%');
              })
              // Búsqueda en relación Tipo
              ->orWhereHas('tipoDispositivo', function($subQ) {
                  $subQ->where('nombre', 'like', '%' . $this->search . '%');
              })
              // Búsqueda en relación Trabajador
              ->orWhereHas('trabajador', function($subQ) {
                  $subQ->where('nombres', 'like', '%' . $this->search . '%')
                       ->orWhere('apellidos', 'like', '%' . $this->search . '%');
              });
        });

        // 4. Ordenamos y Paginamos
        $dispositivos = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                              ->paginate(10);

        // Catálogos para los selects
        $marcas = Marca::where('activo', true)->orderBy('nombre')->get();
        $tipos = TipoDispositivo::where('activo', true)->orderBy('nombre')->get();
        $puertos = Puerto::where('activo', true)->orderBy('nombre')->get();
        $departamentos = Departamento::where('activo', true)->orderBy('nombre')->get();
        $computadores = Computador::with('marca')->where('activo', true)->get();

        // Filtramos trabajadores por departamento si hay uno seleccionado
        $trabajadores = Trabajador::where('activo', true)
            ->when($this->departamento_id, function($q) {
                return $q->where('departamento_id', $this->departamento_id);
            })
            ->orderBy('nombres')
            ->get();
            
        return view('livewire.inventario.dispositivos', compact(
            'dispositivos', 'marcas', 'tipos', 'trabajadores', 'puertos', 'departamentos', 'computadores'
        ));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-dispositivos'), 403);
        $this->resetCampos();
        $this->tituloModal = 'Nuevo Dispositivo';
        $this->dispatch('abrir-modal', id: 'modalDispositivo');
    }

    public function guardar()
    {
        $esEdicion = (bool) $this->dispositivo_id;
        abort_if(Gate::denies($esEdicion ? 'editar-dispositivos' : 'crear-dispositivos'), 403);

        $rules = [
            'codigo'         => 'nullable|string|unique:dispositivos,codigo,' . $this->dispositivo_id,
            'serial'         => 'nullable|string|unique:dispositivos,serial,' . $this->dispositivo_id,
            'ip'             => 'nullable|ipv4',
            'estado'         => 'required|string',
            'nombre'         => 'required|string',
            'departamento_id'=> 'required|exists:departamentos,id'
        ];
        if ($esEdicion) {
            $rules['justificacion'] = 'required|string|min:10';
        }
        $this->validate($rules);

        try {
            if ($this->creando_marca && !empty($this->nueva_marca)) {
                $marca = Marca::firstOrCreate(['nombre' => $this->nueva_marca], ['activo' => true]);
                $this->marca_id = $marca->id;
            }
            if ($this->creando_tipo && !empty($this->nuevo_tipo)) {
                $tipo = TipoDispositivo::firstOrCreate(['nombre' => $this->nuevo_tipo], ['activo' => true]);
                $this->tipo_dispositivo_id = $tipo->id;
            }

            $payloadNuevo = [
                'codigo'              => $this->codigo,
                'serial'              => $this->serial,
                'tipo_dispositivo_id' => $this->tipo_dispositivo_id,
                'marca_id'            => $this->marca_id,
                'nombre'              => $this->nombre,
                'ip'                  => $this->ip,
                'estado'              => $this->estado,
                'departamento_id'     => $this->departamento_id,
                'trabajador_id'       => $this->trabajador_id ?: null,
                'computador_id'       => $this->computador_id ?: null,
                'notas'               => $this->notas,
                'activo'              => $this->activo,
                'puertos'             => $this->puertos_seleccionados,
            ];

            if (!$esEdicion) {
                $dispositivo = Dispositivo::create($payloadNuevo);
                $dispositivo->puertos()->sync($this->puertos_seleccionados);
                $this->dispatch('cerrar-modal', id: 'modalDispositivo');
                $this->dispatch('mostrar-toast', mensaje: 'Dispositivo registrado.', tipo: 'success');
                $this->resetCampos();
                return;
            }

            $dispositivo = Dispositivo::with('puertos')->findOrFail($this->dispositivo_id);
            $payloadAnterior = $dispositivo->toArray();
            $payloadAnterior['puertos'] = $dispositivo->puertos->pluck('id')->toArray();

            if (Gate::allows('movimientos-dispositivos-ejecutar-directo')) {
                $dispositivo->update($payloadNuevo);
                $dispositivo->puertos()->sync($this->puertos_seleccionados);
                MovimientoDispositivo::create([
                    'dispositivo_id'   => $dispositivo->id,
                    'tipo_operacion'   => 'actualizacion_datos',
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $payloadNuevo,
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'    => $this->justificacion,
                    'solicitante_id'   => Auth::id(),
                    'aprobador_id'     => Auth::id(),
                    'aprobado_at'      => now(),
                ]);
                $this->dispatch('cerrar-modal', id: 'modalDispositivo');
                $this->dispatch('mostrar-toast', mensaje: 'Dispositivo actualizado directamente.', tipo: 'success');
            } else {
                MovimientoDispositivo::create([
                    'dispositivo_id'   => $dispositivo->id,
                    'tipo_operacion'   => 'actualizacion_datos',
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $payloadNuevo,
                    'estado_workflow'  => 'borrador',
                    'justificacion'    => $this->justificacion,
                    'solicitante_id'   => Auth::id(),
                ]);
                $this->dispatch('cerrar-modal', id: 'modalDispositivo');
                $this->dispatch('mostrar-toast',
                    mensaje: 'Cambio guardado como borrador. Ve a Movimientos para enviarlo a revisión.',
                    tipo: 'info'
                );
            }
            $this->resetCampos();
        } catch (\Exception $e) {
            Log::error('Error guardando dispositivo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Ocurrió un error guardando el dispositivo.', tipo: 'error');
        }
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-dispositivos'), 403);
        $this->resetValidation();
        $this->es_edicion = true;
        $this->justificacion = '';
        $dispositivo = Dispositivo::with(['puertos'])->findOrFail($id);
        
        $this->dispositivo_id = $dispositivo->id;
        $this->codigo = $dispositivo->codigo;
        $this->serial = $dispositivo->serial;
        $this->tipo_dispositivo_id = $dispositivo->tipo_dispositivo_id;
        $this->marca_id = $dispositivo->marca_id;
        $this->nombre = $dispositivo->nombre;
        $this->ip = $dispositivo->ip;
        $this->estado = $dispositivo->estado;
        $this->departamento_id = $dispositivo->departamento_id;
        $this->trabajador_id = $dispositivo->trabajador_id;
        $this->computador_id = $dispositivo->computador_id;
        $this->notas = $dispositivo->notas;
        $this->activo = (bool) $dispositivo->activo; 

        // Recuperar Puertos
        $this->puertos_seleccionados = $dispositivo->puertos->pluck('id')->toArray();

        $this->tituloModal = 'Editar Dispositivo';
        $this->dispatch('abrir-modal', id: 'modalDispositivo');
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-dispositivos'), 403);
        $this->dispositivo_detalle = Dispositivo::with(['marca', 'tipoDispositivo', 'departamento', 'trabajador', 'computador', 'puertos'])->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-dispositivos'), 403);
        try {
            $dispositivo = Dispositivo::findOrFail($id);
            if (Gate::allows('movimientos-dispositivos-ejecutar-directo')) {
                $dispositivo->delete();
                MovimientoDispositivo::create([
                    'dispositivo_id'   => $id,
                    'tipo_operacion'   => 'baja',
                    'payload_anterior' => $dispositivo->toArray(),
                    'payload_nuevo'    => ['activo' => false, 'baja' => true],
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'    => 'Baja directa.',
                    'solicitante_id'   => Auth::id(),
                    'aprobador_id'     => Auth::id(),
                    'aprobado_at'      => now(),
                ]);
                $this->dispatch('mostrar-toast', mensaje: 'Dispositivo dado de baja.', tipo: 'success');
            } else {
                MovimientoDispositivo::create([
                    'dispositivo_id'   => $dispositivo->id,
                    'tipo_operacion'   => 'baja',
                    'payload_anterior' => $dispositivo->toArray(),
                    'payload_nuevo'    => ['activo' => false, 'baja' => true],
                    'estado_workflow'  => 'borrador',
                    'justificacion'    => 'Solicitud de baja pendiente.',
                    'solicitante_id'   => Auth::id(),
                ]);
                $this->dispatch('mostrar-toast',
                    mensaje: 'Solicitud de baja creada como borrador.',
                    tipo: 'warning'
                );
            }
        } catch (\Exception $e) {
            Log::error('Error eliminando dispositivo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Ocurrió un error eliminando el dispositivo.', tipo: 'error');
        }
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-dispositivos'), 403);
        try {
            $dispositivo = Dispositivo::findOrFail($id);
            $nuevoEstado = !$dispositivo->activo;
            if (Gate::allows('movimientos-dispositivos-ejecutar-directo')) {
                $dispositivo->activo = $nuevoEstado;
                $dispositivo->save();
                MovimientoDispositivo::create([
                    'dispositivo_id'   => $dispositivo->id,
                    'tipo_operacion'   => 'toggle_activo',
                    'payload_anterior' => ['activo' => !$nuevoEstado],
                    'payload_nuevo'    => ['activo' => $nuevoEstado],
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'    => 'Cambio de estatus directo.',
                    'solicitante_id'   => Auth::id(),
                    'aprobador_id'     => Auth::id(),
                    'aprobado_at'      => now(),
                ]);
                $estado = $nuevoEstado ? 'activado' : 'inactivado';
                $this->dispatch('mostrar-toast', mensaje: "Dispositivo $estado.", tipo: 'success');
            } else {
                MovimientoDispositivo::create([
                    'dispositivo_id'   => $dispositivo->id,
                    'tipo_operacion'   => 'toggle_activo',
                    'payload_anterior' => ['activo' => !$nuevoEstado],
                    'payload_nuevo'    => ['activo' => $nuevoEstado],
                    'estado_workflow'  => 'borrador',
                    'justificacion'    => 'Solicitud de cambio de estatus pendiente.',
                    'solicitante_id'   => Auth::id(),
                ]);
                $this->dispatch('mostrar-toast',
                    mensaje: 'Solicitud de cambio de estatus guardada en borrador.',
                    tipo: 'info'
                );
            }
        } catch (\Exception $e) {
            Log::error('Error en toggleActivo dispositivo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al cambiar el estatus.', tipo: 'error');
        }
    }

    public function resetCampos()
    {
        $this->reset([
            'dispositivo_id', 'codigo', 'serial', 'tipo_dispositivo_id', 'marca_id', 
            'nombre', 'ip', 'estado', 'departamento_id', 'trabajador_id', 'computador_id', 
            'notas', 'dispositivo_detalle', 'nueva_marca', 'nuevo_tipo', 'justificacion'
        ]);
        $this->creando_marca = false;
        $this->creando_tipo = false;
        $this->estado = 'operativo';
        $this->activo = true;
        $this->es_edicion = false;
        $this->puertos_seleccionados = [];
        $this->resetValidation();
    }

    // --- MÉTODOS PARA EL MODAL DE TRABAJADOR ---

    public function abrirModalTrabajador()
    {
        $this->dispatch('cerrar-modal', id: 'modalDispositivo');
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
        $this->dispatch('abrir-modal', id: 'modalDispositivo');
    }

    public function guardarTrabajadorRapido()
    {
        $this->validate([
            'nuevo_trab_nombres' => 'required|string|max:255',
            'nuevo_trab_apellidos' => 'required|string|max:255',
            'nuevo_trab_cedula' => 'nullable|string|unique:trabajadores,cedula', 
            'nuevo_trab_departamento_id' => 'required|exists:departamentos,id',
        ]);

        try {
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
            $this->dispatch('abrir-modal', id: 'modalDispositivo');
            $this->dispatch('mostrar-toast', mensaje: 'Trabajador y cuenta de usuario creados.', tipo:'success');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en trabajador rápido: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al registrar trabajador.', tipo:'error');
        }
    }
}
