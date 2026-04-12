<?php

namespace App\Livewire\Movimientos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SolicitudPerfil;
use Illuminate\Support\Facades\Auth;

use App\Exports\SolicitudesPerfilExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class SolicitudesPerfil extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filtro_estado = 'pendiente';

    // Para rechazar
    public $motivo_rechazo = '';
    public $solicitud_id;

    public function aprobar($id)
    {
        $sol = SolicitudPerfil::findOrFail($id);
        $user = $sol->user;

        if ($sol->tipo === 'nombre') $user->name = $sol->valor_nuevo;
        if ($sol->tipo === 'username') $user->username = $sol->valor_nuevo;
        if ($sol->tipo === 'email') $user->email = $sol->valor_nuevo;
        if ($sol->tipo === 'password') $user->password = $sol->valor_nuevo;

        $user->save();

        $sol->update([
            'estado' => 'aprobado',
            'revisado_por' => Auth::id()
        ]);

        $this->dispatch('mostrar-toast', mensaje: 'Solicitud aprobada y aplicada con éxito.', tipo: 'success');
    }

    public function modalRechazar($id)
    {
        $this->solicitud_id = $id;
        $this->motivo_rechazo = '';
        $this->dispatch('abrir-modal', id: 'modalRechazo');
    }

    public function rechazar()
    {
        $this->validate([
            'motivo_rechazo' => 'required|min:5'
        ]);

        $sol = SolicitudPerfil::findOrFail($this->solicitud_id);
        
        $sol->update([
            'estado' => 'rechazado',
            'motivo_rechazo' => $this->motivo_rechazo,
            'revisado_por' => Auth::id()
        ]);

        $this->dispatch('mostrar-toast', mensaje: 'Solicitud rechazada.', tipo: 'info');
        $this->dispatch('cerrar-modal', id: 'modalRechazo');
    }

    public function getQueryProperty()
    {
        return SolicitudPerfil::with(['user', 'revisor'])
            ->when($this->filtro_estado !== 'todos', function($q) {
                return $q->where('estado', $this->filtro_estado);
            })
            ->whereHas('user', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('username', 'like', '%' . $this->search . '%');
            })
            ->latest();
    }

    public function exportExcel()
    {
        return Excel::download(new SolicitudesPerfilExport($this->query), 'solicitudes_perfil_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportPDF()
    {
        $solicitudes = $this->query->get();
        $pdf = Pdf::loadView('reports.pdf_solicitudes_perfil', [
            'solicitudes' => $solicitudes,
            'filtro' => $this->filtro_estado
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'solicitudes_perfil_' . now()->format('Ymd_His') . '.pdf');
    }

    public function render()
    {
        $solicitudes = $this->query->paginate(10);

        return view('livewire.movimientos.solicitudes-perfil', [
            'solicitudes' => $solicitudes
        ])->layout('components.layouts.app');
    }
}
