<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h3 class="mb-0">Catálogo de Marcas</h3>
        </div>
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar marca...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            @can('crear-marcas')
                <button wire:click="crear" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle me-1"></i> Nueva Marca
                </button>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @can('ver-estado-marcas')
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
                            <th wire:click="sortBy('id')" style="cursor: pointer;">
                                ID 
                                @if($sortField === 'id') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                Nombre 
                                @if($sortField === 'nombre') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @can('ver-estado-marcas')
                            <th wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado
                                @if($sortField === 'activo') <i class="bi bi-sort-down ms-1"></i> @endif
                            </th>
                            @endcan
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($marcas as $marca)
                            <tr>
                                <td>{{ $marca->id }}</td>
                                <td>{{ $marca->nombre }}</td>
                                @can('ver-estado-marcas')
                                    <td>
                                        @if($marca->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                @endcan
                                <td class="text-end">
                                    
                                    @can('cambiar-estatus-marcas')
                                        <button wire:click="toggleActivo({{ $marca->id }})" class="btn btn-sm {{ $marca->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                            <i class="bi {{ $marca->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan

                                    @can('ver-marcas')
                                        <button wire:click="ver({{ $marca->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('editar-marcas')
                                        <button wire:click="editar({{ $marca->id }})" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('eliminar-marcas')
                                        <button wire:click="eliminar({{ $marca->id }})" wire:confirm="¿Estás seguro de que deseas eliminar esta marca?" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No se encontraron marcas que coincidan con la búsqueda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $marcas->links() }}
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalMarca" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Marca <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" wire:model="nombre">
                            @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                            <label class="form-check-label" for="activo">Marca Activa</label>
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
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalles de la Marca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($marca_detalle)
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>ID:</strong> {{ $marca_detalle->id }}</li>
                            <li class="list-group-item"><strong>Nombre:</strong> {{ $marca_detalle->nombre }}</li>
                            <li class="list-group-item">
                                <strong>Estado:</strong> 
                                @if($marca_detalle->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>Creado el:</strong> {{ $marca_detalle->created_at->format('d/m/Y H:i A') }}</li>
                            <li class="list-group-item"><strong>Última actualización:</strong> {{ $marca_detalle->updated_at->format('d/m/Y H:i A') }}</li>
                        </ul>
                    @else
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Cargando detalles...</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
</div>