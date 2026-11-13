<?php

namespace App\Livewire;

use App\Models\Computador;
use App\Models\Departamento;
use App\Models\Dependencia;
use App\Models\Dispositivo;
use App\Models\Gpu;
use App\Models\Insumo;
use App\Models\Marca;
use App\Models\Procesador;
use App\Models\SistemaOperativo;
use App\Models\Trabajador;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class AsociacionesDashboard extends Component
{
    public $tipo;

    public $modelo_id;

    public $modelo;

    public $titulo = 'Asociaciones';

    public $subtitulo = '';

    public function mount($tipo, $id)
    {
        abort_if(Gate::denies('ver-dashboard'), 403);
        $this->tipo = $tipo;
        $this->modelo_id = $id;

        switch ($tipo) {
            case 'departamento':
                $this->modelo = Departamento::findOrFail($id);
                $this->titulo = 'Departamento: '.$this->modelo->nombre;
                break;
            case 'dependencia':
                $this->modelo = Dependencia::with('departamento')->findOrFail($id);
                $this->titulo = 'Dependencia: '.$this->modelo->nombre;
                $this->subtitulo = 'Perteneciente a: '.($this->modelo->departamento->nombre ?? 'N/A');
                break;
            case 'trabajador':
                $this->modelo = Trabajador::with('departamento')->findOrFail($id);
                $this->titulo = 'Trabajador: '.$this->modelo->nombres.' '.$this->modelo->apellidos;
                $this->subtitulo = $this->modelo->cargo.' - '.($this->modelo->departamento->nombre ?? 'Sin departamento');
                break;
            case 'procesador':
                $this->modelo = Procesador::with('marca')->findOrFail($id);
                $this->titulo = 'Procesador: '.($this->modelo->marca->nombre ?? '').' '.$this->modelo->modelo;
                break;
            case 'gpu':
                $this->modelo = Gpu::with('marca')->findOrFail($id);
                $this->titulo = 'Tarjeta Gráfica: '.($this->modelo->marca->nombre ?? '').' '.$this->modelo->modelo;
                break;
            case 'so':
                $this->modelo = SistemaOperativo::findOrFail($id);
                $this->titulo = 'Sistema Operativo: '.$this->modelo->nombre;
                break;
            case 'marca':
                $this->modelo = Marca::findOrFail($id);
                $this->titulo = 'Marca: '.$this->modelo->nombre;
                break;
            case 'computador':
                $this->modelo = Computador::findOrFail($id);
                $this->titulo = 'Computador: Bien Nacional '.$this->modelo->bien_nacional;
                $this->subtitulo = 'Serial: '.$this->modelo->serial;
                break;
            case 'dispositivo':
                $this->modelo = Dispositivo::findOrFail($id);
                $this->titulo = 'Dispositivo: '.$this->modelo->nombre;
                $this->subtitulo = 'BN: '.$this->modelo->bien_nacional.' | Serial: '.$this->modelo->serial;
                break;
            case 'insumo':
                $this->modelo = Insumo::findOrFail($id);
                $this->titulo = 'Insumo: '.$this->modelo->nombre;
                $this->subtitulo = 'BN: '.$this->modelo->bien_nacional.' | Serial: '.$this->modelo->serial;
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
