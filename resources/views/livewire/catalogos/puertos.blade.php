<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-5">
            <h3 class="mb-0">Catálogo de Puertos</h3>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar puerto (Ej: HDMI)...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            @can('crear-puertos')
                <button wire:click="crear" class="btn btn-primary w-100">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo Puerto
                </button>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('id')" style="cursor: pointer;">
                                ID @if($sortField === 'id') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                Nombre @if($sortField === 'nombre') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado @if($sortField === 'activo') <i class="bi bi-sort-down ms-1"></i> @endif
                            </th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($puertos as $puerto)
                            <tr>
                                <td>{{ $puerto->id }}</td>
                                <td>{{ $puerto->nombre }}</td>
                                <td>
                                    @if($puerto->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @can('cambiar-estatus-puertos')
                                        <button wire:click="toggleActivo({{ $puerto->id }})" class="btn btn-sm {{ $puerto->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                            <i class="bi {{ $puerto->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan

                                    @can('ver-puertos')
                                        <button wire:click="ver({{ $puerto->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('editar-puertos')
                                        <button wire:click="editar({{ $puerto->id }})" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('eliminar-puertos')
                                        <button wire:click="eliminar({{ $puerto->id }})" wire:confirm="¿Deseas eliminar este puerto?" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No se encontraron registros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $puertos->links() }}
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalPuerto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Puerto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror" wire:model="nombre" placeholder="Ej: USB 3.0, HDMI">
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
                    @if($puerto_detalle)
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>ID:</strong> {{ $puerto_detalle->id }}</li>
                            <li class="list-group-item"><strong>Nombre:</strong> {{ $puerto_detalle->nombre }}</li>
                            <li class="list-group-item">
                                <strong>Estado:</strong> 
                                @if($puerto_detalle->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>Creado el:</strong> {{ $puerto_detalle->created_at->format('d/m/Y H:i') }}</li>
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