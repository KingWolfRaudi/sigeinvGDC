<div>
    <h2 class="mb-4">Bienvenido al {{ env('ORG_NOMBRE', 'Sistema de Inventario') }}</h2>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary">Tu Perfil</h5>
                    <p class="card-text">
                        <strong>Usuario:</strong> {{ Auth::user()->name }}<br>
                        <strong>Rol:</strong> 
                        <span class="badge bg-secondary">
                            {{ Auth::user()->roles->pluck('name')->implode(', ') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Equipos Activos</h5>
                    <h2 class="display-4">0</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Movimientos Pendientes</h5>
                    <h2 class="display-4">0</h2>
                </div>
            </div>
        </div>
    </div>
</div>