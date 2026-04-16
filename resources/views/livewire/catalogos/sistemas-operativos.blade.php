<div>
    <!-- Header Especial -->
    @if(!isset($ocultarTitulos) || !$ocultarTitulos)
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                    <i class="bi bi-windows fs-3"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark">Catálogo de Sistemas Operativos</h2>
                    <p class="text-muted mb-0">Gestión de plataformas de software y sistemas de operación instalados en los activos.</p>
                </div>
            </div>
            <div class="text-end">
                <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-collection me-1"></i> Total Sistemas: <span class="fw-bold fs-6 ms-1">{{ $sistemas->total() }}</span>
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
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar sistema (Ej: Windows 11)...">
                    </div>
                </div>
                
                @can('ver-estado-sistemas-operativos')
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
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.catalogo.excel', ['tipo' => 'so', 'search' => $search, 'estado' => $filtro_estado]) }}"><i class="bi bi-filter me-2 text-success"></i> Vista Actual</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.catalogo.excel', ['tipo' => 'so']) }}"><i class="bi bi-list-check me-2 text-primary"></i> Todo el Catálogo</a></li>
                        </ul>
                    </div>
                    @endcan
                    @can('crear-sistemas-operativos')
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

                            <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                Nombre @if($sortField === 'nombre') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @can('ver-estado-sistemas-operativos')
                            <th class="th-estado" wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado @if($sortField === 'activo') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @endcan
                            <th class="th-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sistemas as $sistema)
                            <tr>

                                <td>{{ $sistema->nombre }}</td>
                                @can('ver-estado-sistemas-operativos')
                                    <td>
                                        @if($sistema->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                @endcan
                                <td class="text-end">
                                    @can('cambiar-estatus-sistemas-operativos')
                                        <button wire:click="toggleActivo({{ $sistema->id }})" class="btn btn-sm {{ $sistema->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                            <i class="bi {{ $sistema->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan

                                    @can('ver-sistemas-operativos')
                                        <button wire:click="ver({{ $sistema->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('editar-sistemas-operativos')
                                        <button wire:click="editar({{ $sistema->id }})" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('eliminar-sistemas-operativos')
                                        <button wire:click="eliminar({{ $sistema->id }})" wire:confirm="¿Deseas eliminar este sistema operativo?" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No se encontraron registros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $sistemas->links() }}
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalSistema" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Sistema <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" wire:model="nombre" placeholder="Ej: Windows 10, Ubuntu 22.04, macOS">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                            <label class="form-check-label" for="activo">Activo en el sistema</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="guardar">Guardar</span>
                            <span wire:loading wire:target="guardar">Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    @if($sistema_detalle)
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Nombre:</strong> {{ $sistema_detalle->nombre }}</li>
                            <li class="list-group-item">
                                <strong>Estado:</strong> 
                                @if($sistema_detalle->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>Creado el:</strong> {{ $sistema_detalle->created_at->format('d/m/Y H:i A') }}</li>
                            <li class="list-group-item"><strong>Última actualización:</strong> {{ $sistema_detalle->updated_at->format('d/m/Y H:i A') }}</li>
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