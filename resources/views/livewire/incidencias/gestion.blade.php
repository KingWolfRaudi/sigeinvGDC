<div>
    <!-- Header Especial -->
    @if(!$ocultarTitulos)
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                    <i class="bi bi-list-task fs-3"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-body">Gestión de Incidencias</h2>
                    <p class="text-muted mb-0">Seguimiento de problemas técnicos, soporte a usuarios y mantenimiento de activos.</p>
                </div>
            </div>
            <div class="text-end">
                <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-collection me-1"></i> Total Tickets: <span class="fw-bold fs-6 ms-1">{{ $incidencias->total() }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Card de Búsqueda y Acciones -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <!-- Botones de Acción (Arriba) -->
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div class="text-muted small">
                    <i class="bi bi-filter-circle me-1"></i> Use los filtros para segmentar la vista
                </div>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-success border-2 fw-bold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-file-earmark-excel me-1"></i> Exportar
                        </button>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.incidencias.excel', ['search' => $search, 'departamento_id' => $filtro_departamento, 'estado' => $filtro_estado]) }}"><i class="bi bi-filter me-2 text-success"></i> Vista Actual (Excel)</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.incidencias.excel') }}"><i class="bi bi-list-check me-2 text-primary"></i> Todo el Historial (Excel)</a></li>
                        </ul>
                    </div>
                    @can('crear-ticket')
                    <button type="button" class="btn btn-primary shadow-sm fw-bold px-4" wire:click="crear">
                        <i class="bi bi-plus-lg me-1"></i> Nueva Incidencia
                    </button>
                    @endcan
                </div>
            </div>

            <!-- Fila de Filtros -->
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-body border-end-0"><i class="bi bi-search text-primary"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar por folio, descripción o nombres..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                
                @if(!$ocultarTitulos)
                <div class="col-md-2">
                    <select class="form-select shadow-sm border-2" wire:model.live="filtro_departamento">
                        <option value="">Todos los Departamentos</option>
                        @foreach($departamentos as $depto)
                            <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-2">
                    <select class="form-select shadow-sm border-2" wire:model.live="filtro_problema">
                        <option value="">Todas las Categorías</option>
                        @foreach($problemas_dropdown as $prob)
                            <option value="{{ $prob->id }}">{{ $prob->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                @if(Auth::user()->hasRole(['super-admin', 'administrador', 'coordinador']))
                <div class="col-md-2">
                    <select class="form-select shadow-sm border-2" wire:model.live="filtro_tecnico">
                        <option value="">Todos los Técnicos</option>
                        @foreach($tecnicos_dropdown as $tec)
                            <option value="{{ $tec->id }}">{{ $tec->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-md-2">
                    <select class="form-select shadow-sm border-2" wire:model.live="filtro_estado">
                        <option value="">Todos los Estados</option>
                        <option value="abierto">Abiertos</option>
                        <option value="solventado">Solventados</option>
                        <option value="cerrados">Cerrados</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal (Tabla) -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light py-3">
                        <tr>
                            <th class="ps-4" wire:click="sortBy('id')" style="cursor: pointer;">
                                Folio @if($sortField === 'id') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                                Fecha @if($sortField === 'created_at') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th wire:click="sortBy('trabajador_id')" style="cursor: pointer;">
                                Trabajador / Depto @if($sortField === 'trabajador_id') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th wire:click="sortBy('problema_id')" style="cursor: pointer;">
                                Problema @if($sortField === 'problema_id') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th wire:click="sortBy('user_id')" style="cursor: pointer;">
                                Técnico @if($sortField === 'user_id') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th>Activo Relacionado</th>
                            <th class="text-center" wire:click="sortBy('cerrado')" style="cursor: pointer;">
                                Estado @if($sortField === 'cerrado') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidencias as $inc)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-primary">#{{ str_pad($inc->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>{{ $inc->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if($inc->trabajador)
                                            <span class="fw-bold text-body">{{ $inc->trabajador->nombres }} {{ $inc->trabajador->apellidos }}</span>
                                        @else
                                            <span class="fw-bold text-body">{{ $inc->creator->name ?? 'Usuario Sistema' }} <span class="badge bg-secondary ms-1 py-0 px-1" style="font-size: 0.65rem;">Externo</span></span>
                                        @endif
                                        <small class="text-muted">
                                            {{ $inc->departamento->nombre ?? 'Sin Departamento' }}
                                            @if($inc->dependencia)
                                                <br><i class="bi bi-arrow-return-right"></i> {{ $inc->dependencia->nombre }}
                                            @endif
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-body-secondary text-body border d-block mb-1">{{ $inc->formato_problema ?? $inc->problema->nombre }}</span>
                                    <small class="text-primary"><i class="bi bi-diagram-3"></i> {{ $inc->problema->especialidad->nombre ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($inc->tecnico)
                                        <span class="text-body">{{ $inc->tecnico->name }}</span>
                                    @else
                                        <span class="text-danger small fst-italic"><i class="bi bi-hourglass-split"></i> Pendiente</span>
                                    @endif
                                </td>
                                <td>
                                    @if($inc->modelo)
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold small text-truncate" style="max-width: 150px;">
                                                <i class="bi bi-box me-1"></i>
                                                {{ class_basename($inc->modelo_type) }}
                                            </span>
                                            <small class="text-muted">
                                                {{ $inc->modelo->bien_nacional ?? $inc->modelo->serial ?? $inc->modelo->nombre }}
                                            </small>
                                            @if($inc->amerita_movimiento)
                                                <span class="badge bg-warning text-body mt-1" style="font-size: 0.6rem; width: fit-content;">
                                                    <i class="bi bi-arrow-left-right me-1"></i> Movimiento
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($inc->cerrado)
                                        <span class="badge bg-dark rounded-pill px-3">Cerrado</span>
                                    @elseif($inc->solventado)
                                        <span class="badge bg-success rounded-pill px-3">Solventado</span>
                                    @else
                                        <span class="badge bg-warning text-body rounded-pill px-3">En Curso</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        @can('reportes-pdf')
                                            <a href="{{ route('reportes.incidencia.ficha', $inc->id) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Descargar PDF">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                        @endcan
                                        @if($inc->amerita_movimiento && $inc->modelo_id)
                                            <button wire:click="crearMovimiento({{ $inc->id }})" class="btn btn-sm btn-outline-warning" title="Generar Movimiento">
                                                <i class="bi bi-arrow-left-right"></i>
                                            </button>
                                        @endif
                                        <button wire:click="ver({{ $inc->id }})" class="btn btn-sm btn-outline-info" title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @if(!$inc->cerrado)
                                            <button wire:click="editar({{ $inc->id }})" class="btn btn-sm btn-outline-primary" title="Editar / Gestionar">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        @elseif(Auth::user()->can('admin-incidencias'))
                                            <button wire:click="editar({{ $inc->id }})" class="btn btn-sm btn-outline-secondary" title="Historial / Reabrir">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    No se encontraron incidencias que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($incidencias->hasPages())
            <div class="card-footer bg-body py-3 border-0">
                {{ $incidencias->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario -->
    <div wire:ignore.self class="modal fade" id="modalIncidencia" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title h6">
                        <i class="bi bi-{{ $incidencia_id ? ($es_lectura ? 'eye' : 'pencil-square') : 'plus-circle' }} me-2"></i>
                        {{ $incidencia_id ? ($es_lectura ? 'Detalles de Incidencia (Solo Lectura)' : 'Gestionar Incidencia') : 'Reportar Nueva Incidencia' }}
                    </h5>
                    @if($incidencia_id)
                        <span class="badge bg-body text-primary ms-3">#{{ str_pad($incidencia_id, 5, '0', STR_PAD_LEFT) }}</span>
                    @endif
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetForm"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body p-4 bg-body-secondary" style="max-height: 75vh; overflow-y: auto;">
                        @if($incidencia_id && $cerrado)
                            @if($cierre_irreversible)
                                <div class="alert alert-danger d-flex align-items-center py-2 mb-3 border-0 shadow-sm" style="font-size: 0.85rem;">
                                    <i class="bi bi-shield-lock-fill me-2 fs-5"></i>
                                    <div>
                                        <strong>Cierre Irreversible:</strong> Esta incidencia ha sido finalizada y no permite cambios adicionales según las reglas del sistema.
                                    </div>
                                </div>
                            @elseif(Auth::user()->can('admin-incidencias') && !$es_lectura)
                                <div class="alert alert-warning d-flex align-items-center py-2 mb-3 border-0 shadow-sm" style="font-size: 0.85rem;">
                                    <i class="bi bi-unlock-fill me-2 fs-5"></i>
                                    <div>
                                        <strong>Modo Administrador:</strong> Esta incidencia está cerrada, pero usted tiene permisos para reabrirla o editar sus detalles.
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-dark d-flex align-items-center py-2 mb-3 border-0 shadow-sm" style="font-size: 0.85rem;">
                                    <i class="bi bi-lock-fill me-2 fs-5"></i>
                                    <div>
                                        <strong>Ticket Cerrado:</strong> Usted está viendo esta incidencia en modo de solo lectura.
                                    </div>
                                </div>
                            @endif
                        @endif
                        <div class="row g-3">
                            <!-- Sección 1: Responsable y Ubicación -->
                            <div class="col-12"><h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2">1. Ubicación y Solicitante</h6></div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Departamento <span class="text-danger">*</span></label>
                                <select class="form-select @error('departamento_id') is-invalid @enderror" wire:model.live="departamento_id" @disabled($es_lectura) wire:key="select-depto-modal">
                                    <option value="">Seleccione...</option>
                                    @foreach($departamentos as $depto)
                                        <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('departamento_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Dependencia <span class="text-muted fw-normal small">(Opcional)</span></label>
                                <select class="form-select @error('dependencia_id') is-invalid @enderror" wire:model.live="dependencia_id" {{ count($dependencias_disponibles) == 0 || $es_lectura ? 'disabled' : '' }} wire:key="select-depen-modal-{{ $departamento_id }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($dependencias_disponibles as $depen)
                                        <option value="{{ $depen->id }}">{{ $depen->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('dependencia_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Solicitado por (Trabajador)</label>
                                <select class="form-select" wire:model.live="trabajador_id" @disabled(!$departamento_id || $es_lectura)>
                                    <option value="">Seleccione...</option>
                                    @foreach($trabajadores as $trab)
                                        <option value="{{ $trab->id }}">{{ $trab->nombres }} {{ $trab->apellidos }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Filtrado por departamento.</small>
                            </div>

                            <!-- Sección 2: El Activo (Polimórfico) -->
                            <div class="col-12 mt-4"><h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2">2. Activo Relacionado</h6></div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipo de Activo @if($activo_obligatorio) <span class="text-danger">*</span> @endif</label>
                                <select class="form-select @error('modelo_type') is-invalid @enderror" wire:model.live="modelo_type" @disabled(!$departamento_id || $es_lectura) wire:key="select-tipo-activo-modal">
                                    <option value="">Ninguno / No Aplica</option>
                                    <option value="App\Models\Computador">Computador</option>
                                    <option value="App\Models\Dispositivo">Dispositivo Especial</option>
                                    <option value="App\Models\Insumo">Insumo / Consumible</option>
                                </select>
                                @error('modelo_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Elegir Activo @if($activo_obligatorio) <span class="text-danger">*</span> @endif</label>
                                <select class="form-select @error('modelo_id') is-invalid @enderror" wire:model.live="modelo_id" @disabled(count($activos) == 0 || $es_lectura) wire:key="select-activo-especifico-modal-{{ $modelo_type }}-{{ $departamento_id }}-{{ $dependencia_id }}">
                                    <option value="">Seleccione...</option>
                                    @foreach($activos as $act)
                                        <option value="{{ $act->id }}">
                                            @if($modelo_type == 'App\Models\Computador')
                                                [{{ $act->bien_nacional }}] {{ $act->marca->nombre }} {{ $act->sistemaOperativo->nombre ?? '' }}
                                            @else
                                                [{{ $act->bien_nacional }}] {{ $act->nombre }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('modelo_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Sección 3: El Caso -->
                            <div class="col-12 mt-4"><h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2">3. Información del Caso</h6></div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipo de Problema <span class="text-danger">*</span></label>
                                <select class="form-select @error('problema_id') is-invalid @enderror" wire:model="problema_id" @disabled($es_lectura)>
                                    <option value="">Seleccione...</option>
                                    @foreach($problemas_dropdown as $prob)
                                        <option value="{{ $prob->id }}">{{ $prob->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('problema_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Técnico Resolutor (Asignación)</label>
                                <select class="form-select @error('user_id') is-invalid @enderror" wire:model="user_id" @disabled($es_lectura)>
                                    <option value="">Pendiente por Asignar...</option>
                                    @foreach($tecnicos_dropdown as $tec)
                                        <option value="{{ $tec->id }}">{{ $tec->name }} ({{ $tec->especialidad->nombre ?? 'Sin Especialidad' }})</option>
                                    @endforeach
                                </select>
                                @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Descripción del Reporte <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" rows="3" wire:model="descripcion" placeholder="Detalle la falla reportada por el usuario..." @disabled($es_lectura)></textarea>
                                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Notas de Resolución / Seguimiento</label>
                                <textarea class="form-control" rows="3" wire:model="nota_resolucion" placeholder="Acciones tomadas para resolver la falla..." @disabled($es_lectura)></textarea>
                            </div>

                            <!-- Sección 4: Estatus Final -->
                            <div class="col-12 mt-4"><h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2">4. Control y Seguimiento</h6></div>

                            <div class="col-6">
                                <div class="form-check form-switch p-3 border rounded bg-body">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" id="solventCheck" wire:model.live="solventado" @disabled($es_lectura)>
                                    <label class="form-check-label fw-bold" for="solventCheck">¿Caso Solventado?</label>
                                </div>
                            </div>

                             <div class="col-6">
                                <div class="form-check form-switch p-3 border rounded bg-body">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" id="movimientoCheck" wire:model.live="amerita_movimiento" @disabled($es_lectura)>
                                    <label class="form-check-label fw-bold" for="movimientoCheck">¿Amerita Movimiento?</label>
                                    <div class="small text-muted" style="font-size: 0.7rem;">Traslado o retiro de equipo.</div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-check form-switch p-3 border rounded bg-body border-danger shadow-sm">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" id="cerrarCheck" wire:model="cerrado" @disabled(!$solventado || $es_lectura)>
                                    <label class="form-check-label fw-bold text-danger" for="cerrarCheck text-danger">¿CERRAR INCIDENCIA?</label>
                                    <div class="small text-muted mt-1">Bloqueo de edición.</div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer bg-body border-top-0 p-4">
                        @if($incidencia_id)
                            @can('reportes-pdf')
                                <a href="{{ route('reportes.incidencia.ficha', $incidencia_id) }}" target="_blank" class="btn btn-danger px-4 me-auto">
                                    <i class="bi bi-file-pdf me-1"></i> Ficha PDF
                                </a>
                            @endcan
                        @endif


                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        @if(!$es_lectura)
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bi bi-save me-1"></i> {{ $incidencia_id ? 'Actualizar' : 'Registrar Incidencia' }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detalle -->
    <div wire:ignore.self class="modal fade" id="modalDetalleIncidencia" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white border-bottom-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 p-2 rounded-3 me-3">
                            <i class="bi bi-eye-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title h6 mb-0">Detalles de la Incidencia</h5>
                            @if($incidencia_detalle)
                                <small class="opacity-75">Folio: #{{ str_pad($incidencia_detalle->id, 5, '0', STR_PAD_LEFT) }}</small>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetForm"></button>
                </div>
                
                @if($incidencia_detalle)
                <div class="modal-body p-0 bg-body-secondary overflow-hidden">
                    <!-- Banner de Estado -->
                    <div class="px-4 py-3 bg-body border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            @if($incidencia_detalle->cerrado)
                                <span class="badge bg-dark rounded-pill px-3"><i class="bi bi-lock-fill me-1"></i> Cerrado</span>
                            @elseif($incidencia_detalle->solventado)
                                <span class="badge bg-success rounded-pill px-3"><i class="bi bi-check-circle-fill me-1"></i> Solventado</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-3"><i class="bi bi-clock-history me-1"></i> En Curso</span>
                            @endif

                            @if($incidencia_detalle->amerita_movimiento)
                                <span class="badge bg-info text-white rounded-pill px-3"><i class="bi bi-arrow-left-right me-1"></i> Requiere Movimiento</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i> {{ $incidencia_detalle->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="row g-4">
                            <!-- Columna Izquierda: Información General -->
                            <div class="col-md-7">
                                <!-- Problema y Descripción -->
                                <div class="card border-0 shadow-sm rounded-3 mb-4">
                                    <div class="card-body">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                            <i class="bi bi-chat-left-text me-2"></i> Descripción del Caso
                                        </h6>
                                        <div class="mb-3">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-2">
                                                {{ $incidencia_detalle->problema->nombre }}
                                            </span>
                                            <p class="mb-0 text-body" style="white-space: pre-wrap;">{{ $incidencia_detalle->descripcion }}</p>
                                        </div>
                                        
                                        @if($incidencia_detalle->nota_resolucion)
                                        <div class="mt-4 p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">
                                            <h6 class="text-success fw-bold small mb-2"><i class="bi bi-check2-square me-1"></i> Resolución:</h6>
                                            <p class="mb-0 text-body small" style="white-space: pre-wrap;">{{ $incidencia_detalle->nota_resolucion }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Ubicación -->
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-body">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                            <i class="bi bi-geo-alt me-2"></i> Ubicación y Solicitante
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="text-muted small d-block">Departamento</label>
                                                <span class="fw-bold">{{ $incidencia_detalle->departamento->nombre }}</span>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-muted small d-block">Dependencia</label>
                                                <span class="fw-bold">{{ $incidencia_detalle->dependencia->nombre ?? 'N/A' }}</span>
                                            </div>
                                            <div class="col-12">
                                                <label class="text-muted small d-block">Solicitante</label>
                                                @if($incidencia_detalle->trabajador)
                                                    <span class="fw-bold">{{ $incidencia_detalle->trabajador->nombres }} {{ $incidencia_detalle->trabajador->apellidos }}</span>
                                                    <small class="text-muted d-block">{{ $incidencia_detalle->trabajador->cargo ?? '' }}</small>
                                                @else
                                                    <span class="fw-bold">{{ $incidencia_detalle->creator->name ?? 'N/A' }}</span>
                                                    <span class="badge bg-secondary ms-1">Externo</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna Derecha: Activo y Personal -->
                            <div class="col-md-5">
                                <!-- Activo -->
                                <div class="card border-0 shadow-sm rounded-3 mb-4">
                                    <div class="card-body">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                            <i class="bi bi-laptop me-2"></i> Activo Relacionado
                                        </h6>
                                        @if($incidencia_detalle->modelo)
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-light p-3 rounded-3 me-3 text-primary border">
                                                    <i class="bi bi-{{ $incidencia_detalle->modelo_type == 'App\Models\Computador' ? 'pc-display' : 'router' }} fs-4"></i>
                                                </div>
                                                <div>
                                                    <span class="badge bg-secondary mb-1">{{ class_basename($incidencia_detalle->modelo_type) }}</span>
                                                    <h6 class="mb-0 fw-bold">
                                                        {{ $incidencia_detalle->modelo->nombre ?? ($incidencia_detalle->modelo->marca->nombre . ' ' . ($incidencia_detalle->modelo->nombre_equipo ?? '')) }}
                                                    </h6>
                                                </div>
                                            </div>
                                            <div class="bg-light p-3 rounded-3 border border-dashed">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">Bien Nacional:</span>
                                                    <span class="fw-bold small">{{ $incidencia_detalle->modelo->bien_nacional ?? 'N/A' }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted small">Serial:</span>
                                                    <span class="fw-bold small text-truncate" style="max-width: 120px;" title="{{ $incidencia_detalle->modelo->serial }}">{{ $incidencia_detalle->modelo->serial ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center py-4 text-muted">
                                                <i class="bi bi-slash-circle display-6 d-block mb-2"></i>
                                                <p class="small mb-0">Sin activo relacionado</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Personal Asignado -->
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-body">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                            <i class="bi bi-person-gear me-2"></i> Personal
                                        </h6>
                                        <div class="mb-3">
                                            <label class="text-muted small d-block">Técnico Resolutor</label>
                                            @if($incidencia_detalle->tecnico)
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                                        {{ substr($incidencia_detalle->tecnico->name, 0, 1) }}
                                                    </div>
                                                    <span class="fw-bold">{{ $incidencia_detalle->tecnico->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-danger fst-italic small"><i class="bi bi-hourglass-split"></i> Pendiente por Asignar</span>
                                            @endif
                                        </div>
                                        <div>
                                            <label class="text-muted small d-block">Registrado por</label>
                                            <span class="small">{{ $incidencia_detalle->creator->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body border-top-0 p-4">
                    <div class="me-auto">
                        @can('reportes-pdf')
                            <a href="{{ route('reportes.incidencia.ficha', $incidencia_detalle->id) }}" target="_blank" class="btn btn-outline-danger px-4">
                                <i class="bi bi-file-pdf me-1"></i> Descargar Ficha PDF
                            </a>
                        @endcan
                    </div>
                    
                    @if(!$incidencia_detalle->cerrado)
                        <button wire:click="editar({{ $incidencia_detalle->id }})" class="btn btn-primary px-4" data-bs-dismiss="modal">
                            <i class="bi bi-pencil-square me-1"></i> Gestionar Caso
                        </button>
                    @endif
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
