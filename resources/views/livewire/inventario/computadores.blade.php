<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            @if(!$ocultarTitulos)
                <h3 class="mb-0">Inventario de Computadores</h3>
            @endif
        </div>
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por Bien Nacional, Serial o IP...">
            </div>
        </div>
        <div class="col-md-3 text-end d-flex gap-2">
            @can('reportes-excel')
            <div class="dropdown w-100">
                <button class="btn btn-outline-success border-2 fw-bold w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </button>
                <ul class="dropdown-menu shadow border-0">
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('reportes.inventario.computadores.excel', ['search' => $search, 'estado' => $filtro_estado, 'departamento_id' => $departamento_id]) }}">
                            <i class="bi bi-filter me-2 text-success"></i> Vista Actual (Filtrado)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('reportes.inventario.computadores.excel') }}">
                            <i class="bi bi-list-check me-2 text-primary"></i> Todo el Inventario
                        </a>
                    </li>
                </ul>
            </div>
            @endcan
            @can('crear-computadores')
                <button wire:click="crear" class="btn btn-primary w-100">
                    <i class="bi bi-pc-display me-1"></i> Nuevo
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
                            
                            <th wire:click="sortBy('nombre_equipo')" style="cursor: pointer;">
                                Tipo / Equipo
                                @if($sortField === 'nombre_equipo') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            
                            <th>Marca / Modelo</th>
                            
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
                                        <button wire:click="verCambioPendiente({{ $comp->id }})"
                                            class="badge bg-warning text-dark border-0 ms-1"
                                            title="{{ $comp->pendientes_count }} cambio(s) en revisión — clic para ver">
                                            <i class="bi bi-hourglass-split"></i> En revisión
                                        </button>
                                    @endif
                                    @if($comp->mis_borradores_count > 0)
                                        <button wire:click="verCambioPendiente({{ $comp->id }})"
                                            class="badge bg-info text-white border-0 ms-1"
                                            title="{{ $comp->mis_borradores_count }} borrador(es) tuyos — clic para ver">
                                            <i class="bi bi-pencil"></i> Borrador
                                        </button>
                                    @endif
                                    <br>
                                    <small class="text-muted">Serial: {{ $comp->serial ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $comp->tipo_computador }}</strong><br>
                                    <small class="text-muted">{{ $comp->nombre_equipo }}</small>
                                </td>
                                <td>
                                    <strong>{{ $comp->marca->nombre ?? 'N/A' }}</strong><br>
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
                                    @can('cambiar-estatus-computadores')
                                        <button wire:click="toggleActivo({{ $comp->id }})" class="btn btn-sm {{ $comp->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                            <i class="bi {{ $comp->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan
                                    @can('ver-computadores')
                                        <button wire:click="ver({{ $comp->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles"><i class="bi bi-eye"></i></button>
                                        @can('reportes-pdf')
                                        <a href="{{ route('reportes.computador.ficha', $comp->id) }}" target="_blank" class="btn btn-sm btn-danger text-white shadow-sm fw-bold border-2" title="Descargar Ficha PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                        @endcan
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
                                <label class="form-label">Nombre del Equipo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre_equipo') is-invalid @enderror" wire:model="nombre_equipo" maxlength="15" placeholder="Ej: PC-ADM-01">
                                @error('nombre_equipo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
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
                                <label class="form-label">Tipo de Computador <span class="text-danger">*</span></label>
                                <select class="form-select @error('tipo_computador') is-invalid @enderror" wire:model="tipo_computador">
                                    <option value="">Seleccione...</option>
                                    <option value="Computador de escritorio">Computador de escritorio</option>
                                    <option value="Laptop">Laptop</option>
                                    <option value="Mini Laptop">Mini Laptop</option>
                                </select>
                                @error('tipo_computador') <span class="text-danger small">{{ $message }}</span> @enderror
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

    <div wire:ignore.self class="modal fade" id="modalDetalleComputador" tabindex="-1" aria-hidden="true">
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
                                    <li class="mb-1"><strong>Nombre Equipo:</strong> <span class="text-primary fw-bold">{{ $computador_detalle->nombre_equipo }}</span></li>
                                    <li class="mb-1"><strong>Tipo de Computador:</strong> {{ $computador_detalle->tipo_computador }}</li>
                                    <li class="mb-1"><strong>Bien Nacional:</strong> {{ $computador_detalle->bien_nacional ?? 'No especificado' }}</li>
                                    <li class="mb-1"><strong>Serial:</strong> {{ $computador_detalle->serial ?? 'No especificado' }}</li>
                                    <li class="mb-1"><strong>Marca:</strong> {{ $computador_detalle->marca->nombre ?? 'No especificado' }}</li>
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
                <div class="modal-footer bg-light d-flex justify-content-between">
                    @if($computador_detalle)
                        <div>
                            <a href="{{ route('asociaciones', ['tipo' => 'computador', 'id' => $computador_detalle->id]) }}" class="btn btn-outline-primary shadow-sm me-2">
                                <i class="bi bi-diagram-3 me-1"></i> Asociaciones
                            </a>
                        </div>
                    @else
                        <div></div>
                    @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cerrar</button>
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

    {{-- Modal: Vista Rápida de Cambio Pendiente --}}
    <div wire:ignore.self class="modal fade" id="modalCambioPendiente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                @if($movimiento_preview)
                @php
                    $est = $movimiento_preview->estado_workflow;
                    $esRevisión = $est === 'pendiente';
                    $tiposLabel = [
                        'actualizacion_datos'     => 'Actualización de Datos',
                        'cambio_departamento'     => 'Cambio de Departamento',
                        'reasignacion_trabajador' => 'Reasignación de Trabajador',
                        'cambio_estado'           => 'Cambio de Estado Físico',
                        'toggle_activo'           => 'Cambio de Estatus',
                        'baja'                    => 'Baja del Sistema',
                    ];
                @endphp
                <div class="modal-header {{ $esRevisión ? 'bg-warning text-dark' : 'bg-info text-white' }}">
                    <h5 class="modal-title">
                        <i class="bi bi-{{ $esRevisión ? 'hourglass-split' : 'pencil-square' }} me-2"></i>
                        Cambio {{ $esRevisión ? 'En Revisión' : 'En Borrador' }}
                    </h5>
                    <button type="button"
                        class="btn-close {{ $esRevisión ? '' : 'btn-close-white' }}"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Solicitante y Metadata --}}
                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded mb-3">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                                style="width:42px;height:42px;font-size:1rem;">
                                {{ strtoupper(substr($movimiento_preview->solicitante->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $movimiento_preview->solicitante->name ?? 'Usuario desconocido' }}</div>
                            <div class="text-muted small">
                                <i class="bi bi-clock me-1"></i>
                                Solicitado {{ $movimiento_preview->created_at->diffForHumans() }}
                                · {{ $movimiento_preview->created_at->format('d/m/Y H:i') }}
                            </div>
                            <div class="mt-1">
                                <span class="badge bg-secondary fw-normal">
                                    {{ $tiposLabel[$movimiento_preview->tipo_operacion] ?? ucwords(str_replace('_',' ',$movimiento_preview->tipo_operacion)) }}
                                </span>
                                <span class="badge {{ $esRevisión ? 'bg-warning text-dark' : 'bg-info' }} fw-normal ms-1">
                                    {{ $esRevisión ? 'En Revisión' : 'Borrador' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($movimiento_preview->justificacion)
                    <div class="mb-3">
                        <p class="small text-muted mb-1 fw-semibold"><i class="bi bi-chat-quote me-1"></i>Justificación:</p>
                        <blockquote class="blockquote-footer ps-3 border-start border-3 mb-0">
                            <em>{{ $movimiento_preview->justificacion }}</em>
                        </blockquote>
                    </div>
                    @endif

                    {{-- Diff del cambio --}}
                    <div class="border rounded p-3">
                        <h6 class="text-success mb-3">
                            <i class="bi bi-pencil-square me-1"></i>Modificación Propuesta
                        </h6>
                        @include('livewire.movimientos._detalle-cambios', [
                            'movimiento_detalle' => $movimiento_preview
                        ])
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    @if($esRevisión)
                        @can('movimientos-computadores-aprobar')
                        <button wire:click="aprobarMovimientoPreview"
                            wire:confirm="¿Confirmar aprobación y aplicar cambios al computador?"
                            class="btn btn-success btn-sm">
                            <i class="bi bi-check-lg me-1"></i> Aprobar
                        </button>
                        @endcan
                    @endif
                    <a href="{{ route('movimientos.computadores') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>Ir a Movimientos
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>