<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
            <i class="bi bi-gear-fill fs-3 text-primary"></i>
        </div>
        <div>
            <h2 class="fw-bold mb-0">Configuración General</h2>
            <p class="text-muted mb-0">Gestiona los parámetros globales del sistema y módulos.</p>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Navigation (Tabs) -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="list-group list-group-flush border-0">
                    @can('admin-incidencias')
                    <button wire:click="setTab('incidencias-ajustes')" 
                        class="list-group-item list-group-item-action border-0 py-3 d-flex align-items-center {{ $activeTab === 'incidencias-ajustes' ? 'active fw-bold' : '' }}">
                        <i class="bi bi-tools me-3 fs-5"></i> Ajustes de Incidencias
                    </button>
                    <button wire:click="setTab('incidencias-catalogo')" 
                        class="list-group-item list-group-item-action border-0 py-3 d-flex align-items-center {{ $activeTab === 'incidencias-catalogo' ? 'active fw-bold' : '' }}">
                        <i class="bi bi-list-check me-3 fs-5"></i> Catálogo de Problemas
                    </button>
                    @endcan
                    <button wire:click="setTab('perfil-ajustes')" 
                        class="list-group-item list-group-item-action border-0 py-3 d-flex align-items-center {{ $activeTab === 'perfil-ajustes' ? 'active fw-bold' : '' }}">
                        <i class="bi bi-person-gear me-3 fs-5"></i> Perfil de Usuario
                    </button>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    
                    <!-- TAB: AJUSTES DE INCIDENCIAS -->
                    @can('admin-incidencias')
                    @if($activeTab === 'incidencias-ajustes')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Reglas de Incidencias</h5>
                            <button wire:click="guardarConfigIncidencias" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-save me-2"></i> Guardar Cambios
                            </button>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Roles con permisos de Soporte Técnico</label>
                                <div class="p-3 bg-light rounded-3 border border-dashed">
                                    <div class="row g-2">
                                        @foreach($rolesDisponibles as $rol)
                                            <div class="col-md-4">
                                                <div class="form-check form-switch card p-2 border-0 shadow-sm">
                                                    <input class="form-check-input ms-0 me-2" type="checkbox" 
                                                           wire:model="roles_tecnicos" value="{{ $rol->name }}" id="rol_{{ $rol->id }}">
                                                    <label class="form-check-label fw-medium" for="rol_{{ $rol->id }}">
                                                        {{ ucfirst($rol->name) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-text mt-2">Los usuarios con estos roles podrán atender y cerrar incidencias.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border border-light p-3 h-100">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" wire:model="cierre_irreversible" id="cierre_irreversible">
                                        <label class="form-check-label fw-bold" for="cierre_irreversible">Cierre Irreversible</label>
                                    </div>
                                    <p class="small text-muted mb-0">Si está activo, las incidencias cerradas no podrán volver a abrirse.</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border border-light p-3 h-100">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" wire:model="activo_obligatorio" id="activo_obligatorio">
                                        <label class="form-check-label fw-bold" for="activo_obligatorio">Activo Fijo Obligatorio</label>
                                    </div>
                                    <p class="small text-muted mb-0">Obliga a seleccionar un equipo o dispositivo al reportar una incidencia.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    @endcan

                    <!-- TAB: CATÁLOGO DE PROBLEMAS -->
                    @can('admin-incidencias')
                    @if($activeTab === 'incidencias-catalogo')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Tipos de Incidencias</h5>
                            <button class="btn btn-dark px-4 shadow-sm" wire:click="resetProblema" data-bs-toggle="modal" data-bs-target="#modalProblema">
                                <i class="bi bi-plus-lg me-2"></i> Nuevo Tipo
                            </button>
                        </div>

                        <div class="mb-3">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control border-start-0" placeholder="Buscar tipo de incidencia..." wire:model.live="searchProblema">
                            </div>
                        </div>

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3" style="cursor:pointer;" wire:click="sortBy('nombre')">
                                            Nombre @if($sortField === 'nombre') <i class="bi bi-sort-{{ $sortAsc ? 'alpha-down' : 'alpha-up' }}"></i> @endif
                                        </th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-end pe-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($problemas as $prob)
                                        <tr>
                                            <td class="ps-3 fw-medium">{{ $prob->nombre }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $prob->activo ? 'success' : 'danger' }} bg-opacity-10 text-{{ $prob->activo ? 'success' : 'danger' }} rounded-pill px-3">
                                                    {{ $prob->activo ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <button class="btn btn-sm btn-outline-primary border-0" wire:click="editarProblema({{ $prob->id }})">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger border-0" 
                                                        wire:click="eliminarProblema({{ $prob->id }})" 
                                                        wire:confirm="¿Estás seguro de eliminar este tipo de incidencia?">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $problemas->links() }}
                        </div>
                    @endif
                    @endcan

                    <!-- TAB: PERFIL DE USUARIO -->
                    @if($activeTab === 'perfil-ajustes')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Configuración de Perfil</h5>
                            <button wire:click="guardarConfigPerfil" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-save me-2"></i> Guardar Cambios
                            </button>
                        </div>

                        <div class="alert alert-info border-0 bg-info bg-opacity-10 mb-4 p-3 rounded-4 d-flex">
                            <i class="bi bi-info-circle-fill fs-4 text-info me-3"></i>
                            <div class="small">
                                Define qué información pueden solicitar cambiar los usuarios desde su panel de perfil. 
                                Todos los cambios requieren aprobación administrativa (excepto para super-admins).
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card p-3 border border-light shadow-sm">
                                    <div class="form-check form-switch d-flex justify-content-between align-items-center p-0">
                                        <div>
                                            <label class="form-check-label fw-bold d-block" for="p_nombre">Cambio de Nombre</label>
                                            <span class="text-muted small">Permite solicitar cambio de nombre completo.</span>
                                        </div>
                                        <input class="form-check-input ms-0" type="checkbox" wire:model="perfil_solicitar_nombre" id="p_nombre">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card p-3 border border-light shadow-sm">
                                    <div class="form-check form-switch d-flex justify-content-between align-items-center p-0">
                                        <div>
                                            <label class="form-check-label fw-bold d-block" for="p_username">Cambio de Usuario (@)</label>
                                            <span class="text-muted small">Permite solicitar cambio de username.</span>
                                        </div>
                                        <input class="form-check-input ms-0" type="checkbox" wire:model="perfil_solicitar_username" id="p_username">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card p-3 border border-light shadow-sm">
                                    <div class="form-check form-switch d-flex justify-content-between align-items-center p-0">
                                        <div>
                                            <label class="form-check-label fw-bold d-block" for="p_email">Cambio de Email</label>
                                            <span class="text-muted small">Permite solicitar cambio de correo electrónico.</span>
                                        </div>
                                        <input class="form-check-input ms-0" type="checkbox" wire:model="perfil_solicitar_email" id="p_email">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card p-3 border border-light shadow-sm">
                                    <div class="form-check form-switch d-flex justify-content-between align-items-center p-0">
                                        <div>
                                            <label class="form-check-label fw-bold d-block" for="p_password">Cambio de Contraseña</label>
                                            <span class="text-muted small">Habilita la sección de cambio de clave.</span>
                                        </div>
                                        <input class="form-check-input ms-0" type="checkbox" wire:model="perfil_solicitar_password" id="p_password">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 p-4 bg-light rounded-4 border border-dashed text-center">
                            <i class="bi bi-clock-history fs-3 text-muted d-block mb-2"></i>
                            <h6 class="fw-bold mb-1">Regla de los 180 días</h6>
                            <p class="text-muted small mb-0 px-md-5">
                                Por seguridad, el sistema restringe automáticamente solicitudes del mismo tipo durante un periodo de 180 días tras el último cambio aprobado.
                            </p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PROBLEMA -->
    <div wire:ignore.self class="modal fade" id="modalProblema" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">{{ $problema_id ? 'Editar' : 'Nuevo' }} Tipo de Incidencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Problema</label>
                        <input type="text" class="form-control" wire:model="nombre_problema" placeholder="Ej: Falla de Software">
                        @error('nombre_problema') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" wire:model="problema_activo" id="p_activo">
                        <label class="form-check-label" for="p_activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary px-4" wire:click="guardarProblema">
                        {{ $problema_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
