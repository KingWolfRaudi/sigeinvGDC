<div>
    <!-- Header Especial -->
    @if(!$ocultarTitulos)
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                    <i class="bi bi-pc-display fs-3"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-body">Inventario de Computadores</h2>
                    <p class="text-muted mb-0">Gestión de equipos, laptops y servidores registrados.</p>
                </div>
            </div>
            <div class="text-end">
                <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-pc me-1"></i> Total Equipos: <span class="fw-bold fs-6 ms-1">{{ $computadores->total() }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Card de Búsqueda y Acciones -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 justify-content-between align-items-center">
                <div class="col-md-5">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-body border-end-0"><i class="bi bi-search text-primary"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por Bien Nacional, Serial o IP...">
                    </div>
                </div>
                
                @can('ver-estado-computadores')
                <div class="col-md-3">
                    <select class="form-select shadow-sm" wire:model.live="filtro_estado">
                        <option value="todos">Mostrar Todos</option>
                        <option value="activos">Solo Activos</option>
                        <option value="inactivos">Solo Inactivos (Bajas)</option>
                    </select>
                </div>
                @endcan

                <div class="col-md-4 text-end d-flex gap-2 justify-content-end">
                    @can('reportes-excel')
                    <div class="dropdown">
                        <button class="btn btn-outline-success border-2 fw-bold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.inventario.computadores.excel', ['search' => $search, 'estado' => $filtro_estado, 'departamento_id' => $departamento_id]) }}"><i class="bi bi-filter me-2 text-success"></i> Vista Actual</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.inventario.computadores.excel') }}"><i class="bi bi-list-check me-2 text-primary"></i> Todo el Inventario</a></li>
                        </ul>
                    </div>
                    @endcan
                    @can('crear-computadores')
                        <button wire:click="crear" class="btn btn-primary shadow-sm fw-bold px-4">
                            <i class="bi bi-plus-lg me-1"></i> Nuevo
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal (Tabla) -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('bien_nacional')" style="cursor: pointer; min-width: 140px;">
                                Identificación 
                                @if($sortField === 'bien_nacional') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            
                            <th wire:click="sortBy('nombre_equipo')" style="cursor: pointer;">
                                Tipo / Equipo
                                @if($sortField === 'nombre_equipo') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            
                            <th>Marca / Modelo</th>
                            
                            <th wire:click="sortBy('ip')" style="cursor: pointer;">
                                Red 
                                @if($sortField === 'ip') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            <th wire:click="sortBy('departamento_id')" style="cursor: pointer;">
                                Ubicación 
                                @if($sortField === 'departamento_id') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            <th wire:click="sortBy('estado_fisico')" style="cursor: pointer;">
                                Condición 
                                @if($sortField === 'estado_fisico') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            @can('ver-estado-computadores')
                            <th class="th-estado" wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado 
                                @if($sortField === 'activo') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @endcan
                            
                            <th class="th-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($computadores as $comp)
                            <tr>
                                <td>
                                    <strong>BN:</strong> {{ $comp->bien_nacional ?? 'N/A' }}
                                    @if($comp->pendientes_count > 0)
                                        <button wire:click="verCambioPendiente({{ $comp->id }})"
                                            class="badge bg-warning text-body border-0 ms-1"
                                            title="{{ $comp->pendientes_count }} cambio(s) en revisión — clic para ver">
                                            <i class="bi bi-hourglass-split"></i> En revisión
                                        </button>
                                    @endif
                                    @if($comp->mis_borradores_count > 0)
                                        <button wire:click="verCambioPendiente({{ $comp->id }})"
                                            class="badge bg-info text-white border-0 ms-1"
                                            title="{{ $comp->mis_borradores_count }} borrador(es) tuyos — clic para ver">
                                            <i class="bi bi-pencil"></i> Borrador
                                        </button>
                                    @endif
                                    <br>
                                    <small class="text-muted">Serial: {{ $comp->serial ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $comp->tipo_computador }}</strong><br>
                                    <small class="text-muted">{{ $comp->nombre_equipo }}</small>
                                </td>
                                <td>
                                    <strong>{{ $comp->marca->nombre ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">
                                        {{ $comp->total_ram }} RAM | {{ $comp->total_almacenamiento }} Almac.
                                    </small>
                                </td>
                                <td>
                                    {{ $comp->ip ?? 'Sin IP' }}<br>
                                    <small class="text-muted">{{ $comp->mac ?? 'Sin MAC' }}</small>
                                </td>
                                <td>
                                    {{ $comp->departamento->nombre ?? 'N/A' }}
                                    @if($comp->dependencia)
                                        <br><small class="text-muted"><i class="bi bi-arrow-return-right"></i> {{ $comp->dependencia->nombre }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $comp->estado_fisico === 'operativo' ? 'success' : ($comp->estado_fisico === 'danado' ? 'danger' : 'warning') }}">
                                        {{ strtoupper($comp->estado_fisico) }}
                                    </span>
                                </td>
                                
                                @can('ver-estado-computadores')
                                    <td>
                                        {!! $comp->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' !!}
                                    </td>
                                @endcan
                                <td class="text-end">
                                    @can('cambiar-estatus-computadores')
                                        <button wire:click="toggleActivo({{ $comp->id }})" class="btn btn-sm {{ $comp->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                            <i class="bi {{ $comp->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan
                                    @can('ver-computadores')
                                        <button wire:click="ver({{ $comp->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles"><i class="bi bi-eye"></i></button>
                                        @can('reportes-pdf')
                                        <a href="{{ route('reportes.computador.ficha', $comp->id) }}" target="_blank" class="btn btn-sm btn-danger text-white shadow-sm fw-bold border-2" title="Descargar Ficha PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                        @endcan
                                    @endcan
                                    @can('editar-computadores')
                                        <button wire:click="editar({{ $comp->id }})" class="btn btn-sm btn-primary" title="Editar"><i class="bi bi-pencil-square"></i></button>
                                    @endcan
                                    
                                    @role('super-admin')
                                        <button wire:click="eliminar({{ $comp->id }})" wire:confirm="¿Está seguro de dar de baja permanentemente este computador (Fin de vida útil)?" class="btn btn-sm btn-danger" title="Dar de Baja Definitiva"><i class="bi bi-trash"></i></button>
                                    @endrole
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No se encontraron computadores registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $computadores->links() }}</div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalComputador" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-body-secondary">
                    <h5 class="modal-title"><i class="bi bi-pc-display me-2"></i>{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                        @include('livewire.inventario.partials._form_fields')
                        
                        {{-- ── Campo Justificación (solo en modo edición) ─── --}}
                        @if($es_edicion)
                        <div class="alert alert-warning border-warning mb-0 mt-4 py-2">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-shield-lock-fill text-warning mt-1"></i>
                                <div class="w-100">
                                    <strong class="small">Justificación del Cambio (requerida)</strong>
                                    <textarea class="form-control form-control-sm mt-1 @error('justificacion') is-invalid @enderror"
                                        wire:model="justificacion" rows="2"
                                        placeholder="Describa el motivo técnico u operativo de esta modificación (mín. 10 caracteres)..."></textarea>
                                    @error('justificacion') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>


                    <div class="modal-footer bg-body-secondary">
                        @can('cambiar-estatus-computadores')
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                                <label class="form-check-label" for="activo">Equipo Operativo (Activo)</label>
                            </div>
                        @endcan
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Computador</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalDetalleComputador" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-body-secondary">
                    <h5 class="modal-title"><i class="bi bi-pc-display me-2"></i>Detalles del Equipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    @if($computador_detalle)
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <h6 class="border-bottom pb-2 text-primary">Identificación y Asignación</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-1"><strong>Estado Operativo:</strong> 
                                        {!! $computador_detalle->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' !!}
                                    </li>
                                    <li class="mb-1"><strong>Nombre Equipo:</strong> {{ $computador_detalle->nombre_equipo }}</li>
                                    <li class="mb-1"><strong>Tipo Equipo:</strong> {{ $computador_detalle->tipo_computador }}</li>
                                    <li class="mb-1"><strong>Bien Nacional:</strong> {{ $computador_detalle->bien_nacional ?? 'N/A' }}</li>
                                    <li class="mb-1"><strong>Serial:</strong> {{ $computador_detalle->serial ?? 'N/A' }}</li>
                                    <li class="mb-1"><strong>Marca:</strong> {{ $computador_detalle->marca->nombre ?? 'N/A' }}</li>
                                    <li class="mb-1"><strong>Ubicación:</strong> 
                                        {{ $computador_detalle->departamento->nombre ?? 'Sin asignar' }}
                                        @if($computador_detalle->dependencia)
                                            <span class="text-muted"><br><i class="bi bi-arrow-return-right"></i> {{ $computador_detalle->dependencia->nombre }}</span>
                                        @endif
                                    </li>
                                    <li class="mb-1"><strong>Responsable:</strong> {{ $computador_detalle->trabajador ? ($computador_detalle->trabajador->nombres . ' ' . $computador_detalle->trabajador->apellidos) : 'Sin asignar' }}</li>
                                </ul>
                            </div>

                            <div class="col-md-4 mb-4">
                                <h6 class="border-bottom pb-2 text-primary">Hardware y Especificaciones</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-1"><strong>Sistema Operativo:</strong> {{ $computador_detalle->sistemaOperativo->nombre ?? 'No especificado' }}</li>
                                    <li class="mb-1"><strong>Procesador:</strong> {{ $computador_detalle->procesador->marca->nombre ?? '' }} {{ $computador_detalle->procesador->modelo ?? 'No especificado' }}</li>
                                    <li class="mb-1"><strong>GPU Dedicada:</strong> {{ $computador_detalle->gpu ? $computador_detalle->gpu->marca->nombre . ' ' . $computador_detalle->gpu->modelo : 'No posee / Integrada' }}</li>
                                    <li class="mb-1"><strong>RAM ({{ $computador_detalle->tipo_ram ?? 'N/A' }}):</strong> {{ $computador_detalle->total_ram }} en {{ $computador_detalle->rams->count() }} módulos.</li>
                                    <li class="mb-1"><strong>Almacenamiento:</strong> {{ $computador_detalle->total_almacenamiento }} total.
                                        @if($computador_detalle->discos->count() > 0)
                                            <ul class="mb-0 small text-muted">
                                                @foreach($computador_detalle->discos as $d) 
                                                    <li>{{ $d->capacidad }} ({{ $d->tipo }})</li> 
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="small text-muted">No especificado</span>
                                        @endif
                                    </li>
                                </ul>
                            </div>

                            <div class="col-md-4 mb-4">
                                <h6 class="border-bottom pb-2 text-primary">Conectividad y Otros</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-1"><strong>Dirección MAC:</strong> {{ $computador_detalle->mac ?? 'N/A' }}</li>
                                    <li class="mb-1"><strong>Dirección IP:</strong> {{ $computador_detalle->ip ?? 'N/A' }}</li>
                                    <li class="mb-1"><strong>Conexión:</strong> {{ $computador_detalle->tipo_conexion ?? 'N/A' }}</li>
                                    <li class="mb-1"><strong>Condición Física:</strong> {{ ucfirst(str_replace('_', ' ', $computador_detalle->estado_fisico ?? 'Indeterminado')) }}</li>
                                    <li class="mb-1"><strong>Unidad DVD:</strong> {{ $computador_detalle->unidad_dvd ? 'Sí' : 'No' }}</li>
                                    <li class="mb-1"><strong>Fuente de Poder:</strong> {{ $computador_detalle->fuente_poder ? 'Interna' : 'Externa / Adaptador' }}</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 text-primary">Puertos de Conexión</h6>
                                @if($computador_detalle->puertos->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($computador_detalle->puertos as $puerto)
                                            <span class="badge bg-secondary">{{ $puerto->nombre }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">No se especificaron puertos.</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 text-primary">Observaciones Adicionales</h6>
                                <p class="text-muted small mb-0">{{ $computador_detalle->observaciones ?? 'No hay observaciones registradas para este equipo.' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-body-secondary d-flex justify-content-between">
                    @if($computador_detalle)
                        @can('ver-computadores')
                        <div>
                            <a href="{{ route('asociaciones', ['tipo' => 'computador', 'id' => $computador_detalle->id]) }}" class="btn btn-outline-primary shadow-sm me-2">
                                <i class="bi bi-diagram-3 me-1"></i> Asociaciones
                            </a>
                        </div>
                        @endcan
                    @else
                        <div></div>
                    @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <div wire:ignore.self class="modal fade" id="modalTrabajador" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Nuevo Trabajador</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelarModalTrabajador"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nuevo_trab_nombres') is-invalid @enderror" wire:model="nuevo_trab_nombres">
                            @error('nuevo_trab_nombres') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nuevo_trab_apellidos') is-invalid @enderror" wire:model="nuevo_trab_apellidos">
                            @error('nuevo_trab_apellidos') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cédula (Opcional)</label>
                        <input type="text" class="form-control @error('nuevo_trab_cedula') is-invalid @enderror" wire:model="nuevo_trab_cedula">
                        @error('nuevo_trab_cedula') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Departamento <span class="text-danger">*</span></label>
                        <select class="form-select @error('nuevo_trab_departamento_id') is-invalid @enderror" wire:model="nuevo_trab_departamento_id">
                            <option value="">Seleccione...</option>
                            @foreach($departamentos as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                            @endforeach
                        </select>
                        @error('nuevo_trab_departamento_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-shield-lock me-1"></i> El sistema generará el correo automáticamente usando el formato institucional.
                    </div>
                </div>
                <div class="modal-footer bg-body-secondary">
                    <button type="button" class="btn btn-secondary" wire:click="cancelarModalTrabajador">Volver</button>
                    <button type="button" class="btn btn-primary" wire:click="guardarTrabajadorRapido">Guardar Trabajador</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Vista Rápida de Cambio Pendiente --}}
    <div wire:ignore.self class="modal fade" id="modalCambioPendiente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                @if($movimiento_preview)
                @php
                    $est = $movimiento_preview->estado_workflow;
                    $esRevisión = $est === 'pendiente';
                    $tiposLabel = [
                        'actualizacion_datos'     => 'Actualización de Datos',
                        'cambio_departamento'     => 'Cambio de Departamento',
                        'reasignacion_trabajador' => 'Reasignación de Trabajador',
                        'cambio_estado'           => 'Cambio de Estado Físico',
                        'toggle_activo'           => 'Cambio de Estatus',
                        'baja'                    => 'Baja del Sistema',
                    ];
                @endphp
                <div class="modal-header {{ $esRevisión ? 'bg-warning text-body' : 'bg-info text-white' }}">
                    <h5 class="modal-title">
                        <i class="bi bi-{{ $esRevisión ? 'hourglass-split' : 'pencil-square' }} me-2"></i>
                        Cambio {{ $esRevisión ? 'En Revisión' : 'En Borrador' }}
                    </h5>
                    <button type="button"
                        class="btn-close {{ $esRevisión ? '' : 'btn-close-white' }}"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    {{-- Solicitante y Metadata --}}
                    <div class="d-flex align-items-start gap-3 p-3 bg-body-secondary rounded mb-3">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                                style="width:42px;height:42px;font-size:1rem;">
                                {{ strtoupper(substr($movimiento_preview->solicitante->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $movimiento_preview->solicitante->name ?? 'Usuario desconocido' }}</div>
                            <div class="text-muted small">
                                <i class="bi bi-clock me-1"></i>
                                Solicitado {{ $movimiento_preview->created_at->diffForHumans() }}
                                · {{ $movimiento_preview->created_at->format('d/m/Y H:i') }}
                            </div>
                            <div class="mt-1">
                                <span class="badge bg-secondary fw-normal">
                                    {{ $tiposLabel[$movimiento_preview->tipo_operacion] ?? ucwords(str_replace('_',' ',$movimiento_preview->tipo_operacion)) }}
                                </span>
                                <span class="badge {{ $esRevisión ? 'bg-warning text-body' : 'bg-info' }} fw-normal ms-1">
                                    {{ $esRevisión ? 'En Revisión' : 'Borrador' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($movimiento_preview->justificacion)
                    <div class="mb-3">
                        <p class="small text-muted mb-1 fw-semibold"><i class="bi bi-chat-quote me-1"></i>Justificación:</p>
                        <blockquote class="blockquote-footer ps-3 border-start border-3 mb-0">
                            <em>{{ $movimiento_preview->justificacion }}</em>
                        </blockquote>
                    </div>
                    @endif

                    {{-- Diff del cambio --}}
                    <div class="border rounded p-3">
                        <h6 class="text-success mb-3">
                            <i class="bi bi-pencil-square me-1"></i>Modificación Propuesta
                        </h6>
                        @include('livewire.movimientos._detalle-cambios', [
                            'movimiento_detalle' => $movimiento_preview
                        ])
                    </div>
                </div>
                <div class="modal-footer bg-body-secondary">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    @if($esRevisión)
                        @can('movimientos-computadores-aprobar')
                        <button wire:click="aprobarMovimientoPreview"
                            wire:confirm="¿Confirmar aprobación y aplicar cambios al computador?"
                            class="btn btn-success btn-sm">
                            <i class="bi bi-check-lg me-1"></i> Aprobar
                        </button>
                        @endcan
                    @endif
                    <a href="{{ route('movimientos.computadores') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>Ir a Movimientos
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>