<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-5">
            <h3 class="mb-0">Catálogo de Tipos de Dispositivo</h3>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar tipo...">
            </div>
        </div>
        <div class="col-md-3 text-end d-flex gap-2">
            <div class="dropdown w-100">
                <button class="btn btn-outline-success border-2 fw-bold w-100 dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </button>
                <ul class="dropdown-menu shadow border-0">
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('reportes.catalogo.excel', ['tipo' => 'tipos', 'search' => $search, 'estado' => $filtro_estado]) }}">
                            <i class="bi bi-filter me-2 text-success"></i> Vista Actual
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('reportes.catalogo.excel', ['tipo' => 'tipos']) }}">
                            <i class="bi bi-list-check me-2 text-primary"></i> Todo el Catálogo
                        </a>
                    </li>
                </ul>
            </div>
            @can('crear-tipos-dispositivo')
                <button wire:click="crear" class="btn btn-primary w-100 shadow-sm fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo
                </button>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @can('ver-estado-tipos-dispositivo')
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filtro_estado">
                        <option value="todos">Todos los Estados</option>
                        <option value="activos">Solo Activos</option>
                        <option value="inactivos">Solo Inactivos</option>
                    </select>
                </div>
            @endcan
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>

                            <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                Nombre @if($sortField === 'nombre') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @can('ver-estado-tipos-dispositivo')
                            <th class="th-estado" wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado @if($sortField === 'activo') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @endcan
                            <th class="th-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tipos as $tipo)
                            <tr>

                                <td>{{ $tipo->nombre }}</td>
                                @can('ver-estado-tipos-dispositivo')
                                    <td>
                                        @if($tipo->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                @endcan
                                <td class="text-end">
                                    @can('cambiar-estatus-tipos-dispositivo')
                                        <button wire:click="toggleActivo({{ $tipo->id }})" class="btn btn-sm {{ $tipo->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                            <i class="bi {{ $tipo->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan

                                    @can('ver-tipos-dispositivo')
                                        <button wire:click="ver({{ $tipo->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('editar-tipos-dispositivo')
                                        <button wire:click="editar({{ $tipo->id }})" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('eliminar-tipos-dispositivo')
                                        <button wire:click="eliminar({{ $tipo->id }})" wire:confirm="¿Deseas eliminar este tipo de dispositivo?" class="btn btn-sm btn-danger" title="Eliminar">
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
                {{ $tipos->links() }}
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalTipo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Tipo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" wire:model="nombre" placeholder="Ej: Laptop, Monitor, Impresora">
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
                <div class="modal-body">
                    @if($tipo_detalle)
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>ID:</strong> {{ $tipo_detalle->id }}</li>
                            <li class="list-group-item"><strong>Nombre:</strong> {{ $tipo_detalle->nombre }}</li>
                            <li class="list-group-item">
                                <strong>Estado:</strong> 
                                @if($tipo_detalle->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>Creado el:</strong> {{ $tipo_detalle->created_at->format('d/m/Y H:i') }}</li>
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