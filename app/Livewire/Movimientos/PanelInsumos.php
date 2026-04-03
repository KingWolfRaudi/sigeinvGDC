<?php

namespace App\Livewire\Movimientos;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\MovimientoInsumo;
use App\Models\Insumo;

class PanelInsumos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $pestana = 'borradores';
    public string $search = '';
    public string $filtro_tipo = '';

    public ?int $rechazando_id = null;
    public string $motivo_rechazo = '';
    public $movimiento_detalle = null;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPestana() { $this->resetPage(); }

    public function render()
    {
        abort_if(Gate::denies('movimientos-insumos-ver'), 403);

        $query = MovimientoInsumo::with(['insumo.marca', 'insumo.categoriaInsumo', 'solicitante', 'aprobador'])
            ->when($this->search, function ($q) {
                $q->whereHas('insumo', function ($sub) {
                    $sub->where('nombre', 'like', '%' . $this->search . '%')
                        ->orWhere('bien_nacional', 'like', '%' . $this->search . '%')
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
            'borradores' => MovimientoInsumo::where('estado_workflow', 'borrador')
                               ->where('solicitante_id', Auth::id())->count(),
            'pendientes' => MovimientoInsumo::where('estado_workflow', 'pendiente')->count(),
        ];

        return view('livewire.movimientos.panel-insumos', compact('movimientos', 'conteo'));
    }

    public function enviarARevision(int $id): void
    {
        abort_if(Gate::denies('movimientos-insumos-enviar'), 403);
        try {
            $mov = MovimientoInsumo::where('id', $id)
                ->where('solicitante_id', Auth::id())
                ->where('estado_workflow', 'borrador')
                ->firstOrFail();
            $mov->update(['estado_workflow' => 'pendiente']);
            $this->dispatch('mostrar-toast', mensaje: 'Movimiento enviado a revisión.', tipo: 'success');
        } catch (\Exception $e) {
            Log::error('Error enviando movimiento insumo a revisión: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al enviar a revisión.', tipo: 'error');
        }
    }

    public function verDetalle(int $id): void
    {
        abort_if(Gate::denies('movimientos-insumos-ver'), 403);
        $this->movimiento_detalle = MovimientoInsumo::with([
            'insumo.marca', 'insumo.categoriaInsumo', 'solicitante', 'aprobador'
        ])->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function aprobar(int $id): void
    {
        abort_if(Gate::denies('movimientos-insumos-aprobar'), 403);
        try {
            $mov = MovimientoInsumo::where('estado_workflow', 'pendiente')->findOrFail($id);
            $payload = $mov->payload_nuevo;
            $insumo = Insumo::withTrashed()->findOrFail($mov->insumo_id);

            match ($mov->tipo_operacion) {
                'baja'         => $insumo->delete(),
                'toggle_activo' => tap($insumo)->update(['activo' => $payload['activo'] ?? !$insumo->activo]),
                'salida_consumo' => tap($insumo)->update([
                    'medida_actual' => max(0, $insumo->medida_actual - ($mov->cantidad_movida ?? 0))
                ]),
                'entrada_stock' => tap($insumo)->update([
                    'medida_actual' => $insumo->medida_actual + ($mov->cantidad_movida ?? 0)
                ]),
                'devolucion'   => tap($insumo)->update([
                    'medida_actual' => $insumo->medida_actual + ($mov->cantidad_movida ?? 0)
                ]),
                default        => $insumo->update($payload),
            };

            $mov->update([
                'estado_workflow' => 'aprobado',
                'aprobador_id'   => Auth::id(),
                'aprobado_at'    => now(),
            ]);

            $this->dispatch('mostrar-toast', mensaje: 'Movimiento aprobado y aplicado.', tipo: 'success');
        } catch (\Exception $e) {
            Log::error('Error aprobando movimiento insumo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al aprobar.', tipo: 'error');
        }
    }

    public function abrirRechazo(int $id): void
    {
        abort_if(Gate::denies('movimientos-insumos-rechazar'), 403);
        $this->rechazando_id = $id;
        $this->motivo_rechazo = '';
        $this->dispatch('abrir-modal', id: 'modalRechazo');
    }

    public function confirmarRechazo(): void
    {
        abort_if(Gate::denies('movimientos-insumos-rechazar'), 403);
        $this->validate(['motivo_rechazo' => 'required|string|min:10']);
        try {
            $mov = MovimientoInsumo::where('estado_workflow', 'pendiente')
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
            Log::error('Error rechazando movimiento insumo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al rechazar.', tipo: 'error');
        }
    }
}
