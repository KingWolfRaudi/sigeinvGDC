<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            <h3 class="mb-0">Inventario de Computadores</h3>
        </div>
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por Bien Nacional, Serial o IP...">
            </div>
        </div>
        <div class="col-md-3 text-end">
            @can('crear-computadores')
                <button wire:click="crear" class="btn btn-primary w-100">
                    <i class="bi bi-pc-display me-1"></i> Nuevo Computador
                </button>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @can('ver-estado-computadores')
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filtro_estado">
                        <option value="todos">Mostrar Todos</option>
                        <option value="activos">Solo Activos</option>
                        <option value="inactivos">Solo Inactivos</option>
                    </select>
                </div>
            @endcan        
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('bien_nacional')" style="cursor: pointer; min-width: 140px;">
                                Identificación 
                                @if($sortField === 'bien_nacional') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            
                            <th>Equipo / Detalles</th>
                            
                            <th wire:click="sortBy('ip')" style="cursor: pointer;">
                                Red 
                                @if($sortField === 'ip') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            <th wire:click="sortBy('estado_fisico')" style="cursor: pointer;">
                                Condición 
                                @if($sortField === 'estado_fisico') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            @can('ver-estado-computadores')
                            <th class="th-estado" wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado 
                                @if($sortField === 'activo') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @endcan
                            
                            <th class="th-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($computadores as $comp)
                            <tr>
                                <td>
                                    <strong>BN:</strong> {{ $comp->bien_nacional ?? 'N/A' }}
                                    @if($comp->pendientes_count > 0)
                                        <a href="{{ route('movimientos.computadores') }}" class="badge bg-warning text-dark px-1 py-0 shadow-sm deco-none" title="Tiene {{ $comp->pendientes_count }} cambio(s) pendiente(s) por aprobar">
                                            <i class="bi bi-clock-history"></i> Pendiente
                                        </a>
                                    @endif
                                    <br>
                                    <small class="text-muted">Serial: {{ $comp->serial ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $comp->marca->nombre ?? 'N/A' }} - {{ $comp->tipoDispositivo->nombre ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">
                                        {{ $comp->total_ram }} RAM | {{ $comp->total_almacenamiento }} Almac.
                                    </small>
                                </td>
                                <td>
                                    {{ $comp->ip ?? 'Sin IP' }}<br>
                                    <small class="text-muted">{{ $comp->mac ?? 'Sin MAC' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $comp->estado_fisico === 'operativo' ? 'success' : ($comp->estado_fisico === 'danado' ? 'danger' : 'warning') }}">
                                        {{ strtoupper($comp->estado_fisico) }}
                                    </span>
                                </td>
                                @can('ver-estado-computadores')
                                    <td>
                                        {!! $comp->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' !!}
                                    </td>
                                @endcan
                                <td class="text-end">
                                    @can('ver-computadores')
                                        <button wire:click="ver({{ $comp->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles"><i class="bi bi-eye"></i></button>
                                    @endcan
                                    @can('cambiar-estatus-computadores')
                                        <button wire:click="toggleActivo({{ $comp->id }})" class="btn btn-sm {{ $comp->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                            <i class="bi {{ $comp->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan
                                    @can('editar-computadores')
                                        <button wire:click="editar({{ $comp->id }})" class="btn btn-sm btn-primary" title="Editar"><i class="bi bi-pencil-square"></i></button>
                                    @endcan
                                    
                                    @role('super-admin')
                                        <button wire:click="eliminar({{ $comp->id }})" wire:confirm="¿Está seguro de dar de baja permanentemente este computador (Fin de vida útil)?" class="btn btn-sm btn-danger" title="Dar de Baja Definitiva"><i class="bi bi-trash"></i></button>
                                    @endrole
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No se encontraron computadores registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $computadores->links() }}</div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalComputador" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-pc-display me-2"></i>{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body p-4">
                        
                        <h6 class="border-bottom pb-2 text-primary">1. Identificación y Hardware Base</h6>
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bien Nacional</label>
                                <input type="text" class="form-control @error('bien_nacional') is-invalid @enderror" wire:model="bien_nacional">
                                @error('bien_nacional') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Serial</label>
                                <input type="text" class="form-control @error('serial') is-invalid @enderror" wire:model="serial">
                                @error('serial') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Marca <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    @if($creando_marca)
                                        <input type="text" class="form-control border-primary" wire:model="nueva_marca" placeholder="Nueva marca...">
                                        <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_marca', false)"><i class="bi bi-x-lg"></i></button>
                                    @else
                                        <select class="form-select @error('marca_id') is-invalid @enderror" wire:model="marca_id">
                                            <option value="">Seleccione...</option>
                                            @foreach($marcas as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                                        </select>
                                        <button class="btn btn-outline-success" type="button" wire:click="$set('creando_marca', true)" title="Crear nueva marca"><i class="bi bi-plus-lg"></i></button>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    @if($creando_tipo)
                                        <input type="text" class="form-control border-primary" wire:model="nuevo_tipo" placeholder="Nuevo tipo...">
                                        <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_tipo', false)"><i class="bi bi-x-lg"></i></button>
                                    @else
                                        <select class="form-select @error('tipo_dispositivo_id') is-invalid @enderror" wire:model="tipo_dispositivo_id">
                                            <option value="">Seleccione...</option>
                                            @foreach($tipos as $t) <option value="{{ $t->id }}">{{ $t->nombre }}</option> @endforeach
                                        </select>
                                        <button class="btn btn-outline-success" type="button" wire:click="$set('creando_tipo', true)"><i class="bi bi-plus-lg"></i></button>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Sist. Operativo <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    @if($creando_so)
                                        <input type="text" class="form-control border-primary" wire:model="nuevo_so" placeholder="Nuevo SO...">
                                        <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_so', false)"><i class="bi bi-x-lg"></i></button>
                                    @else
                                        <select class="form-select @error('sistema_operativo_id') is-invalid @enderror" wire:model="sistema_operativo_id">
                                            <option value="">Seleccione...</option>
                                            @foreach($sistemas as $s) <option value="{{ $s->id }}">{{ $s->nombre }}</option> @endforeach
                                        </select>
                                        <button class="btn btn-outline-success" type="button" wire:click="$set('creando_so', true)"><i class="bi bi-plus-lg"></i></button>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Procesador <span class="text-danger">*</span></label>
                                @if($creando_procesador)
                                    <div class="input-group">
                                        <select class="form-select border-primary" wire:model="nuevo_procesador_marca_id">
                                            <option value="">Marca...</option>
                                            @foreach($marcas as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                                        </select>
                                        <input type="text" class="form-control border-primary w-25" wire:model="nuevo_procesador_modelo" placeholder="Modelo...">
                                        <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_procesador', false)"><i class="bi bi-x-lg"></i></button>
                                    </div>
                                @else
                                    <div class="input-group">
                                        <select class="form-select @error('procesador_id') is-invalid @enderror" wire:model="procesador_id">
                                            <option value="">Seleccione...</option>
                                            @foreach($procesadores as $p) <option value="{{ $p->id }}">{{ $p->marca->nombre }} {{ $p->modelo }}</option> @endforeach
                                        </select>
                                        <button class="btn btn-outline-success" type="button" wire:click="$set('creando_procesador', true)" title="Crear rápido"><i class="bi bi-plus-lg"></i></button>
                                    </div>
                                @endif
                                @error('procesador_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">GPU Dedicada (Opcional)</label>
                                @if($creando_gpu)
                                    <div class="input-group">
                                        <select class="form-select border-primary" wire:model="nueva_gpu_marca_id">
                                            <option value="">Marca...</option>
                                            @foreach($marcas as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                                        </select>
                                        <input type="text" class="form-control border-primary w-25" wire:model="nueva_gpu_modelo" placeholder="Modelo...">
                                        <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_gpu', false)"><i class="bi bi-x-lg"></i></button>
                                    </div>
                                @else
                                    <div class="input-group">
                                        <select class="form-select" wire:model="gpu_id">
                                            <option value="">Ninguna / Integrada</option>
                                            @foreach($gpus as $g) <option value="{{ $g->id }}">{{ $g->marca->nombre }} {{ $g->modelo }}</option> @endforeach
                                        </select>
                                        <button class="btn btn-outline-success" type="button" wire:click="$set('creando_gpu', true)" title="Crear rápida"><i class="bi bi-plus-lg"></i></button>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4 mb-3 d-flex flex-column justify-content-center">
                                <label class="form-label d-none d-md-block">&nbsp;</label> <div class="d-flex gap-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="unidad_dvd" wire:model="unidad_dvd">
                                        <label class="form-check-label" for="unidad_dvd">Unidad DVD</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="fuente_poder" wire:model="fuente_poder">
                                        <label class="form-check-label" for="fuente_poder">Fuente de Poder</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 text-primary">2. Componentes Internos</h6>
                        <div class="row mb-4">
                            <div class="col-md-6 border-end">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Módulos de RAM</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <select class="form-select form-select-sm" style="width: 100px;" wire:model="tipo_ram">
                                            <option value="">Tipo...</option>
                                            <option value="DDR2">DDR2</option>
                                            <option value="DDR3">DDR3</option>
                                            <option value="DDR4">DDR4</option>
                                            <option value="DDR5">DDR5</option>
                                        </select>
                                        <button type="button" class="btn btn-sm btn-success" wire:click="addRam"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                                @error('tipo_ram') <span class="text-danger small d-block mb-2">{{ $message }}</span> @enderror
                                
                                @foreach($rams as $index => $ram)
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">Slot {{ $ram['slot'] }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control" wire:model="rams.{{ $index }}.capacidad" min="1" placeholder="Ej: 8">
                                            <span class="input-group-text text-muted">GB</span>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100" wire:click="removeRam({{ $index }})"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Discos de Almacenamiento</label>
                                    <button type="button" class="btn btn-sm btn-success" wire:click="addDisco"><i class="bi bi-plus"></i> Agregar Disco</button>
                                </div>
                                @foreach($discos as $index => $disco)
                                <div class="row mb-2">
                                    <div class="col-5">
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control" wire:model="discos.{{ $index }}.capacidad" min="1" placeholder="Ej: 500">
                                            <span class="input-group-text text-muted">GB</span>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <select class="form-select form-select-sm" wire:model="discos.{{ $index }}.tipo">
                                            <option value="">Tipo...</option>
                                            <option value="SSD">SSD</option>
                                            <option value="NVME">NVME</option>
                                            <option value="M.2">M.2</option>
                                            <option value="HDD">HDD</option>
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100" wire:click="removeDisco({{ $index }})"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 text-primary">3. Red, Asignación y Estado</h6>
                        <div class="row mb-3">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Dirección MAC</label>
                                <input type="text" class="form-control @error('mac') is-invalid @enderror" wire:model="mac" placeholder="XX:XX:XX:XX:XX:XX">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Dirección IP</label>
                                <input type="text" class="form-control @error('ip') is-invalid @enderror" wire:model="ip" placeholder="192.168.X.X">
                                @error('ip') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo Conexión</label>
                                <select class="form-select" wire:model="tipo_conexion">
                                    <option value="">Seleccione...</option>
                                    <option value="Ethernet">Ethernet</option>
                                    <option value="Wi-Fi">Wi-Fi</option>
                                    <option value="Ambas">Ambas</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Estado Físico <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="estado_fisico">
                                    <option value="operativo">Operativo</option>
                                    <option value="danado">Dañado</option>
                                    <option value="en_reparacion">En Reparación</option>
                                    <option value="indeterminado">Indeterminado</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Departamento / Área</label>
                                <select class="form-select" wire:model.live="departamento_id">
                                    <option value="">Sin asignar / En stock</option>
                                    @foreach($departamentos as $dep) 
                                        <option value="{{ $dep->id }}">{{ $dep->nombre }}</option> 
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8 mb-3">
                                <label class="form-label">Trabajador Responsable (Específico)</label>
                                <div class="input-group">
                                    <select class="form-select" wire:model="trabajador_id" @if(!$departamento_id) disabled @endif>
                                        @if(!$departamento_id)
                                            <option value="">Seleccione un departamento primero...</option>
                                        @else
                                            <option value="">Sin trabajador específico (Uso general del área)</option>
                                            @foreach($trabajadores as $t) 
                                                <option value="{{ $t->id }}">{{ $t->nombres }} {{ $t->apellidos }}</option> 
                                            @endforeach
                                        @endif
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" wire:click="abrirModalTrabajador" title="Registrar Trabajador" @if(!$departamento_id) disabled @endif>
                                        <i class="bi bi-person-plus-fill"></i> Nuevo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 text-primary">4. Puertos y Notas</h6>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="border rounded p-3 bg-light">
                                    <div class="row">
                                        @foreach($puertos as $puerto)
                                        <div class="col-md-3 col-sm-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $puerto->id }}" id="puerto_{{ $puerto->id }}" wire:model="puertos_seleccionados">
                                                <label class="form-check-label text-sm" for="puerto_{{ $puerto->id }}">
                                                    {{ $puerto->nombre }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones Adicionales</label>
                                <textarea class="form-control" wire:model="observaciones" rows="2"></textarea>
                            </div>
                        </div>
                        
                    </div>

                    {{-- ── Campo Justificación (solo en modo edición) ─── --}}
                    @if($es_edicion)
                    <div class="alert alert-warning border-warning mx-4 mb-0 mt-2 py-2">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-shield-lock-fill text-warning mt-1"></i>
                            <div class="w-100">
                                <strong class="small">Justificación del Cambio (requerida)</strong>
                                <textarea class="form-control form-control-sm mt-1 @error('justificacion') is-invalid @enderror"
                                    wire:model="justificacion" rows="2"
                                    placeholder="Describa el motivo técnico u operativo de esta modificación (mín. 10 caracteres)..."></textarea>
                                @error('justificacion') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="modal-footer bg-light">
                        @can('cambiar-estatus-computadores')
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                                <label class="form-check-label" for="activo">Equipo Operativo (Activo)</label>
                            </div>
                        @endcan
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Computador</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-pc-display me-2"></i>Detalles del Equipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($computador_detalle)
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <h6 class="border-bottom pb-2 text-primary">Identificación y Asignación</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-1"><strong>Estado:</strong> 
                                        {!! $computador_detalle->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' !!}
                                    </li>
                                    <li class="mb-1"><strong>Bien Nacional:</strong> {{ $computador_detalle->bien_nacional ?? 'No especificado' }}</li>
                                    <li class="mb-1"><strong>Serial:</strong> {{ $computador_detalle->serial ?? 'No especificado' }}</li>
                                    <li class="mb-1"><strong>Marca / Tipo:</strong> {{ $computador_detalle->marca->nombre ?? 'No especificado' }} - {{ $computador_detalle->tipoDispositivo->nombre ?? 'No especificado' }}</li>
                                    <li class="mb-1"><strong>Departamento:</strong> {{ $computador_detalle->departamento->nombre ?? 'No especificado (En Stock)' }}</li>
                                    <li class="mb-1"><strong>Trabajador:</strong> {{ $computador_detalle->trabajador->nombres ?? 'No' }} {{ $computador_detalle->trabajador->apellidos ?? 'especificado' }}</li>
                                </ul>
                            </div>

                            <div class="col-md-4 mb-4">
                                <h6 class="border-bottom pb-2 text-primary">Hardware y Especificaciones</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-1"><strong>Sistema Operativo:</strong> {{ $computador_detalle->sistemaOperativo->nombre ?? 'No especificado' }}</li>
                                    <li class="mb-1"><strong>Procesador:</strong> {{ $computador_detalle->procesador->marca->nombre ?? '' }} {{ $computador_detalle->procesador->modelo ?? 'No especificado' }}</li>
                                    <li class="mb-1"><strong>GPU Dedicada:</strong> {{ $computador_detalle->gpu ? $computador_detalle->gpu->marca->nombre . ' ' . $computador_detalle->gpu->modelo : 'No posee / Integrada' }}</li>
                                    <li class="mb-1"><strong>RAM ({{ $computador_detalle->tipo_ram ?? 'N/A' }}):</strong> {{ $computador_detalle->total_ram }} en {{ $computador_detalle->rams->count() }} módulos.</li>
                                    <li class="mb-1"><strong>Almacenamiento:</strong> {{ $computador_detalle->total_almacenamiento }} total.
                                        @if($computador_detalle->discos->count() > 0)
                                            <ul class="mb-0 small text-muted">
                                                @foreach($computador_detalle->discos as $d) 
                                                    <li>{{ $d->capacidad }} ({{ $d->tipo }})</li> 
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="small text-muted">No especificado</span>
                                        @endif
                                    </li>
                                </ul>
                            </div>

                            <div class="col-md-4 mb-4">
                                <h6 class="border-bottom pb-2 text-primary">Conectividad y Otros</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-1"><strong>Dirección MAC:</strong> {{ $computador_detalle->mac ?? 'No especificada' }}</li>
                                    <li class="mb-1"><strong>Dirección IP:</strong> {{ $computador_detalle->ip ?? 'No especificada' }}</li>
                                    <li class="mb-1"><strong>Tipo de Conexión:</strong> {{ $computador_detalle->tipo_conexion ?? 'No especificado' }}</li>
                                    <li class="mb-1"><strong>Estado Físico:</strong> {{ ucfirst(str_replace('_', ' ', $computador_detalle->estado_fisico ?? 'No especificado')) }}</li>
                                    <li class="mb-1"><strong>Unidad DVD:</strong> {{ $computador_detalle->unidad_dvd ? 'Sí posee' : 'No posee' }}</li>
                                    <li class="mb-1"><strong>Fuente de Poder:</strong> {{ $computador_detalle->fuente_poder ? 'Posee (Interna)' : 'No posee / Adaptador' }}</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 text-primary">Puertos de Conexión</h6>
                                @if($computador_detalle->puertos->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($computador_detalle->puertos as $puerto)
                                            <span class="badge bg-secondary">{{ $puerto->nombre }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">No se especificaron puertos.</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 text-primary">Observaciones Adicionales</h6>
                                <p class="text-muted small mb-0">{{ $computador_detalle->observaciones ?? 'No hay observaciones registradas para este equipo.' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <div wire:ignore.self class="modal fade" id="modalTrabajador" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content border-primary">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Nuevo Trabajador</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelarModalTrabajador"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nuevo_trab_nombres') is-invalid @enderror" wire:model="nuevo_trab_nombres">
                            @error('nuevo_trab_nombres') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nuevo_trab_apellidos') is-invalid @enderror" wire:model="nuevo_trab_apellidos">
                            @error('nuevo_trab_apellidos') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cédula (Opcional)</label>
                        <input type="text" class="form-control @error('nuevo_trab_cedula') is-invalid @enderror" wire:model="nuevo_trab_cedula">
                        @error('nuevo_trab_cedula') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Departamento <span class="text-danger">*</span></label>
                        <select class="form-select @error('nuevo_trab_departamento_id') is-invalid @enderror" wire:model="nuevo_trab_departamento_id">
                            <option value="">Seleccione...</option>
                            @foreach($departamentos as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                            @endforeach
                        </select>
                        @error('nuevo_trab_departamento_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-shield-lock me-1"></i> El sistema generará el correo automáticamente usando el formato institucional.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="cancelarModalTrabajador">Volver</button>
                    <button type="button" class="btn btn-primary" wire:click="guardarTrabajadorRapido">Guardar Trabajador</button>
                </div>
            </div>
        </div>
    </div>
</div>