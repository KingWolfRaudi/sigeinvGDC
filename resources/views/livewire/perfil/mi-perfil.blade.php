<div class="container py-4">
    <div class="row g-4">
        <!-- Columna Izquierda: Tarjeta de Perfil -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 text-center overflow-hidden h-100">
                <div class="card-header bg-primary py-5 border-0 position-relative"></div>
                <div class="card-body pt-0" style="margin-top: -60px;">
                    <div class="position-relative d-inline-block mb-3">
                        @if($nueva_foto)
                            <img src="{{ $nueva_foto->temporaryUrl() }}" class="rounded-circle border border-4 border-white shadow img-thumbnail" style="width: 130px; height: 130px; object-fit: cover;">
                        @elseif(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="rounded-circle border border-4 border-white shadow img-thumbnail" style="width: 130px; height: 130px; object-fit: cover;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D6EFD&color=fff&size=128" class="rounded-circle border border-4 border-white shadow img-thumbnail" style="width: 130px; height: 130px;">
                        @endif
                        
                        @unless(Auth::user()->hasRole('super-admin'))
                        <label for="fotoInput" class="position-absolute bottom-0 end-0 bg-body rounded-circle shadow p-2" style="cursor: pointer;" title="Cambiar Avatar">
                            <i class="bi bi-camera-fill text-primary"></i>
                            <input type="file" id="fotoInput" class="d-none" wire:model="nueva_foto">
                        </label>
                        @endunless
                    </div>

                    <h4 class="mb-1 fw-bold text-body">{{ Auth::user()->name }}</h4>
                    <p class="text-muted small mb-3">@ {{ Auth::user()->username }}</p>
                    <span class="badge bg-body-secondary text-primary border border-primary px-3 rounded-pill">
                        <i class="bi bi-shield-check me-1"></i> {{ Auth::user()->roles->pluck('name')->first() ?? 'Sin Rol' }}
                    </span>
                    <hr class="my-4 text-muted opacity-25">
                    
                    <div class="d-flex flex-column gap-2 text-start small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Email:</span>
                            <span class="fw-bold">{{ Auth::user()->email }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Estado:</span>
                            <span class="badge {{ Auth::user()->activo ? 'bg-success' : 'bg-danger' }}">
                                {{ Auth::user()->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        
                        @if($es_tecnico)
                        <hr class="my-3 text-muted opacity-25">
                        <div class="d-flex justify-content-between align-items-center bg-body-secondary p-3 rounded-4 border border-light shadow-sm">
                            <label class="fw-bold text-body mb-0 cursor-pointer fs-6" for="toggleAsignacion">
                                <i class="bi bi-person-workspace text-primary me-2"></i> Recibir Casos
                            </label>
                            <div class="form-check form-switch m-0 pt-1">
                                <input class="form-check-input shadow-none cursor-pointer" style="width: 2.8em; height: 1.4em;" type="checkbox" id="toggleAsignacion" 
                                       wire:model.live="disponible_asignacion" wire:change="toggleDisponibilidad">
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            @if($disponible_asignacion)
                                <span class="badge bg-success text-success bg-opacity-10 rounded-pill px-4 py-2 fs-6 border border-success border-opacity-25 w-100">
                                    <i class="bi bi-check-circle-fill me-2"></i> Disponible para Asignación
                                </span>
                            @else
                                <span class="badge bg-secondary text-secondary bg-opacity-10 rounded-pill px-4 py-2 fs-6 border border-secondary border-opacity-25 w-100">
                                    <i class="bi bi-dash-circle-fill me-2"></i> No Disponible
                                </span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Secciones / Solicitudes -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-body py-3 border-0">
                    <ul class="nav nav-pills card-header-pills" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-solicitudes" type="button">
                                <i class="bi bi-pencil-square me-1"></i> Solicitudes de Cambio
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-historial" type="button">
                                <i class="bi bi-clock-history me-1"></i> Mis Trámites
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body bg-body-secondary p-4">
                    @if(Auth::user()->hasRole('super-admin'))
                        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Usuario Inmutable</h6>
                                <p class="small mb-0">El perfil del <strong>Super Administrador</strong> es una cuenta de sistema protegida y no puede ser modificada.</p>
                            </div>
                        </div>
                    @endif

                    <div class="tab-content {{ Auth::user()->hasRole('super-admin') ? 'opacity-50' : '' }}">
                        
                        <!-- Pestaña: Solicitudes -->
                        <div class="tab-pane fade show active" id="tab-solicitudes">
                            <div class="row g-4">
                                @php $isImmutable = Auth::user()->hasRole('super-admin'); @endphp

                                @if($config['perfil_solicitar_nombre'] ?? true)
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm p-3 h-100">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Nombre Completo</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" wire:model="nuevo_nombre" {{ $isImmutable ? 'disabled' : '' }}>
                                            <button class="btn btn-outline-primary" wire:click="enviarSolicitud('nombre')" {{ $isImmutable ? 'disabled' : '' }}>Solicitar</button>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($config['perfil_solicitar_username'] ?? true)
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm p-3 h-100">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Nombre de Usuario (@)</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" wire:model="nuevo_username" {{ $isImmutable ? 'disabled' : '' }}>
                                            <button class="btn btn-outline-primary" wire:click="enviarSolicitud('username')" {{ $isImmutable ? 'disabled' : '' }}>Solicitar</button>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($config['perfil_solicitar_email'] ?? true)
                                <div class="col-md-12">
                                    <div class="card border-0 shadow-sm p-3">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Correo Electrónico</label>
                                        <div class="input-group">
                                            <input type="email" class="form-control" wire:model="nuevo_email" {{ $isImmutable ? 'disabled' : '' }}>
                                            <button class="btn btn-outline-primary" wire:click="enviarSolicitud('email')" {{ $isImmutable ? 'disabled' : '' }}>Solicitar</button>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($config['perfil_solicitar_password'] ?? true)
                                <div class="col-12 mt-4">
                                    <hr class="text-muted opacity-25">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-key me-2"></i>Seguridad (Cambio de Contraseña)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <input type="password" class="form-control shadow-sm" placeholder="Nueva Contraseña" wire:model="nuevo_password" {{ $isImmutable ? 'disabled' : '' }}>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="password" class="form-control shadow-sm" placeholder="Repite Contraseña" wire:model="confirmar_password" {{ $isImmutable ? 'disabled' : '' }}>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-dark w-100 shadow-sm" wire:click="enviarSolicitud('password')" {{ $isImmutable ? 'disabled' : '' }}>Cambiar</button>
                                        </div>
                                    </div>
                                    @error('nuevo_password') <span class="text-danger small">{{ $message }}</span> @enderror
                                    @error('confirmar_password') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Pestaña: Historial -->
                        <div class="tab-pane fade" id="tab-historial">
                            <h6 class="fw-bold mb-3">Solicitudes Recientes</h6>
                            <div class="list-group list-group-flush border shadow-sm rounded overflow-hidden">
                                @forelse($misSolicitudes as $sol)
                                    <div class="list-group-item list-group-item-action border-0 py-3 border-bottom">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <h6 class="mb-1 fw-bold text-capitalize">{{ $sol->tipo }}</h6>
                                            <div>
                                                <small class="text-muted me-2">{{ $sol->created_at->diffForHumans() }}</small>
                                                @if($sol->estado === 'pendiente')
                                                    <button class="btn btn-outline-danger btn-sm border-0" title="Cancelar Solicitud" wire:click="cancelarSolicitud({{ $sol->id }})" wire:confirm="¿Deseas cancelar esta solicitud?">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <div>
                                                @if($sol->estado === 'pendiente')
                                                    <span class="badge bg-warning text-body"><i class="bi bi-hourglass-split me-1"></i> Pendiente</span>
                                                @elseif($sol->estado === 'aprobado')
                                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Aprobado</span>
                                                @elseif($sol->estado === 'rechazado')
                                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Rechazado</span>
                                                @elseif($sol->estado === 'cancelado')
                                                    <span class="badge bg-secondary opacity-75"><i class="bi bi-slash-circle me-1"></i> Cancelado</span>
                                                @endif
                                            </div>
                                            @if($sol->motivo_rechazo)
                                                <small class="text-danger italic ps-3">Motivo: "{{ $sol->motivo_rechazo }}"</small>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-muted">
                                        No has realizado solicitudes de cambio aún.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
