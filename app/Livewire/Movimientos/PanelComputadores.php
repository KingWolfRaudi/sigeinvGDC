<?php

namespace App\Livewire\Movimientos;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\MovimientoComputador;
use App\Models\Computador;
use App\Models\ComputadorDisco;
use App\Models\ComputadorRam;
use App\Models\Marca;
use App\Models\SistemaOperativo;
use App\Models\Procesador;
use App\Models\Gpu;
use App\Models\Trabajador;
use App\Models\Puerto;
use App\Models\Departamento;

class PanelComputadores extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $pestana = 'borradores'; // borradores | pendientes | historico
    public string $search = '';
    public string $filtro_tipo = '';

    // Modal de rechazo
    public ?int $rechazando_id = null;
    public string $motivo_rechazo = '';

    // Edición de Borrador
    public ?int $editando_borrador_id = null;
    public string $edit_justificacion = '';

    // Detalle de movimiento
    public $movimiento_detalle = null;

    // ── PROPIEDADES DEL GENERADOR DE MOVIMIENTOS ───────────────────────
    public bool $mostrando_generador = false;
    public int $paso_generador = 1; // 1: Selección, 2: Edición

    // Filtros de Selección (Paso 1)
    public $searchBN = '', $searchSerial = '', $searchDpto = '', $searchTrabajador = '';

    // Campos del Formulario (Paso 2)
    public $computador_id, $bien_nacional, $serial, $nombre_equipo, $marca_id, $tipo_computador, $sistema_operativo_id;
    public $procesador_id, $gpu_id, $departamento_id, $trabajador_id, $tipo_ram, $mac, $ip;
    public $tipo_conexion = 'Ethernet';
    public $estado_fisico = 'operativo';
    public bool $unidad_dvd = true;
    public bool $fuente_poder = true;
    public $observaciones;
    public bool $activo = true;
    public $justificacion = '';

    public $discos = [];
    public $rams = [];
    public $puertos_seleccionados = [];

    // Creación Rápida On The Fly
    public bool $creando_marca = false; public $nueva_marca;
    public bool $creando_so = false; public $nuevo_so;
    public bool $creando_procesador = false; public $nuevo_procesador_modelo, $nuevo_procesador_marca_id;
    public bool $creando_gpu = false; public $nueva_gpu_modelo, $nueva_gpu_marca_id;
    public bool $creando_departamento = false; public $nuevo_departamento;

    // Trabajador On The Fly (Modal)
    public $nuevo_trab_nombres, $nuevo_trab_apellidos, $nuevo_trab_cedula, $nuevo_trab_departamento_id; 

    public $computador_actual = null; // Instancia cargada para Step 2

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPestana() { $this->resetPage(); }

    public function updatedDepartamentoId($value)
    {
        $this->trabajador_id = null;
    }

    public function render()
    {
        abort_if(Gate::denies('movimientos-computadores-ver'), 403);

        $query = MovimientoComputador::with(['computador.marca', 'solicitante', 'aprobador'])
            ->when($this->search, function ($q) {
                $q->whereHas('computador', function ($sub) {
                    $sub->where('bien_nacional', 'like', '%' . $this->search . '%')
                        ->orWhere('serial', 'like', '%' . $this->search . '%');
                })->orWhere('justificacion', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtro_tipo, fn($q) => $q->where('tipo_operacion', $this->filtro_tipo));

        $movimientos = match ($this->pestana) {
            'borradores' => $query->where('estado_workflow', 'borrador')
                                  ->where('solicitante_id', Auth::id())
                                  ->latest()->paginate(10),
            'pendientes' => $query->where('estado_workflow', 'pendiente')
                                  ->latest()->paginate(10),
            default       => $query->whereIn('estado_workflow', ['aprobado', 'rechazado', 'ejecutado_directo'])
                                   ->latest()->paginate(15),
        };

        $conteo = [
            'borradores' => MovimientoComputador::where('estado_workflow', 'borrador')
                               ->where('solicitante_id', Auth::id())->count(),
            'pendientes' => MovimientoComputador::where('estado_workflow', 'pendiente')->count(),
        ];

        // Catálogos para el Generador (Step 2)
        $catalogos = [
            'marcas' => collect(),
            'sistemas' => collect(),
            'procesadores' => collect(),
            'gpus' => collect(),
            'puertos' => collect(),
            'departamentos' => collect(),
            'trabajadores' => collect(),
        ];

        if ($this->mostrando_generador) {
            $catalogos = [
                'marcas' => Marca::where('activo', true)->orderBy('nombre')->get(),
                'sistemas' => SistemaOperativo::where('activo', true)->orderBy('nombre')->get(),
                'procesadores' => Procesador::with('marca')->where('activo', true)->orderBy('modelo')->get(),
                'gpus' => Gpu::with('marca')->where('activo', true)->orderBy('modelo')->get(),
                'puertos' => Puerto::where('activo', true)->orderBy('nombre')->get(),
                'departamentos' => Departamento::where('activo', true)->orderBy('nombre')->get(),
                'trabajadores' => Trabajador::where('activo', true)
                    ->when($this->departamento_id, fn($q) => $q->where('departamento_id', $this->departamento_id))
                    ->orderBy('nombres')->get(),
            ];
        }

        return view('livewire.movimientos.panel-computadores', array_merge([
            'movimientos' => $movimientos,
            'conteo'      => $conteo,
            'equipos'     => $this->equipos_filtrados,
        ], $catalogos));
    }

    // ── Lógica del Generador ─────────────────────────────────────────────

    public function getEquiposFiltradosProperty()
    {
        if (!$this->mostrando_generador || $this->paso_generador != 1) return [];

        return Computador::with(['marca', 'trabajador', 'departamento'])
            ->withCount(['movimientos as pendientes_count' => fn($q) => $q->where('estado_workflow', 'pendiente')])
            ->when($this->searchBN, fn($q) => $q->where('bien_nacional', 'like', "%{$this->searchBN}%"))
            ->when($this->searchSerial, fn($q) => $q->where('serial', 'like', "%{$this->searchSerial}%"))
            ->when($this->searchDpto, fn($q) => $q->where('departamento_id', $this->searchDpto))
            ->when($this->searchTrabajador, fn($q) => $q->where('trabajador_id', $this->searchTrabajador))
            ->latest()
            ->limit(10)
            ->get();
    }

    public function abrirGenerador()
    {
        $this->mostrando_generador = true;
        $this->paso_generador = 1;
        $this->reset([
            'searchBN', 'searchSerial', 'searchDpto', 'searchTrabajador', 'computador_id', 'justificacion',
            'creando_marca', 'nueva_marca', 'creando_so', 'nuevo_so', 'creando_procesador', 
            'nuevo_procesador_modelo', 'nuevo_procesador_marca_id', 'creando_gpu', 
            'nueva_gpu_modelo', 'nueva_gpu_marca_id', 'creando_departamento', 'nuevo_departamento'
        ]);
        $this->dispatch('abrir-modal', id: 'modalGenerador');
    }

    public function seleccionarEquipo($id)
    {
        $comp = Computador::with(['discos', 'rams', 'puertos'])->findOrFail($id);
        $this->computador_actual = $comp;
        $this->computador_id = $id;

        // Cargar datos al formulario
        $this->nombre_equipo   = $comp->nombre_equipo;
        $this->bien_nacional   = $comp->bien_nacional;
        $this->serial          = $comp->serial;
        $this->marca_id        = $comp->marca_id;
        $this->tipo_computador = $comp->tipo_computador;
        $this->sistema_operativo_id = $comp->sistema_operativo_id;
        $this->procesador_id   = $comp->procesador_id;
        $this->gpu_id          = $comp->gpu_id;
        $this->unidad_dvd      = (bool)$comp->unidad_dvd;
        $this->fuente_poder    = (bool)$comp->fuente_poder;
        $this->mac             = $comp->mac;
        $this->ip              = $comp->ip;
        $this->tipo_conexion   = $comp->tipo_conexion;
        $this->estado_fisico   = $comp->estado_fisico;
        $this->departamento_id = $comp->departamento_id;
        $this->trabajador_id   = $comp->trabajador_id;
        $this->observaciones   = $comp->observaciones;
        $this->activo          = (bool)$comp->activo;
        $this->tipo_ram        = $comp->tipo_ram;

        $this->discos = $comp->discos->map(fn($d) => ['capacidad' => (int)filter_var($d->capacidad, FILTER_SANITIZE_NUMBER_INT), 'tipo' => $d->tipo])->toArray();
        $this->rams   = $comp->rams->map(fn($r) => ['capacidad' => (int)filter_var($r->capacidad, FILTER_SANITIZE_NUMBER_INT), 'slot' => $r->slot])->toArray();
        $this->puertos_seleccionados = $comp->puertos->pluck('id')->toArray();

        $this->paso_generador = 2;
    }

    public function guardarNuevoMovimiento()
    {
        $this->validate([
            'nombre_equipo' => 'required|string|max:15',
            'bien_nacional' => 'required|string|unique:computadores,bien_nacional,' . $this->computador_id,
            'serial'        => 'required|string|unique:computadores,serial,' . $this->computador_id,
            'justificacion' => 'required|string|min:10',
        ]);

        try {
            // Resolución de Creación Rápida
            if ($this->creando_marca && !empty($this->nueva_marca)) {
                $m = \App\Models\Marca::firstOrCreate(['nombre' => $this->nueva_marca], ['activo' => true]);
                $this->marca_id = $m->id;
            }
            if ($this->creando_so && !empty($this->nuevo_so)) {
                $so = \App\Models\SistemaOperativo::firstOrCreate(['nombre' => $this->nuevo_so], ['activo' => true]);
                $this->sistema_operativo_id = $so->id;
            }
            if ($this->creando_procesador && !empty($this->nuevo_procesador_modelo)) {
                $pr = \App\Models\Procesador::firstOrCreate([
                    'modelo' => $this->nuevo_procesador_modelo,
                    'marca_id' => $this->nuevo_procesador_marca_id
                ], ['activo' => true]);
                $this->procesador_id = $pr->id;
            }
            if ($this->creando_gpu && !empty($this->nueva_gpu_modelo)) {
                $g = \App\Models\Gpu::firstOrCreate([
                    'modelo' => $this->nueva_gpu_modelo,
                    'marca_id' => $this->nueva_gpu_marca_id
                ], ['activo' => true]);
                $this->gpu_id = $g->id;
            }
            if ($this->creando_departamento && !empty($this->nuevo_departamento)) {
                $d = \App\Models\Departamento::firstOrCreate(['nombre' => $this->nuevo_departamento], ['activo' => true]);
                $this->departamento_id = $d->id;
            }

            $comp = $this->computador_actual ?: Computador::with(['discos', 'rams', 'puertos'])->findOrFail($this->computador_id);
            $payloadAnterior = $comp->toArray();
            $payloadAnterior['discos']  = $comp->discos->toArray();
            $payloadAnterior['rams']    = $comp->rams->toArray();
            $payloadAnterior['puertos'] = $comp->puertos->pluck('id')->toArray();

            $payloadNuevo = [
                'nombre_equipo' => $this->nombre_equipo,
                'bien_nacional' => $this->bien_nacional,
                'serial' => $this->serial,
                'marca_id' => $this->marca_id,
                'tipo_computador' => $this->tipo_computador,
                'sistema_operativo_id' => $this->sistema_operativo_id,
                'procesador_id' => $this->procesador_id,
                'gpu_id' => $this->gpu_id ?: null,
                'unidad_dvd' => $this->unidad_dvd,
                'fuente_poder' => $this->fuente_poder,
                'mac' => $this->mac,
                'ip' => $this->ip,
                'tipo_conexion' => $this->tipo_conexion,
                'estado_fisico' => $this->estado_fisico,
                'departamento_id' => $this->departamento_id ?: null,
                'trabajador_id' => $this->trabajador_id ?: null,
                'observaciones' => $this->observaciones,
                'activo' => $this->activo,
                'tipo_ram' => $this->tipo_ram,
                'discos' => $this->discos,
                'rams' => $this->rams,
                'puertos' => $this->puertos_seleccionados,
            ];

            // Determinar cambios (Diff)
            $cambios = [];
            foreach ($payloadNuevo as $k => $v) {
                $ant = $payloadAnterior[$k] ?? null;
                $iguales = is_array($v) 
                    ? $this->_sonIgualesArrays($ant, $v) 
                    : ((string)($ant ?? '') === (string)($v ?? ''));
                if (!$iguales) $cambios[$k] = $v;
            }

            if (empty($cambios)) {
                $this->dispatch('mostrar-toast', mensaje: 'No se detectaron cambios.', tipo: 'info');
                return;
            }

            if (Gate::allows('movimientos-computadores-ejecutar-directo')) {
                // Ejecución Directa (Bypass Workflow)
                $this->_aplicarPayload($comp, $cambios);

                MovimientoComputador::create([
                    'computador_id'   => $this->computador_id,
                    'tipo_operacion'  => 'actualizacion_datos',
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $cambios,
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'   => $this->justificacion,
                    'solicitante_id'  => Auth::id(),
                    'aprobador_id'    => Auth::id(),
                    'aprobado_at'     => now(),
                ]);

                $this->dispatch('mostrar-toast', mensaje: 'Movimiento ejecutado directamente.', tipo: 'success');
            } else {
                // Flujo Estándar (Crear Borrador)
                MovimientoComputador::create([
                    'computador_id'   => $this->computador_id,
                    'tipo_operacion'  => 'actualizacion_datos',
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $cambios,
                    'estado_workflow'  => 'borrador',
                    'justificacion'   => $this->justificacion,
                    'solicitante_id'  => Auth::id(),
                ]);

                $this->dispatch('mostrar-toast', mensaje: 'Movimiento creado como borrador.', tipo: 'success');
            }

            $this->mostrando_generador = false;
            $this->dispatch('cerrar-modal', id: 'modalGenerador');
            $this->resetPage(); // Refrescar lista
        } catch (\Exception $e) {
            Log::error('Error creando movimiento desde panel: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al crear el movimiento.', tipo: 'error');
        }
    }

    // ── Helpers de Formulario ────────────────────────────────────────────

    public function addDisco() { $this->discos[] = ['capacidad' => '', 'tipo' => '']; }
    public function removeDisco($index) { unset($this->discos[$index]); $this->discos = array_values($this->discos); }
    public function addRam() { 
        if (count($this->rams) < 6) {
            $this->rams[] = ['capacidad' => '', 'slot' => count($this->rams) + 1]; 
        }
    }
    public function removeRam($index) { 
        unset($this->rams[$index]); $this->rams = array_values($this->rams); 
        foreach($this->rams as $i => $r) $this->rams[$i]['slot'] = $i + 1;
    }

    private function _sonIgualesArrays($ant, $nuevo) {
        if (!is_array($ant)) return false;
        // Simplificado para comparación básica de arrays de discos/rams
        return json_encode($ant) === json_encode($nuevo);
    }

    // ── Acciones sobre Borradores Propios ────────────────────────────────

    public function abrirEdicionBorrador(int $id): void
    {
        $mov = MovimientoComputador::where('id', $id)
            ->where('solicitante_id', Auth::id())
            ->where('estado_workflow', 'borrador')
            ->firstOrFail();
        $this->editando_borrador_id = $id;
        $this->edit_justificacion   = $mov->justificacion ?? '';
        $this->dispatch('abrir-modal', id: 'modalEditarBorrador');
    }

    public function guardarEdicionBorrador(): void
    {
        $this->validate(['edit_justificacion' => 'required|string|min:10']);
        try {
            MovimientoComputador::where('id', $this->editando_borrador_id)
                ->where('solicitante_id', Auth::id())
                ->where('estado_workflow', 'borrador')
                ->firstOrFail()
                ->update(['justificacion' => $this->edit_justificacion]);

            $this->editando_borrador_id = null;
            $this->edit_justificacion   = '';
            $this->dispatch('cerrar-modal', id: 'modalEditarBorrador');
            $this->dispatch('mostrar-toast', mensaje: 'Borrador actualizado.', tipo: 'success');
        } catch (\Exception $e) {
            $this->dispatch('mostrar-toast', mensaje: 'Error al actualizar el borrador.', tipo: 'error');
        }
    }

    public function eliminarBorrador(int $id): void
    {
        try {
            MovimientoComputador::where('id', $id)
                ->where('solicitante_id', Auth::id())
                ->where('estado_workflow', 'borrador')
                ->firstOrFail()
                ->delete();
            $this->dispatch('mostrar-toast', mensaje: 'Borrador eliminado.', tipo: 'warning');
        } catch (\Exception $e) {
            $this->dispatch('mostrar-toast', mensaje: 'Error al eliminar el borrador.', tipo: 'error');
        }
    }

    // ── Acciones del Técnico ───────────────────────────────────────────────

    public function enviarARevision(int $id): void
    {
        abort_if(Gate::denies('movimientos-computadores-enviar'), 403);
        try {
            $mov = MovimientoComputador::where('id', $id)
                ->where('solicitante_id', Auth::id())
                ->where('estado_workflow', 'borrador')
                ->firstOrFail();

            $mov->update(['estado_workflow' => 'pendiente']);
            $this->dispatch('mostrar-toast', mensaje: 'Movimiento enviado a revisión.', tipo: 'success');
        } catch (\Exception $e) {
            Log::error('Error enviando movimiento a revisión: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al enviar a revisión.', tipo: 'error');
        }
    }

    public function verDetalle(int $id): void
    {
        abort_if(Gate::denies('movimientos-computadores-ver'), 403);
        $this->movimiento_detalle = MovimientoComputador::with([
            'computador.marca', 'computador.sistemaOperativo',
            'computador.departamento', 'computador.trabajador',
            'solicitante', 'aprobador'
        ])->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    // ── Acciones del Aprobador ────────────────────────────────────────────

    public function aprobar(int $id): void
    {
        abort_if(Gate::denies('movimientos-computadores-aprobar'), 403);
        try {
            $mov = MovimientoComputador::where('estado_workflow', 'pendiente')->findOrFail($id);
            $payload = $mov->payload_nuevo;

            $computador = Computador::withTrashed()->findOrFail($mov->computador_id);

            // Ejecutar la operación según tipo
            match ($mov->tipo_operacion) {
                'baja' => $computador->delete(),
                'toggle_activo' => tap($computador)->update(['activo' => $payload['activo'] ?? !$computador->activo]),
                default => $this->_aplicarPayload($computador, $payload),
            };

            $mov->update([
                'estado_workflow' => 'aprobado',
                'aprobador_id'   => Auth::id(),
                'aprobado_at'    => now(),
            ]);

            $this->dispatch('mostrar-toast', mensaje: 'Movimiento aprobado y aplicado.', tipo: 'success');
        } catch (\Exception $e) {
            Log::error('Error aprobando movimiento: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al aprobar.', tipo: 'error');
        }
    }

    public function abrirRechazo(int $id): void
    {
        abort_if(Gate::denies('movimientos-computadores-rechazar'), 403);
        $this->rechazando_id = $id;
        $this->motivo_rechazo = '';
        $this->dispatch('abrir-modal', id: 'modalRechazo');
    }

    public function confirmarRechazo(): void
    {
        abort_if(Gate::denies('movimientos-computadores-rechazar'), 403);
        $this->validate(['motivo_rechazo' => 'required|string|min:10']);
        try {
            $mov = MovimientoComputador::where('estado_workflow', 'pendiente')
                ->findOrFail($this->rechazando_id);

            $mov->update([
                'estado_workflow' => 'rechazado',
                'motivo_rechazo'  => $this->motivo_rechazo,
                'aprobador_id'    => Auth::id(),
                'aprobado_at'     => now(),
            ]);

            $this->rechazando_id = null;
            $this->motivo_rechazo = '';
            $this->dispatch('cerrar-modal', id: 'modalRechazo');
            $this->dispatch('mostrar-toast', mensaje: 'Movimiento rechazado.', tipo: 'warning');
        } catch (\Exception $e) {
            Log::error('Error rechazando movimiento: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al rechazar.', tipo: 'error');
        }
    }

    // ── Helper: Aplicar Payload al Computador ────────────────────────────

    private function _aplicarPayload(Computador $computador, array $payload): void
    {
        // Extraer sub-entidades antes de actualizar
        $discos  = $payload['discos']  ?? null;
        $rams    = $payload['rams']    ?? null;
        $puertos = $payload['puertos'] ?? null;

        // Campos columna directa
        $camposDirectos = array_diff_key($payload, array_flip(['discos', 'rams', 'puertos']));
        $computador->update($camposDirectos);

        if ($puertos !== null) {
            $computador->puertos()->sync($puertos);
        }

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

    // --- MÉTODOS PARA EL MODAL DE TRABAJADOR ---
    public function abrirModalTrabajador()
    {
        $this->dispatch('cerrar-modal', id: 'modalGeneradorComputadores');
        $this->dispatch('abrir-modal', id: 'modalTrabajador');
    }

    public function cancelarModalTrabajador()
    {
        $this->reset(['nuevo_trab_nombres', 'nuevo_trab_apellidos', 'nuevo_trab_cedula', 'nuevo_trab_departamento_id']);
        $this->dispatch('cerrar-modal', id: 'modalTrabajador');
        $this->dispatch('abrir-modal', id: 'modalGeneradorComputadores');
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
            $trab = \App\Models\Trabajador::create([
                'nombres' => $this->nuevo_trab_nombres,
                'apellidos' => $this->nuevo_trab_apellidos,
                'cedula' => $this->nuevo_trab_cedula,
                'departamento_id' => $this->nuevo_trab_departamento_id,
                'activo' => true
            ]);

            $this->trabajador_id = $trab->id;

            $this->reset(['nuevo_trab_nombres', 'nuevo_trab_apellidos', 'nuevo_trab_cedula', 'nuevo_trab_departamento_id']);
            $this->dispatch('cerrar-modal', id: 'modalTrabajador');
            $this->dispatch('abrir-modal', id: 'modalGeneradorComputadores');
            $this->dispatch('mostrar-toast', mensaje: 'Trabajador creado e importado.', tipo:'success');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en trabajador rápido Panel Computadores: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al registrar trabajador.', tipo:'error');
        }
    }
}
