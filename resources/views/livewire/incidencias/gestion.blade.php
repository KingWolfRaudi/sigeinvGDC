<div>
    <!-- Header Especial -->
    @if(!$ocultarTitulos)
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                <i class="bi bi-list-task fs-3"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-0 text-dark">Gestión de Incidencias</h2>
                <p class="text-muted mb-0">Seguimiento de problemas técnicos, soporte a usuarios y mantenimiento de activos.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Card de Búsqueda y Acciones -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 justify-content-between align-items-center">
                <div class="col-md-4">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar por descripción o trabajador..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <select class="form-select shadow-sm" wire:model.live="filtro_departamento">
                        <option value="">Departamentos</option>
                        @foreach($departamentos as $depto)
                            <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-select shadow-sm" wire:model.live="filtro_estado">
                        <option value="">Todos los Estados</option>
                        <option value="abierto">Abiertos</option>
                        <option value="solventado">Solventados</option>
                        <option value="cerrado">Cerrados</option>
                    </select>
                </div>

                <div class="col-md-4 text-end d-flex gap-2 justify-content-end">
                    <div class="dropdown">
                        <button class="btn btn-outline-success border-2 fw-bold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.incidencias.excel', ['search' => $search, 'departamento_id' => $filtro_departamento, 'estado' => $filtro_estado]) }}"><i class="bi bi-filter me-2 text-success"></i> Vista Actual</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.incidencias.excel') }}"><i class="bi bi-list-check me-2 text-primary"></i> Todo el Historial</a></li>
                        </ul>
                    </div>
                    @can('crear-incidencias') {{-- Assuming this permission exists based on context --}}
                    <button type="button" class="btn btn-primary shadow-sm fw-bold px-4" wire:click="resetForm" data-bs-toggle="modal" data-bs-target="#modalIncidencia">
                        <i class="bi bi-plus-lg me-1"></i> Nueva
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
                    <thead class="table-light py-3">
                        <tr>
                            <th class="ps-4">Folio</th>
                            <th wire:click="sortBy('created_at')" style="cursor: pointer;">
                                Fecha @if($sortField === 'created_at') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th>Trabajador / Depto</th>
                            <th>Problema</th>
                            <th>Técnico</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidencias as $inc)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-primary">#{{ str_pad($inc->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>{{ $inc->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">{{ $inc->trabajador->nombres ?? 'No asignado' }} {{ $inc->trabajador->apellidos ?? '' }}</span>
                                        <small class="text-muted">{{ $inc->departamento->nombre }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $inc->problema->nombre }}</span>
                                </td>
                                <td>{{ $inc->tecnico->name }}</td>
                                <td class="text-center">
                                    @if($inc->cerrado)
                                        <span class="badge bg-dark rounded-pill px-3">Cerrado</span>
                                    @elseif($inc->solventado)
                                        <span class="badge bg-success rounded-pill px-3">Solventado</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill px-3">En Curso</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="editar({{ $inc->id }})" class="btn btn-sm btn-outline-primary" title="Ver / Editar">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    No se encontraron incidencias que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($incidencias->hasPages())
            <div class="card-footer bg-white py-3 border-0">
                {{ $incidencias->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario -->
    <div wire:ignore.self class="modal fade" id="modalIncidencia" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title">
                        <i class="bi bi-clipboard-plus me-2"></i>
                        {{ $incidencia_id ? 'Detalles de Incidencia' : 'Nueva Incidencia' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetForm"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body p-4 bg-light" style="max-height: 65vh; overflow-y: auto;">
                        
                        <div class="row g-3">
                            <!-- Sección 1: Responsable y Ubicación -->
                            <div class="col-12"><h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2">1. Ubicación y Solicitante</h6></div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Departamento <span class="text-danger">*</span></label>
                                <select class="form-select @error('departamento_id') is-invalid @enderror" wire:model.live="departamento_id">
                                    <option value="">Seleccione...</option>
                                    @foreach($departamentos as $depto)
                                        <option value="{{ $depto->id }}">{{ $depto->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('departamento_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Solicitado por (Trabajador)</label>
                                <select class="form-select" wire:model.live="trabajador_id" @disabled(!$departamento_id)>
                                    <option value="">Seleccione...</option>
                                    @foreach($trabajadores as $trab)
                                        <option value="{{ $trab->id }}">{{ $trab->nombres }} {{ $trab->apellidos }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Filtrado por departamento seleccionado.</small>
                            </div>

                            <!-- Sección 2: El Activo (Polimórfico) -->
                            <div class="col-12 mt-4"><h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2">2. Activo Relacionado</h6></div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipo de Activo @if($activo_obligatorio) <span class="text-danger">*</span> @endif</label>
                                <select class="form-select @error('modelo_type') is-invalid @enderror" wire:model.live="modelo_type" @disabled(!$departamento_id)>
                                    <option value="">Ninguno / No Aplica</option>
                                    <option value="App\Models\Computador">Computador</option>
                                    <option value="App\Models\Dispositivo">Dispositivo Especial</option>
                                    <option value="App\Models\Insumo">Insumo / Consumible</option>
                                </select>
                                @error('modelo_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Elegir Activo @if($activo_obligatorio) <span class="text-danger">*</span> @endif</label>
                                <select class="form-select @error('modelo_id') is-invalid @enderror" wire:model="modelo_id" @disabled(count($activos) == 0)>
                                    <option value="">Seleccione...</option>
                                    @foreach($activos as $act)
                                        <option value="{{ $act->id }}">
                                            @if($modelo_type == 'App\Models\Computador')
                                                [{{ $act->bien_nacional }}] {{ $act->marca->nombre }} {{ $act->sistemaOperativo->nombre ?? '' }}
                                            @else
                                                [{{ $act->bien_nacional }}] {{ $act->nombre }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('modelo_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Sección 3: El Caso -->
                            <div class="col-12 mt-4"><h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2">3. Información del Caso</h6></div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipo de Problema <span class="text-danger">*</span></label>
                                <select class="form-select @error('problema_id') is-invalid @enderror" wire:model="problema_id">
                                    <option value="">Seleccione...</option>
                                    @foreach($problemas as $prob)
                                        <option value="{{ $prob->id }}">{{ $prob->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('problema_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Técnico Resolutor <span class="text-danger">*</span></label>
                                <select class="form-select @error('user_id') is-invalid @enderror" wire:model="user_id">
                                    <option value="">Asignar a...</option>
                                    @foreach($tecnicos as $tec)
                                        <option value="{{ $tec->id }}">{{ $tec->name }}</option>
                                    @endforeach
                                </select>
                                @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Descripción del Reporte <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" rows="3" wire:model="descripcion" placeholder="Detalle la falla reportada por el usuario..."></textarea>
                                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Notas de Resolución / Seguimiento</label>
                                <textarea class="form-control" rows="3" wire:model="notas" placeholder="Acciones tomadas para resolver la falla..."></textarea>
                            </div>

                            <!-- Sección 4: Estatus Final -->
                            <div class="col-12 mt-4"><h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2">4. Control y Seguimiento</h6></div>

                            <div class="col-6">
                                <div class="form-check form-switch p-3 border rounded bg-white">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" id="solventCheck" wire:model="solventado">
                                    <label class="form-check-label fw-bold" for="solventCheck">¿Caso Solventado?</label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-check form-switch p-3 border rounded bg-white border-danger shadow-sm">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" id="cerrarCheck" wire:model="cerrado" @disabled(!$solventado)>
                                    <label class="form-check-label fw-bold text-danger" for="cerrarCheck text-danger">¿CERRAR INCIDENCIA?</label>
                                    <div class="small text-muted mt-1">Una vez cerrada, no podrá editarse.</div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer bg-white border-top-0 p-4">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="bi bi-save me-1"></i> {{ $incidencia_id ? 'Actualizar' : 'Registrar Incidencia' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
