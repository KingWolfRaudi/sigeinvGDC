<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-5">
            <h3 class="mb-1"><i class="bi bi-arrow-left-right me-2"></i>Movimientos de Insumos</h3>
            <p class="text-muted small mb-0">Entradas, salidas, préstamos y actualizaciones del almacén.</p>
        </div>
        <div class="col-md-7 text-end d-flex justify-content-end gap-2">
            @can('reportes-excel')
            <div class="dropdown">
                <button class="btn btn-outline-success border-2 fw-bold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </button>
                <ul class="dropdown-menu shadow border-0">
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('reportes.movimientos.excel', ['segmento' => 'insumos', 'search' => $search, 'tipo_operacion' => $filtro_tipo]) }}">
                            <i class="bi bi-filter me-2 text-success"></i> Vista Actual (Filtrado)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('reportes.movimientos.excel', ['segmento' => 'insumos']) }}">
                            <i class="bi bi-list-check me-2 text-primary"></i> Todo el Historial
                        </a>
                    </li>
                </ul>
            </div>
            @endcan

            @can('movimientos-insumos-crear')
            <button wire:click="abrirGenerador" class="btn btn-primary shadow-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Nuevo Movimiento
            </button>
            @endcan
        </div>
        <div class="col-md-12 mt-3">
            <div class="row g-2">
                <div class="col-md-9">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                        placeholder="Buscar por nombre, Bien Nacional, serial o justificación...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filtro_tipo" class="form-select">
                        <option value="">Todos los tipos</option>
                        <option value="entrada_stock">Entrada de Stock</option>
                        <option value="salida_consumo">Salida de Insumo</option>
                        <option value="prestamo">Préstamo</option>
                        <option value="devolucion">Devolución</option>
                        <option value="actualizacion_datos">Actualización de Datos</option>
                        <option value="toggle_activo">Cambio de Estado</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button wire:click="$set('pestana', 'borradores')"
                class="nav-link {{ $pestana === 'borradores' ? 'active' : '' }}">
                <i class="bi bi-pencil-square me-1"></i> Mis Borradores
                @if($conteo['borradores'] > 0)
                    <span class="badge bg-secondary ms-1">{{ $conteo['borradores'] }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="$set('pestana', 'pendientes')"
                class="nav-link {{ $pestana === 'pendientes' ? 'active' : '' }}">
                <i class="bi bi-hourglass-split me-1"></i> Pendientes de Aprobación
                @if($conteo['pendientes'] > 0)
                    <span class="badge bg-danger ms-1">{{ $conteo['pendientes'] }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="$set('pestana', 'historico')"
                class="nav-link {{ $pestana === 'historico' ? 'active' : '' }}">
                <i class="bi bi-clock-history me-1"></i> Histórico
            </button>
        </li>
    </ul>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Insumo / Herramienta</th>
                            <th>Tipo de Operación</th>
                            <th>Cantidad</th>
                            <th>Solicitante</th>
                            <th>Justificación</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr>
                            <td class="text-muted small">{{ $mov->id }}</td>
                            <td>
                                <strong>{{ $mov->insumo->nombre ?? 'N/A' }}</strong><br>
                                <small class="text-muted">
                                    {{ $mov->insumo->categoriaInsumo->nombre ?? '' }} ·
                                    {{ $mov->insumo->marca->nombre ?? '' }}
                                </small>
                            </td>
                            <td>
                                @php
                                $tipos = [
                                    'entrada_stock'      => ['label' => 'Entrada Stock', 'color' => 'success'],
                                    'salida_consumo'     => ['label' => 'Salida Consumo', 'color' => 'warning text-dark'],
                                    'prestamo'           => ['label' => 'Préstamo', 'color' => 'info'],
                                    'devolucion'         => ['label' => 'Devolución', 'color' => 'primary'],
                                    'actualizacion_datos' => ['label' => 'Actualización', 'color' => 'secondary'],
                                    'toggle_activo'      => ['label' => 'Cambio de Estado', 'color' => 'secondary'],
                                    'baja'               => ['label' => 'Baja', 'color' => 'danger'],
                                ];
                                $t = $tipos[$mov->tipo_operacion] ?? ['label' => $mov->tipo_operacion, 'color' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $t['color'] }}">{{ $t['label'] }}</span>
                            </td>
                            <td>
                                @if($mov->cantidad_movida)
                                    <strong>{{ floatval($mov->cantidad_movida) }}</strong>
                                    <small class="text-muted">{{ $mov->insumo->unidad_medida ?? '' }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                {{ $mov->solicitante->name ?? 'N/A' }}<br>
                                <small class="text-muted">{{ $mov->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <span title="{{ $mov->justificacion }}" class="d-inline-block text-truncate" style="max-width: 160px;">
                                    {{ $mov->justificacion }}
                                </span>
                            </td>
                            <td>
                                @php
                                $estados = [
                                    'borrador'          => ['label' => 'Borrador', 'color' => 'secondary'],
                                    'pendiente'         => ['label' => 'En Revisión', 'color' => 'warning text-dark'],
                                    'aprobado'          => ['label' => 'Aprobado', 'color' => 'success'],
                                    'rechazado'         => ['label' => 'Rechazado', 'color' => 'danger'],
                                    'ejecutado_directo' => ['label' => 'Ejecutado', 'color' => 'dark'],
                                ];
                                $e = $estados[$mov->estado_workflow] ?? ['label' => $mov->estado_workflow, 'color' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $e['color'] }}">{{ $e['label'] }}</span>
                                @if($mov->motivo_rechazo)
                                <br><small class="text-danger" title="{{ $mov->motivo_rechazo }}">
                                    <i class="bi bi-x-circle me-1"></i>{{ \Str::limit($mov->motivo_rechazo, 30) }}
                                </small>
                                @endif
                            </td>
                            <td class="text-end">
                                <button wire:click="verDetalle({{ $mov->id }})"
                                    class="btn btn-sm btn-outline-secondary" title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @if($pestana === 'borradores' && $mov->estado_workflow === 'borrador')
                                @can('movimientos-insumos-enviar')
                                <button wire:click="enviarARevision({{ $mov->id }})"
                                    wire:confirm="¿Enviar este borrador a revisión?"
                                    class="btn btn-sm btn-warning text-dark" title="Enviar a Revisión">
                                    <i class="bi bi-send"></i>
                                </button>
                                @endcan
                                {{-- Editar justificación --}}
                                <button wire:click="abrirEdicionBorrador({{ $mov->id }})"
                                    class="btn btn-sm btn-outline-primary" title="Editar Justificación">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                {{-- Cancelar/Eliminar borrador --}}
                                <button wire:click="eliminarBorrador({{ $mov->id }})"
                                    wire:confirm="¿Eliminar este borrador? Esta acción no se puede deshacer."
                                    class="btn btn-sm btn-outline-danger" title="Cancelar Borrador">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                                @if($pestana === 'pendientes' && $mov->estado_workflow === 'pendiente')
                                @can('movimientos-insumos-aprobar')
                                <button wire:click="aprobar({{ $mov->id }})"
                                    wire:confirm="¿Confirmar aprobación y aplicar al stock?"
                                    class="btn btn-sm btn-success" title="Aprobar">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                @endcan
                                @can('movimientos-insumos-rechazar')
                                <button wire:click="abrirRechazo({{ $mov->id }})"
                                    class="btn btn-sm btn-danger" title="Rechazar">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                @endcan
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                @if($pestana === 'borradores') No tienes borradores activos.
                                @elseif($pestana === 'pendientes') No hay movimientos pendientes de aprobación.
                                @else No hay registros en el histórico.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $movimientos->links() }}</div>
        </div>
    </div>

    {{-- Modal: Ver Detalle --}}
    <div wire:ignore.self class="modal fade" id="modalDetalle" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>Detalle del Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    @if($movimiento_detalle)
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 text-primary">Información del Movimiento</h6>
                            <ul class="list-unstyled small">
                                <li><strong>Insumo:</strong> {{ $movimiento_detalle->insumo->nombre ?? 'N/A' }}</li>
                                <li><strong>Categoría:</strong> {{ $movimiento_detalle->insumo->categoriaInsumo->nombre ?? 'N/A' }}</li>
                        @php
                        $tiposModal = [
                            'entrada_stock'       => 'Entrada de Stock',
                            'salida_consumo'      => 'Salida de Insumo',
                            'prestamo'            => 'Préstamo',
                            'devolucion'          => 'Devolución',
                            'actualizacion_datos'  => 'Actualización de Datos',
                            'toggle_activo'        => 'Cambio de Estatus',
                            'baja'                 => 'Baja del Sistema',
                        ];
                        @endphp
                                <li><strong>Operación:</strong> {{ $tiposModal[$movimiento_detalle->tipo_operacion] ?? ucwords(str_replace('_', ' ', $movimiento_detalle->tipo_operacion)) }}</li>
                                @if($movimiento_detalle->cantidad_movida)
                                <li><strong>Cantidad:</strong> {{ floatval($movimiento_detalle->cantidad_movida) }} {{ $movimiento_detalle->insumo->unidad_medida ?? '' }}</li>
                                @endif
                                <li><strong>Estado:</strong> {{ strtoupper($movimiento_detalle->estado_workflow) }}</li>
                                <li><strong>Solicitante:</strong> {{ $movimiento_detalle->solicitante->name ?? 'N/A' }}</li>
                                @if($movimiento_detalle->aprobador)
                                <li><strong>Revisado por:</strong> {{ $movimiento_detalle->aprobador->name }}</li>
                                <li><strong>Fecha resolución:</strong> {{ $movimiento_detalle->aprobado_at?->format('d/m/Y H:i') }}</li>
                                @endif
                                <li class="mt-2"><strong>Justificación:</strong><br>
                                    <em class="text-muted">{{ $movimiento_detalle->justificacion }}</em>
                                </li>
                                @if($movimiento_detalle->motivo_rechazo)
                                <li class="mt-2 text-danger"><strong>Motivo de Rechazo:</strong><br>
                                    <em>{{ $movimiento_detalle->motivo_rechazo }}</em>
                                </li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 text-success">Modificación Propuesta</h6>
                            @include('livewire.movimientos._detalle-cambios', ['movimiento_detalle' => $movimiento_detalle])
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Rechazar --}}
    <div wire:ignore.self class="modal fade" id="modalRechazo" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-octagon me-2"></i>Rechazar Movimiento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        wire:click="$set('rechazando_id', null)"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    <p class="text-muted small">Indique el motivo del rechazo.</p>
                    <label class="form-label fw-bold">Motivo del Rechazo <span class="text-danger">*</span></label>
                    <textarea wire:model="motivo_rechazo" class="form-control @error('motivo_rechazo') is-invalid @enderror"
                        rows="4" placeholder="Explique detalladamente por qué este movimiento no puede ser aprobado..."></textarea>
                    @error('motivo_rechazo') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        wire:click="$set('rechazando_id', null)">Cancelar</button>
                    <button wire:click="confirmarRechazo" class="btn btn-danger">
                        <i class="bi bi-x-octagon me-1"></i> Confirmar Rechazo
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Editar Justificación del Borrador --}}
    <div wire:ignore.self class="modal fade" id="modalEditarBorrador" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Borrador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        wire:click="$set('editando_borrador_id', null)"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    <p class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Puedes corregir la justificación antes de enviar el borrador a revisión.
                        Para cambiar los datos del insumo, elimina este borrador y
                        edita el registro directamente desde el inventario.
                    </p>
                    <label class="form-label fw-bold">Justificación <span class="text-danger">*</span></label>
                    <textarea wire:model="edit_justificacion"
                        class="form-control @error('edit_justificacion') is-invalid @enderror"
                        rows="5"
                        placeholder="Describa el motivo del cambio (mínimo 10 caracteres)..."></textarea>
                    @error('edit_justificacion') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        wire:click="$set('editando_borrador_id', null)">Cancelar</button>
                    <button wire:click="guardarEdicionBorrador" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Modal Generador de Movimiento (Estandarizado) ── --}}
    <div wire:ignore.self class="modal fade" id="modalGeneradorInsumos" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="max-width: 90%;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-plus-circle-fill me-2 text-primary"></i>
                        Generador de Movimiento: Insumos
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetGenerador"></button>
                </div>

                <div class="modal-body p-0" style="max-height: 65vh; overflow-y: auto;">
                    {{-- Barra de Progreso --}}
                    <div class="bg-light border-bottom px-4 py-2 d-flex justify-content-center gap-4">
                        <div class="d-flex align-items-center {{ $paso_generador === 1 ? 'text-primary' : 'text-muted' }}">
                            <span class="badge rounded-circle {{ $paso_generador === 1 ? 'bg-primary' : 'bg-secondary' }} me-2">1</span>
                            <small class="fw-bold">Selección</small>
                        </div>
                        <div class="text-muted opacity-50"><i class="bi bi-chevron-right"></i></div>
                        <div class="d-flex align-items-center {{ $paso_generador === 2 ? 'text-primary' : 'text-muted' }}">
                            <span class="badge rounded-circle {{ $paso_generador === 2 ? 'bg-primary' : 'bg-secondary' }} me-2">2</span>
                            <small class="fw-bold">Configuración</small>
                        </div>
                    </div>

                    <div class="p-4" style="min-height: 400px;">
                        @if($paso_generador === 1)
                            {{-- Paso 1: Buscador Estándar (Grilla 2x2) --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Bien Nacional</label>
                                    <input type="text" wire:model.live.debounce.300ms="searchBN" class="form-control" placeholder="Buscar BN...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Serial</label>
                                    <input type="text" wire:model.live.debounce.300ms="searchSerial" class="form-control" placeholder="Buscar Serial...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Departamento</label>
                                    <select wire:model.live="searchDpto" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($departamentos as $d)
                                            <option value="{{ $d->id }}">{{ $d->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Trabajador</label>
                                    <select wire:model.live="searchTrabajador" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($trabajadores as $t)
                                            <option value="{{ $t->id }}">{{ $t->nombres }} {{ $t->apellidos }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle border rounded shadow-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Identificación</th>
                                            <th>Insumo / Referencia</th>
                                            <th>Categoría</th>
                                            <th>Stock Actual</th>
                                            <th>Ubicación / Responsable</th>
                                            <th class="text-end">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($insumos_lista as $ins)
                                            <tr>
                                                <td>
                                                    <span class="fw-bold fs-6">BN: {{ $ins->bien_nacional ?? 'N/A' }}</span><br>
                                                    <small class="text-muted">Serial: {{ $ins->serial ?? 'N/A' }}</small>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $ins->nombre }}</div>
                                                    <small class="text-muted">{{ $ins->marca->nombre ?? 'Sin Marca' }}</small>
                                                </td>
                                                <td><span class="badge bg-light text-dark border">{{ $ins->categoriaInsumo->nombre ?? 'Sin Categ.' }}</span></td>
                                                <td>
                                                    <span class="fw-bold">{{ floatval($ins->medida_actual) }}</span> {{ $ins->unidad_medida }}
                                                </td>
                                                <td>
                                                    @if($ins->departamento)
                                                        <small class="d-block text-truncate" style="max-width: 150px;">
                                                            <i class="bi bi-building me-1"></i>{{ $ins->departamento->nombre }}
                                                        </small>
                                                    @endif
                                                    @if($ins->trabajador)
                                                        <small class="text-muted text-truncate" style="max-width: 150px;">
                                                            <i class="bi bi-person me-1"></i>{{ $ins->trabajador->nombres }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($ins->pendientes_count > 0)
                                                        <span class="badge bg-warning text-dark me-2">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $ins->pendientes_count }} Pendiente(s)
                                                        </span>
                                                    @endif
                                                    <button wire:click="seleccionarInsumo({{ $ins->id }})" class="btn btn-primary btn-sm px-3 shadow-sm">
                                                        Seleccionar <i class="bi bi-arrow-right-short ms-1"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted small">
                                                    @if(empty($searchBN) && empty($searchSerial) && empty($searchDpto) && empty($searchTrabajador))
                                                        <i class="bi bi-search display-6 d-block mb-2 opacity-50"></i>
                                                        Utilice los filtros superiores para localizar el insumo.
                                                    @else
                                                        No se encontraron resultados para los filtros aplicados.
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if($paso_generador === 2 && $selected_insumo)
                            {{-- Paso 2: Formulario --}}
                            <div class="row">
                                <div class="col-md-9 border-end">
                                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded border border-primary border-opacity-25">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                            <i class="bi bi-box-seam fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $selected_insumo->nombre }}</h6>
                                            <small class="text-muted">
                                                BN: {{ $selected_insumo->bien_nacional }} · Serial: {{ $selected_insumo->serial }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-bold small">Tipo de Operación <span class="text-danger">*</span></label>
                                            <select wire:model.live="tipo_operacion" class="form-select @error('tipo_operacion') is-invalid @enderror">
                                                <option value="actualizacion_datos">Actualizar Datos de Inventario</option>
                                                <option value="entrada_stock">Entrada de Stock (+)</option>
                                                <option value="salida_consumo">Salida de Insumo (-)</option>
                                                <option value="prestamo">Préstamo / Asignación Temporal</option>
                                                <option value="devolucion">Devolución de Herramienta</option>
                                                <option value="toggle_activo">Habilitar / Inhabilitar</option>
                                                <option value="baja">Baja Definitiva</option>
                                            </select>
                                            @error('tipo_operacion') <span class="text-danger tiny">{{ $message }}</span> @enderror
                                        </div>

                                        @if(in_array($tipo_operacion, ['entrada_stock', 'salida_consumo', 'prestamo', 'devolucion']))
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-bold small">Cantidad a Procesar <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" wire:model="cantidad_movida" step="1" min="1" 
                                                    class="form-control @error('cantidad_movida') is-invalid @enderror">
                                                <span class="input-group-text bg-light tiny">{{ $unidad_medida }}</span>
                                            </div>
                                            @error('cantidad_movida') <span class="text-danger tiny">{{ $message }}</span> @enderror
                                        </div>
                                        @endif
                                    </div>

                                    <hr class="my-3 opacity-25">
                                    
                                    {{-- Campos Reutilizables --}}
                                    <div class="{{ !in_array($tipo_operacion, ['actualizacion_datos', 'prestamo']) ? 'opacity-50 pe-none' : '' }} {{ $tipo_operacion === 'prestamo' ? 'border border-primary border-2 p-3 rounded bg-light' : '' }}">
                                        @if($tipo_operacion === 'prestamo')
                                            <div class="mb-3 text-primary fw-bold">
                                                <i class="bi bi-person-check me-1"></i> Seleccione el Destino del Préstamo
                                            </div>
                                        @endif
                                        @include('livewire.inventario.partials._form_fields_insumos')
                                    </div>
                                </div>

                                <div class="col-md-3 bg-light bg-opacity-50 rounded p-3">
                                    <label class="form-label fw-bold text-danger">Justificación del Cambio <span class="text-danger">*</span></label>
                                    
                                    <textarea wire:model="justificacion" class="form-control mb-3 @error('justificacion') is-invalid @enderror" 
                                        rows="6" placeholder="Explica detalladamente el motivo de este movimiento..."></textarea>
                                    @error('justificacion') <small class="text-danger d-block mb-3">{{ $message }}</small> @enderror
                                    <div class="alert alert-warning small py-2">
                                        <i class="bi bi-shield-lock me-1"></i> Este cambio se guardará como <strong>Borrador</strong> y deberá ser enviado a revisión.
                                    </div>
                                    <div class="alert alert-info border-0 shadow-sm py-2 px-3 small">
                                        <i class="bi bi-info-circle-fill me-1"></i>
                                        @if($tipo_operacion === 'actualizacion_datos')
                                            El sistema detectará automáticamente los campos modificados.
                                        @else
                                            Se registrará una operación de <strong>{{ ucwords(str_replace('_',' ',$tipo_operacion)) }}</strong>.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    @if($paso_generador === 2)
                        <button type="button" wire:click="$set('paso_generador', 1)" class="btn btn-outline-secondary px-4 me-auto">
                            <i class="bi bi-arrow-left me-1"></i> Volver a Selección
                        </button>
                    @endif
                    
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" wire:click="resetGenerador">Cancelar</button>
                    
                    @if($paso_generador === 2)
                        <button wire:click="guardarBorrador" class="btn btn-primary px-5 shadow-sm fw-bold">
                            <i class="bi bi-save me-1"></i> Guardar Borrador
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Trabajador (Creación Rápida) -->
    <div wire:ignore.self class="modal fade" id="modalTrabajador" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content border-primary shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="bi bi-person-plus-fill me-2"></i>Nuevo Trabajador</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelarModalTrabajador"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nuevo_trab_nombres') is-invalid @enderror" wire:model="nuevo_trab_nombres" placeholder="Ej: Juan">
                            @error('nuevo_trab_nombres') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nuevo_trab_apellidos') is-invalid @enderror" wire:model="nuevo_trab_apellidos" placeholder="Ej: Pérez">
                            @error('nuevo_trab_apellidos') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Cédula (Opcional)</label>
                        <input type="text" class="form-control @error('nuevo_trab_cedula') is-invalid @enderror" wire:model="nuevo_trab_cedula" placeholder="Ej: 12345678">
                        @error('nuevo_trab_cedula') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Departamento <span class="text-danger">*</span></label>
                        <select class="form-select @error('nuevo_trab_departamento_id') is-invalid @enderror" wire:model="nuevo_trab_departamento_id">
                            <option value="">Seleccione...</option>
                            @foreach(\App\Models\Departamento::orderBy('nombre')->get() as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                            @endforeach
                        </select>
                        @error('nuevo_trab_departamento_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="alert alert-info py-2 small mb-0 border-0 shadow-sm">
                        <i class="bi bi-info-circle-fill me-1"></i> Se creará el registro completo del trabajador en la base de datos.
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" wire:click="cancelarModalTrabajador">Volver</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" wire:click="guardarTrabajadorRapido">
                        <i class="bi bi-save me-1"></i> Guardar Trabajador
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
