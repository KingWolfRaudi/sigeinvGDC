<?php

namespace App\Livewire\Incidencias;

use Livewire\Component;
use App\Models\Incidencia;
use App\Models\Problema;
use App\Models\Configuracion;
use App\Models\User;
use App\Models\Computador;
use App\Models\Dispositivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

class CrearTicket extends Component
{
    public $problema_id;
    public $descripcion;
    public $tipo_activo = ''; // computador | dispositivo
    public $modelo_id;

    public $catalogoProblemas = [];
    public $misEquipos = [];
    public $misDispositivos = [];

    public $catalogoDepartamentos = [];
    public $departamento_id;
    public $dependencia_id;
    public $dependencias_disponibles = [];

    // Validar si el admin configuró los activos como obligatorios
    public $activoObligatorio = false;

    public function mount()
    {
        $this->catalogoProblemas = Problema::where('activo', true)
                                           ->orderBy('nombre')->get();
                                           
        $configActivo = Configuracion::where('clave', 'incidencias_activo_obligatorio')->first();
        $this->activoObligatorio = $configActivo ? (bool)$configActivo->valor : false;

        $user = Auth::user();
        if ($user && $user->trabajador) {
            $this->departamento_id = $user->trabajador->departamento_id;
            $this->dependencia_id = $user->trabajador->dependencia_id;
            $this->misEquipos = Computador::where('trabajador_id', $user->trabajador->id)->get();
            $this->misDispositivos = Dispositivo::where('trabajador_id', $user->trabajador->id)->get();
        } else {
            $this->catalogoDepartamentos = \App\Models\Departamento::where('activo', true)->orderBy('nombre')->get();
        }
    }

    public function updatedDepartamentoId($value)
    {
        $this->dependencia_id = null;
        if (!empty($value)) {
            $this->dependencias_disponibles = \App\Models\Dependencia::where('departamento_id', $value)->where('activo', true)->get();
        } else {
            $this->dependencias_disponibles = [];
        }
    }

    public function submitTicket()
    {
        $user = Auth::user();
        $trabajador = $user->trabajador;

        $rules = [
            'problema_id' => 'required|exists:problemas,id',
            'descripcion' => 'required|string|min:10|max:500',
        ];

        if (!$trabajador) {
            $rules['departamento_id'] = 'required|exists:departamentos,id';
        }

        if ($this->activoObligatorio) {
            $rules['tipo_activo'] = 'required|in:computador,dispositivo,ninguno';
            if ($this->tipo_activo !== 'ninguno') {
                $rules['modelo_id'] = 'required';
            }
        }

        $this->validate($rules);

        // 1. Encontrar el Problema y su Especialidad
        $problema = Problema::find($this->problema_id);
        
        // 2. Buscar Técnico Disponible
        $tecnicoAsignado = null;
        if ($problema && $problema->especialidad_id) {
            $tecnicoAsignado = User::where('especialidad_id', $problema->especialidad_id)
                                   ->where('disponible_asignacion', true)
                                   ->where('activo', true)
                                   ->whereHas('roles', function($q) {
                                       $q->whereIn('name', ['personal-ti', 'resolutor-incidencia']);
                                   })
                                   ->inRandomOrder()
                                   ->first();
        }

        DB::beginTransaction();
        try {
            $ticket = new Incidencia();
            $ticket->problema_id = $this->problema_id;
            $ticket->descripcion = $this->descripcion;
            $ticket->departamento_id = $trabajador ? $trabajador->departamento_id : $this->departamento_id; 
            $ticket->dependencia_id = $trabajador ? $trabajador->dependencia_id : ($this->dependencia_id ?: null);
            $ticket->trabajador_id = $trabajador ? $trabajador->id : null;
            $ticket->user_id = $tecnicoAsignado ? $tecnicoAsignado->id : null; // Asignación automática o null
            $ticket->amerita_movimiento = false;

            if ($this->tipo_activo === 'computador') {
                $ticket->modelo_type = Computador::class;
                $ticket->modelo_id = $this->modelo_id;
            } elseif ($this->tipo_activo === 'dispositivo') {
                $ticket->modelo_type = Dispositivo::class;
                $ticket->modelo_id = $this->modelo_id;
            }

            $ticket->save();
            DB::commit();

            $folio = str_pad($ticket->id, 5, '0', STR_PAD_LEFT);
            $this->dispatch('mostrar-toast', mensaje: 'Ticket generado con éxito. Su folio de seguimiento es #' . $folio, tipo: 'success');
            
            $this->reset(['problema_id', 'descripcion', 'tipo_activo', 'modelo_id']);
            
            // Redirigir al Dashboard si es trabajador, sino a Gestión
            if (Auth::user()->hasRole('trabajador') && !Auth::user()->can('ver-incidencias')) {
                $this->redirect(route('dashboard'));
            } else {
                $this->redirect(route('incidencias.gestion'));
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mostrar-toast', mensaje: 'Error al generar ticket: ' . $e->getMessage(), tipo: 'danger');
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.incidencias.crear-ticket');
    }
}
