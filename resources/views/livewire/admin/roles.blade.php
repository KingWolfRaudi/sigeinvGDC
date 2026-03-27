<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Gestión de Roles</h3>
        <button wire:click="crear" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Rol
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Rol (Sistema)</th>
                            <th>Descripción</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $rol)
                            <tr>
                                <td>{{ $rol->id }}</td>
                                <td>
                                    <span class="badge bg-dark fs-6">{{ $rol->name }}</span>
                                </td>
                                <td>{{ $rol->descripcion ?? 'Sin descripción' }}</td>
                                <td class="text-end">
                                    <button wire:click="editar({{ $rol->id }})" class="btn btn-sm btn-primary" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    
                                    @if($rol->name !== 'super-admin')
                                        <button wire:click="eliminar({{ $rol->id }})" wire:confirm="¿Estás seguro de que deseas eliminar este rol?" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled title="Protegido del sistema">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No hay roles registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $roles->links() }}
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="modalRol" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $tituloModal }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetCampos"></button>
                </div>
                <form wire:submit.prevent="guardar">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Identificador del Rol <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name" placeholder="Ej: auditor, recursos-humanos" {{ $name === 'super-admin' ? 'readonly' : '' }}>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" wire:model="descripcion" rows="2" placeholder="Describe brevemente qué hace este rol..."></textarea>
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="border-top pt-3">
                            <h6 class="mb-3"><i class="bi bi-key me-2"></i>Asignación de Permisos por Módulo</h6>
                            
                            @if($name === 'super-admin')
                                <div class="alert alert-info py-2 text-sm">
                                    <i class="bi bi-info-circle me-1"></i> El <strong>Super Admin</strong> tiene acceso total al sistema por defecto. No es necesario asignarle permisos individuales.
                                </div>
                            @else
                                @forelse($permisosAgrupados as $grupo => $permisos)
                                    <div class="mb-4">
                                        <h6 class="text-primary border-bottom pb-1 mb-3">
                                            <i class="bi bi-box me-1"></i> Módulo: {{ $grupo }}
                                        </h6>
                                        
                                        <div class="row g-2">
                                            @foreach($permisos as $permiso)
                                                @php
                                                    $accion = str_replace('-' . strtolower($grupo), '', $permiso->name);
                                                    $accionLimpia = ucfirst(str_replace('-', ' ', $accion));
                                                @endphp
                                                
                                                <div class="col-md-4">
                                                    <div class="form-check form-switch shadow-sm border rounded p-2 bg-light h-100 d-flex align-items-center">
                                                        <input class="form-check-input ms-1 flex-shrink-0" type="checkbox" 
                                                               value="{{ $permiso->name }}" 
                                                               id="permiso_{{ $permiso->id }}" 
                                                               wire:model="permisos_seleccionados">
                                                        <label class="form-check-label ms-2 text-wrap" style="font-size: 0.85rem; cursor:pointer;" for="permiso_{{ $permiso->id }}">
                                                            {{ $accionLimpia }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted text-center py-2">
                                        No hay permisos registrados en el sistema aún.
                                    </div>
                                @endforelse
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="resetCampos">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="guardar">Guardar</span>
                            <span wire:loading wire:target="guardar">Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>