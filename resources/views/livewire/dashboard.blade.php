<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-5 align-items-center">
        <div class="col">
            <h5 class="text-primary fw-bold mb-1">Resumen del Sistema</h5>
            <h2 class="fw-bold text-body">Bienvenido, {{ Auth::user()->name }}</h2>
        </div>
        <div class="col-auto">
            <div class="bg-body p-2 rounded-4 shadow-sm border d-flex align-items-center">
                <div class="bg-info bg-opacity-10 p-2 rounded-3 me-3 text-info">
                    <i class="bi bi-calendar-check fs-4"></i>
                </div>
                <div>
                    <div class="small text-muted fw-bold">FECHA ACTUAL</div>
                    <div class="fw-bold">{{ now()->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-4 text-primary shadow-sm">
                            <i class="bi bi-pc-display fs-3"></i>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">En línea</span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $stats['total_pcs'] }}</h3>
                    <p class="text-muted fw-medium mb-0">Computadores Activos</p>
                </div>
                <div class="progress rounded-0" style="height: 4px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-indigo bg-opacity-10 p-3 rounded-4 text-indigo shadow-sm">
                            <i class="bi bi-router fs-3"></i>
                        </div>
                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3 py-1">Stock</span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $stats['total_dispositivos'] }}</h3>
                    <p class="text-muted fw-medium mb-0">Dispositivos Periféricos</p>
                </div>
                <div class="progress rounded-0" style="height: 4px;">
                    <div class="progress-bar bg-indigo" role="progressbar" style="width: 60%"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-danger bg-opacity-10 p-3 rounded-4 text-danger shadow-sm">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1">Pendientes</span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $stats['incidencias_abiertas'] }}</h3>
                    <p class="text-muted fw-medium mb-0">Incidencias Abiertas</p>
                </div>
                <div class="progress rounded-0" style="height: 4px;">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: 40%"></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-teal bg-opacity-10 p-3 rounded-4 text-teal shadow-sm">
                            <i class="bi bi-people fs-3"></i>
                        </div>
                        <span class="badge bg-teal bg-opacity-10 text-teal rounded-pill px-3 py-1">Personal</span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $stats['trabajadores'] }}</h3>
                    <p class="text-muted fw-medium mb-0">Trabajadores Activos</p>
                </div>
                <div class="progress rounded-0" style="height: 4px;">
                    <div class="progress-bar bg-teal" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Reports -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-body border-0 py-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill me-2 text-primary"></i> Estado General de Equipos</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="height: 350px;">
                        <canvas id="inventoryChart" 
                            data-labels='@json($pcsPorEstado->pluck("estado_fisico"))' 
                            data-values='@json($pcsPorEstado->pluck("total"))'>
                        </canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary text-white overflow-hidden position-relative">
                <div class="card-body p-4 position-relative" style="z-index: 2;">
                    <h5 class="fw-bold mb-4 opacity-75">Sesión Actual</h5>
                    <div class="d-flex align-items-center mb-4">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="rounded-circle me-3 border border-3 border-white border-opacity-25 shadow" style="width: 64px; height: 64px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-body text-primary d-flex align-items-center justify-content-center fw-bold shadow me-3" style="width: 64px; height: 64px; font-size: 1.5rem;">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <div class="fw-bold fs-5">{{ Auth::user()->name }}</div>
                            <div class="small opacity-75">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <hr class="bg-body border-opacity-25 my-4">
                    <div class="mb-3">
                        <label class="small opacity-75 d-block mb-1">ROL ASIGNADO</label>
                        <span class="badge bg-body text-primary rounded-pill px-3">{{ Auth::user()->roles->pluck('name')->first() }}</span>
                    </div>
                    <div class="mb-4">
                        <label class="small opacity-75 d-block mb-1">ÚLTIMO ACCESO</label>
                        <div class="fw-bold">Hoy, {{ now()->format('H:i') }}</div>
                    </div>
                    <a href="{{ route('perfil') }}" class="btn btn-light text-primary fw-bold w-100 py-2 rounded-3 shadow-sm border-0">
                        <i class="bi bi-person me-2"></i> Gestionar mi Perfil
                    </a>
                </div>
                <!-- Subtle graphic element -->
                <i class="bi bi-shield-check position-absolute" style="bottom: -50px; right: -20px; font-size: 200px; opacity: 0.05; z-index: 1;"></i>
            </div>
        </div>
    </div>

    <!-- Scripts for Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            const ctx = document.getElementById('inventoryChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: JSON.parse(ctx.dataset.labels),
                    datasets: [{
                        label: 'Cantidad de Equipos',
                        data: JSON.parse(ctx.dataset.values),
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.2)', // primary
                            'rgba(25, 135, 84, 0.2)',  // success
                            'rgba(255, 193, 7, 0.2)',  // warning
                            'rgba(220, 53, 69, 0.2)'   // danger
                        ],
                        borderColor: [
                            'rgb(13, 110, 253)',
                            'rgb(25, 135, 84)',
                            'rgb(255, 193, 7)',
                            'rgb(220, 53, 69)'
                        ],
                        borderWidth: 2,
                        borderRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });

        // Fallback para carga normal (sin navigación de Livewire turbo)
        window.addEventListener('DOMContentLoaded', () => {
             // Reutilizar lógica de arriba si es necesario
        });
    </script>

    <style>
        .text-indigo { color: #6610f2; }
        .bg-indigo { background-color: #6610f2; }
        .text-teal { color: #20c997; }
        .bg-teal { background-color: #20c997; }
        .rounded-4 { border-radius: 1rem !important; }
    </style>
</div>