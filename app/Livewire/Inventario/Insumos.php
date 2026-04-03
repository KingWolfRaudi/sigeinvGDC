<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Insumo;
use App\Models\Marca;
use App\Models\CategoriaInsumo;
use App\Models\MovimientoInsumo;

class Insumos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Campos
    public $insumo_id, $bien_nacional, $serial, $nombre, $descripcion;
    public $marca_id, $categoria_insumo_id;
    public $unidad_medida = 'unidad';
    public $medida_actual = 1.00;
    public $medida_minima = 1.00;
    public $reutilizable = false;
    public $instalable_en_equipo = false;
    public $estado_fisico = 'operativo';
    public bool $activo = true;

    // Workflow de Movimientos
    public $justificacion = '';
    public bool $es_edicion = false;

    // On The Fly
    public $creando_marca = false, $nueva_marca;
    public $creando_categoria = false, $nueva_categoria;

    public $insumo_detalle;
    public $tituloModal = 'Nuevo Insumo/Herramienta';
    public $search = '';
    public $sortField = 'id';
    public $sortAsc = false;
    public $filtro_estado = 'todos'; 

    public function updatingSearch() { $this->resetPage(); }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
            $this->sortField = $field;
        }
    }

    public function render()
    {
        $query = Insumo::with(['marca', 'categoriaInsumo'])
            ->withCount(['movimientos as pendientes_count' => function ($q) {
                $q->where('estado_workflow', 'pendiente');
            }]);

        if (\Illuminate\Support\Facades\Gate::allows('ver-estado-insumos')) {
            if ($this->filtro_estado === 'activos') $query->where('activo', true);
            elseif ($this->filtro_estado === 'inactivos') $query->where('activo', false);
        } else {
            $query->where('activo', true);
        }

        $query->where(function ($q) {
            $q->where('bien_nacional', 'like', '%' . $this->search . '%')
              ->orWhere('serial', 'like', '%' . $this->search . '%')
              ->orWhere('nombre', 'like', '%' . $this->search . '%')
              ->orWhereHas('marca', function($subQ) {
                  $subQ->where('nombre', 'like', '%' . $this->search . '%');
              })
              ->orWhereHas('categoriaInsumo', function($subQ) {
                  $subQ->where('nombre', 'like', '%' . $this->search . '%');
              });
        });

        $insumos = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')->paginate(10);

        $marcas = Marca::where('activo', true)->orderBy('nombre')->get();
        $categorias = CategoriaInsumo::where('activo', true)->orderBy('nombre')->get();

        return view('livewire.inventario.insumos', compact('insumos', 'marcas', 'categorias'));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-insumos'), 403);
        $this->resetCampos();
        $this->tituloModal = 'Nuevo Insumo / Herramienta';
        $this->dispatch('abrir-modal', id: 'modalInsumo');
    }

    public function guardar()
    {
        $esEdicion = (bool) $this->insumo_id;
        abort_if(Gate::denies($esEdicion ? 'editar-insumos' : 'crear-insumos'), 403);

        $rules = [
            'bien_nacional'  => 'nullable|string|unique:insumos,bien_nacional,' . $this->insumo_id,
            'serial'         => 'nullable|string|unique:insumos,serial,' . $this->insumo_id,
            'nombre'         => 'required|string',
            'estado_fisico'  => 'required|string',
            'unidad_medida'  => 'required|string',
            'medida_actual'  => 'required|numeric|min:0',
            'medida_minima'  => 'required|numeric|min:0',
            'marca_id'       => 'required_without:nueva_marca',
            'categoria_insumo_id' => 'required_without:nueva_categoria',
        ];
        if ($esEdicion) {
            $rules['justificacion'] = 'required|string|min:10';
        }
        $this->validate($rules);

        try {
            if ($this->creando_marca && !empty($this->nueva_marca)) {
                $marca = Marca::firstOrCreate(['nombre' => $this->nueva_marca], ['activo' => true]);
                $this->marca_id = $marca->id;
            }
            if ($this->creando_categoria && !empty($this->nueva_categoria)) {
                $cat = CategoriaInsumo::firstOrCreate(['nombre' => $this->nueva_categoria], ['activo' => true]);
                $this->categoria_insumo_id = $cat->id;
            }

            $payloadNuevo = [
                'bien_nacional'        => $this->bien_nacional ?: null,
                'serial'               => $this->serial ?: null,
                'nombre'               => $this->nombre,
                'descripcion'          => $this->descripcion,
                'marca_id'             => $this->marca_id,
                'categoria_insumo_id'  => $this->categoria_insumo_id,
                'unidad_medida'        => $this->unidad_medida,
                'medida_actual'        => $this->medida_actual,
                'medida_minima'        => $this->medida_minima,
                'reutilizable'         => $this->reutilizable,
                'instalable_en_equipo' => $this->instalable_en_equipo,
                'estado_fisico'        => $this->estado_fisico,
                'activo'               => $this->activo,
            ];

            if (!$esEdicion) {
                Insumo::create($payloadNuevo);
                $this->dispatch('cerrar-modal', id: 'modalInsumo');
                $this->dispatch('mostrar-toast', mensaje: 'Registro creado.', tipo: 'success');
                $this->resetCampos();
                return;
            }

            $insumo = Insumo::findOrFail($this->insumo_id);
            $payloadAnterior = $insumo->toArray();

            if (Gate::allows('movimientos-insumos-ejecutar-directo')) {
                $insumo->update($payloadNuevo);
                MovimientoInsumo::create([
                    'insumo_id'        => $insumo->id,
                    'tipo_operacion'   => 'actualizacion_datos',
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $payloadNuevo,
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'    => $this->justificacion,
                    'solicitante_id'   => Auth::id(),
                    'aprobador_id'     => Auth::id(),
                    'aprobado_at'      => now(),
                ]);
                $this->dispatch('cerrar-modal', id: 'modalInsumo');
                $this->dispatch('mostrar-toast', mensaje: 'Registro actualizado directamente.', tipo: 'success');
            } else {
                MovimientoInsumo::create([
                    'insumo_id'        => $insumo->id,
                    'tipo_operacion'   => 'actualizacion_datos',
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $payloadNuevo,
                    'estado_workflow'  => 'borrador',
                    'justificacion'    => $this->justificacion,
                    'solicitante_id'   => Auth::id(),
                ]);
                $this->dispatch('cerrar-modal', id: 'modalInsumo');
                $this->dispatch('mostrar-toast',
                    mensaje: 'Cambio guardado como borrador. Ve a Movimientos para enviarlo a revisión.',
                    tipo: 'info'
                );
            }
            $this->resetCampos();
        } catch (\Exception $e) {
            Log::error('Error guardando insumo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Ocurrió un error guardando el registro.', tipo: 'error');
        }
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-insumos'), 403);
        $this->resetValidation();
        $this->es_edicion = true;
        $this->justificacion = '';
        $insumo = Insumo::findOrFail($id);
        
        $this->insumo_id = $insumo->id;
        $this->bien_nacional = $insumo->bien_nacional;
        $this->serial = $insumo->serial;
        $this->nombre = $insumo->nombre;
        $this->descripcion = $insumo->descripcion;
        $this->marca_id = $insumo->marca_id;
        $this->categoria_insumo_id = $insumo->categoria_insumo_id;
        $this->unidad_medida = $insumo->unidad_medida;
        $this->medida_actual = $insumo->medida_actual;
        $this->medida_minima = $insumo->medida_minima;
        $this->reutilizable = (bool) $insumo->reutilizable;
        $this->instalable_en_equipo = (bool) $insumo->instalable_en_equipo;
        $this->estado_fisico = $insumo->estado_fisico;
        $this->activo = (bool) $insumo->activo; 

        $this->tituloModal = 'Editar Insumo/Herramienta';
        $this->dispatch('abrir-modal', id: 'modalInsumo');
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-insumos'), 403);
        $this->insumo_detalle = Insumo::with(['marca', 'categoriaInsumo'])->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-insumos'), 403);
        try {
            $insumo = Insumo::findOrFail($id);
            if (Gate::allows('movimientos-insumos-ejecutar-directo')) {
                $insumo->delete();
                MovimientoInsumo::create([
                    'insumo_id'        => $id,
                    'tipo_operacion'   => 'baja',
                    'payload_anterior' => $insumo->toArray(),
                    'payload_nuevo'    => ['activo' => false, 'baja' => true],
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'    => 'Baja directa.',
                    'solicitante_id'   => Auth::id(),
                    'aprobador_id'     => Auth::id(),
                    'aprobado_at'      => now(),
                ]);
                $this->dispatch('mostrar-toast', mensaje: 'Registro de Insumo dado de baja.', tipo: 'success');
            } else {
                MovimientoInsumo::create([
                    'insumo_id'        => $insumo->id,
                    'tipo_operacion'   => 'baja',
                    'payload_anterior' => $insumo->toArray(),
                    'payload_nuevo'    => ['activo' => false, 'baja' => true],
                    'estado_workflow'  => 'borrador',
                    'justificacion'    => 'Solicitud de baja pendiente.',
                    'solicitante_id'   => Auth::id(),
                ]);
                $this->dispatch('mostrar-toast',
                    mensaje: 'Solicitud de baja creada como borrador.',
                    tipo: 'warning'
                );
            }
        } catch (\Exception $e) {
            Log::error('Error eliminando insumo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Ocurrió un error eliminando.', tipo: 'error');
        }
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-insumos'), 403);
        try {
            $i = Insumo::findOrFail($id);
            $nuevoEstado = !$i->activo;
            if (Gate::allows('movimientos-insumos-ejecutar-directo')) {
                $i->activo = $nuevoEstado;
                $i->save();
                MovimientoInsumo::create([
                    'insumo_id'        => $i->id,
                    'tipo_operacion'   => 'toggle_activo',
                    'payload_anterior' => ['activo' => !$nuevoEstado],
                    'payload_nuevo'    => ['activo' => $nuevoEstado],
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'    => 'Cambio de estatus directo.',
                    'solicitante_id'   => Auth::id(),
                    'aprobador_id'     => Auth::id(),
                    'aprobado_at'      => now(),
                ]);
                $status = $nuevoEstado ? 'activado' : 'inactivado';
                $this->dispatch('mostrar-toast', mensaje: "Registro $status.", tipo: 'success');
            } else {
                MovimientoInsumo::create([
                    'insumo_id'        => $i->id,
                    'tipo_operacion'   => 'toggle_activo',
                    'payload_anterior' => ['activo' => !$nuevoEstado],
                    'payload_nuevo'    => ['activo' => $nuevoEstado],
                    'estado_workflow'  => 'borrador',
                    'justificacion'    => 'Solicitud de cambio de estatus pendiente.',
                    'solicitante_id'   => Auth::id(),
                ]);
                $this->dispatch('mostrar-toast',
                    mensaje: 'Solicitud de cambio de estatus guardada en borrador.',
                    tipo: 'info'
                );
            }
        } catch (\Exception $e) {
            Log::error('Error cambiando estatus de insumo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Ocurrió un error.', tipo: 'error');
        }
    }

    public function resetCampos()
    {
        $this->reset([
            'insumo_id', 'bien_nacional', 'serial', 'nombre', 'descripcion', 
            'marca_id', 'categoria_insumo_id', 'insumo_detalle', 'nueva_marca', 'nueva_categoria', 'justificacion'
        ]);
        $this->creando_marca = false;
        $this->creando_categoria = false;
        $this->estado_fisico = 'operativo';
        $this->unidad_medida = 'unidad';
        $this->medida_actual = 1.00;
        $this->medida_minima = 1.00;
        $this->reutilizable = false;
        $this->instalable_en_equipo = false;
        $this->activo = true;
        $this->es_edicion = false;
        $this->resetValidation();
    }
}
