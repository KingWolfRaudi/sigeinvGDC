<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="bi bi-gear-wide-connected me-2"></i>Configuración de Incidencias</h3>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'tipos' ? 'active' : '' }}" wire:click="setTab('tipos')">
                <i class="bi bi-list-task me-1"></i> Catálogo de Problemas
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}" wire:click="setTab('general')">
                <i class="bi bi-sliders me-1"></i> Configuración General
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="configTabsContent">
        
        <!-- Tab: Catálogo de Problemas -->
        @if($activeTab === 'tipos')
            <div class="card shadow-sm border-0 animated fadeIn animate__faster">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <h5 class="mb-0 text-secondary">Tipos de Incidencias</h5>
                    <button type="button" class="btn btn-sm btn-primary" wire:click="resetProblema" data-bs-toggle="modal" data-bs-target="#modalProblema">
                        <i class="bi bi-plus-circle me-1"></i> Nuevo Tipo
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-light border-start-0" placeholder="Buscar tipo de incidencia..." wire:model.live.debounce.300ms="searchProblema">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th wire:click="sortBy('nombre')" style="cursor: pointer;">
                                        Nombre @if($sortField === 'nombre') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                                    </th>
                                    <th wire:click="sortBy('activo')" style="cursor: pointer;" class="text-center">
                                        Estado @if($sortField === 'activo') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                                    </th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($problemas as $prob)
                                    <tr>
                                        <td>{{ $prob->nombre }}</td>
                                        <td class="text-center">
                                            @if($prob->activo)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button wire:click="editarProblema({{ $prob->id }})" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button wire:click="eliminarProblema({{ $prob->id }})" wire:confirm="¿Estás seguro de eliminar este tipo de incidencia? Esto solo será posible si no tiene incidencias registradas." class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No se encontraron tipos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $problemas->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Tab: Configuración General -->
        @if($activeTab === 'general')
            <div class="card shadow-sm border-0 animated fadeIn animate__faster">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 text-secondary">Ajustes de Flujo de Trabajo</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="guardarConfiguracion">
                        <div class="mb-4">
                            <label class="form-label fw-bold"><i class="bi bi-people me-1"></i> Roles Técnicos (Resolutores)</label>
                            <p class="text-muted small">Selecciona los roles que pueden ser seleccionados como "Técnico Resolutor" en una incidencia.</p>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                                @foreach($rolesDisponibles as $role)
                                    <div class="col">
                                        <div class="form-check form-switch p-3 border rounded h-100 d-flex align-items-center">
                                            <input class="form-check-input ms-0 me-3" type="checkbox" 
                                                   value="{{ $role->name }}" 
                                                   id="role_{{ $role->id }}" 
                                                   wire:model="roles_tecnicos">
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                {{ ucfirst($role->name) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch p-3 border rounded d-flex align-items-center justify-content-between h-100">
                                    <div>
                                        <label class="form-check-label fw-bold d-block" for="cierreIrr">Cierre Irreversible</label>
                                        <small class="text-muted">Si se activa, el cerrar una incidencia bloquea por completo la edición.</small>
                                    </div>
                                    <input class="form-check-input ms-3" type="checkbox" id="cierreIrr" wire:model="cierre_irreversible">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch p-3 border rounded d-flex align-items-center justify-content-between h-100">
                                    <div>
                                        <label class="form-check-label fw-bold d-block" for="actObl">Activo Obligatorio</label>
                                        <small class="text-muted">Forzar el vínculo de un Computador/Dispositivo a cada incidencia.</small>
                                    </div>
                                    <input class="form-check-input ms-3" type="checkbox" id="actObl" wire:model="activo_obligatorio">
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal para Tipos de Incidencias -->
    <div wire:ignore.self class="modal fade" id="modalProblema" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title">{{ $problema_id ? 'Editar Tipo' : 'Nuevo Tipo' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetProblema"></button>
                </div>
                <form wire:submit.prevent="guardarProblema">
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label for="nombre_prob" class="form-label">Nombre del Tipo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre_problema') is-invalid @enderror" id="nombre_prob" wire:model="nombre_problema" placeholder="Ej: Falla de Software, Pantalla Azul, etc.">
                            @error('nombre_problema') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" id="prob_activo" wire:model="problema_activo">
                            <label class="form-check-label" for="prob_activo">Tipo Activo / Disponible</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetProblema">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
