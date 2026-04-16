<div>
    <!-- Header Especial -->
    @if(!isset($ocultarTitulos) || !$ocultarTitulos)
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                    <i class="bi bi-disc fs-3"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0 text-dark">Catálogo de Software</h2>
                    <p class="text-muted mb-0">Gestión de licencias, versiones y programas informáticos autorizados.</p>
                </div>
            </div>
            <div class="text-end">
                <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-collection me-1"></i> Total Software: <span class="fw-bold fs-6 ms-1">{{ $softwares->total() }}</span>
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por nombre, licencia, serial...">
                    </div>
                </div>
                
                @can('ver-estado-software')
                <div class="col-md-3">
                    <select class="form-select shadow-sm" wire:model.live="filtro_estado">
                        <option value="todos">Mostrar Todos</option>
                        <option value="activos">Solo Activos</option>
                        <option value="inactivos">Solo Inactivos</option>
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
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.inventario.software.excel', ['search' => $search, 'estado' => $filtro_estado]) }}"><i class="bi bi-filter me-2 text-success"></i> Vista Actual</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.inventario.software.excel') }}"><i class="bi bi-list-check me-2 text-primary"></i> Todo el Inventario</a></li>
                        </ul>
                    </div>
                    @endcan
                    @can('crear-software')
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
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th class="ps-4">Nombre del Programa</th>
                        <th>Licencia</th>
                        <th>Descripción</th>
                        @can('ver-estado-software')
                        <th>Estado</th>
                        @endcan
                        <th class="pe-4 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($softwares as $soft)
                        <tr>
                            <td class="ps-4 fw-medium">
                                {{ $soft->nombre_programa }}
                                @if($soft->arquitectura_programa)
                                    <span class="badge bg-secondary ms-1">{{ $soft->arquitectura_programa }}</span>
                                @endif
                            </td>
                            <td>
                                @if($soft->tipo_licencia === 'Privativo')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-key me-1"></i> Privativa</span>
                                @else
                                    <span class="badge bg-success"><i class="bi bi-unlock me-1"></i> Libre</span>
                                @endif
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate text-muted" style="max-width: 200px;" title="{{ $soft->descripcion_programa }}">
                                    {{ $soft->descripcion_programa ?: 'Sin descripción' }}
                                </span>
                            </td>
                            
                            @can('ver-estado-software')
                            <td>
                                {!! $soft->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' !!}
                            </td>
                            @endcan

                            <td class="pe-4 text-end">
                                @can('cambiar-estatus-software')
                                <button wire:click="toggleActivo({{ $soft->id }})" class="btn btn-sm {{ $soft->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                    <i class="bi {{ $soft->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                </button>
                                @endcan

                                <button class="btn btn-sm btn-info text-white" wire:click="verDetalle({{ $soft->id }})" title="Ver Detalles">
                                    <i class="bi bi-eye"></i>
                                </button>
                                
                                @can('reportes-pdf')
                                <button class="btn btn-sm btn-danger text-white shadow-sm fw-bold border-2" wire:click="exportPDF({{ $soft->id }})" title="Ficha PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </button>
                                @endcan

                                @can('editar-software')
                                <button class="btn btn-sm btn-primary" wire:click="editar({{ $soft->id }})" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @endcan

                                @can('eliminar-software')
                                <button class="btn btn-sm btn-danger" wire:click="eliminar({{ $soft->id }})" wire:confirm="¿Estás seguro de que deseas eliminar este software?" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->can('ver-estado-software') ? 5 : 4 }}" class="p-5 text-center text-muted">
                                <i class="bi bi-disc text-secondary opacity-25 d-block mb-3" style="font-size: 3rem;"></i>
                                No se encontró ningún software en el catálogo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($softwares->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $softwares->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Formulario -->
    <div wire:ignore.self class="modal fade" id="modalSoftware" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-disc me-2"></i>{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Programa <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre_programa') is-invalid @enderror" wire:model="nombre_programa" placeholder="Ej: Microsoft Office, VLC Media Player..." maxlength="35">
                            @error('nombre_programa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipo de Licencia <span class="text-danger">*</span></label>
                                <select class="form-select @error('tipo_licencia') is-invalid @enderror" wire:model.live="tipo_licencia">
                                    <option value="">Seleccione...</option>
                                    <option value="Libre">Libre / Open Source</option>
                                    <option value="Privativo">Privativo / De Pago</option>
                                </select>
                                @error('tipo_licencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Arquitectura</label>
                                <select class="form-select @error('arquitectura_programa') is-invalid @enderror" wire:model="arquitectura_programa">
                                    <option value="">Universal / No aplica</option>
                                    <option value="32bits">32 bits</option>
                                    <option value="64bits">64 bits</option>
                                </select>
                                @error('arquitectura_programa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Serial / Clave de Activación
                                @if($tipo_licencia === 'Privativo')
                                    <span class="text-danger">*</span>
                                @else
                                    <span class="text-muted fw-normal">(Opcional)</span>
                                @endif
                            </label>
                            <input type="text" class="form-control @error('serial') is-invalid @enderror" wire:model="serial" placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="50">
                            @error('serial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción / Notas</label>
                            <textarea class="form-control @error('descripcion_programa') is-invalid @enderror" wire:model="descripcion_programa" rows="3" placeholder="Notas adicionales sobre el software..." maxlength="250"></textarea>
                            @error('descripcion_programa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save me-1"></i> Guardar Software</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detalles -->
    <div wire:ignore.self class="modal fade" id="modalDetalleSoftware" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalles del Software</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                    @if($software_detalle)
                        <div class="text-center mb-4">
                            <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-disc text-primary" style="font-size: 2.5rem;"></i>
                            </div>
                            <h4 class="mb-1">{{ $software_detalle->nombre_programa }}</h4>
                            <span class="badge bg-{{ $software_detalle->tipo_licencia === 'Privativo' ? 'warning text-dark' : 'success' }} mb-2">
                                Licencia {{ $software_detalle->tipo_licencia }}
                            </span>
                            @if($software_detalle->arquitectura_programa)
                                <span class="badge bg-secondary mb-2">{{ $software_detalle->arquitectura_programa }}</span>
                            @endif
                        </div>
                        
                        <div class="card bg-light border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold border-bottom pb-2 mb-3">Información de Licencia</h6>
                                <p class="mb-1"><strong class="text-muted">Serial:</strong></p>
                                <p>
                                    @if($software_detalle->serial)
                                        <code class="fs-6 text-dark bg-white p-2 border rounded d-block">{{ $software_detalle->serial }}</code>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="card bg-light border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold border-bottom pb-2 mb-3">Descripción</h6>
                                <p class="mb-0">{{ $software_detalle->descripcion_programa ?: 'Sin descripción detallada.' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar Detalle</button>
                </div>
            </div>
        </div>
    </div>
</div>
