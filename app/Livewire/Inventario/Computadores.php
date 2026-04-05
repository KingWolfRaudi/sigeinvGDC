<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
use App\Models\MovimientoComputador;


class Computadores extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Campos principales
    public $computador_id, $bien_nacional, $serial, $marca_id, $tipo_dispositivo_id, $sistema_operativo_id;
    public $procesador_id, $gpu_id, $departamento_id, $trabajador_id, $tipo_ram, $mac, $ip;
    public $tipo_conexion = 'Ethernet'; // Valor por defecto
    public $estado_fisico = 'operativo';
    public bool $unidad_dvd = true;
    public bool $fuente_poder = true;
    public $observaciones;
    public bool $activo = true;

    // Workflow de Movimientos
    public $justificacion = '';
    public bool $es_edicion = false;
    public $movimiento_preview = null; // Vista rápida de cambio pendiente desde el inventario

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
    public $filtro_estado = 'todos'; // Por defecto muestra todos (para quien tenga permiso)
    
    // Variables para Reutilización y Anidación
    public $presetFiltro = [];
    public $ocultarTitulos = false;

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
        // 1. Iniciamos la consulta base
        $userId = Auth::id();
        $query = Computador::with(['marca', 'tipoDispositivo', 'trabajador', 'discos', 'rams'])
            ->withCount([
                'movimientos as pendientes_count' => fn($q) => $q->where('estado_workflow', 'pendiente'),
                'movimientos as mis_borradores_count' => fn($q) => $q->where('estado_workflow', 'borrador')->where('solicitante_id', $userId),
            ]);

        // 2. LÓGICA DE ESTADOS Y VISIBILIDAD
        if (\Illuminate\Support\Facades\Gate::allows('ver-estado-computadores')) {
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
            
            // Si el preset es sobre departamento, limitamos los catálogos a ese departamento
            if (isset($this->presetFiltro['departamento_id'])) {
                $this->departamento_id = $this->presetFiltro['departamento_id'];
            }
        }

        // 3. Búsqueda profunda (Deep Search) - Multiples tablas
        $query->where(function ($q) {
            $search = '%' . $this->search . '%';
            
            // Campos nativos
            $q->where('bien_nacional', 'like', $search)
              ->orWhere('serial', 'like', $search)
              ->orWhere('ip', 'like', $search)
              ->orWhere('mac', 'like', $search)
              ->orWhere('observaciones', 'like', $search)
              
              // Relaciones directas simples
              ->orWhereHas('marca', fn($subQ) => $subQ->where('nombre', 'like', $search))
              ->orWhereHas('tipoDispositivo', fn($subQ) => $subQ->where('nombre', 'like', $search))
              ->orWhereHas('sistemaOperativo', fn($subQ) => $subQ->where('nombre', 'like', $search))
              ->orWhereHas('departamento', fn($subQ) => $subQ->where('nombre', 'like', $search))
              
              // Componentes Internos
              ->orWhereHas('procesador', function($subQ) use ($search) {
                  $subQ->where('modelo', 'like', $search)
                       ->orWhereHas('marca', fn($m) => $m->where('nombre', 'like', $search));
              })
              ->orWhereHas('gpu', function($subQ) use ($search) {
                  $subQ->where('modelo', 'like', $search)
                       ->orWhereHas('marca', fn($m) => $m->where('nombre', 'like', $search));
              })
              ->orWhereHas('rams', function($subQ) use ($search) {
                  $subQ->where('capacidad', 'like', $search);
              })
              ->orWhereHas('discos', function($subQ) use ($search) {
                  $subQ->where('capacidad', 'like', $search)
                       ->orWhere('tipo', 'like', $search);
              })

              // Trabajador asignado
              ->orWhereHas('trabajador', function($subQ) use ($search) {
                  $subQ->where('nombres', 'like', $search)
                       ->orWhere('apellidos', 'like', $search)
                       ->orWhere('cedula', 'like', $search)
                       ->orWhere('cargo', 'like', $search);
              });
        });

        // 4. Ordenamos y Paginamos
        $computadores = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                              ->paginate(10);

        // Catálogos para los selects
        $marcas = Marca::where('activo', true)->orderBy('nombre')->get();
        $tipos = TipoDispositivo::where('activo', true)->orderBy('nombre')->get();
        $sistemas = SistemaOperativo::where('activo', true)->orderBy('nombre')->get();
        $procesadores = Procesador::with('marca')->where('activo', true)->orderBy('modelo')->get();
        $gpus = Gpu::with('marca')->where('activo', true)->orderBy('modelo')->get();
        $puertos = Puerto::where('activo', true)->orderBy('nombre')->get();
        $departamentos = Departamento::where('activo', true)->orderBy('nombre')->get();

        // Filtramos trabajadores por departamento si hay uno seleccionado
        $trabajadores = Trabajador::where('activo', true)
            ->when($this->departamento_id, function($q) {
                return $q->where('departamento_id', $this->departamento_id);
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
        $esEdicion = (bool) $this->computador_id;
        abort_if(Gate::denies($esEdicion ? 'editar-computadores' : 'crear-computadores'), 403);

        // Validación base
        $rules = [
            'bien_nacional' => 'nullable|string|unique:computadores,bien_nacional,' . $this->computador_id,
            'serial'        => 'nullable|string|unique:computadores,serial,' . $this->computador_id,
            'ip'            => 'nullable|ipv4',
            'mac'           => 'nullable|string|unique:computadores,mac,' . $this->computador_id,
            'estado_fisico' => 'required|string',
            'tipo_ram'      => 'required|string',
        ];
        // La justificación es OBLIGATORIA solo en ediciones
        if ($esEdicion) {
            $rules['justificacion'] = 'required|string|min:10';
        }
        $this->validate($rules);

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

        // Payload con los datos propuestos
        $payloadNuevo = [
            'bien_nacional' => $this->bien_nacional, 'serial' => $this->serial,
            'marca_id' => $this->marca_id, 'tipo_dispositivo_id' => $this->tipo_dispositivo_id,
            'sistema_operativo_id' => $this->sistema_operativo_id, 'procesador_id' => $this->procesador_id,
            'gpu_id' => $this->gpu_id ?: null, 'unidad_dvd' => $this->unidad_dvd,
            'fuente_poder' => $this->fuente_poder, 'departamento_id' => $this->departamento_id ?: null,
            'trabajador_id' => $this->trabajador_id ?: null, 'tipo_ram' => $this->tipo_ram,
            'mac' => $this->mac, 'ip' => $this->ip, 'tipo_conexion' => $this->tipo_conexion,
            'estado_fisico' => $this->estado_fisico, 'observaciones' => $this->observaciones,
            'activo' => $this->activo, 'discos' => $this->discos, 'rams' => $this->rams,
            'puertos' => $this->puertos_seleccionados,
        ];

        try {
            // ── CREACIÓN: Siempre inmediata ──────────────────────────────────
            if (!$esEdicion) {
                $computador = Computador::create($payloadNuevo);
                $computador->puertos()->sync($this->puertos_seleccionados);
                $this->_procesarDiscosYRams($computador, false);

                $this->dispatch('cerrar-modal', id: 'modalComputador');
                $this->dispatch('mostrar-toast', mensaje: 'Computador registrado exitosamente.', tipo: 'success');
                $this->resetCampos();
                return;
            }

            // ── EDICIÓN: Verificar si tiene ejecución directa ────────────────
            $computador = Computador::with(['discos','rams','puertos'])->findOrFail($this->computador_id);
            $payloadAnterior = $computador->toArray();
            $payloadAnterior['discos']  = $computador->discos->toArray();
            $payloadAnterior['rams']    = $computador->rams->toArray();
            $payloadAnterior['puertos'] = $computador->puertos->pluck('id')->toArray();

            // ── Computar solo los campos que CAMBIARON ──
            $candidato = [
                'bien_nacional'       => $this->bien_nacional,    'serial'              => $this->serial,
                'marca_id'            => $this->marca_id,          'tipo_dispositivo_id' => $this->tipo_dispositivo_id,
                'sistema_operativo_id'=> $this->sistema_operativo_id, 'procesador_id'    => $this->procesador_id,
                'gpu_id'              => $this->gpu_id ?: null,   'unidad_dvd'          => $this->unidad_dvd,
                'fuente_poder'        => $this->fuente_poder,     'departamento_id'     => $this->departamento_id ?: null,
                'trabajador_id'       => $this->trabajador_id ?: null, 'tipo_ram'         => $this->tipo_ram,
                'mac'                 => $this->mac,              'ip'                  => $this->ip,
                'tipo_conexion'       => $this->tipo_conexion,    'estado_fisico'       => $this->estado_fisico,
                'observaciones'       => $this->observaciones,    'activo'              => $this->activo,
                'discos'              => $this->discos,           'rams'                => $this->rams,
                'puertos'             => $this->puertos_seleccionados,
            ];
            $boolCampos = ['activo', 'unidad_dvd', 'fuente_poder'];
            $payloadNuevo = [];
            foreach ($candidato as $k => $v) {
                $ant = $payloadAnterior[$k] ?? null;
                $iguales = in_array($k, $boolCampos)
                    ? ((bool)$ant === (bool)$v)
                    : (is_array($v) 
                        ? $this->_sonIgualesArrays($ant, $v, $k) 
                        : ((string)($ant ?? '') === (string)($v ?? '')));
                if (!$iguales) {
                    $payloadNuevo[$k] = $v;
                }
            }
            if (empty($payloadNuevo)) {
                $this->dispatch('mostrar-toast', mensaje: 'No se detectaron cambios.', tipo: 'info');
                $this->dispatch('cerrar-modal', id: 'modalComputador');
                $this->resetCampos();
                return;
            }

            if (Gate::allows('movimientos-computadores-ejecutar-directo')) {
                // Ejecutar directamente en BD
                $computador->update($payloadNuevo);
                $computador->puertos()->sync($this->puertos_seleccionados);
                $this->_procesarDiscosYRams($computador, true);

                // Registrar trazabilidad
                MovimientoComputador::create([
                    'computador_id'   => $computador->id,
                    'tipo_operacion'  => 'actualizacion_datos',
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $payloadNuevo,
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'   => $this->justificacion,
                    'solicitante_id'  => Auth::id(),
                    'aprobador_id'    => Auth::id(),
                    'aprobado_at'     => now(),
                ]);

                $this->dispatch('cerrar-modal', id: 'modalComputador');
                $this->dispatch('mostrar-toast', mensaje: 'Computador actualizado directamente.', tipo: 'success');
            } else {
                // Crear borrador — No toca la BD real
                MovimientoComputador::create([
                    'computador_id'   => $computador->id,
                    'tipo_operacion'  => 'actualizacion_datos',
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $payloadNuevo,
                    'estado_workflow'  => 'borrador',
                    'justificacion'   => $this->justificacion,
                    'solicitante_id'  => Auth::id(),
                ]);

                $this->dispatch('cerrar-modal', id: 'modalComputador');
                $this->dispatch('mostrar-toast',
                    mensaje: 'Cambio guardado como borrador. Ve a Movimientos para enviarlo a revisión.',
                    tipo: 'info'
                );
            }

            $this->resetCampos();
        } catch (\Exception $e) {
            Log::error('Error guardando computador: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Ocurrió un error al guardar.', tipo: 'error');
        }
    }

    /** Helper para comparar arrays de forma lógica (evita falsos positivos por IDs o unidades) */
    private function _sonIgualesArrays($ant, $nuevo, $tipo): bool
    {
        // Si ambos están vacíos o son nulos, son iguales
        if (empty($ant) && empty($nuevo)) return true;
        
        // Si uno está vacío y el otro no, son diferentes
        if (empty($ant) || empty($nuevo)) return false;

        if ($tipo === 'puertos') {
            // Comparar listas de IDs ignorando el orden
            $a = (array)$ant; sort($a);
            $b = (array)$nuevo; sort($b);
            return $a == $b;
        }

        if ($tipo === 'discos' || $tipo === 'rams') {
            // Normalizar el "Anterior" (que viene de DB con IDs y 'GB') al formato del "Nuevo" (del form)
            $antNorm = array_map(function($item) use ($tipo) {
                if ($tipo === 'discos') {
                    return [
                        'capacidad' => str_replace('GB', '', $item['capacidad'] ?? ''),
                        'tipo'      => $item['tipo'] ?? ''
                    ];
                } else {
                    return [
                        'capacidad' => str_replace('GB', '', $item['capacidad'] ?? ''),
                        'slot'      => (int)($item['slot'] ?? 0)
                    ];
                }
            }, (array)$ant);

            // Normalizar el "Nuevo" (asegurar tipos de datos)
            $nuevoNorm = array_map(function($item) use ($tipo) {
                if ($tipo === 'discos') {
                    return [
                        'capacidad' => (string)($item['capacidad'] ?? ''),
                        'tipo'      => (string)($item['tipo'] ?? '')
                    ];
                } else {
                    return [
                        'capacidad' => (string)($item['capacidad'] ?? ''),
                        'slot'      => (int)($item['slot'] ?? 0)
                    ];
                }
            }, (array)$nuevo);

            // Ordenar ambos para que la comparación sea independiente del orden
            usort($antNorm, fn($a, $b) => json_encode($a) <=> json_encode($b));
            usort($nuevoNorm, fn($a, $b) => json_encode($a) <=> json_encode($b));

            return json_encode($antNorm) === json_encode($nuevoNorm);
        }

        return $ant == $nuevo;
    }

    /** Helper privado para procesar discos y RAMs */
    private function _procesarDiscosYRams(Computador $computador, bool $esEdicion): void
    {
        if ($esEdicion) {
            ComputadorDisco::where('computador_id', $computador->id)->delete();
            ComputadorRam::where('computador_id', $computador->id)->delete();
        }
        foreach ($this->discos as $disco) {
            if (!empty($disco['capacidad']) && !empty($disco['tipo'])) {
                $computador->discos()->create([
                    'capacidad' => $disco['capacidad'] . 'GB',
                    'tipo'      => $disco['tipo']
                ]);
            }
        }
        foreach ($this->rams as $index => $ram) {
            if (!empty($ram['capacidad'])) {
                $computador->rams()->create([
                    'capacidad' => $ram['capacidad'] . 'GB',
                    'slot'      => $index + 1
                ]);
            }
        }
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-computadores'), 403);
        $this->resetValidation();
        $this->es_edicion = true; // Activa el campo de justificación en el formulario
        $this->justificacion = '';
        $computador = Computador::with(['puertos', 'discos', 'rams'])->findOrFail($id);
        
        $this->computador_id = $computador->id;
        $this->bien_nacional = $computador->bien_nacional;
        $this->serial = $computador->serial;
        $this->marca_id = $computador->marca_id;
        $this->tipo_dispositivo_id = $computador->tipo_dispositivo_id;
        $this->sistema_operativo_id = $computador->sistema_operativo_id;
        $this->procesador_id = $computador->procesador_id;
        $this->gpu_id = $computador->gpu_id;
        $this->unidad_dvd = (bool) $computador->unidad_dvd;     // <-- Agregado
        $this->fuente_poder = (bool) $computador->fuente_poder; // <-- Agregado
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
        $this->dispatch('abrir-modal', id: 'modalDetalleComputador');
    }

    public function verCambioPendiente(int $computadorId): void
    {
        abort_if(Gate::denies('ver-computadores'), 403);
        $this->movimiento_preview = MovimientoComputador::with('solicitante')
            ->where('computador_id', $computadorId)
            ->whereIn('estado_workflow', ['pendiente', 'borrador'])
            ->orderByRaw("CASE estado_workflow WHEN 'pendiente' THEN 0 ELSE 1 END")
            ->latest()
            ->first();
        if ($this->movimiento_preview) {
            $this->dispatch('abrir-modal', id: 'modalCambioPendiente');
        }
    }

    /** Aprueba el movimiento que está en $movimiento_preview directamente desde el inventario */
    public function aprobarMovimientoPreview(): void
    {
        abort_if(Gate::denies('movimientos-computadores-aprobar'), 403);
        if (!$this->movimiento_preview || $this->movimiento_preview->estado_workflow !== 'pendiente') {
            $this->dispatch('mostrar-toast', mensaje: 'Solo se pueden aprobar movimientos en estado Pendiente.', tipo: 'warning');
            return;
        }
        try {
            $mov        = MovimientoComputador::where('estado_workflow', 'pendiente')->findOrFail($this->movimiento_preview->id);
            $computador = Computador::withTrashed()->findOrFail($mov->computador_id);
            $payload    = $mov->payload_nuevo;

            match ($mov->tipo_operacion) {
                'baja'          => $computador->delete(),
                'toggle_activo' => $computador->update(['activo' => $payload['activo'] ?? !$computador->activo]),
                default         => $this->_aplicarPayloadComputador($computador, $payload),
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

    private function _aplicarPayloadComputador(Computador $computador, array $payload): void
    {
        $discos  = $payload['discos']  ?? null;
        $rams    = $payload['rams']    ?? null;
        $puertos = $payload['puertos'] ?? null;
        $directos = array_diff_key($payload, array_flip(['discos', 'rams', 'puertos']));
        $computador->update($directos);
        if ($puertos !== null) { $computador->puertos()->sync($puertos); }
        if ($discos !== null) {
            ComputadorDisco::where('computador_id', $computador->id)->delete();
            foreach ($discos as $d) {
                if (!empty($d['capacidad']) && !empty($d['tipo'])) {
                    $computador->discos()->create([
                        'capacidad' => str_contains($d['capacidad'], 'GB') ? $d['capacidad'] : $d['capacidad'] . 'GB',
                        'tipo'      => $d['tipo'],
                    ]);
                }
            }
        }
        if ($rams !== null) {
            ComputadorRam::where('computador_id', $computador->id)->delete();
            foreach ($rams as $i => $r) {
                if (!empty($r['capacidad'])) {
                    $computador->rams()->create([
                        'capacidad' => str_contains($r['capacidad'], 'GB') ? $r['capacidad'] : $r['capacidad'] . 'GB',
                        'slot'      => $i + 1,
                    ]);
                }
            }
        }
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-computadores'), 403);
        try {
            $computador = Computador::findOrFail($id);

            if (Gate::allows('movimientos-computadores-ejecutar-directo')) {
                $computador->delete();
                MovimientoComputador::create([
                    'computador_id'   => $id,
                    'tipo_operacion'  => 'baja',
                    'payload_anterior' => $computador->toArray(),
                    'payload_nuevo'    => ['activo' => false, 'baja' => true],
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'   => 'Baja directa por usuario con permisos de ejecución.',
                    'solicitante_id'  => Auth::id(),
                    'aprobador_id'    => Auth::id(),
                    'aprobado_at'     => now(),
                ]);
                $this->dispatch('mostrar-toast', mensaje: 'Computador dado de baja.', tipo: 'success');
            } else {
                MovimientoComputador::create([
                    'computador_id'   => $computador->id,
                    'tipo_operacion'  => 'baja',
                    'payload_anterior' => $computador->toArray(),
                    'payload_nuevo'    => ['activo' => false, 'baja' => true],
                    'estado_workflow'  => 'borrador',
                    'justificacion'   => 'Solicitud de baja pendiente de aprobación.',
                    'solicitante_id'  => Auth::id(),
                ]);
                $this->dispatch('mostrar-toast',
                    mensaje: 'Solicitud de baja creada como borrador. Envíela a revisión desde Movimientos.',
                    tipo: 'warning'
                );
            }
        } catch (\Exception $e) {
            Log::error('Error eliminando computador: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Ocurrió un error al procesar la baja.', tipo: 'error');
        }
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-computadores'), 403);
        try {
            $computador = Computador::findOrFail($id);
            $nuevoEstado = !$computador->activo;

            if (Gate::allows('movimientos-computadores-ejecutar-directo')) {
                $computador->activo = $nuevoEstado;
                $computador->save();
                MovimientoComputador::create([
                    'computador_id'   => $computador->id,
                    'tipo_operacion'  => 'toggle_activo',
                    'payload_anterior' => ['activo' => !$nuevoEstado],
                    'payload_nuevo'    => ['activo' => $nuevoEstado],
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'   => 'Cambio de estatus directo.',
                    'solicitante_id'  => Auth::id(),
                    'aprobador_id'    => Auth::id(),
                    'aprobado_at'     => now(),
                ]);
                $estado = $nuevoEstado ? 'activado' : 'inactivado';
                $this->dispatch('mostrar-toast', mensaje: "Computador $estado.", tipo: 'success');
            } else {
                MovimientoComputador::create([
                    'computador_id'   => $computador->id,
                    'tipo_operacion'  => 'toggle_activo',
                    'payload_anterior' => ['activo' => !$nuevoEstado],
                    'payload_nuevo'    => ['activo' => $nuevoEstado],
                    'estado_workflow'  => 'borrador',
                    'justificacion'   => 'Solicitud de cambio de estatus pendiente.',
                    'solicitante_id'  => Auth::id(),
                ]);
                $this->dispatch('mostrar-toast',
                    mensaje: 'Solicitud de cambio de estatus guardada en borrador.',
                    tipo: 'info'
                );
            }
        } catch (\Exception $e) {
            Log::error('Error en toggleActivo computador: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al cambiar el estatus.', tipo: 'error');
        }
    }

    public function resetCampos()
    {
        $this->reset([
            'computador_id', 'bien_nacional', 'serial', 'marca_id', 'tipo_dispositivo_id', 
            'sistema_operativo_id', 'procesador_id', 'gpu_id', 'departamento_id', 'trabajador_id', 'tipo_ram', 
            'mac', 'ip', 'tipo_conexion', 'estado_fisico', 'observaciones', 'computador_detalle',
            'nueva_marca', 'nuevo_tipo', 'nuevo_so', 'justificacion'
        ]);
        
        $this->creando_marca = false;
        $this->creando_tipo = false;
        $this->creando_so = false;
        $this->creando_procesador = false;
        $this->creando_gpu = false;
        $this->unidad_dvd = true;
        $this->fuente_poder = true;
        $this->tipo_conexion = 'Ethernet';
        $this->activo = true;
        $this->es_edicion = false;
        
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
        $this->dispatch('mostrar-toast', mensaje: 'Trabajador y cuenta de usuario creados.', tipo:'success');
    }
}