<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="bi bi-people-fill me-2"></i>Gestión de Usuarios</h3>
        @can('crear-usuarios')
            <button wire:click="crear" class="btn btn-primary">
                <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
            </button>
        @endcan
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Usuario / Correo</th>
                            <th>Roles</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
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
                                    @foreach($user->roles as $rol)
                                        <span class="badge bg-dark">{{ $rol->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($user->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
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
                                <td colspan="6" class="text-center text-muted py-4">No hay usuarios registrados.</td>
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
        <div class="modal-dialog modal-lg"> <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body">
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

                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" id="activo" wire:model="activo" {{ Auth::id() == $user_id ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="activo">Usuario Activo en el sistema</label>
                                </div>
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
                                        <div class="text-muted small">No hay roles creados.</div>
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
</div>