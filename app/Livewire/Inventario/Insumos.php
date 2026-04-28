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
use App\Models\Dependencia;
use App\Models\MovimientoInsumo;

class Insumos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Campos
    public $insumo_id, $bien_nacional, $serial, $nombre, $descripcion;
    public $marca_id, $categoria_insumo_id;
    public $unidad_medida = 'unidad';
    public int $medida_actual = 1;
    public int $medida_minima = 1;
    public $reutilizable = false;
    public $instalable_en_equipo = false;
    public $estado_fisico = 'operativo';
    public bool $activo = true;

    // Asociaciones
    public $departamento_id, $dependencia_id, $trabajador_id, $dispositivo_id, $computador_id;
    public $dependencias_disponibles = [];

    // Workflow de Movimientos
    public $justificacion = '';
    public bool $es_edicion = false;
    public $movimiento_preview = null;

    // On The Fly
    public $creando_marca = false, $nueva_marca;
    public $creando_categoria = false, $nueva_categoria;
    public $creando_departamento = false, $nuevo_departamento;

    // Trabajador On The Fly (Modal)
    public $nuevo_trab_nombres, $nuevo_trab_apellidos, $nuevo_trab_cedula, $nuevo_trab_departamento_id;

    public $insumo_detalle;
    public $tituloModal = 'Nuevo Insumo/Herramienta';
    public $search = '';
    public $sortField = 'id';
    public $sortAsc = false;
    public $filtro_estado = 'todos'; 
    
    // Variables para Reutilización y Anidación
    public $presetFiltro = [];
    public $ocultarTitulos = false;

    public function mount($presetFiltro = [], $ocultarTitulos = false)
    {
        $this->presetFiltro = $presetFiltro;
        $this->ocultarTitulos = $ocultarTitulos;
    }

    public function updatingSearch() { $this->resetPage(); }

    public function updatedDepartamentoId($value)
    {
        $this->trabajador_id = null;
        $this->dependencia_id = null;
        if (!empty($value)) {
            $this->dependencias_disponibles = Dependencia::where('departamento_id', $value)->where('activo', true)->get();
        } else {
            $this->dependencias_disponibles = [];
        }
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

    public function render()
    {
        $userId = Auth::id();
        $query = Insumo::with(['marca', 'categoriaInsumo', 'departamento', 'dependencia'])
            ->withCount([
                'movimientos as pendientes_count'     => fn($q) => $q->where('estado_workflow', 'pendiente'),
                'movimientos as mis_borradores_count'  => fn($q) => $q->where('estado_workflow', 'borrador')->where('solicitante_id', $userId),
            ]);

        if (\Illuminate\Support\Facades\Gate::allows('ver-estado-insumos')) {
            if ($this->filtro_estado === 'activos') $query->where('activo', true);
            elseif ($this->filtro_estado === 'inactivos') $query->where('activo', false);
        } else {
            $query->where('activo', true);
        }

        // Filtros Prediseñados (Cuando el componente se renderiza dentro de un Asociaciones Dashboard)
        if (!empty($this->presetFiltro)) {
            foreach($this->presetFiltro as $col => $val) {
                if ($val !== null) {
                    $query->where($col, $val);
                }
            }
        }

        $query->where(function ($q) {
            $search = '%' . $this->search . '%';
            $q->where('bien_nacional', 'like', $search)
              ->orWhere('serial', 'like', $search)
              ->orWhere('nombre', 'like', $search)
              ->orWhere('descripcion', 'like', $search)
              ->orWhereHas('marca', fn($subQ) => $subQ->where('nombre', 'like', $search))
              ->orWhereHas('categoriaInsumo', fn($subQ) => $subQ->where('nombre', 'like', $search));
        });

        $insumos = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')->paginate(10);

        $marcas = Marca::where('activo', true)->orderBy('nombre')->get();
        $categorias = CategoriaInsumo::where('activo', true)->orderBy('nombre')->get();

        $departamentos = \App\Models\Departamento::orderBy('nombre')->get();
        $trabajadores = [];
        $dispositivos = [];
        $computadores = [];

        if ($this->departamento_id) {
            $trabajadores = \App\Models\Trabajador::where('departamento_id', $this->departamento_id)
                ->where('activo', true)
                ->orderBy('nombres')
                ->get();
            $dispositivos = \App\Models\Dispositivo::where('departamento_id', $this->departamento_id)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
            $computadores = \App\Models\Computador::where('departamento_id', $this->departamento_id)
                ->where('activo', true)
                ->orderBy('nombre_equipo')
                ->get();
        }

        return view('livewire.inventario.insumos', compact('insumos', 'marcas', 'categorias', 'departamentos', 'trabajadores', 'dispositivos', 'computadores'));
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
            'marca_id'       => 'required_without:nueva_marca',
            'categoria_insumo_id' => 'required_without:nueva_categoria',
            'departamento_id' => 'nullable|required_with:nuevo_departamento',
            'dependencia_id'  => 'nullable|exists:dependencias,id',
            'trabajador_id'   => 'nullable|exists:trabajadores,id',
            'dispositivo_id'  => 'nullable|exists:dispositivos,id',
            'computador_id'   => 'nullable|exists:computadores,id',
        ];

        $rules['medida_actual'] = 'required|integer|min:0';
        $rules['medida_minima'] = 'required|integer|min:0';
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
            if ($this->creando_departamento && !empty($this->nuevo_departamento)) {
                $dpto = \App\Models\Departamento::firstOrCreate(['nombre' => $this->nuevo_departamento], ['activo' => true]);
                $this->departamento_id = $dpto->id;
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
                'departamento_id'      => $this->departamento_id ?: null,
                'dependencia_id'       => $this->dependencia_id ?: null,
                'trabajador_id'        => $this->trabajador_id ?: null,
                'dispositivo_id'       => $this->dispositivo_id ?: null,
                'computador_id'        => $this->computador_id ?: null,
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

            // ── Computar solo los campos que CAMBIARON ──
            $candidatoNuevo = [
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
                'departamento_id'      => $this->departamento_id ?: null,
                'dependencia_id'       => $this->dependencia_id ?: null,
                'trabajador_id'        => $this->trabajador_id ?: null,
                'dispositivo_id'       => $this->dispositivo_id ?: null,
                'computador_id'        => $this->computador_id ?: null,
            ];
            $boolCampos = ['activo', 'reutilizable', 'instalable_en_equipo'];
            $payloadNuevo = [];
            foreach ($candidatoNuevo as $k => $v) {
                $ant = $payloadAnterior[$k] ?? null;
                $iguales = in_array($k, $boolCampos)
                    ? ((bool)$ant === (bool)$v)
                    : ((string)($ant ?? '') === (string)($v ?? ''));
                if (!$iguales) {
                    $payloadNuevo[$k] = $v;
                }
            }
            if (empty($payloadNuevo)) {
                $this->dispatch('mostrar-toast', mensaje: 'No se detectaron cambios para registrar.', tipo: 'info');
                $this->dispatch('cerrar-modal', id: 'modalInsumo');
                $this->resetCampos();
                return;
            }

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
            \Illuminate\Support\Facades\Log::error('Error guardando insumo: ' . $e->getMessage());
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
        $this->departamento_id = $insumo->departamento_id;
        $this->dependencia_id = $insumo->dependencia_id;
        $this->trabajador_id = $insumo->trabajador_id;
        $this->dispositivo_id = $insumo->dispositivo_id;
        $this->computador_id = $insumo->computador_id;

        if ($this->departamento_id) {
            $this->dependencias_disponibles = Dependencia::where('departamento_id', $this->departamento_id)->where('activo', true)->get();
        }

        $this->tituloModal = 'Editar Insumo/Herramienta';
        $this->dispatch('abrir-modal', id: 'modalInsumo');
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-insumos'), 403);
        $this->insumo_detalle = Insumo::with(['marca', 'categoriaInsumo', 'departamento', 'trabajador', 'dispositivo', 'computador'])->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalleInsumo');
    }

    public function verCambioPendiente(int $insumoId): void
    {
        abort_if(Gate::denies('ver-insumos'), 403);
        $this->movimiento_preview = MovimientoInsumo::with('solicitante')
            ->where('insumo_id', $insumoId)
            ->whereIn('estado_workflow', ['pendiente', 'borrador'])
            ->orderByRaw("CASE estado_workflow WHEN 'pendiente' THEN 0 ELSE 1 END")
            ->latest()
            ->first();
        if ($this->movimiento_preview) {
            $this->dispatch('abrir-modal', id: 'modalCambioPendiente');
        }
    }

    public function aprobarMovimientoPreview(): void
    {
        abort_if(Gate::denies('movimientos-insumos-aprobar'), 403);
        if (!$this->movimiento_preview || $this->movimiento_preview->estado_workflow !== 'pendiente') {
            $this->dispatch('mostrar-toast', mensaje: 'Solo se pueden aprobar movimientos en estado Pendiente.', tipo: 'warning');
            return;
        }
        try {
            $mov    = MovimientoInsumo::where('estado_workflow', 'pendiente')->findOrFail($this->movimiento_preview->id);
            $insumo = Insumo::withTrashed()->findOrFail($mov->insumo_id);

            match ($mov->tipo_operacion) {
                'baja'          => $insumo->delete(),
                'toggle_activo' => $insumo->update(['activo' => $mov->payload_nuevo['activo'] ?? !$insumo->activo]),
                'entrada_stock' => $insumo->increment('medida_actual', $mov->cantidad_movida ?? 0),
                'salida_consumo'=> $insumo->decrement('medida_actual', $mov->cantidad_movida ?? 0),
                default         => $insumo->update($mov->payload_nuevo),
            };

            $mov->update(['estado_workflow' => 'aprobado', 'aprobador_id' => Auth::id(), 'aprobado_at' => now()]);
            $this->movimiento_preview = null;
            $this->dispatch('cerrar-modal', id: 'modalCambioPendiente');
            $this->dispatch('mostrar-toast', mensaje: 'Movimiento aprobado y aplicado.', tipo: 'success');
        } catch (\Exception $e) {
            Log::error('Error aprobando movimiento desde inventario: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al aprobar.', tipo: 'error');
        }
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
            'marca_id', 'categoria_insumo_id', 'insumo_detalle', 'nueva_marca', 'nueva_categoria', 'nuevo_departamento', 'justificacion',
            'departamento_id', 'dependencia_id', 'dependencias_disponibles', 'trabajador_id', 'dispositivo_id', 'computador_id'
        ]);
        $this->creando_marca = false;
        $this->creando_categoria = false;
        $this->creando_departamento = false;
        $this->estado_fisico = 'operativo';
        $this->unidad_medida = 'unidad';
        $this->medida_actual = 1;
        $this->medida_minima = 1;
        $this->reutilizable = false;
        $this->instalable_en_equipo = false;
        $this->activo = true;
        $this->es_edicion = false;
        $this->resetValidation();
    }

    // --- MÉTODOS PARA EL MODAL DE TRABAJADOR ---
    public function abrirModalTrabajador()
    {
        $this->dispatch('cerrar-modal', id: 'modalInsumo');
        $this->dispatch('abrir-modal', id: 'modalTrabajador');
    }

    public function cancelarModalTrabajador()
    {
        $this->reset([
            'nuevo_trab_nombres', 
            'nuevo_trab_apellidos', 
            'nuevo_trab_cedula', 
            'nuevo_trab_departamento_id'
        ]);
        $this->dispatch('cerrar-modal', id: 'modalTrabajador');
        $this->dispatch('abrir-modal', id: 'modalInsumo');
    }

    public function guardarTrabajadorRapido()
    {
        $this->validate([
            'nuevo_trab_nombres' => 'required|string|max:255',
            'nuevo_trab_apellidos' => 'required|string|max:255',
            'nuevo_trab_cedula' => 'nullable|string|unique:trabajadores,cedula', 
            'nuevo_trab_departamento_id' => 'required|exists:departamentos,id',
        ]);

        try {
            $trab = \App\Models\Trabajador::create([
                'nombres' => $this->nuevo_trab_nombres,
                'apellidos' => $this->nuevo_trab_apellidos,
                'cedula' => $this->nuevo_trab_cedula,
                'departamento_id' => $this->nuevo_trab_departamento_id,
                'activo' => true
            ]);

            $this->trabajador_id = $trab->id;

            $this->reset(['nuevo_trab_nombres', 'nuevo_trab_apellidos', 'nuevo_trab_cedula', 'nuevo_trab_departamento_id']);
            $this->dispatch('cerrar-modal', id: 'modalTrabajador');
            $this->dispatch('abrir-modal', id: 'modalInsumo');
            $this->dispatch('mostrar-toast', mensaje: 'Trabajador creado.', tipo:'success');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error trabajador rapido (Insumos): ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al registrar trabajador.', tipo:'error');
        }
    }
}
