<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use App\Models\Software as SoftwareModel;
use App\Exports\SoftwareExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class Software extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Propiedades del formulario
    public $software_id;
    public $nombre_programa;
    public $arquitectura_programa = '';
    public $tipo_licencia = '';
    public $serial;
    public $descripcion_programa;
    public $activo = true;

    // Propiedades de la vista
    public $search = '';
    public $filtro_estado = 'todos';
    public $sortField = 'id';
    public $sortAsc = false;
    public $tituloModal = 'Nuevo Software';
    public $software_detalle = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        if (Gate::denies('ver-estado-software')) {
            $this->filtro_estado = 'activos';
        }
    }

    public function getQueryProperty()
    {
        $query = SoftwareModel::query();

        // Filtro de estado
        if ($this->filtro_estado === 'activos') {
            $query->where('activo', true);
        } elseif ($this->filtro_estado === 'inactivos') {
            $query->where('activo', false);
        }

        // Búsqueda profunda en todos los campos
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $search = '%' . $this->search . '%';
                $q->where('nombre_programa', 'like', $search)
                  ->orWhere('arquitectura_programa', 'like', $search)
                  ->orWhere('tipo_licencia', 'like', $search)
                  ->orWhere('serial', 'like', $search)
                  ->orWhere('descripcion_programa', 'like', $search);
            });
        }

        return $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc');
    }

    public function render()
    {
        $softwares = $this->query->paginate(10);
        return view('livewire.inventario.software', [
            'softwares' => $softwares
        ])->layout('components.layouts.app');
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-software'), 403);
        $this->resetCampos();
        $this->tituloModal = 'Nuevo Software';
        $this->dispatch('abrir-modal', id: 'modalSoftware');
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-software'), 403);
        $this->resetCampos();
        
        $software = SoftwareModel::findOrFail($id);
        $this->software_id = $software->id;
        $this->nombre_programa = $software->nombre_programa;
        $this->arquitectura_programa = $software->arquitectura_programa;
        $this->tipo_licencia = $software->tipo_licencia;
        $this->serial = $software->serial;
        $this->descripcion_programa = $software->descripcion_programa;
        $this->activo = $software->activo;

        $this->tituloModal = 'Editar Software';
        $this->dispatch('abrir-modal', id: 'modalSoftware');
    }

    public function guardar()
    {
        $esEdicion = (bool) $this->software_id;
        abort_if(Gate::denies($esEdicion ? 'editar-software' : 'crear-software'), 403);

        $this->validate([
            'nombre_programa' => 'required|string|max:100',
            'arquitectura_programa' => 'nullable|in:32bits,64bits,Universal',
            'tipo_licencia' => 'required|in:Libre,Privativo',
            'serial' => 'nullable|required_if:tipo_licencia,Privativo|string|max:50',
            'descripcion_programa' => 'nullable|string|max:250',
        ]);

        SoftwareModel::updateOrCreate(
            ['id' => $this->software_id],
            [
                'nombre_programa' => $this->nombre_programa,
                'arquitectura_programa' => empty($this->arquitectura_programa) ? null : $this->arquitectura_programa,
                'tipo_licencia' => $this->tipo_licencia,
                'serial' => $this->tipo_licencia === 'Libre' && empty($this->serial) ? null : $this->serial,
                'descripcion_programa' => empty($this->descripcion_programa) ? null : $this->descripcion_programa,
                'activo' => $this->activo
            ]
        );

        $mensaje = $esEdicion ? 'Software actualizado con éxito.' : 'Software registrado con éxito.';
        $this->dispatch('mostrar-toast', mensaje: $mensaje, tipo: 'success');
        $this->dispatch('cerrar-modal', id: 'modalSoftware');
        $this->resetCampos();
    }

    public function verDetalle($id)
    {
        $this->software_detalle = SoftwareModel::findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalleSoftware');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-software'), 403);
        $software = SoftwareModel::findOrFail($id);
        $software->activo = !$software->activo;
        $software->save();

        $estado = $software->activo ? 'activado' : 'inactivado';
        $this->dispatch('mostrar-toast', mensaje: "Software $estado con éxito.", tipo: 'success');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-software'), 403);
        $software = SoftwareModel::findOrFail($id);
        $software->delete(); // Uso de SoftDeletes
        
        $this->dispatch('mostrar-toast', mensaje: 'Software eliminado con éxito.', tipo: 'success');
    }

    public function resetCampos()
    {
        $this->reset([
            'software_id', 'nombre_programa', 'arquitectura_programa', 'tipo_licencia',
            'serial', 'descripcion_programa', 'software_detalle'
        ]);
        $this->activo = true;
        $this->resetValidation();
    }

    // --- Exportaciones (Requieren sus clases/vistas correspondientes) ---
    public function exportPDF($id)
    {
        abort_if(Gate::denies('reportes-pdf'), 403);
        $software = SoftwareModel::findOrFail($id);
        $pdf = Pdf::loadView('reports.pdf_software_ficha', [
            'software' => $software
        ]);

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'ficha_software_' . $software->id . '_' . now()->format('Ymd_His') . '.pdf');
    }
}
