<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h3 class="mb-0">Gestión de Trabajadores</h3>
        </div>
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar trabajador por cédula, nombre o departamento...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            @can('crear-trabajadores')
                <button wire:click="crear" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalTrabajador">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo Trabajador
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
                            <th wire:click="sortBy('cedula')" style="cursor: pointer;">
                                Cédula 
                                @if($sortField === 'cedula') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th wire:click="sortBy('nombres')" style="cursor: pointer;">
                                Trabajador 
                                @if($sortField === 'nombres') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th wire:click="sortBy('cargo')" style="cursor: pointer;">
                                Cargo
                                @if($sortField === 'cargo') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th>Departamento</th>
                            <th wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado
                                @if($sortField === 'activo') <i class="bi bi-sort-down ms-1"></i> @endif
                            </th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trabajadores as $trabajador)
                            <tr>
                                <td>{{ $trabajador->cedula }}</td>
                                <td>{{ $trabajador->nombres }} {{ $trabajador->apellidos }}</td>
                                <td>{{ $trabajador->cargo ?? 'N/A' }}</td>
                                <td>{{ $trabajador->departamento->nombre ?? 'Sin Asignar' }}</td>
                                <td>
                                    @if($trabajador->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    
                                    @can('editar-trabajadores')
                                        <button wire:click="toggleActivo({{ $trabajador->id }})" class="btn btn-sm {{ $trabajador->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                            <i class="bi {{ $trabajador->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan

                                    @can('ver-trabajadores')
                                        <button wire:click="ver({{ $trabajador->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles" data-bs-toggle="modal" data-bs-target="#modalDetalle">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('editar-trabajadores')
                                        <button wire:click="editar({{ $trabajador->id }})" class="btn btn-sm btn-primary" title="Editar" data-bs-toggle="modal" data-bs-target="#modalTrabajador">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('eliminar-trabajadores')
                                        <button wire:click="eliminar({{ $trabajador->id }})" wire:confirm="¿Estás seguro de que deseas eliminar este trabajador?" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No se encontraron trabajadores que coincidan con la búsqueda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $trabajadores->links() }}
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalTrabajador" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombres') is-invalid @enderror" wire:model="nombres">
                                @error('nombres') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('apellidos') is-invalid @enderror" wire:model="apellidos">
                                @error('apellidos') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Cédula (Opcional)</label>
                                <input type="text" class="form-control @error('cedula') is-invalid @enderror" wire:model="cedula">
                                @error('cedula') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cargo</label>
                                <input type="text" class="form-control @error('cargo') is-invalid @enderror" wire:model="cargo">
                                @error('cargo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Departamento <span class="text-danger">*</span></label>
                                @if(!$creando_departamento)
                                    <div class="input-group">
                                        <select class="form-select @error('departamento_id') is-invalid @enderror" wire:model="departamento_id">
                                            <option value="">Seleccione un departamento...</option>
                                            @foreach($departamentos as $dep)
                                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-outline-secondary" type="button" wire:click="$set('creando_departamento', true)">
                                            <i class="bi bi-plus"></i> Nuevo
                                        </button>
                                    </div>
                                    @error('departamento_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @else
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('nuevo_departamento') is-invalid @enderror" wire:model="nuevo_departamento" placeholder="Nombre del nuevo departamento...">
                                        <button class="btn btn-success" type="button" wire:click="guardarDepartamento">
                                            Guardar Dpto
                                        </button>
                                        <button class="btn btn-danger" type="button" wire:click="$set('creando_departamento', false)">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    @error('nuevo_departamento') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                                    <label class="form-check-label" for="activo">Trabajador Activo</label>
                                </div>
                            </div>
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
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalles del Trabajador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="$set('trabajador_detalle', null)"></button>
                </div>
                <div class="modal-body">
                    @if($trabajador_detalle)
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Cédula:</strong> {{ $trabajador_detalle->cedula }}</li>
                            <li class="list-group-item"><strong>Nombres:</strong> {{ $trabajador_detalle->nombres }}</li>
                            <li class="list-group-item"><strong>Apellidos:</strong> {{ $trabajador_detalle->apellidos }}</li>
                            <li class="list-group-item"><strong>Cargo:</strong> {{ $trabajador_detalle->cargo ?? 'No especificado' }}</li>
                            <li class="list-group-item"><strong>Departamento:</strong> {{ $trabajador_detalle->departamento->nombre ?? 'Sin Asignar' }}</li>
                            <li class="list-group-item">
                                <strong>Estado:</strong> 
                                @if($trabajador_detalle->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </li>
                            <li class="list-group-item"><strong>Registrado el:</strong> {{ $trabajador_detalle->created_at->format('d/m/Y H:i A') }}</li>
                        </ul>
                    @else
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Cargando detalles...</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$set('trabajador_detalle', null)">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('cerrar-modal', () => {
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalTrabajador'));
                if (modal) { modal.hide(); }
            });
        });
    </script>
</div>