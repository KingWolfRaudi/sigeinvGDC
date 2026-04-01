<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h3 class="mb-0">Gestión de Computadores</h3>
        </div>
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por serial, bien nacional, equipo o marca...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            @can('crear-computadores')
                <button wire:click="crear" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalComputador">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo Computador
                </button>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th wire:click="sortBy('bien_nacional')" style="cursor: pointer;" class="ps-3">
                            Bien Nacional / Serial
                            @if($sortField === 'bien_nacional') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                        </th>
                        <th>Equipo & Tipo</th>
                        <th>Sistema Operativo</th>
                        <th>Especificaciones</th>
                        <th wire:click="sortBy('activo')" style="cursor: pointer;">Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($computadores as $computador)
                        <tr>
                            <td class="ps-3">
                                <strong>BN:</strong> {{ $computador->bien_nacional ?? 'N/A' }} <br>
                                <small class="text-muted">SN: {{ $computador->serial ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="fw-bold">{{ $computador->nombre_equipo ?? 'Sin Nombre' }}</span> <br>
                                <small>{{ $computador->marca->nombre ?? 'Marca Eliminada' }} - {{ $computador->tipoDispositivo->nombre ?? 'Tipo Eliminado' }}</small>
                            </td>
                            <td>{{ $computador->sistemaOperativo->nombre ?? 'N/A' }}</td>
                            <td>
                                <small>
                                    <strong>RAM:</strong> {{ $computador->memoria_ram }}GB {{ $computador->tipo_memoria }} <br>
                                    <strong>Disco:</strong> {{ $computador->almacenamiento }}GB {{ $computador->tipo_almacenamiento }}
                                </small>
                            </td>
                            <td>
                                @if($computador->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                @can('editar-computadores')
                                    <button wire:click="toggleActivo({{ $computador->id }})" class="btn btn-sm {{ $computador->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                        <i class="bi {{ $computador->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                    </button>
                                @endcan

                                @can('ver-computadores')
                                    <button wire:click="ver({{ $computador->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles" data-bs-toggle="modal" data-bs-target="#modalDetalle">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @endcan
                                
                                @can('editar-computadores')
                                    <button wire:click="editar({{ $computador->id }})" class="btn btn-sm btn-primary" title="Editar" data-bs-toggle="modal" data-bs-target="#modalComputador">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                @endcan
                                
                                @can('eliminar-computadores')
                                    <button wire:click="eliminar({{ $computador->id }})" wire:confirm="¿Estás seguro de que deseas eliminar este computador de forma lógica?" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-pc-display fs-2 d-block mb-2"></i>
                                No se encontraron computadores que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0">
            {{ $computadores->links() }}
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalComputador" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pc-display me-2"></i> {{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="guardar" id="formComputador">
                        <div class="row g-4">
                            
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 text-primary">1. Datos de Identificación</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bien Nacional</label>
                                <input type="text" class="form-control @error('bien_nacional') is-invalid @enderror" wire:model="bien_nacional">
                                @error('bien_nacional') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Serial del Equipo</label>
                                <input type="text" class="form-control @error('serial') is-invalid @enderror" wire:model="serial">
                                @error('serial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nombre en Red (Host)</label>
                                <input type="text" class="form-control @error('nombre_equipo') is-invalid @enderror" wire:model="nombre_equipo" placeholder="Ej: PC-RRHH-01">
                                @error('nombre_equipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2 text-primary">2. Clasificación Base</h6>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Marca <span class="text-danger">*</span></label>
                                @if(!$creando_marca)
                                    <div class="input-group">
                                        <select class="form-select @error('marca_id') is-invalid @enderror" wire:model="marca_id" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($marcas as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                                        </select>
                                        <button class="btn btn-outline-secondary" type="button" wire:click="$set('creando_marca', true)" title="Nueva Marca"><i class="bi bi-plus"></i></button>
                                    </div>
                                    @error('marca_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @else
                                    <div class="input-group">
                                        <input type="text" class="form-control" wire:model="nueva_marca" placeholder="Nombre...">
                                        <button class="btn btn-success" type="button" wire:click="guardarMarcaRapida"><i class="bi bi-check"></i></button>
                                        <button class="btn btn-danger" type="button" wire:click="$set('creando_marca', false)"><i class="bi bi-x"></i></button>
                                    </div>
                                    @error('nueva_marca') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tipo Dispositivo <span class="text-danger">*</span></label>
                                @if(!$creando_tipo)
                                    <div class="input-group">
                                        <select class="form-select @error('tipo_dispositivo_id') is-invalid @enderror" wire:model="tipo_dispositivo_id" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($tipos as $t) <option value="{{ $t->id }}">{{ $t->nombre }}</option> @endforeach
                                        </select>
                                        <button class="btn btn-outline-secondary" type="button" wire:click="$set('creando_tipo', true)" title="Nuevo Tipo"><i class="bi bi-plus"></i></button>
                                    </div>
                                    @error('tipo_dispositivo_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @else
                                    <div class="input-group">
                                        <input type="text" class="form-control" wire:model="nuevo_tipo" placeholder="Nombre...">
                                        <button class="btn btn-success" type="button" wire:click="guardarTipoRapido"><i class="bi bi-check"></i></button>
                                        <button class="btn btn-danger" type="button" wire:click="$set('creando_tipo', false)"><i class="bi bi-x"></i></button>
                                    </div>
                                    @error('nuevo_tipo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Sistema Operativo <span class="text-danger">*</span></label>
                                @if(!$creando_so)
                                    <div class="input-group">
                                        <select class="form-select @error('sistema_operativo_id') is-invalid @enderror" wire:model="sistema_operativo_id" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($sistemas as $s) <option value="{{ $s->id }}">{{ $s->nombre }}</option> @endforeach
                                        </select>
                                        <button class="btn btn-outline-secondary" type="button" wire:click="$set('creando_so', true)" title="Nuevo SO"><i class="bi bi-plus"></i></button>
                                    </div>
                                    @error('sistema_operativo_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @else
                                    <div class="input-group">
                                        <input type="text" class="form-control" wire:model="nuevo_so" placeholder="Nombre...">
                                        <button class="btn btn-success" type="button" wire:click="guardarSORapido"><i class="bi bi-check"></i></button>
                                        <button class="btn btn-danger" type="button" wire:click="$set('creando_so', false)"><i class="bi bi-x"></i></button>
                                    </div>
                                    @error('nuevo_so') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2 text-primary">3. Componentes Internos</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Procesador <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select @error('procesador_id') is-invalid @enderror" wire:model="procesador_id" required>
                                        <option value="">Seleccione un procesador...</option>
                                        @foreach($procesadores as $p)
                                            <option value="{{ $p->id }}">{{ $p->marca->nombre ?? '' }} {{ $p->modelo }} ({{ $p->frecuencia_base }}MHz)</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalProcesadorRapido" title="Nuevo Procesador">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                                @error('procesador_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tarjeta de Video (Opcional)</label>
                                <div class="input-group">
                                    <select class="form-select @error('gpu_id') is-invalid @enderror" wire:model="gpu_id">
                                        <option value="">Gráficos Integrados / Sin Tarjeta Dedicada</option>
                                        @foreach($gpus as $g)
                                            <option value="{{ $g->id }}">{{ $g->marca->nombre ?? '' }} {{ $g->modelo }} ({{ $g->memoria }}GB)</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalGpuRapida" title="Nueva Tarjeta de Video">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                                @error('gpu_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Memoria RAM <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" class="form-control @error('memoria_ram') is-invalid @enderror" wire:model="memoria_ram" required>
                                    <span class="input-group-text">GB</span>
                                </div>
                                @error('memoria_ram') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo RAM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-uppercase @error('tipo_memoria') is-invalid @enderror" wire:model="tipo_memoria" placeholder="Ej: DDR4" required>
                                @error('tipo_memoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Almacenamiento <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="1" min="1" class="form-control @error('almacenamiento') is-invalid @enderror" wire:model="almacenamiento" required>
                                    <span class="input-group-text">GB</span>
                                </div>
                                @error('almacenamiento') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo Disco <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-uppercase @error('tipo_almacenamiento') is-invalid @enderror" wire:model="tipo_almacenamiento" placeholder="Ej: SSD NVME" required>
                                @error('tipo_almacenamiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2 text-primary">4. Puertos Físicos y Conexiones (Opcional)</h6>
                                <div class="row g-2 mt-2">
                                    @foreach($puertos as $puerto)
                                        <div class="col-md-3 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="puerto_{{ $puerto->id }}" value="{{ $puerto->id }}" wire:model="puertos_seleccionados">
                                                <label class="form-check-label" for="puerto_{{ $puerto->id }}">
                                                    {{ $puerto->nombre }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2 text-primary">5. Información Adicional</h6>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" wire:model="observaciones" rows="2" placeholder="Detalles estéticos, fallas previas, etc."></textarea>
                                @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                                    <label class="form-check-label" for="activo">Computador Operativo</label>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                    <button type="submit" form="formComputador" class="btn btn-primary px-4">
                        <span wire:loading.remove wire:target="guardar"><i class="bi bi-save me-1"></i> Guardar Computador</span>
                        <span wire:loading wire:target="guardar" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span wire:loading wire:target="guardar"> Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalles del Computador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="$set('computador_detalle', null)"></button>
                </div>
                <div class="modal-body p-0">
                    @if($computador_detalle)
                        <div class="p-4 bg-primary text-white text-center">
                            <h4 class="mb-1">{{ $computador_detalle->nombre_equipo ?? 'Equipo sin nombre' }}</h4>
                            <p class="mb-0 fs-5">{{ $computador_detalle->marca->nombre ?? 'N/A' }} - {{ $computador_detalle->tipoDispositivo->nombre ?? 'N/A' }}</p>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>Bien Nacional:</strong> {{ $computador_detalle->bien_nacional ?? 'N/A' }}</span>
                                <span><strong>Serial:</strong> {{ $computador_detalle->serial ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item"><strong>Sistema Operativo:</strong> {{ $computador_detalle->sistemaOperativo->nombre ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Procesador:</strong> {{ $computador_detalle->procesador->marca->nombre ?? '' }} {{ $computador_detalle->procesador->modelo ?? 'N/A' }}</li>
                            <li class="list-group-item"><strong>Tarjeta de Video:</strong> {{ $computador_detalle->gpu_id ? ($computador_detalle->gpu->marca->nombre.' '.$computador_detalle->gpu->modelo) : 'Gráficos Integrados' }}</li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><strong>RAM:</strong> {{ $computador_detalle->memoria_ram }}GB ({{ $computador_detalle->tipo_memoria }})</span>
                                <span><strong>Disco:</strong> {{ $computador_detalle->almacenamiento }}GB ({{ $computador_detalle->tipo_almacenamiento }})</span>
                            </li>
                            <li class="list-group-item">
                                <strong>Puertos Disponibles:</strong><br>
                                <div class="mt-2">
                                    @forelse($computador_detalle->puertos as $puerto)
                                        <span class="badge bg-secondary me-1 mb-1">{{ $puerto->nombre }}</span>
                                    @empty
                                        <span class="text-muted fst-italic">No se registraron puertos.</span>
                                    @endforelse
                                </div>
                            </li>
                            @if($computador_detalle->observaciones)
                                <li class="list-group-item text-danger"><strong>Observaciones:</strong> {{ $computador_detalle->observaciones }}</li>
                            @endif
                        </ul>
                    @else
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Cargando...</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$set('computador_detalle', null)">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalProcesadorRapido" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-cpu me-2"></i>Crear Procesador Rápido</h5>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Marca <span class="text-danger">*</span></label>
                            <select class="form-select @error('proc_marca_id') is-invalid @enderror" wire:model="proc_marca_id">
                                <option value="">Seleccione...</option>
                                @foreach($marcas as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                            </select>
                            @error('proc_marca_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modelo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('proc_modelo') is-invalid @enderror" wire:model="proc_modelo" placeholder="Ej: Core i7, Ryzen 5">
                            @error('proc_modelo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Generación</label>
                            <input type="text" class="form-control" wire:model="proc_generacion" placeholder="Ej: 12va, 5000 Series">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Núcleos</label>
                            <input type="number" class="form-control @error('proc_nucleos') is-invalid @enderror" wire:model="proc_nucleos" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hilos</label>
                            <input type="number" class="form-control @error('proc_hilos') is-invalid @enderror" wire:model="proc_hilos" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Frecuencia Base (MHz)</label>
                            <input type="number" class="form-control @error('proc_frecuencia_base') is-invalid @enderror" wire:model="proc_frecuencia_base" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Frecuencia Máxima (MHz)</label>
                            <input type="number" class="form-control @error('proc_frecuencia_maxima') is-invalid @enderror" wire:model="proc_frecuencia_maxima" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalComputador">Volver al Computador</button>
                    <button type="button" class="btn btn-primary" wire:click="guardarProcesadorRapido">
                        <span wire:loading.remove wire:target="guardarProcesadorRapido">Guardar Procesador</span>
                        <span wire:loading wire:target="guardarProcesadorRapido">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalGpuRapida" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-gpu-card me-2"></i>Crear Tarjeta de Video Rápida</h5>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Marca <span class="text-danger">*</span></label>
                            <select class="form-select @error('gr_marca_id') is-invalid @enderror" wire:model="gr_marca_id">
                                <option value="">Seleccione...</option>
                                @foreach($marcas as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                            </select>
                            @error('gr_marca_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modelo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('gr_modelo') is-invalid @enderror" wire:model="gr_modelo" placeholder="Ej: RTX 3060">
                            @error('gr_modelo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Memoria (GB)</label>
                            <input type="number" class="form-control @error('gr_memoria') is-invalid @enderror" wire:model="gr_memoria" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo Memoria</label>
                            <input type="text" class="form-control text-uppercase @error('gr_tipo_memoria') is-invalid @enderror" wire:model="gr_tipo_memoria" placeholder="Ej: GDDR6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Frecuencia (MHz)</label>
                            <input type="number" class="form-control @error('gr_frecuencia') is-invalid @enderror" wire:model="gr_frecuencia" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ancho de Bus (Bits)</label>
                            <input type="number" class="form-control @error('gr_bus') is-invalid @enderror" wire:model="gr_bus" min="1">
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="form-label fw-bold border-bottom pb-2">Puertos de Salida de Video (Opcional)</h6>
                            <div class="row g-2 mt-1">
                                @foreach($puertos as $puerto)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="gr_puerto_{{ $puerto->id }}" value="{{ $puerto->id }}" wire:model="gr_puertos_seleccionados">
                                            <label class="form-check-label" for="gr_puerto_{{ $puerto->id }}">
                                                {{ $puerto->nombre }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalComputador">Volver al Computador</button>
                    <button type="button" class="btn btn-success text-white" wire:click="guardarGpuRapida">
                        <span wire:loading.remove wire:target="guardarGpuRapida">Guardar GPU</span>
                        <span wire:loading wire:target="guardarGpuRapida">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>