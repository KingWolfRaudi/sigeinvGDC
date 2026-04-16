<div>
    <!-- Header Especial -->
    @if(!$ocultarTitulos)
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                    <i class="bi bi-person-badge fs-3"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark">Gestión de Trabajadores</h2>
                    <p class="text-muted mb-0">Control y administración del personal, cargos y dependencias.</p>
                </div>
            </div>
            <div class="text-end">
                <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-people me-1"></i> Total Personal: <span class="fw-bold fs-6 ms-1">{{ $trabajadores->total() }}</span>
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar trabajador por cédula, nombre o departamento...">
                    </div>
                </div>
                
                @can('ver-estado-trabajadores')
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
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.catalogo.excel', ['tipo' => 'trabajadores', 'search' => $search, 'estado' => $filtro_estado]) }}"><i class="bi bi-filter me-2 text-success"></i> Vista Actual</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.catalogo.excel', ['tipo' => 'trabajadores']) }}"><i class="bi bi-list-check me-2 text-primary"></i> Todo el Catálogo</a></li>
                        </ul>
                    </div>
                    @endcan
                    @can('crear-trabajadores')
                        <button wire:click="crear" class="btn btn-primary shadow-sm fw-bold px-4" data-bs-toggle="modal" data-bs-target="#modalTrabajador">
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
                            <th wire:click="sortBy('departamento_id')" style="cursor: pointer;">
                                Departamento
                                @if($sortField === 'departamento_id') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @can('ver-estado-trabajadores')
                            <th class="th-estado" wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado
                                @if($sortField === 'activo') <i class="bi bi-sort-down ms-1"></i> @endif
                            </th>
                            @endcan
                            <th class="text-end th-acciones">Acciones</th>
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
                                    
                                    @can('ver-estado-trabajadores')
                                        <button wire:click="toggleActivo({{ $trabajador->id }})" class="btn btn-sm {{ $trabajador->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                            <i class="bi {{ $trabajador->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan

                                    @can('ver-trabajadores')
                                        <button wire:click="ver({{ $trabajador->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles" data-bs-toggle="modal" data-bs-target="#modalDetalleTrabajador">
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
                    <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
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

    <div wire:ignore.self class="modal fade" id="modalDetalleTrabajador" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalles del Trabajador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="$set('trabajador_detalle', null)"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
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
                <div class="modal-footer d-flex justify-content-between">
                    @if($trabajador_detalle)
                        <a href="{{ route('asociaciones', ['tipo' => 'trabajador', 'id' => $trabajador_detalle->id]) }}" class="btn btn-outline-primary shadow-sm">
                            <i class="bi bi-diagram-3 me-1"></i> Ver Asociaciones Completas
                        </a>
                    @else
                        <div></div>
                    @endif
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