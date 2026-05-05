<div>
    <!-- Encabezado de la Página -->
    <div class="mb-4">
        <h2 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-clock text-primary me-2"></i> Auditoría de Técnicos
        </h2>
        <p class="text-muted mt-2">
            Supervisión del rendimiento operativo y la actividad de acceso del personal técnico.
        </p>
    </div>

    <!-- Panel de Filtros -->
    <div class="card shadow-sm border-0 mb-4 rounded-3">
        <div class="card-body bg-light rounded-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Técnico (Resolutor)</label>
                    <select wire:model.live="filtro_tecnico" class="form-select border-0 shadow-sm">
                        <option value="">Todos los técnicos</option>
                        @foreach($tecnicos as $tec)
                            <option value="{{ $tec->id }}">{{ $tec->name }} ({{ $tec->username }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold">Desde</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control border-0 shadow-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold">Hasta</label>
                    <input type="date" wire:model.live="dateTo" class="form-control border-0 shadow-sm">
                </div>
                <div class="col-md-2">
                    <button wire:click="calcularKpis" class="btn btn-primary w-100 shadow-sm">
                        <i class="fas fa-sync-alt me-1"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs (Gestión y Tiempos) -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
        <div class="col">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tickets Asignados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kpis['asignados'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300" style="opacity: 0.4;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tickets Resueltos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kpis['resueltos'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300" style="opacity: 0.4;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm border-start border-warning border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tickets Abiertos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kpis['abiertos'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300" style="opacity: 0.4;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm border-start border-info border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tasa de Resolución</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $kpis['tasa_resolucion'] }}%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2" style="height: 5px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $kpis['tasa_resolucion'] }}%" aria-valuenow="{{ $kpis['tasa_resolucion'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300" style="opacity: 0.4;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm border-start border-secondary border-4 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Tiempo Promedio</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $kpis['tiempo_promedio'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300" style="opacity: 0.4;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Actividad Detallada -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Historial de Actividad de Tickets</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Fecha / Hora</th>
                            <th>Responsable / IP</th>
                            <th>Acción</th>
                            <th>Módulo Afectado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actividades as $log)
                            <tr>
                                <td class="ps-4 text-nowrap text-muted small">
                                    {{ $log->created_at->format('d/m/Y') }} <br>
                                    {{ $log->created_at->format('h:i:s A') }}
                                </td>
                                <td>
                                    @if($log->causer)
                                        <div class="fw-bold text-dark">{{ $log->causer->name }}</div>
                                        <div class="text-muted small">{{ $log->causer->email }}</div>
                                    @else
                                        <div class="fw-bold text-danger">No Autenticado</div>
                                        @if(isset($log->properties['identificador_intentado']))
                                            <div class="text-muted small">Intento: {{ $log->properties['identificador_intentado'] }}</div>
                                        @endif
                                    @endif
                                    @if(isset($log->properties['ip']))
                                        <span class="badge bg-light text-dark border mt-1">IP: {{ $log->properties['ip'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        $iconClass = 'fas fa-info-circle';
                                        if (str_contains($log->description, 'Inicio de sesión')) {
                                            $badgeClass = 'bg-primary';
                                            $iconClass = 'fas fa-sign-in-alt';
                                        } elseif (str_contains($log->description, 'Cierre de sesión')) {
                                            $badgeClass = 'bg-dark';
                                            $iconClass = 'fas fa-sign-out-alt';
                                        } elseif (str_contains($log->description, 'Intento fallido')) {
                                            $badgeClass = 'bg-danger';
                                            $iconClass = 'fas fa-exclamation-triangle';
                                        } elseif ($log->description === 'created') {
                                            $badgeClass = 'bg-success';
                                            $iconClass = 'fas fa-plus-circle';
                                        } elseif ($log->description === 'updated') {
                                            $badgeClass = 'bg-info text-dark';
                                            $iconClass = 'fas fa-edit';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} px-2 py-1">
                                        <i class="{{ $iconClass }} me-1"></i> {{ ucfirst($log->description) }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->subject_type)
                                        <span class="fw-bold text-dark">{{ class_basename($log->subject_type) }}</span>
                                        <span class="text-muted small">(ID: {{ $log->subject_id }})</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-list fa-3x mb-3 text-light"></i><br>
                                    No se encontraron registros para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($actividades->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $actividades->links() }}
            </div>
        @endif
    </div>
</div>
