<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            @if(!$ocultarTitulos)
                <h3 class="mb-0">Inventario de Dispositivos</h3>
            @endif
        </div>
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por Código, Serial, Modelo o IP...">
            </div>
        </div>
        <div class="col-md-3 text-end d-flex gap-2">
            <div class="dropdown w-100">
                <button class="btn btn-outline-success border-2 fw-bold w-100 dropdown-toggle shadow-sm py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </button>
                <ul class="dropdown-menu shadow border-0">
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('reportes.inventario.dispositivos.excel', ['search' => $search, 'estado' => $filtro_estado, 'departamento_id' => $departamento_id]) }}">
                            <i class="bi bi-filter me-2 text-success"></i> Vista Actual (Filtrado)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('reportes.inventario.dispositivos.excel') }}">
                            <i class="bi bi-list-check me-2 text-primary"></i> Todo el Inventario
                        </a>
                    </li>
                </ul>
            </div>
            @can('crear-dispositivos')
            <button wire:click="crear" class="btn btn-primary w-100 shadow-sm py-2 fw-bold">
                <i class="bi bi-printer me-1"></i> Nuevo
            </button>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @can('ver-estado-dispositivos')
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
                            <th wire:click="sortBy('codigo')" style="cursor: pointer; min-width: 140px;">
                                Identificación
                                @if($sortField === 'codigo') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                Dispositivo / Modelo
                                @if($sortField === 'nombre') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            <th wire:click="sortBy('ip')" style="cursor: pointer;">
                                Red
                                @if($sortField === 'ip') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            <th wire:click="sortBy('estado')" style="cursor: pointer;">
                                Condición
                                @if($sortField === 'estado') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            <th wire:click="sortBy('departamento_id')" style="cursor: pointer;">
                                Ubicación
                                @if($sortField === 'departamento_id') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            @can('ver-estado-dispositivos')
                            <th class="th-estado" wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado
                                @if($sortField === 'activo') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @endcan

                            <th class="th-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dispositivos as $disp)
                        <tr>
                                <td>
                                <strong>CÓD:</strong> {{ $disp->codigo ?? 'N/A' }}
                                @if($disp->pendientes_count > 0)
                                    <button wire:click="verCambioPendiente({{ $disp->id }})"
                                        class="badge bg-warning text-dark border-0 ms-1"
                                        title="{{ $disp->pendientes_count }} cambio(s) en revisión — clic para ver">
                                        <i class="bi bi-hourglass-split"></i> En revisión
                                    </button>
                                @endif
                                @if($disp->mis_borradores_count > 0)
                                    <button wire:click="verCambioPendiente({{ $disp->id }})"
                                        class="badge bg-info text-white border-0 ms-1"
                                        title="{{ $disp->mis_borradores_count }} borrador(es) tuyos — clic para ver">
                                        <i class="bi bi-pencil"></i> Borrador
                                    </button>
                                @endif
                                <br>
                                <small class="text-muted">Serial: {{ $disp->serial ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <strong>{{ $disp->marca->nombre ?? 'N/A' }} - {{ $disp->tipoDispositivo->nombre ?? 'N/A' }}</strong><br>
                                <small class="text-muted">Modelo: {{ $disp->nombre }} </small>
                            </td>
                            <td>
                                {{ $disp->ip ?? 'Sin IP' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $disp->estado === 'operativo' ? 'success' : ($disp->estado === 'dañado' ? 'danger' : 'warning') }}">
                                    {{ strtoupper(str_replace('_', ' ', $disp->estado)) }}
                                </span>
                            </td>
                            <td>
                                {{ $disp->departamento->nombre ?? 'N/A' }}
                            </td>
                            @can('ver-estado-dispositivos')
                            <td>
                                {!! $disp->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' !!}
                            </td>
                            @endcan
                            <td class="text-end">
                                @can('cambiar-estatus-dispositivos')
                                <button wire:click="toggleActivo({{ $disp->id }})" class="btn btn-sm {{ $disp->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                    <i class="bi {{ $disp->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                </button>
                                @endcan
                                @can('ver-dispositivos')
                                    <button wire:click="ver({{ $disp->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles"><i class="bi bi-eye"></i></button>
                                    @can('reportes-pdf')
                                    <a href="{{ route('reportes.dispositivo.ficha', $disp->id) }}" target="_blank" class="btn btn-sm btn-danger text-white shadow-sm fw-bold border-2" title="Ficha Técnica PDF">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                    @endcan
                                @endcan
                                @can('editar-dispositivos')
                                <button wire:click="editar({{ $disp->id }})" class="btn btn-sm btn-primary" title="Editar"><i class="bi bi-pencil-square"></i></button>
                                @endcan
                                @role('super-admin')
                                <button wire:click="eliminar({{ $disp->id }})" wire:confirm="¿Está seguro de dar de baja permanentemente este dispositivo?" class="btn btn-sm btn-danger" title="Dar de Baja Definitiva"><i class="bi bi-trash"></i></button>
                                @endrole
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se encontraron dispositivos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $dispositivos->links() }}</div>
        </div>
    </div>

    <!-- Modal Principal -->
    <div wire:ignore.self class="modal fade" id="modalDispositivo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-printer me-2"></i>{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body p-4">

                        <h6 class="border-bottom pb-2 text-primary">1. Identificación y Atributos Físicos</h6>
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Código Interno</label>
                                <input type="text" class="form-control @error('codigo') is-invalid @enderror" wire:model="codigo">
                                @error('codigo') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Serial Fabricante</label>
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
                                <label class="form-label">Tipo Dispositivo <span class="text-danger">*</span></label>
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

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Modelo / Diseño Específico <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" wire:model="nombre" placeholder="Ej: LaserJet Pro M402dn">
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Estado de Funcionamiento <span class="text-danger">*</span></label>
                                <select class="form-select @error('estado') is-invalid @enderror" wire:model="estado">
                                    <option value="operativo">Operativo</option>
                                    <option value="dañado">Dañado</option>
                                    <option value="en_reparacion">En Reparación</option>
                                    <option value="indeterminado">Indeterminado</option>
                                </select>
                                @error('estado') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Dirección IP (Red)</label>
                                <input type="text" class="form-control @error('ip') is-invalid @enderror" wire:model="ip" placeholder="192.168.X.X">
                                @error('ip') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 text-primary">2. Distribución y Asignación</h6>
                        <div class="row w-100">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Departamento / Área <span class="text-danger">*</span></label>
                                <select class="form-select @error('departamento_id') is-invalid @enderror" wire:model.live="departamento_id">
                                    <option value="">Seleccione...</option>
                                    @foreach($departamentos as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('departamento_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Trabajador Responsable</label>
                                <div class="input-group">
                                    <select class="form-select" wire:model="trabajador_id" @if(!$departamento_id) disabled @endif>
                                        @if(!$departamento_id)
                                        <option value="">Seleccione dep. primero...</option>
                                        @else
                                        <option value="">(Sin asignar)</option>
                                        @foreach($trabajadores as $t)
                                        <option value="{{ $t->id }}">{{ $t->nombres }} {{ $t->apellidos }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" wire:click="abrirModalTrabajador" title="Registrar Trabajador" @if(!$departamento_id) disabled @endif>
                                        <i class="bi bi-person-plus-fill"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Conectado a Computador</label>
                                <select class="form-select" wire:model="computador_id">
                                    <option value="">Seleccione si aplica...</option>
                                    @foreach($computadores as $comp)
                                    <option value="{{ $comp->id }}">BN: {{ $comp->bien_nacional }} - {{ $comp->marca->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 text-primary">3. Puertos de Salida y Notas Adicionales</h6>
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
                                <textarea class="form-control" wire:model="notas" rows="2"></textarea>
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
                        @can('cambiar-estatus-dispositivos')
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                            <label class="form-check-label" for="activo">Dispositivo Operativo (Activo)</label>
                        </div>
                        @endcan
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Dispositivo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detalles -->
    <div wire:ignore.self class="modal fade" id="modalDetalleDispositivo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-printer me-2"></i>Detalles del Dispositivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($dispositivo_detalle)
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <h6 class="border-bottom pb-2 text-primary">Identificación y Asignación</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><strong>Estado Dispositivo:</strong>
                                    {!! $dispositivo_detalle->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' !!}
                                </li>
                                <li class="mb-1"><strong>Código Físico:</strong> {{ $dispositivo_detalle->codigo ?? 'No especificado' }}</li>
                                <li class="mb-1"><strong>Serial:</strong> {{ $dispositivo_detalle->serial ?? 'No especificado' }}</li>
                                <li class="mb-1"><strong>Marca / Tipo:</strong> {{ $dispositivo_detalle->marca->nombre ?? 'No especificado' }} - {{ $dispositivo_detalle->tipoDispositivo->nombre ?? 'No especificado' }}</li>
                                <li class="mb-1"><strong>Departamento:</strong> {{ $dispositivo_detalle->departamento->nombre ?? 'Sin Dep.' }}</li>
                                <li class="mb-1"><strong>Responsable:</strong> {{ $dispositivo_detalle->trabajador->nombres ?? 'Ninguno' }} {{ $dispositivo_detalle->trabajador->apellidos ?? '' }}</li>
                            </ul>
                        </div>

                        <div class="col-md-4 mb-4">
                            <h6 class="border-bottom pb-2 text-primary">Hardware y Especificaciones</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><strong>Modelo de Referencia:</strong> {{ $dispositivo_detalle->nombre ?? 'No especificado' }}</li>
                                <li class="mb-1"><strong>Conectado en Red por medio de la IP:</strong> {{ $dispositivo_detalle->ip ?? 'No / USB Directo' }}</li>
                                <li class="mb-1"><strong>Conectado local a PC:</strong> {{ $dispositivo_detalle->computador ? 'BN: ' . $dispositivo_detalle->computador->bien_nacional : 'Libre o en Red' }}</li>
                                <li class="mb-1"><strong>Estado Físico Actual:</strong> {{ ucfirst(str_replace('_', ' ', $dispositivo_detalle->estado ?? 'Indeterminado')) }}</li>
                            </ul>
                        </div>

                        <div class="col-md-4 mb-4">
                            <h6 class="border-bottom pb-2 text-primary">Puertos Adaptados</h6>
                            @if($dispositivo_detalle->puertos->count() > 0)
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($dispositivo_detalle->puertos as $puerto)
                                <span class="badge bg-secondary">{{ $puerto->nombre }}</span>
                                @endforeach
                            </div>
                            @else
                            <span class="text-muted small">No se especificaron puertos.</span>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 text-primary">Observaciones Adicionales</h6>
                            <p class="text-muted small mb-0">{{ $dispositivo_detalle->notas ?? 'No hay observaciones registradas para este equipo.' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    @if($dispositivo_detalle)
                        <div>
                            <a href="{{ route('asociaciones', ['tipo' => 'dispositivo', 'id' => $dispositivo_detalle->id]) }}" class="btn btn-outline-primary shadow-sm me-2">
                                <i class="bi bi-diagram-3 me-1"></i> Asociaciones
                            </a>
                            <a href="{{ route('reportes.dispositivo.ficha', $dispositivo_detalle->id) }}" target="_blank" class="btn btn-dark shadow-sm">
                                <i class="bi bi-file-pdf me-1"></i> Ficha Técnica (PDF)
                            </a>
                        </div>
                    @else
                        <div></div>
                    @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Trabajador (Creación Rápida) -->
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
                        <i class="bi bi-shield-lock me-1"></i> El sistema generará el usuario automáticamente.
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
                        @can('movimientos-dispositivos-aprobar')
                        <button wire:click="aprobarMovimientoPreview"
                            wire:confirm="¿Confirmar aprobación y aplicar cambios al dispositivo?"
                            class="btn btn-success btn-sm">
                            <i class="bi bi-check-lg me-1"></i> Aprobar
                        </button>
                        @endcan
                    @endif
                    <a href="{{ route('movimientos.dispositivos') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>Ir a Movimientos
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>