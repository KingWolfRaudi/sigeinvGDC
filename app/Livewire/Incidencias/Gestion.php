<?php

namespace App\Livewire\Incidencias;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Incidencia;
use App\Models\Departamento;
use App\Models\Trabajador;
use App\Models\Problema;
use App\Models\Configuracion;
use App\Models\User;
use App\Models\Computador;
use App\Models\Dispositivo;
use App\Models\Insumo;
use Illuminate\Support\Facades\Auth;

class Gestion extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Listado y Filtros
    public $search = '';
    public $filtro_departamento = '';
    public $filtro_estado = '';
    public $sortField = 'created_at';
    public $sortAsc = false;
    public $presetFiltro = [];
    public $ocultarTitulos = false;

    // Formulario de Creación/Edición
    public $incidencia_id;
    public $departamento_id, $trabajador_id, $problema_id, $user_id; // user_id es el técnico
    public $modelo_type, $modelo_id;
    public $descripcion, $notas;
    public $solventado = false;
    public $cerrado = false;

    // Listas dinámicas para el formulario
    public $trabajadores = [];
    public $activos = [];
    public $tecnicos = [];

    // Propiedades de configuración
    public $cierre_irreversible = false;
    public $activo_obligatorio = false;

    public function mount($presetFiltro = [], $ocultarTitulos = false)
    {
        $this->presetFiltro = $presetFiltro;
        $this->ocultarTitulos = $ocultarTitulos;

        // Aplicar filtros iniciales
        if (isset($this->presetFiltro['departamento_id'])) {
            $this->filtro_departamento = $this->presetFiltro['departamento_id'];
        }

        $this->tecnicos = $this->obtenerTecnicos();
        
        $configCierre = Configuracion::where('clave', 'incidencias_cierre_irreversible')->first();
        $this->cierre_irreversible = $configCierre ? (bool)$configCierre->valor : false;

        $configActivo = Configuracion::where('clave', 'incidencias_activo_obligatorio')->first();
        $this->activo_obligatorio = $configActivo ? (bool)$configActivo->valor : false;
    }

    public function obtenerTecnicos()
    {
        $configRoles = Configuracion::where('clave', 'incidencias_roles_tecnicos')->first();
        $rolesNombres = $configRoles ? json_decode($configRoles->valor, true) : ['administrador', 'personal-ti'];

        return User::role($rolesNombres)->where('activo', true)->get();
    }

    // --- CASCADING DROPDOWNS ---

    public function updatedDepartamentoId($value)
    {
        $this->trabajadores = Trabajador::where('departamento_id', $value)->where('activo', true)->get();
        $this->trabajador_id = null;
        $this->activos = [];
        $this->modelo_id = null;
    }

    public function updatedTrabajadorId($value)
    {
        $this->cargarActivos();
    }

    public function updatedModeloType($value)
    {
        $this->cargarActivos();
    }

    public function cargarActivos()
    {
        if (!$this->departamento_id || !$this->modelo_type) {
            $this->activos = [];
            return;
        }

        $query = null;
        if ($this->modelo_type === Computador::class) {
            $query = Computador::where('departamento_id', $this->departamento_id);
            if ($this->trabajador_id) $query->where('trabajador_id', $this->trabajador_id);
        } elseif ($this->modelo_type === Dispositivo::class) {
            $query = Dispositivo::where('departamento_id', $this->departamento_id);
            if ($this->trabajador_id) $query->where('trabajador_id', $this->trabajador_id);
        } elseif ($this->modelo_type === Insumo::class) {
            // Los insumos no suelen tener trabajador_id directo, así que mostramos los del depto o todos si no hay relación clara
            $query = Insumo::query(); // Ajustar si Insumo tuviera depto_id
        }

        $this->activos = $query ? $query->where('activo', true)->get() : [];
        $this->modelo_id = null;
    }

    // --- CRUD ---

    public function guardar()
    {
        $rules = [
            'departamento_id' => 'required',
            'problema_id' => 'required',
            'user_id' => 'required', // Técnico
            'descripcion' => 'required|min:10',
        ];

        if ($this->activo_obligatorio) {
            $rules['modelo_type'] = 'required';
            $rules['modelo_id'] = 'required';
        }

        $this->validate($rules);

        Incidencia::updateOrCreate(
            ['id' => $this->incidencia_id],
            [
                'problema_id' => $this->problema_id,
                'departamento_id' => $this->departamento_id,
                'trabajador_id' => $this->trabajador_id,
                'user_id' => $this->user_id,
                'modelo_type' => $this->modelo_type ?: null,
                'modelo_id' => $this->modelo_id ?: null,
                'descripcion' => $this->descripcion,
                'notas' => $this->notas,
                'solventado' => $this->solventado,
                'cerrado' => $this->cerrado,
            ]
        );

        $this->dispatch('mostrar-toast', mensaje: $this->incidencia_id ? 'Incidencia actualizada.' : 'Incidencia registrada con éxito.', tipo: 'success');
        $this->resetForm();
        $this->dispatch('cerrar-modal', id: 'modalIncidencia');
    }

    public function editar($id)
    {
        $inc = Incidencia::findOrFail($id);
        
        if ($inc->cerrado && !\Illuminate\Support\Facades\Auth::user()->can('admin-incidencias')) {
            $this->dispatch('mostrar-toast', mensaje: 'Esta incidencia está cerrada y no se puede editar.', tipo: 'danger');
            return;
        }

        $this->incidencia_id = $inc->id;
        $this->problema_id = $inc->problema_id;
        $this->departamento_id = $inc->departamento_id;
        $this->trabajador_id = $inc->trabajador_id;
        $this->user_id = $inc->user_id;
        $this->modelo_type = $inc->modelo_type;
        $this->modelo_id = $inc->modelo_id;
        $this->descripcion = $inc->descripcion;
        $this->notas = $inc->notas;
        $this->solventado = $inc->solventado;
        $this->cerrado = $inc->cerrado;

        // Cargar listas dependientes
        $this->trabajadores = Trabajador::where('departamento_id', $this->departamento_id)->get();
        $this->cargarActivos();

        $this->dispatch('abrir-modal', id: 'modalIncidencia');
    }

    public function resetForm()
    {
        $this->reset([
            'incidencia_id', 'departamento_id', 'trabajador_id', 'problema_id', 
            'user_id', 'modelo_type', 'modelo_id', 'descripcion', 'notas', 
            'solventado', 'cerrado', 'trabajadores', 'activos'
        ]);
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

    public function render()
    {
        $incidencias = Incidencia::with(['problema', 'departamento', 'trabajador', 'tecnico', 'modelo'])
            ->where(function($query) {
                $query->where('descripcion', 'like', '%' . $this->search . '%')
                      ->orWhereHas('trabajador', function($q) {
                          $q->where('nombres', 'like', '%' . $this->search . '%')
                            ->orWhere('apellidos', 'like', '%' . $this->search . '%');
                      });
            });

        if ($this->filtro_departamento) {
            $incidencias->where('departamento_id', $this->filtro_departamento);
        }

        if ($this->filtro_estado === 'abierto') {
            $incidencias->where('cerrado', false);
        } elseif ($this->filtro_estado === 'cerrado') {
            $incidencias->where('cerrado', true);
        } elseif ($this->filtro_estado === 'solventado') {
            $incidencias->where('solventado', true);
        }

        // Filtros de Preset (Asociaciones)
        if (isset($this->presetFiltro['trabajador_id'])) {
            $incidencias->where('trabajador_id', $this->presetFiltro['trabajador_id']);
        }
        if (isset($this->presetFiltro['modelo_type']) && isset($this->presetFiltro['modelo_id'])) {
            $incidencias->where('modelo_type', $this->presetFiltro['modelo_type'])
                        ->where('modelo_id', $this->presetFiltro['modelo_id']);
        }

        $incidencias = $incidencias->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                                   ->paginate(10);

        return view('livewire.incidencias.gestion', [
            'incidencias' => $incidencias,
            'departamentos' => Departamento::where('activo', true)->orderBy('nombre')->get(),
            'problemas' => Problema::where('activo', true)->orderBy('nombre')->get(),
        ])->layout('components.layouts.app');
    }
}
