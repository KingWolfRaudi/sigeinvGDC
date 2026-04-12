<div>
    <!-- Header Especial -->
    @if(!isset($ocultarTitulos) || !$ocultarTitulos)
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                <i class="bi bi-cpu fs-3"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-0 text-dark">Catálogo de Procesadores</h2>
                <p class="text-muted mb-0">Gestión de unidades centrales de procesamiento (CPU) para estaciones y servidores.</p>
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
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por modelo o marca...">
                    </div>
                </div>
                
                @can('ver-estado-procesadores')
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
                        <button class="btn btn-outline-success border-2 fw-bold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.catalogo.excel', ['tipo' => 'procesadores', 'search' => $search, 'estado' => $filtro_estado]) }}"><i class="bi bi-filter me-2 text-success"></i> Vista Actual</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.catalogo.excel', ['tipo' => 'procesadores']) }}"><i class="bi bi-list-check me-2 text-primary"></i> Todo el Catálogo</a></li>
                        </ul>
                    </div>
                    @endcan
                    @can('crear-procesadores')
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
                            <th class="th-id" wire:click="sortBy('marca_id')" style="cursor: pointer;">Marca @if($sortField === 'marca_id') <i class="bi bi-sort-down ms-1"></i> @endif</th>
                            <th wire:click="sortBy('modelo')" style="cursor: pointer;">Modelo @if($sortField === 'modelo') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif</th>
                            <th>Especificaciones</th>
                            @can('ver-estado-procesadores')
                                <th class="th-estado" wire:click="sortBy('activo')" style="cursor: pointer;">Estado</th>
                            @endcan
                            <th class="text-end th-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($procesadores as $proc)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $proc->marca->nombre }}</span></td>
                                <td><strong>{{ $proc->modelo }}</strong> <br><small class="text-muted">{{ $proc->generacion }}</small></td>
                                <td>
                                    <small class="text-muted">
                                        {{ $proc->nucleos ? $proc->nucleos.' Núcleos' : '' }} 
                                        {{ $proc->frecuencia_base ? '| '.$proc->frecuencia_base : '' }}
                                    </small>
                                </td>
                                @can('ver-estado-procesadores')
                                    <td>
                                        @if($proc->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                @endcan
                                <td class="text-end">
                                    @can('cambiar-estatus-procesadores')
                                        <button wire:click="toggleActivo({{ $proc->id }})" class="btn btn-sm {{ $proc->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                            <i class="bi {{ $proc->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan
                                    @can('ver-procesadores')
                                        <button wire:click="ver({{ $proc->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles"><i class="bi bi-eye"></i></button>
                                    @endcan
                                    @can('editar-procesadores')
                                        <button wire:click="editar({{ $proc->id }})" class="btn btn-sm btn-primary" title="Editar"><i class="bi bi-pencil-square"></i></button>
                                    @endcan
                                    @can('eliminar-procesadores')
                                        <button wire:click="eliminar({{ $proc->id }})" wire:confirm="¿Deseas eliminar este procesador?" class="btn btn-sm btn-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No se encontraron procesadores.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $procesadores->links() }}</div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalProcesador" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label">Marca <span class="text-danger">*</span></label>
                                
                                @if(!$creando_marca)
                                    <div class="input-group">
                                        <select class="form-select @error('marca_id') is-invalid @enderror" wire:model="marca_id">
                                            <option value="">Seleccione una marca...</option>
                                            @foreach($marcas as $m)
                                                <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline-secondary" type="button" wire:click="$set('creando_marca', true)" title="Crear nueva marca">
                                            <i class="bi bi-plus"></i> Nuevo
                                        </button>
                                    </div>
                                    @error('marca_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @else
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('nueva_marca') is-invalid @enderror" wire:model="nueva_marca" placeholder="Nombre de la nueva marca...">
                                        <button class="btn btn-success" type="button" wire:click="guardarMarca">
                                            Guardar
                                        </button>
                                        <button class="btn btn-danger" type="button" wire:click="$set('creando_marca', false)" title="Cancelar">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    @error('nueva_marca') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Modelo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('modelo') is-invalid @enderror" wire:model="modelo" placeholder="Ej: Core i7, Ryzen 5">
                                @error('modelo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Generación</label>
                                <input type="text" class="form-control" wire:model="generacion" placeholder="Ej: 12va Gen, Zen 3">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Frecuencia Base</label>
                                <input type="text" class="form-control" wire:model="frecuencia_base" placeholder="Ej: 2.5 GHz">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Frecuencia Máxima (Turbo)</label>
                                <input type="text" class="form-control" wire:model="frecuencia_maxima" placeholder="Ej: 4.8 GHz">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Núcleos</label>
                                <input type="number" class="form-control" wire:model="nucleos" min="1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Hilos</label>
                                <input type="number" class="form-control" wire:model="hilos" min="1">
                            </div>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                            <label class="form-check-label" for="activo">Activo en el sistema</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-cpu me-2"></i>Detalles del Procesador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    @if($procesador_detalle)
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Marca:</strong> {{ $procesador_detalle->marca->nombre ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Modelo:</strong> {{ $procesador_detalle->modelo }}</li>
                            <li class="list-group-item"><strong>Generación:</strong> {{ $procesador_detalle->generacion ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Frec. Base:</strong> {{ $procesador_detalle->frecuencia_base ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Frec. Máxima:</strong> {{ $procesador_detalle->frecuencia_maxima ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Núcleos / Hilos:</strong> {{ $procesador_detalle->nucleos ?? '-' }} / {{ $procesador_detalle->hilos ?? '-' }}</li>
                            <li class="list-group-item"><strong>Creado el:</strong> {{ $procesador_detalle->created_at->format('d/m/Y H:i A') }}</li>
                            <li class="list-group-item"><strong>Última actualización:</strong> {{ $procesador_detalle->updated_at->format('d/m/Y H:i A') }}</li>
                        </ul>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>