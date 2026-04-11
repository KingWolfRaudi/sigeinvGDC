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
    public $dispositivo_id, $bien_nacional, $serial, $tipo_dispositivo_id, $marca_id;
    public $nombre, $ip, $estado = 'operativo', $departamento_id, $trabajador_id;
    public $computador_id, $notas;
    public bool $activo = true;

    // Workflow de Movimientos
    public $justificacion = '';
    public bool $es_edicion = false;
    public $movimiento_preview = null;

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
    public $filtro_estado = 'todos';

    // Variables para Reutilización y Anidación
    public $presetFiltro = [];
    public $ocultarTitulos = false; // Por defecto muestra todos (para quien tenga permiso)

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

    public function mount($presetFiltro = [], $ocultarTitulos = false)
    {
        $this->presetFiltro = $presetFiltro;
        $this->ocultarTitulos = $ocultarTitulos;
    }

    // Este método se ejecuta AUTOMÁTICAMENTE cuando $departamento_id cambia en la vista
    public function updatedDepartamentoId($value)
    {
        $this->trabajador_id = null; // Reseteamos al trabajador para forzar la actualización
    }
    
    public function render()
    {
        // 1. Iniciamos la consulta base
        $userId = Auth::id();
        $query = Dispositivo::with(['marca', 'tipoDispositivo', 'trabajador', 'departamento'])
            ->withCount([
                'movimientos as pendientes_count'    => fn($q) => $q->where('estado_workflow', 'pendiente'),
                'movimientos as mis_borradores_count' => fn($q) => $q->where('estado_workflow', 'borrador')->where('solicitante_id', $userId),
            ]);

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

        // Filtros Prediseñados (Cuando el componente se renderiza dentro de un Asociaciones Dashboard)
        if (!empty($this->presetFiltro)) {
            foreach($this->presetFiltro as $col => $val) {
                if ($val !== null) {
                    $query->where($col, $val);
                }
            }
            if (isset($this->presetFiltro['departamento_id'])) {
                $this->departamento_id = $this->presetFiltro['departamento_id'];
            }
        }

        // 3. Búsqueda profunda (Deep Search)
        // 3. Búsqueda profunda (Deep Search) - Multiples tablas
        $query->where(function ($q) {
            $search = '%' . $this->search . '%';
            
            $q->where('bien_nacional', 'like', $search)
              ->orWhere('serial', 'like', $search)
              ->orWhere('nombre', 'like', $search)
              ->orWhere('ip', 'like', $search)
              ->orWhere('notas', 'like', $search)
              
              // Relaciones directas simples
              ->orWhereHas('marca', fn($subQ) => $subQ->where('nombre', 'like', $search))
              ->orWhereHas('tipoDispositivo', fn($subQ) => $subQ->where('nombre', 'like', $search))
              ->orWhereHas('departamento', fn($subQ) => $subQ->where('nombre', 'like', $search))
              
              // Relación con Computador
              ->orWhereHas('computador', fn($subQ) => $subQ->where('bien_nacional', 'like', $search)
                  ->orWhere('serial', 'like', $search)
                  ->orWhere('nombre_equipo', 'like', $search)
                  ->orWhere('tipo_computador', 'like', $search))

              // Trabajador asignado
              ->orWhereHas('trabajador', function($subQ) use ($search) {
                  $subQ->where('nombres', 'like', $search)
                       ->orWhere('apellidos', 'like', $search)
                       ->orWhere('cedula', 'like', $search)
                       ->orWhere('cargo', 'like', $search);
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
            'bien_nacional'  => 'required|string|unique:dispositivos,bien_nacional,' . $this->dispositivo_id,
            'serial'         => 'required|string|unique:dispositivos,serial,' . $this->dispositivo_id,
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
                'bien_nacional'       => $this->bien_nacional,
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

            // ── Computar solo los campos que CAMBIARON ──
            $candidato = [
                'bien_nacional'       => $this->bien_nacional,       'serial'          => $this->serial,
                'tipo_dispositivo_id' => $this->tipo_dispositivo_id, 'marca_id'        => $this->marca_id,
                'nombre'              => $this->nombre,              'ip'              => $this->ip,
                'estado'              => $this->estado,              'departamento_id' => $this->departamento_id,
                'trabajador_id'       => $this->trabajador_id ?: null, 'computador_id' => $this->computador_id ?: null,
                'notas'               => $this->notas,              'activo'          => $this->activo,
                'puertos'             => $this->puertos_seleccionados,
            ];
            $boolCampos = ['activo'];
            $payloadNuevo = [];
            foreach ($candidato as $k => $v) {
                $ant = $payloadAnterior[$k] ?? null;
                $iguales = in_array($k, $boolCampos)
                    ? ((bool)$ant === (bool)$v)
                    : (is_array($v) 
                        ? $this->_sonIgualesArrays($ant, $v) 
                        : ((string)($ant ?? '') === (string)($v ?? '')));
                if (!$iguales) {
                    $payloadNuevo[$k] = $v;
                }
            }
            if (empty($payloadNuevo)) {
                $this->dispatch('mostrar-toast', mensaje: 'No se detectaron cambios.', tipo: 'info');
                $this->dispatch('cerrar-modal', id: 'modalDispositivo');
                $this->resetCampos();
                return;
            }

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
        $this->bien_nacional = $dispositivo->bien_nacional;
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
        $this->dispatch('abrir-modal', id: 'modalDetalleDispositivo');
    }

    public function verCambioPendiente(int $dispositivoId): void
    {
        abort_if(Gate::denies('ver-dispositivos'), 403);
        $this->movimiento_preview = MovimientoDispositivo::with('solicitante')
            ->where('dispositivo_id', $dispositivoId)
            ->whereIn('estado_workflow', ['pendiente', 'borrador'])
            ->orderByRaw("CASE estado_workflow WHEN 'pendiente' THEN 0 ELSE 1 END")
            ->latest()
            ->first();
        if ($this->movimiento_preview) {
            $this->dispatch('abrir-modal', id: 'modalCambioPendiente');
        }
    }

    public function aprobarMovimientoPreview(): void
    {
        abort_if(Gate::denies('movimientos-dispositivos-aprobar'), 403);
        if (!$this->movimiento_preview || $this->movimiento_preview->estado_workflow !== 'pendiente') {
            $this->dispatch('mostrar-toast', mensaje: 'Solo se pueden aprobar movimientos en estado Pendiente.', tipo: 'warning');
            return;
        }
        try {
            $mov        = MovimientoDispositivo::where('estado_workflow', 'pendiente')->findOrFail($this->movimiento_preview->id);
            $dispositivo = Dispositivo::withTrashed()->findOrFail($mov->dispositivo_id);
            $payload    = $mov->payload_nuevo;

            match ($mov->tipo_operacion) {
                'baja'          => $dispositivo->delete(),
                'toggle_activo' => $dispositivo->update(['activo' => $payload['activo'] ?? !$dispositivo->activo]),
                default         => $this->_aplicarPayloadDispositivo($dispositivo, $payload),
            };

            $mov->update(['estado_workflow' => 'aprobado', 'aprobador_id' => Auth::id(), 'aprobado_at' => now()]);
            $this->movimiento_preview = null;
            $this->dispatch('cerrar-modal', id: 'modalCambioPendiente');
            $this->dispatch('mostrar-toast', mensaje: 'Movimiento aprobado y aplicado.', tipo: 'success');
        } catch (\Exception $e) {
            Log::error('Error aprobando movimiento desde inventario: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al aprobar el movimiento.', tipo: 'error');
        }
    }

    /** Helper para comparar arrays de ID de puertos de forma lógica */
    private function _sonIgualesArrays($ant, $nuevo): bool
    {
        if (empty($ant) && empty($nuevo)) return true;
        if (empty($ant) || empty($nuevo)) return false;

        $a = (array)$ant; sort($a);
        $b = (array)$nuevo; sort($b);
        return $a == $b;
    }

    private function _aplicarPayloadDispositivo(Dispositivo $dispositivo, array $payload): void
    {
        $puertos = $payload['puertos'] ?? null;
        $directos = array_diff_key($payload, array_flip(['puertos']));
        $dispositivo->update($directos);
        if ($puertos !== null) { $dispositivo->puertos()->sync($puertos); }
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
            'dispositivo_id', 'bien_nacional', 'serial', 'tipo_dispositivo_id', 'marca_id', 
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
