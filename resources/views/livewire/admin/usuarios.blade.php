<div>
    <!-- Header Especial -->
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                <i class="bi bi-people-fill fs-3"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-0 text-dark">Gestión de Usuarios</h2>
                <p class="text-muted mb-0">Administración de cuentas, perfiles de acceso y directorio de personal del sistema.</p>
            </div>
        </div>
    </div>

    <!-- Card de Búsqueda y Acciones -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 justify-content-between align-items-center">
                <div class="col-md-5">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar por nombre, usuario o correo..." wire:model.live="search">
                    </div>
                </div>
                
                @can('ver-estado-usuarios')
                <div class="col-md-3">
                    <select class="form-select shadow-sm" wire:model.live="filtro_estado">
                        <option value="todos">Todos los Estados</option>
                        <option value="activos">Solo Activos</option>
                        <option value="inactivos">Solo Inactivos</option>
                    </select>
                </div>
                @endcan

                <div class="col-md-4 text-end d-flex gap-2 justify-content-end">
                    <div class="dropdown">
                        <button class="btn btn-outline-success border-2 fw-bold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </button>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.usuarios.excel', ['search' => $search, 'estado' => $filtro_estado]) }}"><i class="bi bi-filter me-2 text-success"></i> Vista Actual</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('reportes.usuarios.excel') }}"><i class="bi bi-list-check me-2 text-primary"></i> Todo el Directorio</a></li>
                        </ul>
                    </div>
                    @can('crear-usuarios')
                        <button wire:click="crear" class="btn btn-primary shadow-sm fw-bold px-4">
                            <i class="bi bi-person-plus-fill me-1"></i> Nuevo
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
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>

                            <th wire:click="sortBy('name')" style="cursor: pointer;">
                                Nombre @if($sortField === 'name') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th wire:click="sortBy('username')" style="cursor: pointer;">
                                Usuario / Correo @if($sortField === 'username') <i class="bi bi-sort-alpha-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            <th>Roles</th>
                            @can('ver-estado-usuarios')
                            <th class="th-estado" wire:click="sortBy('activo')" style="cursor: pointer;">
                                Estado @if($sortField === 'activo') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }} ms-1"></i> @endif
                            </th>
                            @endcan
                            <th class="th-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $user)
                            <tr>

                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if(Auth::id() == $user->id)
                                        <span class="badge bg-primary ms-1">Tú</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-muted small">{{ $user->username }}</div>
                                    <div class="text-muted small">{{ $user->email }}</div>
                                </td>
                                <td>
                                    @forelse($user->roles as $rol)
                                        <span class="badge bg-dark">{{ $rol->name }}</span>
                                    @empty
                                        <span class="text-muted small">Sin rol</span>
                                    @endforelse
                                </td>
                                @can('ver-estado-usuarios')
                                    <td>
                                        @if($user->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                @endcan
                                <td class="text-end">
                                    @can('ver-usuarios')
                                        <button wire:click="ver({{ $user->id }})" class="btn btn-sm btn-info text-white" title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endcan

                                    @can('cambiar-estatus-usuarios')
                                        <button wire:click="toggleActivo({{ $user->id }})" class="btn btn-sm {{ $user->activo ? 'btn-success' : 'btn-secondary' }} text-white" title="Alternar Estado" {{ Auth::id() == $user->id ? 'disabled' : '' }}>
                                            <i class="bi {{ $user->activo ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('editar-usuarios')
                                        <button wire:click="editar({{ $user->id }})" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('eliminar-usuarios')
                                        <button wire:click="eliminar({{ $user->id }})" wire:confirm="¿Seguro que deseas eliminar este usuario?" class="btn btn-sm btn-danger" title="Eliminar" {{ Auth::id() == $user->id ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No hay usuarios registrados que coincidan con la búsqueda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg"> 
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                        <div class="row">
                            <div class="col-md-6 border-end pr-3">
                                <h6 class="mb-3"><i class="bi bi-person-lines-fill me-2"></i>Datos Personales</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" wire:model="username" placeholder="ej: jdoe">
                                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Contraseña {!! !$user_id ? '<span class="text-danger">*</span>' : '' !!}</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password" placeholder="{{ $user_id ? 'Dejar en blanco para no cambiar' : 'Mínimo 8 caracteres' }}">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                @can('cambiar-estatus-usuarios')
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" id="activo" wire:model="activo" {{ Auth::id() == $user_id ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="activo">Usuario Activo en el sistema</label>
                                    </div>
                                @endcan
                            </div>

                            <div class="col-md-6 pl-3">
                                <h6 class="mb-3"><i class="bi bi-shield-lock me-2"></i>Asignación de Roles</h6>
                                
                                <div class="bg-light p-3 rounded border">
                                    @forelse($roles as $rol)
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" 
                                                   value="{{ $rol->name }}" 
                                                   id="rol_{{ $rol->id }}" 
                                                   wire:model="roles_seleccionados">
                                            <label class="form-check-label text-capitalize" for="rol_{{ $rol->id }}">
                                                {{ str_replace('-', ' ', $rol->name) }}
                                            </label>
                                        </div>
                                    @empty
                                        <div class="text-muted small">No hay roles disponibles.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="guardar">Guardar Usuario</span>
                            <span wire:loading wire:target="guardar">Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalDetalleUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-info">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-person-vcard me-2"></i>Detalles del Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    @if($usuario_detalle)
                        <ul class="list-group list-group-flush">
                            @can('ver-estado-usuarios')
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Estado:</strong>
                                    @if($usuario_detalle->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </li>
                            @endcan
                            
                            <li class="list-group-item">
                                <strong>Nombre:</strong> {{ $usuario_detalle->name }}
                            </li>
                            <li class="list-group-item">
                                <strong>Username:</strong> {{ $usuario_detalle->username }}
                            </li>
                            <li class="list-group-item">
                                <strong>Correo Electrónico:</strong> {{ $usuario_detalle->email }}
                            </li>
                            
                            <li class="list-group-item bg-light">
                                <strong><i class="bi bi-shield-lock me-1"></i> Roles Asignados:</strong><br>
                                @forelse($usuario_detalle->roles as $rol)
                                    <span class="badge bg-dark mt-1">{{ str_replace('-', ' ', $rol->name) }}</span>
                                @empty
                                    <span class="text-muted fst-italic small">No tiene roles asignados</span>
                                @endforelse
                            </li>
                        </ul>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>