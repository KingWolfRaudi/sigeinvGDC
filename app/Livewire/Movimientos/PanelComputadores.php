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

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPestana() { $this->resetPage(); }

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

        return view('livewire.movimientos.panel-computadores', compact('movimientos', 'conteo'));
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
            'computador.marca', 'computador.tipoDispositivo',
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
}
