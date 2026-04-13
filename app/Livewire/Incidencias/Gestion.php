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
use Livewire\Attributes\Layout;

class Gestion extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Listado y Filtros
    public $search = '';
    public $filtro_departamento = '';
    public $filtro_tecnico = '';
    public $filtro_problema = '';
    public $filtro_estado = '';
    public $sortField = 'created_at';
    public $sortAsc = false;
    public $presetFiltro = [];
    public $ocultarTitulos = false;

    // Formulario de Creación/Edición
    public $incidencia_id;
    public $departamento_id, $trabajador_id, $problema_id, $user_id; // user_id es el técnico
    public $modelo_type, $modelo_id;
    public $descripcion, $nota_resolucion;
    public $solventado = false;
    public $cerrado = false;
    public $amerita_movimiento = false;
    public $es_lectura = false;

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
        return User::role('resolutor-incidencia')->where('activo', true)->get();
    }

    // --- CASCADING DROPDOWNS ---

    public function updatedDepartamentoId($value)
    {
        $this->trabajadores = Trabajador::where('departamento_id', $value)->where('activo', true)->get();
        $this->trabajador_id = null;
        $this->modelo_id = null;
        $this->activos = [];
        $this->cargarActivos();
    }

    public function updatedTrabajadorId($value)
    {
        $this->cargarActivos();
    }

    public function updatedModeloType($value)
    {
        $this->modelo_id = null;
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
    }

    // --- CRUD ---

    public function guardar()
    {
        $rules = [
            'departamento_id' => 'required',
            'problema_id' => 'required',
            'user_id' => 'nullable|exists:users,id', // Técnico puede ser null (Pendiente por Asignar)
            'descripcion' => 'required|min:10',
            'nota_resolucion' => 'nullable|string|max:500',
        ];

        if ($this->activo_obligatorio) {
            $rules['modelo_type'] = 'required';
            $rules['modelo_id'] = 'required';
        }

        if ($this->incidencia_id) {
            $checkInc = Incidencia::find($this->incidencia_id);
            if ($checkInc && $checkInc->cerrado) {
                // Si la regla de cierre irreversible está activa, nadie edita.
                if ($this->cierre_irreversible) {
                    $this->dispatch('mostrar-toast', mensaje: 'No se puede editar esta incidencia: el cierre es irreversible.', tipo: 'danger');
                    return;
                }
                
                // Si no es irreversible, debe ser admin para editar.
                if (!Auth::user()->can('admin-incidencias')) {
                    $this->dispatch('mostrar-toast', mensaje: 'No tiene permisos para editar incidencias cerradas.', tipo: 'danger');
                    return;
                }
            }
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
                'nota_resolucion' => $this->nota_resolucion,
                'solventado' => $this->solventado,
                'cerrado' => $this->cerrado,
                'amerita_movimiento' => $this->amerita_movimiento,
            ]
        );

        $this->dispatch('mostrar-toast', mensaje: $this->incidencia_id ? 'Incidencia actualizada.' : 'Incidencia registrada con éxito.', tipo: 'success');
        $this->resetForm();
        $this->dispatch('cerrar-modal', id: 'modalIncidencia');
    }

    public function crearMovimiento()
    {
        if (!$this->modelo_type || !$this->modelo_id) {
            $this->dispatch('mostrar-toast', mensaje: 'Debe seleccionar un activo para generar un movimiento.', tipo: 'warning');
            return;
        }

        $route = match ($this->modelo_type) {
            Computador::class => 'movimientos.computadores',
            Dispositivo::class => 'movimientos.dispositivos',
            Insumo::class => 'movimientos.insumos',
            default => null
        };

        if ($route) {
            return redirect()->route($route, [
                'auto_open' => 1,
                'modelo_id' => $this->modelo_id,
                'incidencia_id' => $this->incidencia_id
            ]);
        }
    }

    public function editar($id)
    {
        $inc = Incidencia::findOrFail($id);
        
        // Lógica de Solo Lectura:
        // Bloqueado si está cerrado, A MENOS que sea admin y el cierre NO sea irreversible.
        $this->es_lectura = $inc->cerrado;
        if ($inc->cerrado && !$this->cierre_irreversible && Auth::user()->can('admin-incidencias')) {
            $this->es_lectura = false;
        }

        $this->incidencia_id = $inc->id;
        $this->problema_id = $inc->problema_id;
        $this->departamento_id = $inc->departamento_id;
        $this->trabajador_id = $inc->trabajador_id;
        $this->user_id = $inc->user_id;
        $this->modelo_type = $inc->modelo_type;
        $this->modelo_id = $inc->modelo_id;
        $this->descripcion = $inc->descripcion;
        $this->nota_resolucion = $inc->nota_resolucion;
        $this->solventado = $inc->solventado;
        $this->cerrado = $inc->cerrado;
        $this->amerita_movimiento = $inc->amerita_movimiento;

        // Cargar listas dependientes
        $this->trabajadores = Trabajador::where('departamento_id', $this->departamento_id)->get();
        $this->cargarActivos();

        $this->dispatch('abrir-modal', id: 'modalIncidencia');
    }

    public function resetForm()
    {
        $this->reset([
            'incidencia_id', 'departamento_id', 'trabajador_id', 'problema_id', 
            'user_id', 'modelo_type', 'modelo_id', 'descripcion', 'nota_resolucion', 
            'solventado', 'cerrado', 'amerita_movimiento', 'trabajadores', 'activos', 'es_lectura'
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

    #[Layout('components.layouts.app')]
    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $incidencias = Incidencia::with(['problema.especialidad', 'departamento', 'trabajador', 'tecnico', 'modelo', 'creator'])
            ->where(function($query) {
                $query->where('descripcion', 'like', '%' . $this->search . '%')
                      ->orWhereHas('trabajador', function($q) {
                          $q->where('nombres', 'like', '%' . $this->search . '%')
                            ->orWhere('apellidos', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('creator', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('departamento', function($q) {
                          $q->where('nombre', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('problema', function($q) {
                          $q->where('nombre', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('tecnico', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhere('id', 'like', '%' . $this->search . '%');
            });

        // Filtrar por rol de usuario
        if (!$user->hasRole(['super-admin', 'administrador', 'coordinador'])) {
            // Técnicos ven lo asignado a ellos o pendientes que coincidan con su especialidad
            $incidencias->where(function($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->especialidad_id) {
                    $q->orWhere(function($subq) use ($user) {
                        $subq->whereNull('user_id')
                             ->whereHas('problema', function($pq) use ($user) {
                                 $pq->where('especialidad_id', $user->especialidad_id);
                             });
                    });
                }
            });
        }

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

        if ($this->filtro_tecnico) {
            $incidencias->where('user_id', $this->filtro_tecnico);
        }

        if ($this->filtro_problema) {
            $incidencias->where('problema_id', $this->filtro_problema);
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
            'problemas_dropdown' => Problema::where('activo', true)->orderBy('nombre')->get(),
            'tecnicos_dropdown' => $this->obtenerTecnicos()
        ]);
    }
}
