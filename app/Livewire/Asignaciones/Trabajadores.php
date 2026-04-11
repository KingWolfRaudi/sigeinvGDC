<?php

namespace App\Livewire\Asignaciones;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Trabajador;
use App\Models\Departamento;

class Trabajadores extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    // Estado general
    public $search = '';
    public $sortField = 'id';
    public $sortAsc = false;
    public $tituloModal = 'Nuevo Trabajador';
    public $filtro_estado = 'todos';

    // Variables de formulario
    public $trabajador_id, $nombres, $apellidos, $cedula, $cargo, $departamento_id;
    public $activo = true;
    
    // Variables de Anidación
    public $presetFiltro = [];
    public $ocultarTitulos = false;

    public function mount($presetFiltro = [], $ocultarTitulos = false)
    {
        $this->presetFiltro = $presetFiltro;
        $this->ocultarTitulos = $ocultarTitulos;
    }

    // Variables para departamento rápido y detalle
    public $nuevo_departamento = '';
    public $creando_departamento = false;
    public $trabajador_detalle = null;

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
        }
        $this->sortField = $field;
    }

    public function render()
    {
        // 1. Iniciamos la consulta base con la relación
        $query = Trabajador::with('departamento');

        // 2. LÓGICA DE ESTADOS Y VISIBILIDAD (Data Scoping)
        if (\Illuminate\Support\Facades\Gate::allows('ver-estado-trabajadores')) {
            // Si tiene el permiso, aplicamos el filtro del select
            if ($this->filtro_estado === 'activos') {
                $query->where('activo', true);
            } elseif ($this->filtro_estado === 'inactivos') {
                $query->where('activo', false);
            }
        } else {
            // Si no tiene el permiso, solo puede ver a los activos
            $query->where('activo', true);
        }

        // Filtros Prediseñados (Cuando el componente se renderiza dentro de un Asociaciones Dashboard)
        if (!empty($this->presetFiltro)) {
            foreach($this->presetFiltro as $col => $val) {
                if ($val !== null) {
                    $query->where($col, $val);
                }
            }
        }

        // 3. Búsqueda profunda (Deep Search) Multiples tablas
        $query->where(function ($q) {
            $search = '%' . $this->search . '%';
            
            $q->where('nombres', 'like', $search)
            ->orWhere('apellidos', 'like', $search)
            ->orWhere('cedula', 'like', $search)
            ->orWhere('cargo', 'like', $search)
            ->orWhereHas('departamento', function ($subQ) use ($search) {
                $subQ->where('nombre', 'like', $search);
            })
            // Buscar por equipos que el trabajador tenga asignados
            ->orWhereHas('computadores', function ($subQ) use ($search) {
                $subQ->where('bien_nacional', 'like', $search)
                     ->orWhere('serial', 'like', $search);
            })
            ->orWhereHas('dispositivos', function ($subQ) use ($search) {
                $subQ->where('bien_nacional', 'like', $search)
                     ->orWhere('serial', 'like', $search);
            });
        });

        // 4. Ejecución de la consulta con orden y paginación
        $trabajadores = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                            ->paginate(10);

        // Catálogo para el select del formulario
        $departamentos = Departamento::orderBy('nombre', 'asc')->get();

        return view('livewire.asignaciones.trabajadores', compact('trabajadores', 'departamentos'));
    }

    public function crear()
    {
        $this->resetCampos();
        $this->tituloModal = 'Nuevo Trabajador';
    }

    public function ver($id)
    {
        $this->trabajador_detalle = Trabajador::with('departamento')->findOrFail($id);
    }

    public function editar($id)
    {
        $this->resetCampos();
        $this->tituloModal = 'Editar Trabajador';
        $trabajador = Trabajador::findOrFail($id);
        
        $this->trabajador_id = $trabajador->id;
        $this->nombres = $trabajador->nombres;
        $this->apellidos = $trabajador->apellidos;
        $this->cedula = $trabajador->cedula;
        $this->cargo = $trabajador->cargo;
        $this->departamento_id = $trabajador->departamento_id;
        $this->activo = $trabajador->activo;
    }

    public function guardarDepartamento()
    {
        $this->validate([
            'nuevo_departamento' => 'required|unique:departamentos,nombre|min:3'
        ]);

        // Ya NO forzamos strtoupper() aquí
        $dep = Departamento::create([
            'nombre' => $this->nuevo_departamento
        ]);

        $this->departamento_id = $dep->id;
        $this->creando_departamento = false;
        $this->nuevo_departamento = '';
        
        $this->dispatch('toast', mensaje: 'Departamento creado', tipo: 'success');
    }

    public function guardar()
    {
        $this->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            
            // CAMBIO AQUÍ: Cambiamos 'required' por 'nullable'
            'cedula' => 'nullable|string|unique:trabajadores,cedula,' . $this->trabajador_id,
            
            'departamento_id' => 'required|exists:departamentos,id',
        ]);

        // Ya NO forzamos strtoupper() en los datos personales
        Trabajador::updateOrCreate(
            ['id' => $this->trabajador_id],
            [
                'nombres' => $this->nombres,
                'apellidos' => $this->apellidos,
                'cedula' => $this->cedula,
                'cargo' => $this->cargo,
                'departamento_id' => $this->departamento_id,
                'activo' => $this->activo,
            ]
        );

        $this->resetCampos();
        $this->dispatch('cerrar-modal'); 
        $this->dispatch('toast', mensaje: 'Trabajador guardado exitosamente', tipo: 'success');
    }

    public function toggleActivo($id)
    {
        abort_if(\Illuminate\Support\Facades\Gate::denies('cambiar-estatus-trabajadores'), 403);
        $trabajador = Trabajador::findOrFail($id);
        $trabajador->activo = !$trabajador->activo;
        $trabajador->save();
        $this->dispatch('toast', mensaje: 'Estado actualizado', tipo: 'success');
    }

    public function eliminar($id)
    {
        $trabajador = Trabajador::findOrFail($id);
        $trabajador->delete();
        $this->dispatch('toast', mensaje: 'Trabajador eliminado', tipo: 'success');
    }

    public function resetCampos()
    {
        $this->reset(['trabajador_id', 'nombres', 'apellidos', 'cedula', 'cargo', 'departamento_id', 'nuevo_departamento', 'creando_departamento', 'trabajador_detalle']);
        $this->activo = true;
        $this->resetValidation();
    }
}