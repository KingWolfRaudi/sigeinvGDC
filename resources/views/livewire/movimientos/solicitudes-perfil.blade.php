<div class="container-fluid py-4">
    <!-- Header Especial -->
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                <i class="bi bi-person-gear fs-3"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-0 text-dark">Solicitudes de Cambio de Perfil</h2>
                <p class="text-muted mb-0">Gestión de requerimientos para actualización de datos y perfiles de usuario.</p>
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
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar por nombre o usuario..." wire:model.live="search">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <select class="form-select shadow-sm" wire:model.live="filtro_estado">
                        <option value="todos">Todas las solicitudes</option>
                        <option value="pendiente">Solo Pendientes</option>
                        <option value="aprobado">Solo Aprobados</option>
                        <option value="rechazado">Solo Rechazados</option>
                    </select>
                </div>

                <div class="col-md-4 text-end d-flex gap-2 justify-content-end">
                    <button wire:click="exportExcel" class="btn btn-outline-success border-2 fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i> Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal (Tabla) -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th class="ps-4">Usuario</th>
                        <th>Tipo</th>
                        <th>Cambio Solicitado</th>
                        <th>Fecha</th>
                        @if($filtro_estado !== 'pendiente')
                            <th>Atendido Por</th>
                        @endif
                        <th class="pe-4 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($solicitudes as $sol)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="bi bi-person-fill text-primary"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold d-block">{{ $sol->user->name }}</span>
                                        <small class="text-muted">@ {{ $sol->user->username }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border p-2 text-capitalize">
                                    {{ $sol->tipo }}
                                </span>
                            </td>
                            <td>
                                @if($sol->tipo === 'password')
                                    <span class="text-muted"><i class="bi bi-shield-lock me-1"></i> (Hash de Nueva Contraseña)</span>
                                @elseif($sol->tipo === 'email')
                                    <code class="text-primary">{{ $sol->valor_nuevo }}</code>
                                @else
                                    <span class="fw-medium">{{ $sol->valor_nuevo }}</span>
                                @endif
                                
                                @if($sol->estado === 'pendiente')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pendiente</span>
                                @elseif($sol->estado === 'aprobado')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Aprobado</span>
                                @elseif($sol->estado === 'rechazado')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Rechazado</span>
                                @elseif($sol->estado === 'cancelado')
                                    <span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i> Cancelado</span>
                                @endif

                                @if($sol->estado === 'rechazado' && $sol->motivo_rechazo)
                                    <div class="mt-2 p-2 bg-danger bg-opacity-10 border border-danger-subtle rounded small">
                                        <strong class="text-danger">Motivo:</strong> {{ $sol->motivo_rechazo }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $sol->created_at->format('d/m/Y h:i A') }}</span>
                            </td>
                            @if($filtro_estado !== 'pendiente')
                                <td>
                                    @if($sol->revisor)
                                        <span class="small">{{ $sol->revisor->name }}</span>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                            @endif
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-outline-danger me-2 shadow-sm" wire:click="exportPDF({{ $sol->id }})" title="Exportar PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </button>
                                @if($sol->estado === 'pendiente')
                                    <button class="btn btn-sm btn-success px-3 shadow-sm me-1" wire:click="aprobar({{ $sol->id }})" wire:confirm="¿Aprobar y aplicar este cambio?">
                                        <i class="bi bi-check-lg"></i> Aprobar
                                    </button>
                                    <button class="btn btn-sm btn-danger px-3 shadow-sm" wire:click="modalRechazar({{ $sol->id }})">
                                        <i class="bi bi-x-lg"></i> Rechazar
                                    </button>
                                @else
                                    <span class="text-muted small opacity-50"><i class="bi bi-lock"></i> Finalizada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-5 text-center text-muted">
                                <i class="bi bi-clipboard-x fs-1 opacity-25 d-block mb-3"></i>
                                No se encontraron solicitudes con el filtro seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($solicitudes->hasPages())
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $solicitudes->links() }}
            </div>
        @endif
    </div>

    <!-- Modal para rechazar -->
    <div wire:ignore.self class="modal fade shadow" id="modalRechazo" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-x-circle me-2"></i> Rechazar Solicitud</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                    <div class="alert alert-info border-0 shadow-sm small py-2 mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i> Indica el motivo detallado para que el usuario pueda corregirlo.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo del Rechazo</label>
                        <textarea class="form-control" rows="4" wire:model="motivo_rechazo" placeholder="Ej: El nombre contiene caracteres inválidos..."></textarea>
                        @error('motivo_rechazo') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom">
                    <button type="button" class="btn btn-secondary px-4 shadow-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger px-4 shadow-sm" wire:click="rechazar">Confirmar Rechazo</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
           Livewire.on('abrir-modal', (event) => {
               const modal = new bootstrap.Modal(document.getElementById(event.id));
               modal.show();
           });
           Livewire.on('cerrar-modal', (event) => {
               const modalElement = document.getElementById(event.id);
               const modal = bootstrap.Modal.getInstance(modalElement);
               if (modal) {
                   modal.hide();
                   // Cleanup manual de backdrops de Bootstrap para evitar bloqueo de UI
                   setTimeout(() => {
                       document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                       document.body.style.overflow = 'auto';
                       document.body.classList.remove('modal-open');
                   }, 300);
               }
           });
        });
    </script>
</div>
