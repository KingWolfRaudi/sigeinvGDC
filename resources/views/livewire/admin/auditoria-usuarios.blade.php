<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-9 d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border border-primary border-opacity-25 shadow-sm">
                <i class="bi bi-person-lines-fill fs-3"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-0 text-body">Auditoría de Usuarios</h2>
                <p class="text-muted mb-0">Inspecciona el perfil y el historial detallado de actividad por usuario.</p>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Buscar Usuario</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-body border-end-0"><i class="bi bi-search text-primary"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Buscar por nombre, usuario o email..." wire:model.live="search">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Estado</label>
                    <select class="form-select shadow-sm" wire:model.live="filtro_estado">
                        <option value="todos">Todos los Estados</option>
                        <option value="activos">Solo Activos</option>
                        <option value="inactivos">Solo Inactivos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-secondary">
                        <tr>
                            <th class="ps-4 py-3" style="cursor:pointer" wire:click="sortBy('id')">ID</th>
                            <th class="py-3" style="cursor:pointer" wire:click="sortBy('name')">Usuario</th>
                            <th class="py-3">Roles</th>
                            <th class="py-3" style="cursor:pointer" wire:click="sortBy('activo')">Estado</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $user)
                            <tr>
                                <td class="ps-4 fw-bold text-muted small">#{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-pill p-1 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($user->activo)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i class="bi bi-check-circle me-1"></i> Activo</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i class="bi bi-x-circle me-1"></i> Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="verDetalle({{ $user->id }})" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm">
                                        <i class="bi bi-eye"></i> Detalle y Auditoría
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    No se encontraron usuarios.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($usuarios->hasPages())
            <div class="card-footer bg-body py-3">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Detalle y Auditoría -->
    <div wire:ignore.self class="modal fade" id="modalAuditoriaUsuario" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                @if($selectedUser)
                    <div class="modal-header bg-dark text-white border-0 py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-person-badge me-2"></i> Perfil y Auditoría: {{ $selectedUser->name }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0" style="max-height: 75vh; overflow-y: auto;">
                        <div class="p-4 bg-body-secondary border-bottom">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="fw-bold mb-1">{{ $selectedUser->name }}</h4>
                                    <p class="text-muted mb-2"><i class="bi bi-envelope me-1"></i> {{ $selectedUser->email }} | <i class="bi bi-person me-1"></i> {{ $selectedUser->username }}</p>
                                    <div>
                                        @foreach($selectedUser->roles as $role)
                                            <span class="badge bg-primary">{{ $role->name }}</span>
                                        @endforeach
                                        @if($selectedUser->especialidad)
                                            <span class="badge bg-info text-dark">Esp: {{ $selectedUser->especialidad->nombre }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="{{ route('reportes.auditoria-usuario.pdf', ['id' => $selectedUser->id, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]) }}" target="_blank" class="btn btn-danger shadow-sm border-0 fw-bold">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Reporte PDF
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i> Historial de Actividad</h5>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Desde</label>
                                    <input type="date" class="form-control form-control-sm shadow-sm" wire:model.live="dateFrom">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Hasta</label>
                                    <input type="date" class="form-control form-control-sm shadow-sm" wire:model.live="dateTo">
                                </div>
                            </div>

                            <div class="table-responsive rounded-3 border">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="bg-body-secondary fs-7 text-uppercase">
                                        <tr>
                                            <th class="ps-3 py-2">Fecha / Hora</th>
                                            <th class="py-2">Acción</th>
                                            <th class="py-2">Módulo Afectado</th>
                                            <th class="py-2">Detalles Adicionales</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @forelse($actividades as $act)
                                            <tr>
                                                <td class="ps-3 py-2 text-muted">
                                                    <div class="fw-bold">{{ $act->created_at->format('d/m/Y') }}</div>
                                                    <div>{{ $act->created_at->format('h:i:s A') }}</div>
                                                </td>
                                                <td class="py-2">
                                                    @php
                                                        $color = match($act->description) {
                                                            'created' => 'success',
                                                            'updated' => 'info',
                                                            'deleted' => 'danger',
                                                            'Inicio de sesión exitoso' => 'primary',
                                                            'Intento fallido de inicio de sesión' => 'danger',
                                                            'Cierre de sesión' => 'secondary',
                                                            default => 'secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} border-opacity-25 px-2">
                                                        {{ ucfirst($act->description) }}
                                                    </span>
                                                </td>
                                                <td class="py-2 fw-bold text-body">
                                                    {{ $act->subject_type ? class_basename($act->subject_type) : 'Sistema' }}
                                                    @if($act->subject_id)
                                                        <span class="text-muted small fw-normal">(ID: {{ $act->subject_id }})</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 text-muted" style="max-width: 350px;">
                                                    @php
                                                        $props = $act->properties;
                                                        $hasChanges = false;
                                                    @endphp
                                                    
                                                    @if(isset($props['attributes']) && count($props['attributes']) > 0)
                                                        @php $hasChanges = true; @endphp
                                                        <ul class="mb-0 ps-3" style="font-size: 0.8rem;">
                                                        @foreach($props['attributes'] as $key => $newValue)
                                                            @php
                                                                if ($key === 'updated_at') continue;
                                                                $oldValue = $props['old'][$key] ?? null;
                                                                
                                                                $formatValue = function($k, $v) {
                                                                    if ($v === null || $v === '') return 'N/A';
                                                                    if (is_array($v)) return json_encode($v);
                                                                    if (is_bool($v)) return $v ? 'Sí' : 'No';
                                                                    $lk = strtolower($k);
                                                                    if (in_array($lk, ['activo', 'solventado', 'disponible'])) return $v == 1 ? 'Sí' : 'No';
                                                                    if (str_ends_with($lk, '_id') || str_ends_with($lk, '_by')) return 'ID ' . $v;
                                                                    return (string)$v;
                                                                };

                                                                $oldStr = $formatValue($key, $oldValue);
                                                                $newStr = $formatValue($key, $newValue);
                                                                $showOld = isset($props['old']) && array_key_exists($key, $props['old']);
                                                            @endphp
                                                            <li class="mb-1">
                                                                <strong class="text-body">{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> 
                                                                @if($showOld)
                                                                    <span class="text-danger text-decoration-line-through">{{ $oldStr }}</span> 
                                                                    <i class="bi bi-arrow-right mx-1 text-muted"></i> 
                                                                @endif
                                                                <span class="text-success fw-bold">{{ $newStr }}</span>
                                                            </li>
                                                        @endforeach
                                                        </ul>
                                                    @elseif(isset($props['old']) && count($props['old']) > 0 && $act->description === 'deleted')
                                                        @php $hasChanges = true; @endphp
                                                        <span class="text-danger"><i class="bi bi-trash me-1"></i> Registro eliminado (Soft Delete)</span>
                                                    @elseif(isset($props['ip']))
                                                        @php $hasChanges = true; @endphp
                                                        IP: {{ $props['ip'] }}
                                                    @endif

                                                    @if(!$hasChanges)
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">
                                                    No hay actividad en el rango seleccionado.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if($actividades->count() === 100)
                                <div class="text-end text-muted small mt-2">Mostrando los últimos 100 registros. Restrinja la fecha para ver anteriores.</div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 pe-4">
                        <button type="button" class="btn btn-secondary px-4 shadow-sm" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
