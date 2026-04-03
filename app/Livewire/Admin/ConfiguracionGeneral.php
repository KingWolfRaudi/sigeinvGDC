<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Problema;
use App\Models\Configuracion;
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

    // Propiedades para Catálogo de Problemas (Incidencias)
    public $problema_id, $nombre_problema;
    public $problema_activo = true;
    public $searchProblema = '';
    public $sortField = 'nombre';
    public $sortAsc = true;

    // Propiedades para Configuración de Incidencias
    public $roles_tecnicos = [];
    public $cierre_irreversible = false;
    public $activo_obligatorio = false;

    // Propiedades para Configuración de Perfil
    public $perfil_solicitar_nombre = true;
    public $perfil_solicitar_username = true;
    public $perfil_solicitar_email = true;
    public $perfil_solicitar_password = true;

    public function mount()
    {
        // 1. Cargar Configuración de Incidencias
        $this->cargarConfigIncidencias();

        // 2. Cargar Configuración de Perfil
        $this->cargarConfigPerfil();
    }

    private function cargarConfigIncidencias()
    {
        $configRoles = Configuracion::where('clave', 'incidencias_roles_tecnicos')->first();
        if ($configRoles) $this->roles_tecnicos = json_decode($configRoles->valor, true) ?? [];

        $configCierre = Configuracion::where('clave', 'incidencias_cierre_irreversible')->first();
        if ($configCierre) $this->cierre_irreversible = (bool)$configCierre->valor;

        $configActivo = Configuracion::where('clave', 'incidencias_activo_obligatorio')->first();
        if ($configActivo) $this->activo_obligatorio = (bool)$configActivo->valor;
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
        $this->validate([
            'nombre_problema' => 'required|min:3|unique:problemas,nombre,' . $this->problema_id,
        ]);

        Problema::updateOrCreate(
            ['id' => $this->problema_id],
            ['nombre' => $this->nombre_problema, 'activo' => $this->problema_activo]
        );

        $this->dispatch('mostrar-toast', mensaje: $this->problema_id ? 'Tipo de incidencia actualizado.' : 'Nuevo tipo de incidencia creado.', tipo: 'success');
        $this->resetProblema();
        $this->dispatch('cerrar-modal', id: 'modalProblema');
    }

    public function editarProblema($id)
    {
        $problema = Problema::findOrFail($id);
        $this->problema_id = $problema->id;
        $this->nombre_problema = $problema->nombre;
        $this->problema_activo = $problema->activo;
        $this->dispatch('abrir-modal', id: 'modalProblema');
    }

    public function eliminarProblema($id)
    {
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
        $this->reset(['problema_id', 'nombre_problema', 'problema_activo']);
    }

    // --- LÓGICA DE GUARDADO DE CONFIGURACIÓN ---
    public function guardarConfigIncidencias()
    {
        abort_if(Gate::denies('admin-incidencias'), 403);

        Configuracion::updateOrCreate(['clave' => 'incidencias_roles_tecnicos'], ['valor' => json_encode($this->roles_tecnicos), 'grupo' => 'incidencias']);
        Configuracion::updateOrCreate(['clave' => 'incidencias_cierre_irreversible'], ['valor' => $this->cierre_irreversible ? '1' : '0', 'grupo' => 'incidencias']);
        Configuracion::updateOrCreate(['clave' => 'incidencias_activo_obligatorio'], ['valor' => $this->activo_obligatorio ? '1' : '0', 'grupo' => 'incidencias']);

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
        $problemas = Problema::where('nombre', 'like', '%' . $this->searchProblema . '%')
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(8);

        $rolesDisponibles = Role::where('name', '!=', 'super-admin')->get();

        return view('livewire.admin.configuracion-general', compact('problemas', 'rolesDisponibles'));
    }
}
