<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-9 d-flex align-items-center">
            <div class="bg-dark bg-opacity-10 p-3 rounded-3 me-3 text-dark border shadow-sm">
                <i class="bi bi-clock-history fs-3"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-0 text-dark">Logs del Sistema</h2>
                <p class="text-muted mb-0">Trazabilidad total de acciones, cambios y auditoría de datos.</p>
            </div>
        </div>
        <div class="col-md-3 text-end">
            @can('reportes-masivos-filtros')
                <button wire:click="abrirReporte" class="btn btn-primary w-100 shadow-sm border-0 py-2 fw-bold">
                    <i class="bi bi-file-earmark-plus me-1"></i> Generador Pro
                </button>
            @endcan
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Responsable</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Nombre o email..." wire:model.live="searchUser">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Módulo / Acción</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Ej: Computador..." wire:model.live="searchModule">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Desde</label>
                    <input type="date" class="form-control shadow-sm" wire:model.live="dateFrom">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Hasta</label>
                    <input type="date" class="form-control shadow-sm" wire:model.live="dateTo">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-outline-secondary w-100 shadow-sm" wire:click="$set('searchUser', ''); $set('searchModule', ''); $set('dateFrom', ''); $set('dateTo', '')">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3" style="cursor:pointer" wire:click="sortBy('id')">ID 
                                @if($sortField === 'id') <i class="bi bi-sort-numeric-{{ $sortAsc ? 'down' : 'up' }}"></i> @endif
                            </th>
                            <th class="py-3">Responsable</th>
                            <th class="py-3">Acción</th>
                            <th class="py-3">Módulo afectado</th>
                            <th class="py-3" style="cursor:pointer" wire:click="sortBy('created_at')">Fecha / Hora
                                @if($sortField === 'created_at') <i class="bi bi-sort-{{ $sortAsc ? 'down' : 'up' }}"></i> @endif
                            </th>
                            <th class="text-end pe-4 py-3">Cambios</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-4 fw-bold text-muted small">#{{ $log->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-pill p-1 me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold;">
                                            {{ $log->causer ? substr($log->causer->name, 0, 1) : 'S' }}
                                        </div>
                                        <div>
                                            <div class="fw-bold small">{{ $log->causer->name ?? 'Sistema' }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $log->causer->email ?? 'Automático' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $color = match($log->description) {
                                            'created' => 'success',
                                            'updated' => 'info',
                                            'deleted' => 'danger',
                                            'restored' => 'warning',
                                            default => 'secondary'
                                        };
                                        $icon = match($log->description) {
                                            'created' => 'plus-circle',
                                            'updated' => 'pencil-square',
                                            'deleted' => 'trash',
                                            'restored' => 'arrow-counterclockwise',
                                            default => 'info-circle'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} rounded-pill px-3 py-2 fw-medium border border-{{ $color }} border-opacity-25">
                                        <i class="bi bi-{{ $icon }} me-1"></i> {{ ucfirst($log->description) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark fw-medium">{{ class_basename($log->subject_type) }}</span>
                                    @if($log->subject_id)
                                        <span class="text-muted small"> (ID: {{ $log->subject_id }})</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    <div class="fw-bold">{{ $log->created_at->format('d/m/Y') }}</div>
                                    <div>{{ $log->created_at->format('h:i:s A') }}</div>
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="verDetalle({{ $log->id }})" class="btn btn-sm btn-outline-dark rounded-pill shadow-sm">
                                        <i class="bi bi-eye"></i> Detalle
                                    </button>
                                </td>
                            </tr>
@empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        No se encontraron registros de actividad.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Detalle -->
    <div wire:ignore.self class="modal fade" id="modalDetalleLog" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                @if($selectedLog)
                    <div class="modal-header bg-dark text-white border-0 py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-info-circle me-2"></i> Detalle del Cambio #{{ $selectedLog->id }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="p-4 bg-light border-bottom">
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="text-muted small fw-bold d-block mb-1 text-uppercase">Entidad / Módulo</label>
                                    <div class="fw-bold text-primary fs-5">{{ class_basename($selectedLog->subject_type) }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small fw-bold d-block mb-1 text-uppercase">Responsable</label>
                                    <div class="fw-bold">{{ $selectedLog->causer->name ?? 'Sistema' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            @php
                                $properties = $selectedLog->properties;
                                $old = $properties['old'] ?? [];
                                $new = $properties['attributes'] ?? [];
                            @endphp

                            @if(count($new) > 0)
                                <div class="table-responsive rounded-3 border">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="bg-light fs-7 text-uppercase">
                                            <tr>
                                                <th class="ps-3 py-2" style="width: 30%">Campo</th>
                                                <th class="py-2">Valor Anterior</th>
                                                <th class="py-2">Valor Nuevo</th>
                                            </tr>
                                        </thead>
                                        <tbody class="small">
                                            @foreach($new as $key => $value)
                                                @php
                                                    $oldValue = $old[$key] ?? 'N/A';
                                                    $isChanged = $oldValue != $value && $oldValue !== 'N/A';
                                                @endphp
                                                <tr class="{{ $isChanged ? 'table-info' : '' }}">
                                                    <td class="ps-3 py-2 fw-bold text-muted">{{ ucfirst($key) }}</td>
                                                    <td class="py-2 text-decoration-line-through text-danger opacity-75">
                                                        {{ is_array($oldValue) ? json_encode($oldValue) : $oldValue }}
                                                    </td>
                                                    <td class="py-2 fw-bold text-success">
                                                        {{ is_array($value) ? json_encode($value) : $value }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @elseif($selectedLog->description === 'deleted')
                                <div class="alert alert-danger d-flex align-items-center rounded-3">
                                    <i class="bi bi-exclamation-triangle-fill fs-2 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Registro Eliminado</h6>
                                        <p class="mb-0 small">Este registro ha sido marcado como eliminado (Soft Delete). Se conservan los datos previos para auditoría.</p>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info py-4 text-center">
                                    <i class="bi bi-info-circle fs-2 d-block mb-2"></i>
                                    No hay cambios pormenorizados registrados en las propiedades.
                                </div>
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
    <!-- Modal Reporte Masivo -->
    <div wire:ignore.self class="modal fade" id="modalReporteMasivo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i> Generador de Reportes Multi-Módulo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <p class="text-muted mb-4">Seleccione los módulos que desea incluir en el archivo Excel y aplique los filtros necesarios para cada uno.</p>

                    <div class="row g-4">
                        @foreach($reportSelections as $module => $data)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm rounded-3 {{ $data['selected'] ? 'ring-primary' : '' }}">
                                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" wire:model.live="reportSelections.{{ $module }}.selected" style="transform: scale(1.2);">
                                        <label class="form-check-label fw-bold text-dark">{{ ucfirst(str_replace('-', ' ', $module)) }}</label>
                                    </div>
                                    @if($data['selected'])
                                        <i class="bi bi-check-circle-fill text-primary"></i>
                                    @endif
                                </div>
                                <div class="card-body py-2 px-3 {{ !$data['selected'] ? 'opacity-50 grayscale' : '' }}">
                                    @if($data['selected'])
                                        <div class="form-check form-switch mb-3 bg-light p-2 rounded border">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" id="full_{{ $module }}" wire:model.live="reportSelections.{{ $module }}.full_inventory">
                                            <label class="form-check-label small fw-bold" for="full_{{ $module }}">
                                                {{ $data['full_inventory'] ? 'Todo el Inventario' : 'Vista con Filtros' }}
                                            </label>
                                        </div>

                                        @if(!$data['full_inventory'])
                                            <div class="mb-2">
                                                <label class="small fw-bold text-muted mb-1">Estado</label>
                                                <select class="form-select form-select-sm" wire:model.live="reportSelections.{{ $module }}.filters.estado">
                                                    <option value="">Cualquiera</option>
                                                    <option value="activos">Solo Activos</option>
                                                    <option value="inactivos">Solo Inactivos (Bajas)</option>
                                                </select>
                                            </div>

                                            @if(in_array($module, ['computadores', 'dispositivos']))
                                                <div class="mb-2">
                                                    <label class="small fw-bold text-muted mb-1">Departamento</label>
                                                    <select class="form-select form-select-sm" wire:model.live="reportSelections.{{ $module }}.filters.departamento_id">
                                                        <option value="">Cualquiera</option>
                                                        @foreach($departamentos as $dep)
                                                            <option value="{{ $dep->id }}">{{ $dep->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-center py-3">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                                    <i class="bi bi-lightning-fill me-1"></i> Sin Restricciones
                                                </span>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center py-4 text-muted small">
                                            Habilitar para filtrar
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer bg-white border-0 p-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="generarReporteMasivo" class="btn btn-primary px-5 shadow-sm fw-bold">
                        <i class="bi bi-cloud-download me-2"></i> Generar Excel Consolidado
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para descarga masiva (POST) -->
    <form id="massReportForm" action="{{ route('reportes.masivo.excel') }}" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="selections" id="massReportData">
    </form>

    <script>
        document.addEventListener('livewire:init', () => {
           Livewire.on('descargar-masivo', (event) => {
               const data = event.selections;
               document.getElementById('massReportData').value = JSON.stringify(data);
               document.getElementById('massReportForm').submit();
           });
        });
    </script>

    <style>
        .ring-primary { border: 2px solid var(--bs-primary) !important; }
        .grayscale { filter: grayscale(1); }
        .fs-7 { font-size: 0.75rem; }
    </style>
</div>
