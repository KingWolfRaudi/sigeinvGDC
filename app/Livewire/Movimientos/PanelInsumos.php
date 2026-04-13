<?php

namespace App\Livewire\Movimientos;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\MovimientoInsumo;
use App\Models\Insumo;

class PanelInsumos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $modelo_id = request()->query('modelo_id');
        $auto_open = request()->query('auto_open');
        $this->incidencia_id = request()->query('incidencia_id');

        if ($auto_open && $modelo_id && Gate::allows('movimientos-insumos-crear')) {
            $this->mostrando_generador = true;
            $this->paso_generador = 1;
            $this->seleccionarInsumo((int)$modelo_id);
            $this->dispatch('abrir-modal', id: 'modalGeneradorInsumos');

            if ($this->incidencia_id) {
                $this->justificacion = "Vinculado a la incidencia #" . str_pad($this->incidencia_id, 5, '0', STR_PAD_LEFT);
            }
        }
    }


    // Propiedades de Filtrado y Estado
    public string $pestana = 'borradores';
    public string $search = '';
    public string $filtro_tipo = '';
    public ?int $incidencia_id = null;

    public ?int $rechazando_id = null;
    public string $motivo_rechazo = '';

    // Edición de Borrador
    public ?int $editando_borrador_id = null;
    public string $edit_justificacion = '';

    public $movimiento_detalle = null;

    // ── PROPIEDADES DEL GENERADOR DE MOVIMIENTOS ───────────────────────
    public bool $mostrando_generador = false;
    public int $paso_generador = 1; // 1: Selección, 2: Edición

    // Filtros de Selección (Paso 1)
    public $searchBN = '', $searchSerial = '', $searchDpto = '', $searchTrabajador = '';

    // Campos del Formulario (Paso 2)
    public ?Insumo $selected_insumo = null;
    public $insumo_id;
    public string $tipo_operacion = 'actualizacion_datos';
    public $cantidad_movida = 0;
    public $bien_nacional, $serial, $nombre, $descripcion;
    public $marca_id, $categoria_insumo_id;
    public $unidad_medida = 'unidad';
    public int $medida_actual = 0, $medida_minima = 0;
    public $reutilizable = false, $instalable_en_equipo = false, $estado_fisico = 'operativo';
    public $departamento_id, $trabajador_id, $dispositivo_id, $computador_id;
    public $justificacion = '';

    // Creación Rápida (On-The-Fly)
    public bool $creando_marca = false; public $nueva_marca;
    public bool $creando_categoria = false; public $nueva_categoria;
    public bool $creando_departamento = false; public $nuevo_departamento;

    // Trabajador On The Fly (Modal)
    public $nuevo_trab_nombres, $nuevo_trab_apellidos, $nuevo_trab_cedula, $nuevo_trab_departamento_id;


    public function updatingSearch() { $this->resetPage(); }
    public function updatingPestana() { $this->resetPage(); }

    public function updatedDepartamentoId($value)
    {
        $this->trabajador_id = null;
    }

    public function render()
    {
        abort_if(Gate::denies('movimientos-insumos-ver'), 403);

        return view('livewire.movimientos.panel-insumos', array_merge([
            'movimientos'   => $this->_getMovimientos(),
            'conteo'        => $this->_getConteos(),
            'insumos_lista' => $this->insumos_filtrados,
        ], $this->_getCatalogos()));
    }

    private function _getMovimientos()
    {
        $query = MovimientoInsumo::with(['insumo.marca', 'insumo.categoriaInsumo', 'solicitante', 'aprobador'])
            ->when($this->search, function ($q) {
                $q->whereHas('insumo', function ($sub) {
                    $sub->where('nombre', 'like', '%' . $this->search . '%')
                        ->orWhere('bien_nacional', 'like', '%' . $this->search . '%')
                        ->orWhere('serial', 'like', '%' . $this->search . '%');
                })->orWhere('justificacion', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtro_tipo, fn($q) => $q->where('tipo_operacion', $this->filtro_tipo));

        return match ($this->pestana) {
            'borradores' => $query->where('estado_workflow', 'borrador')
                                  ->where('solicitante_id', Auth::id())
                                  ->latest()->paginate(10),
            'pendientes' => $query->where('estado_workflow', 'pendiente')
                                  ->latest()->paginate(10),
            default       => $query->whereIn('estado_workflow', ['aprobado', 'rechazado', 'ejecutado_directo'])
                                   ->latest()->paginate(15),
        };
    }

    private function _getConteos()
    {
        return [
            'borradores' => MovimientoInsumo::where('estado_workflow', 'borrador')
                                ->where('solicitante_id', Auth::id())->count(),
            'pendientes' => MovimientoInsumo::where('estado_workflow', 'pendiente')->count(),
        ];
    }

    private function _getCatalogos()
    {
        if (!$this->mostrando_generador) {
            return [
                'marcas' => collect(), 'categorias' => collect(), 'departamentos' => collect(),
                'trabajadores' => collect(), 'dispositivos' => collect(), 'computadores' => collect(),
            ];
        }

        return [
            'marcas' => \App\Models\Marca::where('activo', true)->orderBy('nombre')->get(),
            'categorias' => \App\Models\CategoriaInsumo::where('activo', true)->orderBy('nombre')->get(),
            'departamentos' => \App\Models\Departamento::orderBy('nombre')->get(),
            'trabajadores' => \App\Models\Trabajador::where('activo', true)
                ->when($this->departamento_id, fn($q) => $q->where('departamento_id', $this->departamento_id))
                ->get(),
            'dispositivos' => \App\Models\Dispositivo::where('activo', true)
                ->when($this->departamento_id, fn($q) => $q->where('departamento_id', $this->departamento_id))
                ->get(),
            'computadores' => \App\Models\Computador::where('activo', true)
                ->when($this->departamento_id, fn($q) => $q->where('departamento_id', $this->departamento_id))
                ->get(),
        ];
    }

    public function getInsumosFiltradosProperty()
    {
        if (!$this->mostrando_generador || $this->paso_generador != 1) return [];

        return Insumo::with(['marca', 'categoriaInsumo'])
            ->withCount(['movimientos as pendientes_count' => fn($q) => $q->where('estado_workflow', 'pendiente')])
            ->when($this->searchBN, fn($q) => $q->where('bien_nacional', 'like', "%{$this->searchBN}%"))
            ->when($this->searchSerial, fn($q) => $q->where('serial', 'like', "%{$this->searchSerial}%"))
            ->when($this->searchDpto, fn($q) => $q->where('departamento_id', $this->searchDpto))
            ->when($this->searchTrabajador, fn($q) => $q->where('trabajador_id', $this->searchTrabajador))
            ->latest()
            ->limit(10)
            ->get();
    }

    // ── Lógica del Generador ──────────────────────────────────

    public function abrirGenerador(): void
    {
        abort_if(Gate::denies('movimientos-insumos-crear'), 403);
        $this->mostrando_generador = true;
        $this->paso_generador = 1;
        $this->resetGenerador();
        $this->dispatch('abrir-modal', id: 'modalGeneradorInsumos');
    }

    public function seleccionarInsumo(int $id): void
    {
        $insumo = Insumo::findOrFail($id);
        $this->selected_insumo = $insumo;
        $this->insumo_id = $id;
        
        // Cargar datos actuales al formulario
        $this->bien_nacional = $insumo->bien_nacional;
        $this->serial = $insumo->serial;
        $this->nombre = $insumo->nombre;
        $this->descripcion = $insumo->descripcion;
        $this->marca_id = $insumo->marca_id;
        $this->categoria_insumo_id = $insumo->categoria_insumo_id;
        $this->unidad_medida = $insumo->unidad_medida;
        $this->medida_actual = (int)$insumo->medida_actual;
        $this->medida_minima = (int)$insumo->medida_minima;
        $this->reutilizable = (bool)$insumo->reutilizable;
        $this->instalable_en_equipo = (bool)$insumo->instalable_en_equipo;
        $this->estado_fisico = $insumo->estado_fisico;
        $this->departamento_id = $insumo->departamento_id;
        $this->trabajador_id = $insumo->trabajador_id;
        $this->dispositivo_id = $insumo->dispositivo_id;
        $this->computador_id = $insumo->computador_id;
        
        $this->paso_generador = 2;
    }

    public function guardarBorrador(): void
    {
        abort_if(Gate::denies('movimientos-insumos-crear'), 403);
        
        $rules = [
            'justificacion' => 'required|string|min:10',
            'tipo_operacion' => 'required|string',
        ];
        
        if (in_array($this->tipo_operacion, ['entrada_stock', 'salida_consumo', 'prestamo', 'devolucion'])) {
            $rules['cantidad_movida'] = 'required|integer|min:1';
        }

        $this->validate($rules);

        try {
            // Resolución de Creación Rápida
            if ($this->creando_marca && !empty($this->nueva_marca)) {
                $m = \App\Models\Marca::firstOrCreate(['nombre' => $this->nueva_marca], ['activo' => true]);
                $this->marca_id = $m->id;
            }
            if ($this->creando_categoria && !empty($this->nueva_categoria)) {
                $c = \App\Models\CategoriaInsumo::firstOrCreate(['nombre' => $this->nueva_categoria], ['activo' => true]);
                $this->categoria_insumo_id = $c->id;
            }
            if ($this->creando_departamento && !empty($this->nuevo_departamento)) {
                $d = \App\Models\Departamento::firstOrCreate(['nombre' => $this->nuevo_departamento], ['activo' => true]);
                $this->departamento_id = $d->id;
            }

            $insumo = $this->selected_insumo;
            
            // Validación de Existencias para salidas/préstamos
            if (in_array($this->tipo_operacion, ['salida_consumo', 'prestamo'])) {
                if ($this->cantidad_movida > $insumo->medida_actual) {
                    $this->addError('cantidad_movida', "Insuficiente stock disponible. Actual: {$insumo->medida_actual}");
                    return;
                }
            }

            $payloadAnterior = $insumo->toArray();
            
            // Diff Engine
            $candidatoNuevo = [
                'bien_nacional' => $this->bien_nacional ?: null,
                'serial' => $this->serial ?: null,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'marca_id' => $this->marca_id,
                'categoria_insumo_id' => $this->categoria_insumo_id,
                'unidad_medida' => $this->unidad_medida,
                'medida_actual' => $this->medida_actual,
                'medida_minima' => $this->medida_minima,
                'reutilizable' => $this->reutilizable,
                'instalable_en_equipo' => $this->instalable_en_equipo,
                'estado_fisico' => $this->estado_fisico,
                'departamento_id' => $this->departamento_id ?: null,
                'trabajador_id' => $this->trabajador_id ?: null,
                'dispositivo_id' => $this->dispositivo_id ?: null,
                'computador_id' => $this->computador_id ?: null,
            ];

            $payloadNuevo = [];
            $bools = ['reutilizable', 'instalable_en_equipo'];
            
            foreach ($candidatoNuevo as $key => $value) {
                $ant = $payloadAnterior[$key] ?? null;
                $iguales = in_array($key, $bools) 
                    ? ((bool)$ant === (bool)$value)
                    : ((string)($ant ?? '') === (string)($value ?? ''));
                
                if (!$iguales) {
                    $payloadNuevo[$key] = $value;
                }
            }

            // Si es actualización de datos y no hay cambios, notificar
            if ($this->tipo_operacion === 'actualizacion_datos' && empty($payloadNuevo)) {
                $this->dispatch('mostrar-toast', mensaje: 'No hay cambios detectados.', tipo: 'info');
                return;
            }

            if (Gate::allows('movimientos-insumos-ejecutar-directo')) {
                // Ejecución Directa (Bypass Workflow)
                match ($this->tipo_operacion) {
                    'baja'         => $insumo->delete(),
                    'toggle_activo' => $insumo->update(['activo' => $payloadNuevo['activo'] ?? !$insumo->activo]),
                    'salida_consumo' => $insumo->update(['medida_actual' => max(0, $insumo->medida_actual - ($this->cantidad_movida ?? 0))]),
                    'entrada_stock' => $insumo->update(['medida_actual' => $insumo->medida_actual + ($this->cantidad_movida ?? 0)]),
                    'prestamo'      => $insumo->update(['medida_actual' => max(0, $insumo->medida_actual - ($this->cantidad_movida ?? 0))]),
                    'devolucion'   => $insumo->update(['medida_actual' => $insumo->medida_actual + ($this->cantidad_movida ?? 0)]),
                    default        => $insumo->update($payloadNuevo),
                };

                MovimientoInsumo::create([
                    'insumo_id'        => $insumo->id,
                    'tipo_operacion'   => $this->tipo_operacion,
                    'cantidad_movida'  => in_array($this->tipo_operacion, ['entrada_stock', 'salida_consumo', 'prestamo', 'devolucion']) ? $this->cantidad_movida : null,
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $payloadNuevo,
                    'estado_workflow'  => 'ejecutado_directo',
                    'justificacion'    => $this->justificacion,
                    'solicitante_id'   => Auth::id(),
                    'aprobador_id'     => Auth::id(),
                    'aprobado_at'      => now(),
                    'incidencia_id'    => $this->incidencia_id,
                ]);

                $this->dispatch('cerrar-modal', id: 'modalGeneradorInsumos');
                $this->dispatch('mostrar-toast', mensaje: 'Movimiento ejecutado directamente.', tipo: 'success');
            } else {
                // Flujo Estándar (Crear Borrador)
                MovimientoInsumo::create([
                    'insumo_id'        => $insumo->id,
                    'tipo_operacion'   => $this->tipo_operacion,
                    'cantidad_movida'  => in_array($this->tipo_operacion, ['entrada_stock', 'salida_consumo', 'prestamo', 'devolucion']) ? $this->cantidad_movida : null,
                    'payload_anterior' => $payloadAnterior,
                    'payload_nuevo'    => $payloadNuevo,
                    'estado_workflow'  => 'borrador',
                    'justificacion'    => $this->justificacion,
                    'solicitante_id'   => Auth::id(),
                    'incidencia_id'    => $this->incidencia_id,
                ]);

                $this->dispatch('cerrar-modal', id: 'modalGeneradorInsumos');
                $this->dispatch('mostrar-toast', mensaje: 'Borrador de movimiento creado.', tipo: 'success');
            }

            $this->resetGenerador();
            
        } catch (\Exception $e) {
            Log::error('Error creando borrador insumo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al procesar el borrador.', tipo: 'error');
        }
    }

    public function resetGenerador(): void
    {
        $this->reset([
            'searchBN', 'searchSerial', 'searchDpto', 'searchTrabajador',
            'insumo_id', 'selected_insumo', 
            'tipo_operacion', 'cantidad_movida', 'justificacion',
            'bien_nacional', 'serial', 'nombre', 'descripcion', 'marca_id', 
            'categoria_insumo_id', 'unidad_medida', 'medida_actual', 'medida_minima',
            'reutilizable', 'instalable_en_equipo', 'estado_fisico',
            'departamento_id', 'trabajador_id', 'dispositivo_id', 'computador_id',
            'nueva_marca', 'nueva_categoria', 'nuevo_departamento'
        ]);
        $this->creando_marca = false;
        $this->creando_categoria = false;
        $this->creando_departamento = false;
        $this->paso_generador = 1;
        $this->resetValidation();
    }


    public function abrirEdicionBorrador(int $id): void
    {
        $mov = MovimientoInsumo::where('id', $id)
            ->where('solicitante_id', Auth::id())
            ->where('estado_workflow', 'borrador')
            ->firstOrFail();
        $this->editando_borrador_id = $id;
        $this->edit_justificacion   = $mov->justificacion ?? '';
        $this->dispatch('abrir-modal', id: 'modalEditarBorrador');
    }

    public function guardarEdicionBorrador(): void
    {
        $this->validate(['edit_justificacion' => 'required|string|min:10']);
        try {
            MovimientoInsumo::where('id', $this->editando_borrador_id)
                ->where('solicitante_id', Auth::id())
                ->where('estado_workflow', 'borrador')
                ->firstOrFail()
                ->update(['justificacion' => $this->edit_justificacion]);
            $this->editando_borrador_id = null;
            $this->edit_justificacion   = '';
            $this->dispatch('cerrar-modal', id: 'modalEditarBorrador');
            $this->dispatch('mostrar-toast', mensaje: 'Borrador actualizado.', tipo: 'success');
        } catch (\Exception $e) {
            $this->dispatch('mostrar-toast', mensaje: 'Error al actualizar el borrador.', tipo: 'error');
        }
    }

    public function eliminarBorrador(int $id): void
    {
        try {
            MovimientoInsumo::where('id', $id)
                ->where('solicitante_id', Auth::id())
                ->where('estado_workflow', 'borrador')
                ->firstOrFail()
                ->delete();
            $this->dispatch('mostrar-toast', mensaje: 'Borrador eliminado.', tipo: 'warning');
        } catch (\Exception $e) {
            $this->dispatch('mostrar-toast', mensaje: 'Error al eliminar el borrador.', tipo: 'error');
        }
    }

    public function enviarARevision(int $id): void
    {
        abort_if(Gate::denies('movimientos-insumos-enviar'), 403);
        try {
            $mov = MovimientoInsumo::where('id', $id)
                ->where('solicitante_id', Auth::id())
                ->where('estado_workflow', 'borrador')
                ->firstOrFail();
            $mov->update(['estado_workflow' => 'pendiente']);
            $this->dispatch('mostrar-toast', mensaje: 'Movimiento enviado a revisión.', tipo: 'success');
        } catch (\Exception $e) {
            Log::error('Error enviando movimiento insumo a revisión: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al enviar a revisión.', tipo: 'error');
        }
    }

    public function verDetalle(int $id): void
    {
        abort_if(Gate::denies('movimientos-insumos-ver'), 403);
        $this->movimiento_detalle = MovimientoInsumo::with([
            'insumo.marca', 'insumo.categoriaInsumo', 'solicitante', 'aprobador'
        ])->findOrFail($id);
        $this->dispatch('abrir-modal', id: 'modalDetalle');
    }

    public function aprobar(int $id): void
    {
        abort_if(Gate::denies('movimientos-insumos-aprobar'), 403);
        try {
            $mov = MovimientoInsumo::where('estado_workflow', 'pendiente')->findOrFail($id);
            $payload = $mov->payload_nuevo;
            $insumo = Insumo::withTrashed()->findOrFail($mov->insumo_id);

            // Re-verificar stock si es salida
            if (in_array($mov->tipo_operacion, ['salida_consumo', 'prestamo'])) {
                if ($mov->cantidad_movida > $insumo->medida_actual) {
                    throw new \Exception("No hay stock suficiente para completar esta operación (Disponible: {$insumo->medida_actual})");
                }
            }

            match ($mov->tipo_operacion) {
                'baja'         => $insumo->delete(),
                'toggle_activo' => tap($insumo)->update(['activo' => $payload['activo'] ?? !$insumo->activo]),
                'salida_consumo', 'prestamo' => tap($insumo)->update([
                    'medida_actual' => max(0, $insumo->medida_actual - ($mov->cantidad_movida ?? 0))
                ]),
                'entrada_stock' => tap($insumo)->update([
                    'medida_actual' => $insumo->medida_actual + ($mov->cantidad_movida ?? 0)
                ]),
                'devolucion'   => tap($insumo)->update([
                    'medida_actual' => $insumo->medida_actual + ($mov->cantidad_movida ?? 0)
                ]),
                default        => $insumo->update($payload),
            };

            $mov->update([
                'estado_workflow' => 'aprobado',
                'aprobador_id'   => Auth::id(),
                'aprobado_at'    => now(),
            ]);

            $this->dispatch('mostrar-toast', mensaje: 'Movimiento aprobado y aplicado.', tipo: 'success');
        } catch (\Exception $e) {
            Log::error('Error aprobando movimiento insumo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al aprobar.', tipo: 'error');
        }
    }

    public function abrirRechazo(int $id): void
    {
        abort_if(Gate::denies('movimientos-insumos-rechazar'), 403);
        $this->rechazando_id = $id;
        $this->motivo_rechazo = '';
        $this->dispatch('abrir-modal', id: 'modalRechazo');
    }

    public function confirmarRechazo(): void
    {
        abort_if(Gate::denies('movimientos-insumos-rechazar'), 403);
        $this->validate(['motivo_rechazo' => 'required|string|min:10']);
        try {
            $mov = MovimientoInsumo::where('estado_workflow', 'pendiente')
                ->findOrFail($this->rechazando_id);

            $mov->update([
                'estado_workflow' => 'rechazado',
                'motivo_rechazo'  => $this->motivo_rechazo,
                'aprobador_id'    => Auth::id(),
                'aprobado_at'     => now(),
            ]);

            $this->rechazando_id = null;
            $this->motivo_rechazo = '';
            $this->dispatch('cerrar-modal', id: 'modalRechazo');
            $this->dispatch('mostrar-toast', mensaje: 'Movimiento rechazado.', tipo: 'warning');
        } catch (\Exception $e) {
            Log::error('Error rechazando movimiento insumo: ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al rechazar.', tipo: 'error');
        }
    }

    // --- MÉTODOS PARA EL MODAL DE TRABAJADOR ---
    public function abrirModalTrabajador()
    {
        $this->dispatch('cerrar-modal', id: 'modalGeneradorInsumos'); // Diferente al de inventario
        $this->dispatch('abrir-modal', id: 'modalTrabajador');
    }

    public function cancelarModalTrabajador()
    {
        $this->reset(['nuevo_trab_nombres', 'nuevo_trab_apellidos', 'nuevo_trab_cedula', 'nuevo_trab_departamento_id']);
        $this->dispatch('cerrar-modal', id: 'modalTrabajador');
        $this->dispatch('abrir-modal', id: 'modalGeneradorInsumos');
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
            $this->dispatch('abrir-modal', id: 'modalGeneradorInsumos');
            $this->dispatch('mostrar-toast', mensaje: 'Trabajador creado.', tipo:'success');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error trabajador rapido (Panel Insumos): ' . $e->getMessage());
            $this->dispatch('mostrar-toast', mensaje: 'Error al registrar trabajador.', tipo:'error');
        }
    }
}
