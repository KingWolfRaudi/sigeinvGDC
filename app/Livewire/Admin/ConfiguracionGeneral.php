<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Problema;
use App\Models\Configuracion;
use App\Models\EspecialidadTecnica;
use Spatie\Permission\Models\Role;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Gate;

class ConfiguracionGeneral extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Tabs
    public $activeTab = 'incidencias-ajustes'; // 'incidencias-catalogo', 'incidencias-ajustes', 'perfil-ajustes'

    // Propiedades para Catálogo de Especialidades Técnicas
    public $especialidad_id, $nombre_especialidad;
    public $especialidad_activo = true;
    public $searchEspecialidad = '';
    public $sortFieldEspecialidad = 'nombre';
    public $sortAscEspecialidad = true;

    // Propiedades para Catálogo de Problemas (Incidencias)
    public $problema_id, $nombre_problema, $problema_especialidad_id;
    public $problema_activo = true;
    public $searchProblema = '';
    public $sortField = 'nombre';
    public $sortAsc = true;

    // Propiedades para Configuración de Incidencias
    public $cierre_irreversible = false;
    public $activo_obligatorio = false;
    public $dashboard_tecnico_ver_global = false;

    // Propiedades para Configuración de Perfil
    public $perfil_solicitar_nombre = true;
    public $perfil_solicitar_username = true;
    public $perfil_solicitar_email = true;
    public $perfil_solicitar_password = true;

    public function mount()
    {
        // Determinar pestaña inicial según permisos
        if (Gate::denies('admin-incidencias')) {
            $this->activeTab = 'perfil-ajustes';
        }

        // 1. Cargar Configuración de Incidencias
        $this->cargarConfigIncidencias();

        // 2. Cargar Configuración de Perfil
        $this->cargarConfigPerfil();
    }

    private function cargarConfigIncidencias()
    {

        $configCierre = Configuracion::where('clave', 'incidencias_cierre_irreversible')->first();
        if ($configCierre) $this->cierre_irreversible = (bool)$configCierre->valor;

        $configActivo = Configuracion::where('clave', 'incidencias_activo_obligatorio')->first();
        if ($configActivo) $this->activo_obligatorio = (bool)$configActivo->valor;

        $configDash = Configuracion::where('clave', 'dashboard_tecnico_ver_global')->first();
        if ($configDash) $this->dashboard_tecnico_ver_global = (bool)$configDash->valor;
    }

    private function cargarConfigPerfil()
    {
        $keys = ['nombre', 'username', 'email', 'password'];
        foreach ($keys as $key) {
            $prop = "perfil_solicitar_" . $key;
            $config = Configuracion::where('clave', 'perfil_solicitar_' . $key)->first();
            if ($config) {
                $this->$prop = (bool)$config->valor;
            }
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // --- LÓGICA DE PROBLEMAS (Incidencias) ---
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
            $this->sortField = $field;
        }
    }

    public function guardarProblema()
    {
        abort_if(Gate::denies('admin-incidencias'), 403);
        $this->validate([
            'nombre_problema' => 'required|min:3|unique:problemas,nombre,' . $this->problema_id,
            'problema_especialidad_id' => 'required|exists:especialidades_tecnicas,id',
        ]);

        Problema::updateOrCreate(
            ['id' => $this->problema_id],
            [
                'nombre' => $this->nombre_problema, 
                'activo' => $this->problema_activo,
                'especialidad_id' => $this->problema_especialidad_id
            ]
        );

        $this->dispatch('mostrar-toast', mensaje: $this->problema_id ? 'Tipo de incidencia actualizado.' : 'Nuevo tipo de incidencia creado.', tipo: 'success');
        $this->resetProblema();
        $this->dispatch('cerrar-modal', id: 'modalProblema');
    }

    public function editarProblema($id)
    {
        abort_if(Gate::denies('admin-incidencias'), 403);
        $problema = Problema::findOrFail($id);
        $this->problema_id = $problema->id;
        $this->nombre_problema = $problema->nombre;
        $this->problema_activo = $problema->activo;
        $this->problema_especialidad_id = $problema->especialidad_id;
        $this->dispatch('abrir-modal', id: 'modalProblema');
    }

    public function eliminarProblema($id)
    {
        abort_if(Gate::denies('admin-incidencias'), 403);
        $problema = Problema::findOrFail($id);
        if ($problema->incidencias()->count() > 0) {
            $this->dispatch('mostrar-toast', mensaje: 'No se puede eliminar un tipo de incidencia que ya tiene registros asociados.', tipo: 'danger');
            return;
        }
        $problema->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Tipo de incidencia eliminado.', tipo: 'success');
    }

    public function resetProblema()
    {
        $this->reset(['problema_id', 'nombre_problema', 'problema_activo', 'problema_especialidad_id']);
    }

    // --- LÓGICA DE ESPECIALIDADES TÉCNICAS ---
    public function sortByEspecialidad($field)
    {
        if ($this->sortFieldEspecialidad === $field) {
            $this->sortAscEspecialidad = !$this->sortAscEspecialidad;
        } else {
            $this->sortAscEspecialidad = true;
            $this->sortFieldEspecialidad = $field;
        }
    }

    public function guardarEspecialidad()
    {
        abort_if(Gate::denies('admin-incidencias'), 403);
        $this->validate([
            'nombre_especialidad' => 'required|min:3|unique:especialidades_tecnicas,nombre,' . $this->especialidad_id,
        ]);

        EspecialidadTecnica::updateOrCreate(
            ['id' => $this->especialidad_id],
            ['nombre' => $this->nombre_especialidad, 'activo' => $this->especialidad_activo]
        );

        $this->dispatch('mostrar-toast', mensaje: $this->especialidad_id ? 'Especialidad actualizada.' : 'Nueva Especialidad creada.', tipo: 'success');
        $this->resetEspecialidad();
        $this->dispatch('cerrar-modal', id: 'modalEspecialidad');
    }

    public function editarEspecialidad($id)
    {
        abort_if(Gate::denies('admin-incidencias'), 403);
        $especialidad = EspecialidadTecnica::findOrFail($id);
        $this->especialidad_id = $especialidad->id;
        $this->nombre_especialidad = $especialidad->nombre;
        $this->especialidad_activo = $especialidad->activo;
        $this->dispatch('abrir-modal', id: 'modalEspecialidad');
    }

    public function eliminarEspecialidad($id)
    {
        abort_if(Gate::denies('admin-incidencias'), 403);
        $especialidad = EspecialidadTecnica::findOrFail($id);
        if ($especialidad->problemas()->count() > 0 || $especialidad->usuarios()->count() > 0) {
            $this->dispatch('mostrar-toast', mensaje: 'No se puede eliminar una Especialidad que ya tiene problemas o usuarios asociados.', tipo: 'danger');
            return;
        }
        $especialidad->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Especialidad eliminada.', tipo: 'success');
    }

    public function resetEspecialidad()
    {
        $this->reset(['especialidad_id', 'nombre_especialidad', 'especialidad_activo']);
    }

    // --- LÓGICA DE GUARDADO DE CONFIGURACIÓN ---
    public function guardarConfigIncidencias()
    {
        abort_if(Gate::denies('admin-incidencias'), 403);

        Configuracion::updateOrCreate(['clave' => 'incidencias_cierre_irreversible'], ['valor' => $this->cierre_irreversible ? '1' : '0', 'grupo' => 'incidencias']);
        Configuracion::updateOrCreate(['clave' => 'incidencias_activo_obligatorio'], ['valor' => $this->activo_obligatorio ? '1' : '0', 'grupo' => 'incidencias']);
        Configuracion::updateOrCreate(['clave' => 'dashboard_tecnico_ver_global'], ['valor' => $this->dashboard_tecnico_ver_global ? '1' : '0', 'grupo' => 'incidencias']);

        $this->dispatch('mostrar-toast', mensaje: 'Configuración de incidencias guardada.', tipo: 'success');
    }

    public function guardarConfigPerfil()
    {
        abort_if(Gate::denies('admin-solicitudes-perfil'), 403);

        $keys = ['nombre', 'username', 'email', 'password'];
        foreach ($keys as $key) {
            $prop = "perfil_solicitar_" . $key;
            Configuracion::updateOrCreate(
                ['clave' => 'perfil_solicitar_' . $key],
                ['valor' => $this->$prop ? '1' : '0', 'grupo' => 'perfil']
            );
        }

        $this->dispatch('mostrar-toast', mensaje: 'Configuración de perfil guardada.', tipo: 'success');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $problemas = Problema::with('especialidad')->where('nombre', 'like', '%' . $this->searchProblema . '%')
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(8, ['*'], 'problemasPage');
            
        $especialidadesList = EspecialidadTecnica::where('nombre', 'like', '%' . $this->searchEspecialidad . '%')
            ->orderBy($this->sortFieldEspecialidad, $this->sortAscEspecialidad ? 'asc' : 'desc')
            ->paginate(8, ['*'], 'especialidadesPage');
            
        $todasEspecialidades = EspecialidadTecnica::where('activo', true)->get();

        return view('livewire.admin.configuracion-general', compact('problemas', 'especialidadesList', 'todasEspecialidades'));
    }
}
