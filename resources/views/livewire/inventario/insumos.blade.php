<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-4">
            @if(!$ocultarTitulos)
                <h3 class="mb-0">Almacén General (Insumos)</h3>
            @endif
        </div>
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Buscar por Nombre, Serial, BN o Marca...">
            </div>
        </div>
        <div class="col-md-3 text-end d-flex gap-2">
            @can('reportes-excel')
            <div class="dropdown w-100">
                <button class="btn btn-outline-success border-2 fw-bold w-100 dropdown-toggle shadow-sm py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </button>
                <ul class="dropdown-menu shadow border-0">
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('reportes.inventario.insumos.excel', ['search' => $search, 'estado' => $filtro_estado]) }}">
                            <i class="bi bi-filter me-2 text-success"></i> Vista Actual (Filtrado)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('reportes.inventario.insumos.excel') }}">
                            <i class="bi bi-list-check me-2 text-primary"></i> Todo el Inventario
                        </a>
                    </li>
                </ul>
            </div>
            @endcan
            @can('crear-insumos')
            <button wire:click="crear" class="btn btn-primary w-100 shadow-sm py-2 fw-bold">
                <i class="bi bi-box-seam me-1"></i> Nuevo
            </button>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @can('ver-estado-insumos')
            <div class="col-md-3">
                <select class="form-select" wire:model.live="filtro_estado">
                    <option value="todos">Mostrar Todos</option>
                    <option value="activos">Solo Activos</option>
                    <option value="inactivos">Solo Inactivos (Bajas)</option>
                </select>
            </div>
            @endcan
            <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('bien_nacional')" style="cursor: pointer; min-width: 140px;">
                                Identificación
                                @if($sortField === 'bien_nacional') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                Insumo / Herramienta
                                @if($sortField === 'nombre') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            <th wire:click="sortBy('medida_actual')" style="cursor: pointer;">
                                Stock / Medida
                                @if($sortField === 'medida_actual') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            <th>Características</th>
                            
                            <th wire:click="sortBy('estado_fisico')" style="cursor: pointer;">
                                Condición Fís.
                                @if($sortField === 'estado_fisico') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>

                            @can('ver-estado-insumos')
                            <th class="th-estado" wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado
                                @if($sortField === 'activo') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @endcan

                            <th class="th-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($insumos as $insumo)
                        <tr class="{{ $insumo->medida_actual <= $insumo->medida_minima ? 'table-danger' : '' }}">
                            <td>
                                <strong>BN:</strong> {{ $insumo->bien_nacional ?? 'N/A' }}
                                @if($insumo->pendientes_count > 0)
                                    <button wire:click="verCambioPendiente({{ $insumo->id }})"
                                        class="badge bg-warning text-dark border-0 ms-1"
                                        title="{{ $insumo->pendientes_count }} cambio(s) en revisión — clic para ver">
                                        <i class="bi bi-hourglass-split"></i> En revisión
                                    </button>
                                @endif
                                @if($insumo->mis_borradores_count > 0)
                                    <button wire:click="verCambioPendiente({{ $insumo->id }})"
                                        class="badge bg-info text-white border-0 ms-1"
                                        title="{{ $insumo->mis_borradores_count }} borrador(es) tuyos — clic para ver">
                                        <i class="bi bi-pencil"></i> Borrador
                                    </button>
                                @endif
                                <br>
                                <small class="text-muted">Serial: {{ $insumo->serial ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <strong>{{ $insumo->nombre }}</strong><br>
                                <small class="text-muted">{{ $insumo->categoriaInsumo->nombre ?? 'Sin Categ.' }} - {{ $insumo->marca->nombre ?? 'Sin Marca' }}</small>
                            </td>
                            <td>
                                <strong>{{ floatval($insumo->medida_actual) }}</strong> {{ $insumo->unidad_medida }}<br>
                                @if($insumo->medida_actual <= $insumo->medida_minima)
                                    <span class="badge bg-danger">Stock Crítico</span>
                                @endif
                            </td>
                            <td>
                                @if($insumo->reutilizable)
                                <span class="badge bg-secondary" title="Retorna al almacén luego de usarse"><i class="bi bi-arrow-repeat me-1"></i> Reutilizable</span><br>
                                @endif
                                @if($insumo->instalable_en_equipo)
                                <span class="badge bg-dark mt-1" title="Puede ensamblarse interno en un Computador"><i class="bi bi-cpu me-1"></i> Instalable en PC</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $insumo->estado_fisico === 'operativo' ? 'success' : ($insumo->estado_fisico === 'danado' ? 'danger' : 'warning') }}">
                                    {{ strtoupper(str_replace('_', ' ', $insumo->estado_fisico)) }}
                                </span>
                            </td>
                            @can('ver-estado-insumos')
                            <td>
                                {!! $insumo->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' !!}
                            </td>
                            @endcan
                            <td class="text-end">
                                @can('cambiar-estatus-insumos')
                                <button wire:click="toggleActivo({{ $insumo->id }})" class="btn btn-sm {{ $insumo->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado">
                                    <i class="bi {{ $insumo->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                </button>
                                @endcan
                                @can('ver-insumos')
                                <button wire:click="ver({{ $insumo->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles"><i class="bi bi-eye"></i></button>
                                    @can('reportes-pdf')
                                        <a href="{{ route('reportes.insumo.ficha', $insumo->id) }}" target="_blank" class="btn btn-sm btn-danger text-white shadow-sm fw-bold border-2" title="Ficha Técnica PDF">
                                           <i class="bi bi-file-pdf"></i>
                                        </a>
                                    @endcan
                                @endcan
                                @can('editar-insumos')
                                <button wire:click="editar({{ $insumo->id }})" class="btn btn-sm btn-primary" title="Editar"><i class="bi bi-pencil-square"></i></button>
                                @endcan
                                @role('super-admin')
                                <button wire:click="eliminar({{ $insumo->id }})" wire:confirm="¿Está seguro de eliminar este registro?" class="btn btn-sm btn-danger" title="Dar de Baja Definitiva"><i class="bi bi-trash"></i></button>
                                @endrole
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se encontraron insumos/herramientas en el almacén.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $insumos->links() }}</div>
        </div>
    </div>

    <!-- Modal Principal -->
    <div wire:ignore.self class="modal fade" id="modalInsumo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body p-4">

                        <h6 class="border-bottom pb-2 text-primary">1. Identificación y Clasificación</h6>
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bien Nacional (Opcional)</label>
                                <input type="text" class="form-control @error('bien_nacional') is-invalid @enderror" wire:model="bien_nacional">
                                @error('bien_nacional') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Serial Fabricante (Opcional)</label>
                                <input type="text" class="form-control @error('serial') is-invalid @enderror" wire:model="serial">
                                @error('serial') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Categoría <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    @if($creando_categoria)
                                    <input type="text" class="form-control border-primary" wire:model="nueva_categoria" placeholder="Nueva categoría...">
                                    <button class="btn btn-outline-danger" type="button" wire:click="$set('creando_categoria', false)"><i class="bi bi-x-lg"></i></button>
                                    @else
                                    <select class="form-select @error('categoria_insumo_id') is-invalid @enderror" wire:model="categoria_insumo_id">
                                        <option value="">Seleccione...</option>
                                        @foreach($categorias as $cat) <option value="{{ $cat->id }}">{{ $cat->nombre }}</option> @endforeach
                                    </select>
                                    <button class="btn btn-outline-success" type="button" wire:click="$set('creando_categoria', true)" title="Crear nueva categoría"><i class="bi bi-plus-lg"></i></button>
                                    @endif
                                </div>
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

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Insumo / Modelo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" wire:model="nombre" placeholder="Ej: Bobina Cable UTP Cat 6 / Memoria RAM DDR4 8GB">
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Estado Físico <span class="text-danger">*</span></label>
                                <select class="form-select @error('estado_fisico') is-invalid @enderror" wire:model="estado_fisico">
                                    <option value="operativo">Operativo (Bueno)</option>
                                    <option value="danado">Dañado</option>
                                    <option value="en_reparacion">En Reparación</option>
                                    <option value="indeterminado">Indeterminado</option>
                                </select>
                                @error('estado_fisico') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 text-primary">2. Ubicación y Responsable (Opcional)</h6>
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Departamento</label>
                                <select class="form-select @error('departamento_id') is-invalid @enderror" wire:model.live="departamento_id">
                                    <option value="">Seleccione Departamento...</option>
                                    @foreach($departamentos as $dep)
                                        <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('departamento_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Responsable / Trabajador</label>
                                <select class="form-select @error('trabajador_id') is-invalid @enderror" wire:model="trabajador_id" @if(!$departamento_id) disabled @endif>
                                    <option value="">Seleccione Trabajador...</option>
                                    @foreach($trabajadores as $trab)
                                        <option value="{{ $trab->id }}">{{ $trab->nombres }} {{ $trab->apellidos }}</option>
                                    @endforeach
                                </select>
                                @if(!$departamento_id) <small class="text-muted">Seleccione un departamento primero</small> @endif
                                @error('trabajador_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Dispositivo Asociado</label>
                                <select class="form-select @error('dispositivo_id') is-invalid @enderror" wire:model="dispositivo_id" @if(!$departamento_id) disabled @endif>
                                    <option value="">Seleccione Dispositivo...</option>
                                    @foreach($dispositivos as $disp)
                                        <option value="{{ $disp->id }}">{{ $disp->nombre }} ({{ $disp->bien_nacional ?? $disp->serial }})</option>
                                    @endforeach
                                </select>
                                @error('dispositivo_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Computador Asociado</label>
                                <select class="form-select @error('computador_id') is-invalid @enderror" wire:model="computador_id" @if(!$departamento_id) disabled @endif>
                                    <option value="">Seleccione Computador...</option>
                                    @foreach($computadores as $comp)
                                        <option value="{{ $comp->id }}">{{ $comp->nombre_equipo }} ({{ $comp->bien_nacional ?? $comp->serial }})</option>
                                    @endforeach
                                </select>
                                @error('computador_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 text-primary">3. Especificaciones de Stock y Naturaleza</h6>
                        <div class="row w-100">
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Unidad de Medición <span class="text-danger">*</span></label>
                                <select class="form-select @error('unidad_medida') is-invalid @enderror" wire:model="unidad_medida">
                                    <option value="unidad">Unidades / Piezas</option>
                                    <option value="metros">Metros (Longitud)</option>
                                    <option value="litros">Litros (Volumen)</option>
                                    <option value="cajas">Cajas / Paquetes</option>
                                    <option value="pares">Pares</option>
                                </select>
                                @error('unidad_medida') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Stock / Cantidad Inicial <span class="text-danger">*</span></label>
                                <input type="number" 
                                    @if(in_array($unidad_medida, ['unidad', 'cajas', 'pares'])) step="1" @else step="0.01" @endif 
                                    min="0" class="form-control @error('medida_actual') is-invalid @enderror" wire:model="medida_actual">
                                @error('medida_actual') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Alerta Stock Crítico <span class="text-danger">*</span></label>
                                <input type="number" 
                                    @if(in_array($unidad_medida, ['unidad', 'cajas', 'pares'])) step="1" @else step="0.01" @endif 
                                    min="0" class="form-control @error('medida_minima') is-invalid @enderror" wire:model="medida_minima" title="Cant. mínima para lanzar alerta visual">
                                @error('medida_minima') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="reutilizable" wire:model="reutilizable">
                                            <label class="form-check-label fw-bold" for="reutilizable">Herramienta Reutilizable (Debe Retornar)</label>
                                        </div>
                                        <p class="text-muted small mb-0 ms-4">Si está activado, en los Movimientos se tratará como "Préstamo" y al devolverse sumará stock de nuevo (+).</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="instalable_en_equipo" wire:model="instalable_en_equipo">
                                            <label class="form-check-label fw-bold" for="instalable_en_equipo">Pieza Incrustable (Para PCs)</label>
                                        </div>
                                        <p class="text-muted small mb-0 ms-4">Si está activado, los administradores podrán "ensamblar" este registro permanentemente dentro de un Equipo del inventario.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mt-2 text-primary">4. Detalles Descriptivos</h6>
                        <div class="row">
                            <div class="col-12">
                                <label class="form-label">Descripción / Ficha Técnica</label>
                                <textarea class="form-control" wire:model="descripcion" rows="3" placeholder="Detalles de velocidad, tipo de rosca, voltaje o características que sean importantes..."></textarea>
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
                        @can('cambiar-estatus-insumos')
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="activo" wire:model="activo">
                            <label class="form-check-label" for="activo">Registro Visualizable e Inventariado (Activo)</label>
                        </div>
                        @endcan
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar en Almacén</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detalles -->
    <div wire:ignore.self class="modal fade" id="modalDetalleInsumo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Ficha de Almacén</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($insumo_detalle)
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="border-bottom pb-2 text-primary">Clasificación Básica</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><strong>Estado en Sistema:</strong>
                                    {!! $insumo_detalle->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo / Baja</span>' !!}
                                </li>
                                <li class="mb-1"><strong>Bien Nacional:</strong> {{ $insumo_detalle->bien_nacional ?? 'N/A' }}</li>
                                <li class="mb-1"><strong>Serial Fabricante:</strong> {{ $insumo_detalle->serial ?? 'N/A' }}</li>
                                <li class="mb-1"><strong>Marca y Categ.:</strong> {{ $insumo_detalle->marca->nombre ?? 'N/A' }} - {{ $insumo_detalle->categoriaInsumo->nombre ?? 'N/A' }}</li>
                                <li class="mb-1"><strong>Referencia:</strong> {{ $insumo_detalle->nombre }}</li>
                            </ul>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h6 class="border-bottom pb-2 text-primary">Condiciones y Métrica</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><strong>Nivel Almacén:</strong> {{ floatval($insumo_detalle->medida_actual) }} {{ $insumo_detalle->unidad_medida }}</li>
                                <li class="mb-1"><strong>Alerta Mínima (Trigger):</strong> {{ floatval($insumo_detalle->medida_minima) }} {{ $insumo_detalle->unidad_medida }}</li>
                                <li class="mb-1"><strong>Condición Estética:</strong> {{ ucfirst($insumo_detalle->estado_fisico) }}</li>
                                <li class="mb-1 mt-2">
                                    {!! $insumo_detalle->reutilizable ? '<span class="badge bg-secondary"><i class="bi bi-exclamation-triangle"></i> Requerirá Devolución</span>' : '<span class="badge bg-light text-dark"><i class="bi bi-trash"></i> Uso Descartable</span>' !!}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="border-bottom pb-2 text-primary">Asociaciones</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><strong>Departamento:</strong> {{ $insumo_detalle->departamento->nombre ?? 'Sin asignar' }}</li>
                                <li class="mb-1"><strong>Responsable:</strong> {{ $insumo_detalle->trabajador ? ($insumo_detalle->trabajador->nombres . ' ' . $insumo_detalle->trabajador->apellidos) : 'Sin asignar' }}</li>
                                <li class="mb-1"><strong>Dispositivo:</strong> {{ $insumo_detalle->dispositivo->nombre ?? 'Sin asignar' }}</li>
                                <li class="mb-1"><strong>Computador:</strong> {{ $insumo_detalle->computador->nombre_equipo ?? 'Sin asignar' }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="border-bottom pb-2 text-primary">Descripción Completa</h6>
                            <div class="p-3 bg-light rounded text-dark small border">
                                {{ $insumo_detalle->descripcion ?? 'El registro no posee descripción ampliada.' }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    @if($insumo_detalle)
                        @can('ver-insumos')
                        <div>
                            <a href="{{ route('asociaciones', ['tipo' => 'insumo', 'id' => $insumo_detalle->id]) }}" class="btn btn-outline-primary shadow-sm me-2">
                                <i class="bi bi-diagram-3 me-1"></i> Asociaciones
                            </a>
                        </div>
                        @endcan
                    @else
                        <div></div>
                    @endif
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
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
                        'entrada_stock'       => 'Entrada de Stock',
                        'salida_consumo'      => 'Salida de Consumo',
                        'prestamo'            => 'Préstamo',
                        'devolucion'          => 'Devolución',
                        'actualizacion_datos' => 'Actualización de Datos',
                        'toggle_activo'       => 'Cambio de Estatus',
                        'baja'                => 'Baja del Sistema',
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
                        @can('movimientos-insumos-aprobar')
                        <button wire:click="aprobarMovimientoPreview"
                            wire:confirm="¿Confirmar aprobación y aplicar cambios al insumo?"
                            class="btn btn-success btn-sm">
                            <i class="bi bi-check-lg me-1"></i> Aprobar
                        </button>
                        @endcan
                    @endif
                    <a href="{{ route('movimientos.insumos') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>Ir a Movimientos
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

