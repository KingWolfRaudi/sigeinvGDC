<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Computador;
use App\Models\Dispositivo;
use App\Models\Insumo;
use App\Models\Incidencia;
use App\Models\Trabajador;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_pcs' => Computador::where('activo', true)->count(),
            'total_dispositivos' => Dispositivo::where('activo', true)->count(),
            'total_insumos' => Insumo::where('activo', true)->count(),
            'incidencias_abiertas' => Incidencia::where('cerrado', false)->count(),
            'trabajadores' => Trabajador::where('activo', true)->count(),
        ];

        // Datos para gráfico: PCs por Estado Físico
        $pcsPorEstado = Computador::select('estado_fisico', DB::raw('count(*) as total'))
            ->groupBy('estado_fisico')
            ->get();

        return view('livewire.dashboard', [
            'stats' => $stats,
            'pcsPorEstado' => $pcsPorEstado
        ]);
    }
}
