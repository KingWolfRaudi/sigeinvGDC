<div>
    <!-- Header Especial -->
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                <i class="bi bi-arrow-left-right fs-3"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-0 text-body">Movimientos de Dispositivos</h2>
                <p class="text-muted mb-0">Historial de cambios, bajas y actualizaciones de dispositivos periféricos.</p>
            </div>
        </div>
    </div>

    <!-- Card de Búsqueda y Acciones -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-body border-end-0"><i class="bi bi-search text-primary"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por Bien Nacional, nombre, serial...">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <select wire:model.live="filtro_tipo" class="form-select shadow-sm">
                        <option value="">Todos los tipos</option>
                        <option value="actualizacion_datos">Actualización de Datos</option>
                        <option value="cambio_departamento">Cambio de Departamento</option>
                        <option value="reasignacion_trabajador">Reasignación Trabajador</option>
                        <option value="cambio_estado">Cambio de Estado Físico</option>
                        <option value="toggle_activo">Cambio de Estatus (Activar/Inactivar)</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>

                <div class="col-md-4 text-end d-flex gap-2 justify-content-end">
                    @can('reportes-excel')
                    <div class="dropdown">
                        <button class="btn btn-outline-success border-2 fw-bold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.movimientos.excel', ['segmento' => 'dispositivos', 'search' => $search, 'tipo_operacion' => $filtro_tipo]) }}"><i class="bi bi-filter me-2 text-success"></i> Vista Actual</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.movimientos.excel', ['segmento' => 'dispositivos']) }}"><i class="bi bi-list-check me-2 text-primary"></i> Todo el Historial</a></li>
                        </ul>
                    </div>
                    @endcan
                    @can('movimientos-dispositivos-crear')
                        <button wire:click="abrirGenerador" class="btn btn-primary shadow-sm fw-bold px-4">
                            <i class="bi bi-plus-lg me-1"></i> Nuevo Movimiento
                        </button>
                    @endcan
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
                            <th>Dispositivo</th>
                            <th>Tipo de Operación</th>
                            <th>Solicitante</th>
                            <th>Justificación</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr>
                            <td class="text-muted small">{{ $mov->id }}</td>
                            <td>
                                <strong>{{ $mov->dispositivo->nombre ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $mov->dispositivo->marca->nombre ?? '' }} · {{ $mov->dispositivo->bien_nacional ?? '' }}</small>
                            </td>
                            <td>
                                @php
                                $tipos = [
                                    'actualizacion_datos'   => ['label' => 'Actualización', 'color' => 'primary'],
                                    'cambio_departamento'   => ['label' => 'Cambio Dpto.', 'color' => 'info'],
                                    'reasignacion_trabajador' => ['label' => 'Reasignación', 'color' => 'info'],
                                    'cambio_estado'         => ['label' => 'Cambio Estado', 'color' => 'warning'],
                                    'toggle_activo'         => ['label' => 'Cambio de Estado', 'color' => 'secondary'],
                                    'baja'                  => ['label' => 'Baja', 'color' => 'danger'],
                                ];
                                $t = $tipos[$mov->tipo_operacion] ?? ['label' => $mov->tipo_operacion, 'color' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $t['color'] }}">{{ $t['label'] }}</span>
                            </td>
                            <td>
                                {{ $mov->solicitante->name ?? 'N/A' }}<br>
                                <small class="text-muted">{{ $mov->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <span title="{{ $mov->justificacion }}" class="d-inline-block text-truncate" style="max-width: 180px;">
                                    {{ $mov->justificacion }}
                                </span>
                            </td>
                            <td>
                                @php
                                $estados = [
                                    'borrador'          => ['label' => 'Borrador', 'color' => 'secondary'],
                                    'pendiente'         => ['label' => 'En Revisión', 'color' => 'warning text-body'],
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
                            <td class="small text-muted">{{ $mov->updated_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <button wire:click="verDetalle({{ $mov->id }})"
                                    class="btn btn-sm btn-outline-secondary" title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @if($pestana === 'borradores' && $mov->estado_workflow === 'borrador')
                                @can('movimientos-dispositivos-enviar')
                                <button wire:click="enviarARevision({{ $mov->id }})"
                                    wire:confirm="¿Enviar este borrador a revisión?"
                                    class="btn btn-sm btn-warning text-body" title="Enviar a Revisión">
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
                                @can('movimientos-dispositivos-aprobar')
                                <button wire:click="aprobar({{ $mov->id }})"
                                    wire:confirm="¿Confirmar aprobación y aplicar cambios?"
                                    class="btn btn-sm btn-success" title="Aprobar">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                @endcan
                                @can('movimientos-dispositivos-rechazar')
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
                <div class="modal-header bg-body-secondary">
                    <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>Detalle del Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    @if($movimiento_detalle)
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 text-primary">Información del Movimiento</h6>
                            <ul class="list-unstyled small">
                                <li><strong>Dispositivo:</strong> {{ $movimiento_detalle->dispositivo->nombre ?? 'N/A' }}</li>
                        @php
                        $tiposModal = [
                            'actualizacion_datos'      => 'Actualización de Datos',
                            'cambio_departamento'      => 'Cambio de Departamento',
                            'reasignacion_trabajador'  => 'Reasignación de Trabajador',
                            'cambio_estado'            => 'Cambio de Estado Físico',
                            'toggle_activo'            => 'Cambio de Estatus',
                            'baja'                     => 'Baja del Sistema',
                        ];
                        @endphp
                                <li><strong>Operación:</strong> {{ $tiposModal[$movimiento_detalle->tipo_operacion] ?? ucwords(str_replace('_', ' ', $movimiento_detalle->tipo_operacion)) }}</li>
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
                <div class="modal-footer bg-body-secondary">
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
                        rows="4" placeholder="Explique por qué este movimiento no puede ser aprobado..."></textarea>
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
                        Para cambiar los datos del dispositivo, elimina este borrador y
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


    {{-- ── MODAL GENERADOR DE MOVIMIENTOS (STANDARDIZED) ────────────────── --}}
    <div wire:ignore.self class="modal fade" id="modalGeneradorDispositivos" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90%;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-magic me-2"></i> Generador de Movimiento: Dispositivos
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetGenerador"></button>
                </div>

                <div class="modal-body p-0" style="max-height: 65vh; overflow-y: auto;">
                    <div class="bg-body-secondary border-bottom px-4 py-2 d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center {{ $paso_generador == 1 ? 'text-primary fw-bold' : 'text-muted' }}">
                            <span class="badge {{ $paso_generador == 1 ? 'bg-primary' : 'bg-secondary' }} me-2">1</span> Selección
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                        <div class="d-flex align-items-center {{ $paso_generador == 2 ? 'text-primary fw-bold' : 'text-muted' }}">
                            <span class="badge {{ $paso_generador == 2 ? 'bg-primary' : 'bg-secondary' }} me-2">2</span> Configuración
                        </div>
                    </div>

                    @if($paso_generador == 1)
                        {{-- PASO 1: SELECCIÓN --}}
                        <div class="p-4">
                            <div class="alert alert-info py-2 small mb-4">
                                <i class="bi bi-info-circle me-2"></i>Filtra y selecciona el dispositivo al que deseas aplicar un cambio.
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Bien Nacional</label>
                                    <input type="text" wire:model.live.debounce.300ms="searchBN" class="form-control form-control-sm" placeholder="Buscar BN...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Serial</label>
                                    <input type="text" wire:model.live.debounce.300ms="searchSerial" class="form-control form-control-sm" placeholder="Buscar Serial...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Departamento</label>
                                    <select wire:model.live="searchDpto" class="form-select form-select-sm">
                                        <option value="">Todos los Dptos...</option>
                                        @foreach(\App\Models\Departamento::where('activo',true)->orderBy('nombre')->get() as $d)
                                            <option value="{{ $d->id }}">{{ $d->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Trabajador</label>
                                    <select wire:model.live="searchTrabajador" class="form-select form-select-sm">
                                        <option value="">Todos los Trabajadores...</option>
                                        @foreach(\App\Models\Trabajador::where('activo',true)->orderBy('nombres')->get() as $t)
                                            <option value="{{ $t->id }}">{{ $t->nombres }} {{ $t->apellidos }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive border rounded bg-body shadow-sm" style="max-height: 400px;">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Dispositivo</th>
                                            <th>Identificadores</th>
                                            <th>Ubicación</th>
                                            <th>Estado/Pendientes</th>
                                            <th class="text-end">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($dispositivos_lista as $dl)
                                            <tr>
                                                <td>
                                                    <span class="fw-bold">{{ $dl->nombre }}</span><br>
                                                    <small class="text-muted">{{ $dl->marca->nombre ?? 'Sin Marca' }}</small>
                                                </td>
                                                <td>
                                                    <code class="text-primary small">BN: {{ $dl->bien_nacional }}</code><br>
                                                    <code class="text-secondary small">SN: {{ $dl->serial }}</code>
                                                </td>
                                                <td>
                                                    <small>{{ $dl->departamento->nombre ?? 'Sin Dpto' }}</small><br>
                                                    <small class="text-muted">{{ $dl->trabajador->nombres ?? '' }} {{ $dl->trabajador->apellidos ?? '' }}</small>
                                                </td>
                                                <td>
                                                    @if($dl->pendientes_count > 0)
                                                        <span class="badge bg-warning text-body animate__animated animate__pulse animate__infinite">
                                                            <i class="bi bi-exclamation-triangle me-1"></i> {{ $dl->pendientes_count }} Pendiente(s)
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success">Limpio</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button wire:click="seleccionarDispositivo({{ $dl->id }})" class="btn btn-sm btn-outline-primary px-3">
                                                        Seleccionar <i class="bi bi-chevron-right ms-1"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted italic">
                                                    <i class="bi bi-search text-primary d-block fs-3 mb-2"></i>
                                                    Usa los filtros para encontrar un dispositivo
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        {{-- PASO 2: CONFIGURACIÓN --}}
                        <div class="p-4 bg-body">
                            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                                <div>
                                    <h5 class="mb-0 text-body">{{ $nombre }} - <span class="text-muted small">{{ $bien_nacional }}</span></h5>
                                    <p class="text-muted small mb-0">Configura los cambios que deseas proponer para este dispositivo.</p>
                                </div>
                                @if($incidencia_id)
                                    <div class="alert alert-primary py-1 px-3 mb-0 me-3 shadow-sm border-0 animate__animated animate__fadeIn">
                                        <i class="bi bi-link-45deg me-1"></i>
                                        <span class="small fw-bold">Vinculado a Incidencia #{{ str_pad($incidencia_id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                @endif
                                <button wire:click="$set('paso_generador', 1)" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Cambiar Equipo
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-md-9 border-end">
                                    {{-- Reutilización del formulario partial --}}
                                    @include('livewire.inventario.partials._form_fields_dispositivos')
                                </div>
                                <div class="col-md-3 bg-body-secondary p-3">
                                    <label class="form-label fw-bold text-danger">Justificación del Cambio <span class="text-danger">*</span></label>
                                    <textarea wire:model="justificacion" class="form-control mb-3" rows="8" 
                                        placeholder="Describe el motivo de este movimiento (Reparación, cambio de usuario, actualización, etc.)"></textarea>
                                    @error('justificacion') <span class="text-danger small d-block mb-3">{{ $message }}</span> @enderror
                                    
                                    <div class="alert alert-warning small py-2">
                                        <i class="bi bi-shield-lock me-1"></i> Este cambio se guardará como <strong>Borrador</strong> y deberá ser enviado a revisión.
                                    </div>
                                    
                                    <button wire:click="guardarBorrador" class="btn btn-success w-100 fw-bold py-2 mt-2">
                                        <i class="bi bi-save me-2"></i> Crear Borrador
                                    </button>
                                </div>
                            </div>
                        </div>
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
                <div class="modal-footer bg-body-secondary px-4 py-3">
                    <button type="button" class="btn btn-secondary px-4" wire:click="cancelarModalTrabajador">Volver</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" wire:click="guardarTrabajadorRapido">
                        <i class="bi bi-save me-1"></i> Guardar Trabajador
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
