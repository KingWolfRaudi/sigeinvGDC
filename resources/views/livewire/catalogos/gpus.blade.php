<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-5">
            <h3 class="mb-0">Catálogo de GPUs</h3>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por modelo o marca...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            @can('crear-gpus')
                <button wire:click="crear" class="btn btn-primary w-100">
                    <i class="bi bi-gpu-card me-1"></i> Nueva GPU
                </button>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @can('ver-estado-gpus')
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
                            <th wire:click="sortBy('marca_id')" style="cursor: pointer;">Marca</th>
                            <th wire:click="sortBy('modelo')" style="cursor: pointer;">Modelo @if($sortField === 'modelo') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif</th>
                            <th>Especificaciones</th>
                            @can('ver-estado-gpus')
                                <th wire:click="sortBy('activo')" style="cursor: pointer;">Estado</th>
                            @endcan
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gpus as $gpu)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $gpu->marca->nombre }}</span></td>
                                <td><strong>{{ $gpu->modelo }}</strong></td>
                                <td>
                                    <small class="text-muted">
                                        {{ $gpu->memoria ?? 'N/A' }} {{ $gpu->tipo_memoria ?? '' }} | Bus: {{ $gpu->bus ?? '-' }}
                                    </small>
                                </td>
                                @can('ver-estado-gpus')
                                    <td>
                                        @if($gpu->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                @endcan
                                <td class="text-end">
                                    @can('cambiar-estatus-gpus')
                                        <button wire:click="toggleActivo({{ $gpu->id }})" class="btn btn-sm {{ $gpu->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado"><i class="bi {{ $gpu->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i></button>
                                    @endcan
                                    @can('ver-gpus')
                                        <button wire:click="ver({{ $gpu->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles"><i class="bi bi-eye"></i></button>
                                    @endcan
                                    @can('editar-gpus')
                                        <button wire:click="editar({{ $gpu->id }})" class="btn btn-sm btn-primary" title="Editar"><i class="bi bi-pencil-square"></i></button>
                                    @endcan
                                    @can('eliminar-gpus')
                                        <button wire:click="eliminar({{ $gpu->id }})" wire:confirm="¿Deseas eliminar esta GPU?" class="btn btn-sm btn-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No se encontraron GPUs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $gpus->links() }}</div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalGpu" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body">
                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label">Marca de la GPU <span class="text-danger">*</span></label>
                                
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
                                <input type="text" class="form-control @error('modelo') is-invalid @enderror" wire:model="modelo" placeholder="Ej: RTX 4090, RX 7900 XTX">
                                @error('modelo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Memoria</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('memoria') is-invalid @enderror" wire:model="memoria" min="1" placeholder="Ej: 8">
                                    <span class="input-group-text bg-light text-muted">GB</span>
                                </div>
                                @error('memoria') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo Memoria</label>
                                <input type="text" class="form-control text-uppercase @error('tipo_memoria') is-invalid @enderror" wire:model="tipo_memoria" placeholder="Ej: GDDR6">
                                @error('tipo_memoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Ancho de Bus</label>
                                <select class="form-select @error('bus') is-invalid @enderror" wire:model="bus">
                                    <option value="">Seleccione...</option>
                                    <option value="32-bit">32-bit</option>
                                    <option value="64-bit">64-bit</option>
                                    <option value="128-bit">128-bit</option>
                                    <option value="256-bit">256-bit</option>
                                    <option value="512-bit">512-bit</option>
                                </select>
                                @error('bus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Frecuencia</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('frecuencia') is-invalid @enderror" wire:model="frecuencia" min="1" placeholder="Ej: 1680">
                                    <span class="input-group-text bg-light text-muted">MHz</span>
                                </div>
                                @error('frecuencia') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label d-block">Puertos de Conexión</label>
                                <div class="border rounded p-3 bg-light">
                                    <div class="row">
                                        @foreach($lista_puertos as $puerto)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $puerto->id }}" id="puerto_{{ $puerto->id }}" wire:model="puertos_seleccionados">
                                                <label class="form-check-label" for="puerto_{{ $puerto->id }}">
                                                    {{ $puerto->nombre }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
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
                    <h5 class="modal-title"><i class="bi bi-gpu-card me-2"></i>Detalles de GPU</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($gpu_detalle)
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Marca:</strong> {{ $gpu_detalle->marca->nombre ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Modelo:</strong> {{ $gpu_detalle->modelo }}</li>
                            <li class="list-group-item"><strong>Memoria:</strong> {{ $gpu_detalle->memoria ?? '-' }} {{ $gpu_detalle->tipo_memoria ?? '' }}</li>
                            <li class="list-group-item"><strong>Bus / Frecuencia:</strong> {{ $gpu_detalle->bus ?? '-' }} / {{ $gpu_detalle->frecuencia ?? '-' }}</li>
                            <li class="list-group-item">
                                <strong>Puertos:</strong> 
                                @forelse($gpu_detalle->puertos as $p)
                                    <span class="badge bg-secondary">{{ $p->nombre }}</span>
                                @empty
                                    <span class="text-muted">Ninguno registrado</span>
                                @endforelse
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>