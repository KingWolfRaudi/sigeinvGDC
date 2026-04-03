<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Departamento;
use App\Models\Trabajador;
use App\Models\Procesador;
use App\Models\Gpu;
use App\Models\SistemaOperativo;
use App\Models\TipoDispositivo;
use App\Models\Marca;
use App\Models\Computador;

class AsociacionesDashboard extends Component
{
    public $tipo;
    public $modelo_id;
    public $modelo; 
    public $titulo = 'Asociaciones';
    public $subtitulo = '';

    public function mount($tipo, $id)
    {
        $this->tipo = $tipo;
        $this->modelo_id = $id;

        switch($tipo) {
            case 'departamento':
                $this->modelo = Departamento::findOrFail($id);
                $this->titulo = 'Departamento: ' . $this->modelo->nombre;
                break;
            case 'trabajador':
                $this->modelo = Trabajador::with('departamento')->findOrFail($id);
                $this->titulo = 'Trabajador: ' . $this->modelo->nombres . ' ' . $this->modelo->apellidos;
                $this->subtitulo = $this->modelo->cargo . ' - ' . ($this->modelo->departamento->nombre ?? 'Sin departamento');
                break;
            case 'procesador':
                $this->modelo = Procesador::with('marca')->findOrFail($id);
                $this->titulo = 'Procesador: ' . ($this->modelo->marca->nombre ?? '') . ' ' . $this->modelo->modelo;
                break;
            case 'gpu':
                $this->modelo = Gpu::with('marca')->findOrFail($id);
                $this->titulo = 'Tarjeta Gráfica: ' . ($this->modelo->marca->nombre ?? '') . ' ' . $this->modelo->modelo;
                break;
            case 'so':
                $this->modelo = SistemaOperativo::findOrFail($id);
                $this->titulo = 'Sistema Operativo: ' . $this->modelo->nombre;
                break;
            case 'marca':
                $this->modelo = Marca::findOrFail($id);
                $this->titulo = 'Marca: ' . $this->modelo->nombre;
                break;
            case 'computador':
                $this->modelo = Computador::findOrFail($id);
                $this->titulo = 'Computador: Bien Nacional ' . $this->modelo->bien_nacional;
                $this->subtitulo = 'Serial: ' . $this->modelo->serial;
                break;
            case 'dispositivo':
                $this->modelo = \App\Models\Dispositivo::findOrFail($id);
                $this->titulo = 'Dispositivo: ' . $this->modelo->nombre;
                $this->subtitulo = 'BN: ' . $this->modelo->bien_nacional . ' | Serial: ' . $this->modelo->serial;
                break;
            case 'insumo':
                $this->modelo = \App\Models\Insumo::findOrFail($id);
                $this->titulo = 'Insumo: ' . $this->modelo->nombre;
                $this->subtitulo = 'BN: ' . $this->modelo->bien_nacional . ' | Serial: ' . $this->modelo->serial;
                break;
            default:
                abort(404);
        }
    }

    public function render()
    {
        return view('livewire.asociaciones-dashboard');
    }
}
