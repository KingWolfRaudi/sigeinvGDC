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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MainDashboard extends Component
{
    // Métricas Operativas
    public $incidenciasPendientes = 0;
    public $incidenciasEnCurso = 0;
    public $movimientosPendientes = 0;
    public $insumosCriticos = 0;

    // Acción Rápida
    // Acción Rápida
    public $incidenciasRecientes = [];
    public $incidenciasResueltas = [];
    public $esResolutor = false;

    // Analítica de Hardware
    public $totalRamGB = 0;
    public $totalAlmacenamientoGB = 0;
    
    // Datos para Gráficos
    public $graficoRam = [];
    public $graficoDiscos = [];

    public function mount()
    {
        $this->cargarMetricasOperativas();
        $this->cargarDatosHardware();
        $user = Auth::user();

        if ($user && $user->hasRole('resolutor-incidencia') && !$user->hasRole('super-admin')) {
            $this->esResolutor = true;
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
        } else {
            $this->esResolutor = false;
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
        $this->incidenciasPendientes = Incidencia::whereNull('user_id')->where('cerrado', false)->count();
        $this->incidenciasEnCurso = Incidencia::whereNotNull('user_id')->where('solventado', false)->where('cerrado', false)->count();
        
        $movsPc = MovimientoComputador::where('estado_workflow', 'solicitado')->count();
        $movsDisp = MovimientoDispositivo::where('estado_workflow', 'solicitado')->count();
        $movsIns = MovimientoInsumo::where('estado_workflow', 'solicitado')->count();
        $this->movimientosPendientes = $movsPc + $movsDisp + $movsIns;

        $this->insumosCriticos = Insumo::whereColumn('medida_actual', '<=', 'medida_minima')->where('activo', true)->count();
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

    public function render()
    {
        return view('livewire.dashboard.main-dashboard')->layout('components.layouts.app');
    }
}
