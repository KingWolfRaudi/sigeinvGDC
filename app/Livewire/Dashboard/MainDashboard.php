<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Incidencia;
use App\Models\MovimientoComputador;
use App\Models\MovimientoDispositivo;
use App\Models\MovimientoInsumo;
use App\Models\Insumo;
use App\Models\ComputadorRam;
use App\Models\ComputadorDisco;
use App\Models\Configuracion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MainDashboard extends Component
{
    // Métricas Operativas
    public $incidenciasPendientes = 0;
    public $incidenciasEnCurso = 0;
    public $movimientosPendientes = 0;
    public $insumosCriticos = 0;
    public $esTecnico = false;
    public $esTrabajador = false;
    public $dashboard_tecnico_ver_global = false;

    // Acción Rápida
    // Acción Rápida
    public $incidenciasRecientes = [];
    public $incidenciasResueltas = [];
    public $incidencia_detalle = null;
    public $esResolutor = false;

    // Analítica de Hardware
    public $totalRamGB = 0;
    public $totalAlmacenamientoGB = 0;
    
    // Datos para Gráficos
    public $graficoRam = [];
    public $graficoDiscos = [];

    public function mount()
    {
        $user = Auth::user();
        $this->esTecnico = $user->hasRole('resolutor-incidencia') && !$user->hasRole(['super-admin', 'administrador', 'coordinador']);
        $this->esTrabajador = $user->hasRole('trabajador') && !$user->hasRole(['super-admin', 'administrador', 'coordinador', 'resolutor-incidencia']);
        
        $configDash = Configuracion::where('clave', 'dashboard_tecnico_ver_global')->first();
        $this->dashboard_tecnico_ver_global = $configDash ? (bool)$configDash->valor : false;

        $this->cargarMetricasOperativas();
        $this->cargarDatosHardware();
        $user = Auth::user();

        if ($this->esResolutor) {
            $this->incidenciasRecientes = Incidencia::with(['departamento', 'trabajador', 'creator'])
                                                    ->where('user_id', $user->id)
                                                    ->where('solventado', false)
                                                    ->where('cerrado', false)
                                                    ->latest()
                                                    ->take(5)
                                                    ->get();
                                                    
            $this->incidenciasResueltas = Incidencia::with(['departamento', 'trabajador', 'creator'])
                                                    ->where('user_id', $user->id)
                                                    ->where(function($q) {
                                                        $q->where('solventado', true)->orWhere('cerrado', true);
                                                    })
                                                    ->latest('updated_at')
                                                    ->take(5)
                                                    ->get();
        } elseif ($this->esTrabajador) {
            $this->incidenciasRecientes = Incidencia::with(['departamento', 'trabajador', 'creator'])
                                                    ->where('created_by', $user->id)
                                                    ->where('solventado', false)
                                                    ->where('cerrado', false)
                                                    ->latest()
                                                    ->take(5)
                                                    ->get();
                                                    
            $this->incidenciasResueltas = Incidencia::with(['departamento', 'trabajador', 'creator'])
                                                    ->where('created_by', $user->id)
                                                    ->where(function($q) {
                                                        $q->where('solventado', true)->orWhere('cerrado', true);
                                                    })
                                                    ->latest('updated_at')
                                                    ->take(5)
                                                    ->get();
        } else {
            $this->incidenciasRecientes = Incidencia::with(['departamento', 'trabajador', 'creator'])
                                                    ->where('solventado', false)
                                                    ->where('cerrado', false)
                                                    ->latest()
                                                    ->take(5)
                                                    ->get();
                                                    
            $this->incidenciasResueltas = Incidencia::with(['departamento', 'trabajador', 'creator'])
                                                    ->where(function($q) {
                                                        $q->where('solventado', true)->orWhere('cerrado', true);
                                                    })
                                                    ->latest('updated_at')
                                                    ->take(5)
                                                    ->get();
        }
    }

    private function cargarMetricasOperativas()
    {
        $user = Auth::user();

        // 1. Incidencias Pendientes (Sin Asignar)
        $queryPendientes = Incidencia::whereNull('user_id')->where('cerrado', false);
        
        if ($this->esTecnico && !$this->dashboard_tecnico_ver_global) {
            // Si es técnico y la config dice "no ver global", filtramos por su especialidad
            if ($user->especialidad_id) {
                $queryPendientes->whereHas('problema', function($q) use ($user) {
                    $q->where('especialidad_id', $user->especialidad_id);
                });
            } else {
                // Si no tiene especialidad asignada, no ve nada pendiente si el filtro está activo
                $queryPendientes->whereRaw('1 = 0');
            }
        } elseif ($this->esTrabajador) {
            // Trabajador solo ve sus propios reportes pendientes
            $queryPendientes->where('created_by', $user->id);
        }
        $this->incidenciasPendientes = $queryPendientes->count();

        // 2. Incidencias En Curso
        $queryEnCurso = Incidencia::whereNotNull('user_id')->where('solventado', false)->where('cerrado', false);
        if ($this->esTecnico) {
            // Técnicos solo ven lo que tienen asignado ellos mismos
            $queryEnCurso->where('user_id', $user->id);
        } elseif ($this->esTrabajador) {
            // Trabajador solo ve sus propios reportes en curso
            $queryEnCurso->where('created_by', $user->id);
        }
        $this->incidenciasEnCurso = $queryEnCurso->count();
        
        // 3. Movimientos Pendientes
        // Se muestran solo si tiene permiso de ver movimientos de algún tipo y NO es trabajador raso
        if (!$this->esTrabajador && ($user->can('movimientos-computadores-ver') || $user->can('movimientos-dispositivos-ver') || $user->can('movimientos-insumos-ver'))) {
            $movsPc = MovimientoComputador::where('estado_workflow', 'solicitado')->count();
            $movsDisp = MovimientoDispositivo::where('estado_workflow', 'solicitado')->count();
            $movsIns = MovimientoInsumo::where('estado_workflow', 'solicitado')->count();
            $this->movimientosPendientes = $movsPc + $movsDisp + $movsIns;
        } else {
            $this->movimientosPendientes = 0;
        }

        $this->insumosCriticos = $this->esTrabajador ? 0 : Insumo::whereColumn('medida_actual', '<=', 'medida_minima')->where('activo', true)->count();
    }

    private function cargarDatosHardware()
    {
        // 1. Procesamiento de RAM
        $rams = ComputadorRam::pluck('capacidad');
        $ramGroups = [
            '4GB o menos' => 0,
            '8GB' => 0,
            '16GB' => 0,
            'Más de 16GB' => 0
        ];

        foreach ($rams as $ramRaw) {
            $val = $this->parseStorageString($ramRaw);
            $this->totalRamGB += $val;

            if ($val <= 4) $ramGroups['4GB o menos']++;
            elseif ($val <= 8) $ramGroups['8GB']++;
            elseif ($val <= 16) $ramGroups['16GB']++;
            else $ramGroups['Más de 16GB']++;
        }

        // Filtramos para el gráfico solo los que tienen datos
        foreach ($ramGroups as $label => $count) {
            if ($count > 0) {
                $this->graficoRam['labels'][] = $label;
                $this->graficoRam['data'][] = $count;
            }
        }

        // 2. Procesamiento de Discos
        $discos = ComputadorDisco::select('capacidad', 'tipo')->get();
        $discoGroups = [
            'Tecnología SSD (M.2/NVME/SATA)' => 0,
            'Discos Mecánicos (HDD)' => 0,
            'Sin Disco Local' => 0
        ];

        foreach ($discos as $disco) {
            $val = $this->parseStorageString($disco->capacidad);
            $this->totalAlmacenamientoGB += $val;

            if (in_array($disco->tipo, ['SSD', 'NVME', 'M.2'])) {
                $discoGroups['Tecnología SSD (M.2/NVME/SATA)']++;
            } elseif ($disco->tipo === 'HDD') {
                $discoGroups['Discos Mecánicos (HDD)']++;
            } else {
                $discoGroups['Sin Disco Local']++;
            }
        }

        foreach ($discoGroups as $label => $count) {
            if ($count > 0) {
                $this->graficoDiscos['labels'][] = $label;
                $this->graficoDiscos['data'][] = $count;
            }
        }
    }

    /**
     * Extrae el valor numérico en GB de un string como "500GB", "1TB", "512 GB".
     */
    private function parseStorageString($str)
    {
        if (empty($str)) return 0;
        
        preg_match('/[0-9]+(\.[0-9]+)?/', $str, $matches);
        $val = floatval($matches[0] ?? 0);
        
        $strUpper = strtoupper($str);
        if (strpos($strUpper, 'TB') !== false) {
            $val *= 1024;
        } elseif (strpos($strUpper, 'MB') !== false) {
            $val = $val / 1024;
        }
        
        return $val;
    }

    public function ver($id)
    {
        $this->incidencia_detalle = Incidencia::with([
            'problema.especialidad',
            'departamento',
            'dependencia',
            'trabajador',
            'tecnico',
            'creator',
            'modelo' 
        ])->findOrFail($id);

        $this->dispatch('abrir-modal', id: 'modalDetalleIncidencia');
    }

    public function render()
    {
        return view('livewire.dashboard.main-dashboard')->layout('components.layouts.app');
    }
}
