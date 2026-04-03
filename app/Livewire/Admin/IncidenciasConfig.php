<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Problema;
use App\Models\Configuracion;
use Spatie\Permission\Models\Role;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;

class IncidenciasConfig extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Tabs
    public $activeTab = 'tipos';

    // Propiedades para Catálogo de Problemas
    public $problema_id, $nombre_problema;
    public $problema_activo = true;
    public $searchProblema = '';
    public $sortField = 'nombre';
    public $sortAsc = true;

    // Propiedades para Configuración General
    public $roles_tecnicos = []; // IDs o Nombres de roles
    public $cierre_irreversible = false;
    public $activo_obligatorio = false;

    public function mount()
    {
        // Cargar configuraciones existentes
        $configRoles = Configuracion::where('clave', 'incidencias_roles_tecnicos')->first();
        if ($configRoles) {
            $this->roles_tecnicos = json_decode($configRoles->valor, true) ?? [];
        }

        $configCierre = Configuracion::where('clave', 'incidencias_cierre_irreversible')->first();
        if ($configCierre) {
            $this->cierre_irreversible = (bool)$configCierre->valor;
        }

        $configActivo = Configuracion::where('clave', 'incidencias_activo_obligatorio')->first();
        if ($configActivo) {
            $this->activo_obligatorio = (bool)$configActivo->valor;
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // --- LÓGICA DE PROBLEMAS ---
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
            [
                'nombre' => $this->nombre_problema,
                'activo' => $this->problema_activo
            ]
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

    // --- LÓGICA DE CONFIGURACIÓN ---
    public function guardarConfiguracion()
    {
        abort_if(Gate::denies('admin-incidencias'), 403);

        Configuracion::updateOrCreate(
            ['clave' => 'incidencias_roles_tecnicos'],
            ['valor' => json_encode($this->roles_tecnicos), 'grupo' => 'incidencias']
        );

        Configuracion::updateOrCreate(
            ['clave' => 'incidencias_cierre_irreversible'],
            ['valor' => $this->cierre_irreversible ? '1' : '0', 'grupo' => 'incidencias']
        );

        Configuracion::updateOrCreate(
            ['clave' => 'incidencias_activo_obligatorio'],
            ['valor' => $this->activo_obligatorio ? '1' : '0', 'grupo' => 'incidencias']
        );

        $this->dispatch('mostrar-toast', mensaje: 'Configuración de incidencias guardada.', tipo: 'success');
    }

    public function render()
    {
        $problemas = Problema::where('nombre', 'like', '%' . $this->searchProblema . '%')
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(10);

        $rolesDisponibles = Role::where('name', '!=', 'super-admin')->get();

        return view('livewire.admin.incidencias-config', compact('problemas', 'rolesDisponibles'))
            ->layout('components.layouts.app');
    }
}
