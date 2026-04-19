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
                    <p class="text-muted fw-semibold mb-0">Tickets por Asignar</p>
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
                    <p class="text-muted fw-semibold mb-0">Casos En Curso</p>
                </div>
                <div class="bg-warning position-absolute bottom-0 start-0 w-100" style="height: 4px;"></div>
            </div>
        </div>

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
    </div>

    <!-- Fase 3: Analítica de Hardware -->
    <div class="row g-4 mb-4">
        <!-- Gráficos -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-body border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-body"><i class="bi bi-cpu text-primary me-2"></i>Composición de Hardware</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <!-- RAM -->
                        <div class="col-md-6 text-center">
                            <h6 class="text-muted mb-3 fw-bold">Distribución de Memoria RAM</h6>
                            <div style="height: 250px; position: relative;">
                                <canvas id="ramChart"></canvas>
                            </div>
                        </div>
                        <!-- Discos -->
                        <div class="col-md-6 text-center mt-4 mt-md-0 border-start-md">
                            <h6 class="text-muted mb-3 fw-bold">Modernización de Almacenamiento</h6>
                            <div style="height: 250px; position: relative;">
                                <canvas id="discoChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totales de Capacidad -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-body border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-body"><i class="bi bi-server text-success me-2"></i>Capacidad Bruta Gestionada</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    
                    <div class="d-flex align-items-center mb-4 bg-body-secondary p-3 rounded-4">
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle me-3">
                            <i class="bi bi-database fs-2"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 fw-bold small text-uppercase">Total Almacenamiento</p>
                            <h2 class="fw-bold mb-0 text-body">
                                {{ $totalAlmacenamientoGB >= 1024 ? number_format($totalAlmacenamientoGB / 1024, 2) . ' TB' : number_format($totalAlmacenamientoGB, 0) . ' GB' }}
                            </h2>
                        </div>
                    </div>

                    <div class="d-flex align-items-center bg-body-secondary p-3 rounded-4">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3">
                            <i class="bi bi-memory fs-2"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-0 fw-bold small text-uppercase">Memoria RAM Instalada</p>
                            <h2 class="fw-bold mb-0 text-body">
                                {{ $totalRamGB >= 1024 ? number_format($totalRamGB / 1024, 2) . ' TB' : number_format($totalRamGB, 0) . ' GB' }}
                            </h2>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Fase 4: Listado de Acción Rápida y Resueltos -->
    <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
        <h4 class="fw-bold text-body mb-0">
            <i class="bi bi-briefcase-fill text-primary me-2"></i>Mesa de Ayuda (HelpDesk)
        </h4>
        <a href="{{ route('incidencias.gestion') }}" class="btn btn-outline-primary fw-bold rounded-pill px-4 shadow-sm">
            Ir al Panel de Gestión <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-4 mb-4">
        <!-- Columna Izquierda: Casos Pendientes -->
        <div class="col-12 col-xxl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-body border-bottom pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-body mb-0">
                        <i class="bi bi-lightning-charge text-warning me-2"></i>
                        {{ $esResolutor ? 'Mis Casos Asignados (Pendientes)' : 'Atención Rápida (Últimos Tickets)' }}
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
                                            <span class="fw-bold text-primary">#{{ str_pad($inc->id, 5, '0', STR_PAD_LEFT) }}</span>
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
                        {{ $esResolutor ? 'Mis Casos Resueltos (Recientes)' : 'Historial de Resoluciones' }}
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
                                            <span class="fw-bold text-muted">#{{ str_pad($inc->id, 5, '0', STR_PAD_LEFT) }}</span>
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
            const ramData = @json($graficoRam);
            const discoData = @json($graficoDiscos);

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
</div>
