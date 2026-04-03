<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-5">
            <h3 class="mb-1"><i class="bi bi-arrow-left-right me-2"></i>Movimientos de Dispositivos</h3>
            <p class="text-muted small mb-0">Historial de cambios, bajas y actualizaciones de dispositivos periféricos.</p>
        </div>
        <div class="col-md-4">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                placeholder="Buscar por código, nombre, serial...">
        </div>
        <div class="col-md-3">
            <select wire:model.live="filtro_tipo" class="form-select">
                <option value="">Todos los tipos</option>
                <option value="actualizacion_datos">Actualización de Datos</option>
                <option value="cambio_departamento">Cambio de Departamento</option>
                <option value="reasignacion_trabajador">Reasignación Trabajador</option>
                <option value="cambio_estado">Cambio de Estado</option>
                <option value="toggle_activo">Cambio de Estatus</option>
                <option value="baja">Baja</option>
            </select>
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
        @can('movimientos-dispositivos-aprobar')
        <li class="nav-item">
            <button wire:click="$set('pestana', 'pendientes')"
                class="nav-link {{ $pestana === 'pendientes' ? 'active' : '' }}">
                <i class="bi bi-hourglass-split me-1"></i> Pendientes de Aprobación
                @if($conteo['pendientes'] > 0)
                    <span class="badge bg-danger ms-1">{{ $conteo['pendientes'] }}</span>
                @endif
            </button>
        </li>
        @endcan
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
                                <small class="text-muted">{{ $mov->dispositivo->marca->nombre ?? '' }} · {{ $mov->dispositivo->codigo ?? '' }}</small>
                            </td>
                            <td>
                                @php
                                $tipos = [
                                    'actualizacion_datos'   => ['label' => 'Actualización', 'color' => 'primary'],
                                    'cambio_departamento'   => ['label' => 'Cambio Dpto.', 'color' => 'info'],
                                    'reasignacion_trabajador' => ['label' => 'Reasignación', 'color' => 'info'],
                                    'cambio_estado'         => ['label' => 'Cambio Estado', 'color' => 'warning'],
                                    'toggle_activo'         => ['label' => 'Cambio de Estatus', 'color' => 'secondary'],
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
                                    class="btn btn-sm btn-warning text-dark" title="Enviar a Revisión">
                                    <i class="bi bi-send"></i>
                                </button>
                                @endcan
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
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>Detalle del Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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
                <div class="modal-body">
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
</div>
