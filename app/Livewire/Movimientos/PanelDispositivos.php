<?php

namespace App\Livewire\Movimientos;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\MovimientoDispositivo;
use App\Models\Dispositivo;
use App\Models\Marca;
use App\Models\TipoDispositivo;
use App\Models\Departamento;
use App\Models\Trabajador;
use App\Models\Computador;
use App\Models\Puerto;

class PanelDispositivos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $pestana = 'borradores';
    public string $search = '';
    public string $filtro_tipo = '';

    public ?int $rechazando_id = null;
    public string $motivo_rechazo = '';

    public $movimiento_detalle = null;

    // Edición de Borrador
    public ?int $editando_borrador_id = null;
    public string $edit_justificacion = '';

    // ── PROPIEDADES DEL GENERADOR DE MOVIMIENTOS ───────────────────────
    public bool $mostrando_generador = false;
    public int $paso_generador = 1; // 1: Selección, 2: Edición

    // Filtros de Selección (Paso 1)
    public $searchBN = '', $searchSerial = '', $searchDpto = '', $searchTrabajador = '';

    // Campos del Formulario (Paso 2)
    public $dispositivo_id, $bien_nacional, $serial, $nombre, $marca_id, $tipo_dispositivo_id;
    public $ip, $estado, $departamento_id, $trabajador_id, $computador_id, $notas;
    public bool $activo = true;
    public $justificacion = '';
    public $puertos_seleccionados = [];

    // Creación Rápida
    public bool $creando_marca = false; public $nueva_marca;
    public bool $creando_tipo = false; public $nuevo_tipo;
    public bool $creando_departamento = false; public $nuevo_departamento;

    // Trabajador On The Fly (Modal)
    public $nuevo_trab_nombres, $nuevo_trab_apellidos, $nuevo_trab_cedula, $nuevo_trab_departamento_id; 

    public $dispositivo_actual = null; // Instancia cargada para Step 2

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPestana() { $this->resetPage(); }

    public function updatedDepartamentoId($value)
    {
        $this->trabajador_id = null;
    }

    public function render()
    {
        abort_if(Gate::denies('movimientos-dispositivos-ver'), 403);

        $query = MovimientoDispositivo::with(['dispositivo.marca', 'solicitante', 'aprobador'])
            ->when($this->search, function ($q) {
                $q->whereHas('dispositivo', function ($sub) {
                    $sub->where('nombre', 'like', '%' . $this->search . '%')
                        ->orWhere('serial', 'like', '%' . $this->search . '%')
                        ->orWhere('bien_nacional', 'like', '%' . $this->search . '%');
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
            'borradores' => MovimientoDispositivo::where('estado_workflow', 'borrador')
                               ->where('solicitante_id', Auth::id())->count(),
            'pendientes' => MovimientoDispositivo::where('estado_workflow', 'pendiente')->count(),
        ];

        // Catálogos para el Generador (Step 2)
        $catalogos = [
            'marcas' => collect(),
            'tipos' => collect(),
            'departamentos' => collect(),
            'trabajadores' => collect(),
            'computadores' => collect(),
            'puertos' => collect(),
        ];

        if ($this->mostrando_generador) {
            $catalogos = [
                'marcas' => Marca::where('activo', true)->orderBy('nombre')->get(),
                'tipos' => TipoDispositivo::where('activo', true)->orderBy('nombre')->get(),
                'departamentos' => Departamento::where('activo', true)->orderBy('nombre')->get(),
                'trabajadores' => Trabajador::where('activo', true)
                    ->when($this->departamento_id, fn($q) => $q->where('departamento_id', $this->departamento_id))
                    ->orderBy('nombres')->get(),
                'computadores' => Computador::where('activo', true)->orderBy('nombre_equipo')->get(),
                'puertos' => Puerto::where('activo', true)->orderBy('nombre')->get(),
            ];
        }

        return view('livewire.movimientos.panel-dispositivos', array_merge([
            'movimientos' => $movimientos,
            'conteo'      => $conteo,
            'dispositivos_lista' => $this->dispositivos_filtrados,
        ], $catalogos));
    }

    public function getDispositivosFiltradosProperty()
    {
        if (!$this->mostrando_generador || $this->paso_generador != 1) return [];

        return Dispositivo::with(['marca', 'trabajador', 'departamento'])
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
        abort_if(Gate::denies('movimientos-dispositivos-crear'), 403);
        $this->mostrando_generador = true;
        $this->paso_generador = 1;
        $this->resetGenerador();
        $this->dispatch('abrir-modal', id: 'modalGeneradorDispositivos');
    }

    public function seleccionarDispositivo(int $id)
    {
        $this->dispositivo_actual = Dispositivo::with('puertos')->findOrFail($id);
        $this->dispositivo_id = $id;

        // Cargar datos al form
        $this->bien_nacional = $this->dispositivo_actual->bien_nacional;
        $this->serial = $this->dispositivo_actual->serial;
        $this->nombre = $this->dispositivo_actual->nombre;
        $this->marca_id = $this->dispositivo_actual->marca_id;
        $this->tipo_dispositivo_id = $this->dispositivo_actual->tipo_dispositivo_id;
        $this->ip = $this->dispositivo_actual->ip;
        $this->estado = $this->dispositivo_actual->estado;
        $this->departamento_id = $this->dispositivo_actual->departamento_id;
        $this->trabajador_id = $this->dispositivo_actual->trabajador_id;
        $this->computador_id = $this->dispositivo_actual->computador_id;
        $this->notas = $this->dispositivo_actual->notas;
        $this->activo = $this->dispositivo_actual->activo;
        $this->puertos_seleccionados = $this->dispositivo_actual->puertos->pluck('id')->toArray();

        $this->paso_generador = 2;
    }

    public function resetGenerador()
    {
        $this->reset([
            'searchBN', 'searchSerial', 'searchDpto', 'searchTrabajador',
            'dispositivo_id', 'bien_nacional', 'serial', 'nombre', 'marca_id', 'tipo_dispositivo_id',
            'ip', 'estado', 'departamento_id', 'trabajador_id', 'computador_id', 'notas',
            'activo', 'justificacion', 'puertos_seleccionados', 'dispositivo_actual',
            'nueva_marca', 'nuevo_tipo', 'nuevo_departamento'
        ]);
        $this->creando_marca = false;
        $this->creando_tipo = false;
        $this->creando_departamento = false;
        $this->paso_generador = 1;
    }

    public function guardarBorrador()
    {
        $this->validate([
            'justificacion' => 'required|min:10',
            'bien_nacional' => 'required',
            'serial' => 'required',
            'nombre' => 'required',
        ]);

        try {
            // Resolución de Creación Rápida
            if ($this->creando_marca && !empty($this->nueva_marca)) {
                $m = Marca::firstOrCreate(['nombre' => $this->nueva_marca], ['activo' => true]);
                $this->marca_id = $m->id;
            }
            if ($this->creando_tipo && !empty($this->nuevo_tipo)) {
                $t = TipoDispositivo::firstOrCreate(['nombre' => $this->nuevo_tipo], ['activo' => true]);
                $this->tipo_dispositivo_id = $t->id;
            }
            if ($this->creando_departamento && !empty($this->nuevo_departamento)) {
                $d = Departamento::firstOrCreate(['nombre' => $this->nuevo_departamento], ['activo' => true]);
                $this->departamento_id = $d->id;
            }

            // Lógica de Diff
            $payloadNuevo = [
                'bien_nacional' => $this->bien_nacional,
                'serial' => $this->serial,
                'nombre' => $this->nombre,
                'marca_id' => $this->marca_id,
                'tipo_dispositivo_id' => $this->tipo_dispositivo_id,
                'ip' => $this->ip,
                'estado' => $this->estado,
                'departamento_id' => $this->departamento_id,
                'trabajador_id' => $this->trabajador_id,
                'computador_id' => $this->computador_id,
                'notas' => $this->notas,
                'activo' => $this->activo,
                'puertos' => $this->puertos_seleccionados,
            ];

            $payloadAnterior = [
                'bien_nacional' => $this->dispositivo_actual->bien_nacional,
                'serial' => $this->dispositivo_actual->serial,
                'nombre' => $this->dispositivo_actual->nombre,
                'marca_id' => $this->dispositivo_actual->marca_id,
                'tipo_dispositivo_id' => $this->dispositivo_actual->tipo_dispositivo_id,
                'ip' => $this->dispositivo_actual->ip,
                'estado' => $this->dispositivo_actual->estado,
                'departamento_id' => $this->dispositivo_actual->departamento_id,
                'trabajador_id' => $this->dispositivo_actual->trabajador_id,
                'computador_id' => $this->dispositivo_actual->computador_id,
                'notas' => $this->dispositivo_actual->notas,
                'activo' => $this->dispositivo_actual->activo,
                'puertos' => $this->dispositivo_actual->puertos->pluck('id')->toArray(),
            ];

            // Filtrar solo los cambios
            $cambios = [];
            foreach ($payloadNuevo as $key => $value) {
                if ($key === 'puertos') {
                    if (array_diff($value, $payloadAnterior[$key]) || array_diff($payloadAnterior[$key], $value)) {
                        $cambios['puertos'] = $value;
                    }
                    continue;
                }
                if ($value != $payloadAnterior[$key]) {
                    $cambios[$key] = $value;
                }
            }

            if (empty($cambios)) {
                $this->dispatch('mostrar-toast', mensaje: 'No hay cambios detectados para registrar.', tipo: 'info');
                return;
            }

            if (Gate::allows('movimientos-dispositivos-ejecutar-directo')) {
                // Ejecución Directa (Bypass Workflow)
                $this->_aplicarPayload($this->dispositivo_actual, $cambios);

                MovimientoDispositivo::create([
                    'dispositivo_id'   => $this->dispositivo_id,
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
                MovimientoDispositivo::create([
                    'dispositivo_id'   => $this->dispositivo_id,
                    'tipo_operacion'  => 'actualizacion_datos',
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $cambios,
                    'estado_workflow'  => 'borrador',
                    'justificacion'   => $this->justificacion,
                    'solicitante_id'  => Auth::id(),
                ]);

                $this->dispatch('mostrar-toast', mensaje: 'Borrador de movimiento creado correctamente.', tipo: 'success');
            }

            $this->dispatch('cerrar-modal', id: 'modalGeneradorDispositivos');
            $this->mostrando_generador = false;
        } catch (\Exception $e) {
            Log::error('Error guardando borrador dispositivo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al guardar el borrador.', tipo: 'error');
        }
    }

    public function abrirEdicionBorrador(int $id): void
    {
        $mov = MovimientoDispositivo::where('id', $id)
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
            MovimientoDispositivo::where('id', $this->editando_borrador_id)
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
            MovimientoDispositivo::where('id', $id)
                ->where('solicitante_id', Auth::id())
                ->where('estado_workflow', 'borrador')
                ->firstOrFail()
                ->delete();
            $this->dispatch('mostrar-toast', mensaje: 'Borrador eliminado.', tipo: 'warning');
        } catch (\Exception $e) {
            $this->dispatch('mostrar-toast', mensaje: 'Error al eliminar el borrador.', tipo: 'error');
        }
    }

    public function enviarARevision(int $id): void
    {
        abort_if(Gate::denies('movimientos-dispositivos-enviar'), 403);
        try {
            $mov = MovimientoDispositivo::where('id', $id)
                ->where('solicitante_id', Auth::id())
                ->where('estado_workflow', 'borrador')
                ->firstOrFail();
            $mov->update(['estado_workflow' => 'pendiente']);
            $this->dispatch('mostrar-toast', mensaje: 'Movimiento enviado a revisión.', tipo: 'success');
        } catch (\Exception $e) {
            Log::error('Error enviando movimiento dispositivo a revisión: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al enviar a revisión.', tipo: 'error');
        }
    }

    public function verDetalle(int $id): void
    {
        abort_if(Gate::denies('movimientos-dispositivos-ver'), 403);
        $this->movimiento_detalle = MovimientoDispositivo::with([
            'dispositivo.marca', 'dispositivo.tipoDispositivo',
            'dispositivo.departamento', 'solicitante', 'aprobador'
        ])->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function aprobar(int $id): void
    {
        abort_if(Gate::denies('movimientos-dispositivos-aprobar'), 403);
        try {
            $mov = MovimientoDispositivo::where('estado_workflow', 'pendiente')->findOrFail($id);
            $payload = $mov->payload_nuevo;
            $dispositivo = Dispositivo::withTrashed()->findOrFail($mov->dispositivo_id);

            match ($mov->tipo_operacion) {
                'baja'         => $dispositivo->delete(),
                'toggle_activo' => tap($dispositivo)->update(['activo' => $payload['activo'] ?? !$dispositivo->activo]),
                default        => $this->_aplicarPayload($dispositivo, $payload),
            };

            $mov->update([
                'estado_workflow' => 'aprobado',
                'aprobador_id'   => Auth::id(),
                'aprobado_at'    => now(),
            ]);

            $this->dispatch('mostrar-toast', mensaje: 'Movimiento aprobado y aplicado.', tipo: 'success');
        } catch (\Exception $e) {
            Log::error('Error aprobando movimiento dispositivo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al aprobar.', tipo: 'error');
        }
    }

    public function abrirRechazo(int $id): void
    {
        abort_if(Gate::denies('movimientos-dispositivos-rechazar'), 403);
        $this->rechazando_id = $id;
        $this->motivo_rechazo = '';
        $this->dispatch('abrir-modal', id: 'modalRechazo');
    }

    public function confirmarRechazo(): void
    {
        abort_if(Gate::denies('movimientos-dispositivos-rechazar'), 403);
        $this->validate(['motivo_rechazo' => 'required|string|min:10']);
        try {
            $mov = MovimientoDispositivo::where('estado_workflow', 'pendiente')
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
            Log::error('Error rechazando movimiento dispositivo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al rechazar.', tipo: 'error');
        }
    }

    private function _aplicarPayload(Dispositivo $dispositivo, array $payload): void
    {
        $puertos = $payload['puertos'] ?? null;
        $camposDirectos = array_diff_key($payload, array_flip(['puertos']));
        $dispositivo->update($camposDirectos);
        if ($puertos !== null) {
            $dispositivo->puertos()->sync($puertos);
        }
    }

    // --- MÉTODOS PARA EL MODAL DE TRABAJADOR ---
    public function abrirModalTrabajador()
    {
        $this->dispatch('cerrar-modal', id: 'modalGeneradorDispositivos');
        $this->dispatch('abrir-modal', id: 'modalTrabajador');
    }

    public function cancelarModalTrabajador()
    {
        $this->reset(['nuevo_trab_nombres', 'nuevo_trab_apellidos', 'nuevo_trab_cedula', 'nuevo_trab_departamento_id']);
        $this->dispatch('cerrar-modal', id: 'modalTrabajador');
        $this->dispatch('abrir-modal', id: 'modalGeneradorDispositivos');
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
            $this->dispatch('abrir-modal', id: 'modalGeneradorDispositivos');
            $this->dispatch('mostrar-toast', mensaje: 'Trabajador creado e importado.', tipo:'success');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en trabajador rápido Panel Dispositivos: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al registrar trabajador.', tipo:'error');
        }
    }
}
