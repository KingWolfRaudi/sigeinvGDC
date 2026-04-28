<div>
    <!-- Header Especial Premium -->
    <div class="d-flex align-items-center mb-4 p-3 rounded-4 shadow-sm bg-body border-0">
        <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary">
            <i class="bi bi-speedometer2 fs-3"></i>
        </div>
        <div>
            <h2 class="fw-bold mb-0 text-body">Panel de Control General</h2>
            <p class="text-muted mb-0 small">Métricas operativas y de infraestructura en tiempo real</p>
        </div>
    </div>

    <!-- Fase 2: KPIs Operativos -->
    <div class="row g-4 mb-4">
        <!-- Incidencias Pendientes -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-2">
                            <i class="bi bi-exclamation-octagon fs-4"></i>
                        </div>
                        <span class="badge bg-danger rounded-pill">Urgente</span>
                    </div>
                    <h3 class="fw-bold display-6 mb-1 text-body">{{ $incidenciasPendientes }}</h3>
                    <p class="text-muted fw-semibold mb-0">
                        {{ $esTecnico && !$dashboard_tecnico_ver_global ? 'Pendientes (Mi Especialidad)' : 'Tickets por Asignar' }}
                    </p>
                </div>
                <div class="bg-danger position-absolute bottom-0 start-0 w-100" style="height: 4px;"></div>
            </div>
        </div>

        <!-- Incidencias En Curso -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2">
                            <i class="bi bi-tools fs-4"></i>
                        </div>
                        <span class="badge bg-warning text-body rounded-pill">En Proceso</span>
                    </div>
                    <h3 class="fw-bold display-6 mb-1 text-body">{{ $incidenciasEnCurso }}</h3>
                    <p class="text-muted fw-semibold mb-0">
                        {{ $esTecnico ? 'Mis Casos Asignados' : 'Casos En Curso' }}
                    </p>
                </div>
                <div class="bg-warning position-absolute bottom-0 start-0 w-100" style="height: 4px;"></div>
            </div>
        </div>

        @if(!$esTrabajador)
        <!-- Movimientos Pendientes -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-info bg-opacity-10 text-info rounded-3 p-2">
                            <i class="bi bi-arrow-left-right fs-4"></i>
                        </div>
                        <span class="badge bg-info text-body rounded-pill">Aprobación</span>
                    </div>
                    <h3 class="fw-bold display-6 mb-1 text-body">{{ $movimientosPendientes }}</h3>
                    <p class="text-muted fw-semibold mb-0">Movimientos Solicitados</p>
                </div>
                <div class="bg-info position-absolute bottom-0 start-0 w-100" style="height: 4px;"></div>
            </div>
        </div>

        <!-- Alertas de Stock -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-dark bg-opacity-10 text-body rounded-3 p-2">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                        <span class="badge bg-dark rounded-pill">Inventario</span>
                    </div>
                    <h3 class="fw-bold display-6 mb-1 text-body">{{ $insumosCriticos }}</h3>
                    <p class="text-muted fw-semibold mb-0">Insumos en Estado Crítico</p>
                </div>
                <div class="bg-dark position-absolute bottom-0 start-0 w-100" style="height: 4px;"></div>
            </div>
        </div>
        @endif
    </div>

    @if(!$esTrabajador)
    <!-- Fase 3: Analítica de Hardware -->
    <div class="row g-4 mb-4">
        ...
    </div>
    @endif

    <!-- Fase 4: Listado de Acción Rápida y Resueltos -->
    <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
        <h4 class="fw-bold text-body mb-0">
            <i class="bi bi-briefcase-fill text-primary me-2"></i>Mesa de Ayuda (HelpDesk)
        </h4>
        @if(!$esTrabajador)
        <a href="{{ route('incidencias.gestion') }}" class="btn btn-outline-primary fw-bold rounded-pill px-4 shadow-sm">
            Ir al Panel de Gestión <i class="bi bi-arrow-right ms-1"></i>
        </a>
        @else
        <a href="{{ route('incidencias.crear') }}" class="btn btn-primary fw-bold rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Reporte
        </a>
        @endif
    </div>

    <div class="row g-4 mb-4">
        <!-- Columna Izquierda: Casos Pendientes -->
        <div class="col-12 col-xxl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-body border-bottom pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-body mb-0">
                        <i class="bi bi-lightning-charge text-warning me-2"></i>
                        @if($esResolutor)
                            Mis Casos Asignados (Pendientes)
                        @elseif($esTrabajador)
                            Mis Reportes Pendientes
                        @else
                            Atención Rápida (Últimos Tickets)
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0">Folio</th>
                                    <th class="border-0">Fecha</th>
                                    <th class="border-0">Solicitante</th>
                                    <th class="border-0">Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incidenciasRecientes as $inc)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-primary" style="cursor: pointer;" wire:click="ver({{ $inc->id }})">#{{ str_pad($inc->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td><small class="text-muted fw-bold">{{ $inc->created_at->diffForHumans() }}</small></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person"></i>
                                                </div>
                                                <span class="fw-bold text-body">{{ $inc->trabajador ? $inc->trabajador->nombres : ($inc->creator->name ?? 'Externo') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($inc->user_id)
                                                <span class="badge bg-warning text-body rounded-pill px-3"><i class="bi bi-tools me-1"></i> Asignado</span>
                                            @else
                                                <span class="badge bg-danger rounded-pill px-3 blink-soft"><i class="bi bi-exclamation-circle me-1"></i> Pendiente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-check-circle display-4 text-success mb-3 d-block opacity-50"></i>
                                            <span class="fw-bold fs-5">¡Excelente trabajo!</span><br>
                                            No hay incidencias pendientes para mostrar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Casos Resueltos -->
        <div class="col-12 col-xxl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-body border-bottom pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-body mb-0">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        @if($esResolutor)
                            Mis Casos Resueltos (Recientes)
                        @elseif($esTrabajador)
                            Mis Reportes Solventados
                        @else
                            Historial de Resoluciones
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0">Folio</th>
                                    <th class="border-0">Actualizado</th>
                                    <th class="border-0">Solicitante</th>
                                    <th class="border-0">Estatus Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incidenciasResueltas as $inc)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-muted" style="cursor: pointer;" wire:click="ver({{ $inc->id }})">#{{ str_pad($inc->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td><small class="text-muted fw-bold">{{ $inc->updated_at->diffForHumans() }}</small></td>
                                        <td>
                                            <div class="d-flex align-items-center opacity-75">
                                                <span class="fw-bold text-body">{{ $inc->trabajador ? $inc->trabajador->nombres : ($inc->creator->name ?? 'Externo') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($inc->cerrado)
                                                <span class="badge bg-secondary rounded-pill px-3"><i class="bi bi-lock me-1"></i> Cerrado</span>
                                            @else
                                                <span class="badge bg-success rounded-pill px-3"><i class="bi bi-check2-all me-1"></i> Solventado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox display-4 text-secondary mb-3 d-block opacity-25"></i>
                                            <span class="fw-bold fs-5">Sin Historial</span><br>
                                            No hay casos resueltos recientemente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts de Gráficos (Fase 4) -->
    <style>
        .border-start-md { border-left: 1px solid #eee; }
        @media (max-width: 767.98px) { .border-start-md { border-left: none; } }
        .blink-soft { animation: blinker 2s linear infinite; }
        @keyframes blinker { 50% { opacity: 0.6; } }
    </style>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const ramData = {!! json_encode($graficoRam) !!};
            const discoData = {!! json_encode($graficoDiscos) !!};

            // Paleta de colores Premium
            const colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1'];

            // Gráfico RAM
            if (ramData.labels && ramData.labels.length > 0) {
                new Chart(document.getElementById('ramChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ramData.labels,
                        datasets: [{
                            data: ramData.data,
                            backgroundColor: colors,
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }
                        },
                        cutout: '70%'
                    }
                });
            } else {
                document.getElementById('ramChart').parentElement.innerHTML = '<div class="h-100 d-flex align-items-center justify-content-center text-muted">Sin datos de RAM registrados.</div>';
            }

            // Gráfico Discos
            if (discoData.labels && discoData.labels.length > 0) {
                new Chart(document.getElementById('discoChart'), {
                    type: 'pie', // Cambiamos un poco el estilo
                    data: {
                        labels: discoData.labels,
                        datasets: [{
                            data: discoData.data,
                            backgroundColor: ['#20c997', '#fd7e14', '#6c757d'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }
                        }
                    }
                });
            } else {
                document.getElementById('discoChart').parentElement.innerHTML = '<div class="h-100 d-flex align-items-center justify-content-center text-muted">Sin datos de Discos registrados.</div>';
            }
        });
    </script>
    @endpush

    <!-- Modal Detalle (Copiado de Gestión) -->
    <div wire:ignore.self class="modal fade" id="modalDetalleIncidencia" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white border-bottom-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 p-2 rounded-3 me-3">
                            <i class="bi bi-eye-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title h6 mb-0">Detalles de la Incidencia</h5>
                            @if($incidencia_detalle)
                                <small class="opacity-75">Folio: #{{ str_pad($incidencia_detalle->id, 5, '0', STR_PAD_LEFT) }}</small>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                @if($incidencia_detalle)
                <div class="modal-body p-0 bg-body-secondary overflow-hidden">
                    <!-- Banner de Estado -->
                    <div class="px-4 py-3 bg-body border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            @if($incidencia_detalle->cerrado)
                                <span class="badge bg-dark rounded-pill px-3"><i class="bi bi-lock-fill me-1"></i> Cerrado</span>
                            @elseif($incidencia_detalle->solventado)
                                <span class="badge bg-success rounded-pill px-3"><i class="bi bi-check-circle-fill me-1"></i> Solventado</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-3"><i class="bi bi-clock-history me-1"></i> En Curso</span>
                            @endif

                            @if($incidencia_detalle->amerita_movimiento)
                                <span class="badge bg-info text-white rounded-pill px-3"><i class="bi bi-arrow-left-right me-1"></i> Requiere Movimiento</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            <i class="bi bi-calendar3 me-1"></i> {{ $incidencia_detalle->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="row g-4">
                            <!-- Columna Izquierda: Información General -->
                            <div class="col-md-7">
                                <!-- Problema y Descripción -->
                                <div class="card border-0 shadow-sm rounded-3 mb-4">
                                    <div class="card-body">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                            <i class="bi bi-chat-left-text me-2"></i> Descripción del Caso
                                        </h6>
                                        <div class="mb-3">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-2">
                                                {{ $incidencia_detalle->problema->nombre }}
                                            </span>
                                            <p class="mb-0 text-body" style="white-space: pre-wrap;">{{ $incidencia_detalle->descripcion }}</p>
                                        </div>
                                        
                                        @if($incidencia_detalle->nota_resolucion)
                                        <div class="mt-4 p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">
                                            <h6 class="text-success fw-bold small mb-2"><i class="bi bi-check2-square me-1"></i> Resolución:</h6>
                                            <p class="mb-0 text-body small" style="white-space: pre-wrap;">{{ $incidencia_detalle->nota_resolucion }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Ubicación -->
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-body">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                            <i class="bi bi-geo-alt me-2"></i> Ubicación y Solicitante
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="text-muted small d-block">Departamento</label>
                                                <span class="fw-bold">{{ $incidencia_detalle->departamento->nombre }}</span>
                                            </div>
                                            <div class="col-6">
                                                <label class="text-muted small d-block">Dependencia</label>
                                                <span class="fw-bold">{{ $incidencia_detalle->dependencia->nombre ?? 'N/A' }}</span>
                                            </div>
                                            <div class="col-12">
                                                <label class="text-muted small d-block">Solicitante</label>
                                                @if($incidencia_detalle->trabajador)
                                                    <span class="fw-bold">{{ $incidencia_detalle->trabajador->nombres }} {{ $incidencia_detalle->trabajador->apellidos }}</span>
                                                    <small class="text-muted d-block">{{ $incidencia_detalle->trabajador->cargo ?? '' }}</small>
                                                @else
                                                    <span class="fw-bold">{{ $incidencia_detalle->creator->name ?? 'N/A' }}</span>
                                                    <span class="badge bg-secondary ms-1">Externo</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna Derecha: Activo y Personal -->
                            <div class="col-md-5">
                                <!-- Activo -->
                                <div class="card border-0 shadow-sm rounded-3 mb-4">
                                    <div class="card-body">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                            <i class="bi bi-laptop me-2"></i> Activo Relacionado
                                        </h6>
                                        @if($incidencia_detalle->modelo)
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-light p-3 rounded-3 me-3 text-primary border">
                                                    <i class="bi bi-{{ $incidencia_detalle->modelo_type == 'App\Models\Computador' ? 'pc-display' : 'router' }} fs-4"></i>
                                                </div>
                                                <div>
                                                    <span class="badge bg-secondary mb-1">{{ class_basename($incidencia_detalle->modelo_type) }}</span>
                                                    <h6 class="mb-0 fw-bold">
                                                        {{ $incidencia_detalle->modelo->nombre ?? ($incidencia_detalle->modelo->marca->nombre . ' ' . ($incidencia_detalle->modelo->nombre_equipo ?? '')) }}
                                                    </h6>
                                                </div>
                                            </div>
                                            <div class="bg-light p-3 rounded-3 border border-dashed">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">Bien Nacional:</span>
                                                    <span class="fw-bold small">{{ $incidencia_detalle->modelo->bien_nacional ?? 'N/A' }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted small">Serial:</span>
                                                    <span class="fw-bold small text-truncate" style="max-width: 120px;" title="{{ $incidencia_detalle->modelo->serial }}">{{ $incidencia_detalle->modelo->serial ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center py-4 text-muted">
                                                <i class="bi bi-slash-circle display-6 d-block mb-2"></i>
                                                <p class="small mb-0">Sin activo relacionado</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Personal Asignado -->
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-body">
                                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                            <i class="bi bi-person-gear me-2"></i> Personal
                                        </h6>
                                        <div class="mb-3">
                                            <label class="text-muted small d-block">Técnico Resolutor</label>
                                            @if($incidencia_detalle->tecnico)
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                                        {{ substr($incidencia_detalle->tecnico->name, 0, 1) }}
                                                    </div>
                                                    <span class="fw-bold">{{ $incidencia_detalle->tecnico->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-danger fst-italic small"><i class="bi bi-hourglass-split"></i> Pendiente por Asignar</span>
                                            @endif
                                        </div>
                                        <div>
                                            <label class="text-muted small d-block">Registrado por</label>
                                            <span class="small">{{ $incidencia_detalle->creator->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body border-top-0 p-4">
                    <div class="me-auto">
                        @can('reportes-pdf')
                            <a href="{{ route('reportes.incidencia.ficha', $incidencia_detalle->id) }}" target="_blank" class="btn btn-outline-danger px-4">
                                <i class="bi bi-file-pdf me-1"></i> Descargar Ficha PDF
                            </a>
                        @endcan
                    </div>
                    @if(!$incidencia_detalle->cerrado)
                        @can('gestionar-incidencias')
                            <a href="{{ route('incidencias.gestion', ['id' => $incidencia_detalle->id]) }}" class="btn btn-primary px-4">
                                <i class="bi bi-pencil-square me-1"></i> Gestionar Caso
                            </a>
                        @endcan
                    @endif
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
