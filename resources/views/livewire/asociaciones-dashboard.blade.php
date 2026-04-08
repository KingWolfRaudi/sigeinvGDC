<div>
    <div class="row mb-4 align-items-center">
        <div class="col-md-9">
            <h3 class="mb-0">
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                {{ $titulo }}
            </h3>
            @if($subtitulo)
                <p class="text-muted mt-1 mb-0 fs-6">{{ $subtitulo }}</p>
            @endif
        </div>
    </div>

    <!-- Pestañas de Asociaciones -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0 pt-3">
            <ul class="nav nav-tabs card-header-tabs" id="asociacionesTabs" role="tablist">
                <!-- Pestaña de Trabajadores (Solo para Departamentos, Marcas, Computadores o Dispositivos) -->
                @if(in_array($tipo, ['departamento', 'marca', 'computador', 'dispositivo']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if(in_array($tipo, ['departamento', 'computador', 'dispositivo'])) active @endif" id="trabajadores-tab" data-bs-toggle="tab" data-bs-target="#trabajadores" type="button" role="tab" aria-controls="trabajadores" aria-selected="true">
                        @if($tipo == 'computador')
                            <i class="bi bi-people-fill"></i> Responsable y Dispositivos
                        @else
                            <i class="bi bi-person-badge"></i> Trabajador / Responsable
                        @endif
                    </button>
                </li>
                @endif

                <!-- Pestaña de Computadores (Casi todos) -->
                @if(in_array($tipo, ['departamento', 'trabajador', 'procesador', 'gpu', 'so', 'marca']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if($tipo != 'departamento') active @endif" id="computadores-tab" data-bs-toggle="tab" data-bs-target="#computadores" type="button" role="tab" aria-controls="computadores" aria-selected="false">
                        <i class="bi bi-pc-display"></i> Computadores
                    </button>
                </li>
                @endif

                <!-- Pestaña de Dispositivos (Solo para Departamento, Trabajador, Computador, Marca) -->
                @if(in_array($tipo, ['departamento', 'trabajador', 'marca']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="dispositivos-tab" data-bs-toggle="tab" data-bs-target="#dispositivos" type="button" role="tab" aria-controls="dispositivos" aria-selected="false">
                        <i class="bi bi-printer"></i> Dispositivos Adjuntos
                    </button>
                </li>
                @endif
                
                <!-- Pestaña de Insumos (Marcas) -->
                @if(in_array($tipo, ['marca']))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="insumos-tab" data-bs-toggle="tab" data-bs-target="#insumos" type="button" role="tab" aria-controls="insumos" aria-selected="false">
                        <i class="bi bi-box-seam"></i> Insumos
                    </button>
                </li>
                @endif

                <!-- Pestaña de Incidencias (PARA TODOS) -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="incidencias-tab" data-bs-toggle="tab" data-bs-target="#incidencias" type="button" role="tab" aria-controls="incidencias" aria-selected="false">
                        <i class="bi bi-exclamation-triangle"></i> Historial de Incidencias
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body bg-light p-4">
            <div class="tab-content" id="asociacionesTabsContent">
                
                @if(in_array($tipo, ['departamento', 'marca', 'computador', 'dispositivo']))
                <div class="tab-pane fade @if(in_array($tipo, ['departamento', 'computador', 'dispositivo'])) show active @endif" id="trabajadores" role="tabpanel" aria-labelledby="trabajadores-tab">
                    @php
                        $filtroTrabajador = [];
                        if($tipo == 'computador' || $tipo == 'dispositivo') {
                            $filtroTrabajador = ['id' => $modelo->trabajador_id ?? 0];
                        } else {
                            $filtroTrabajador = [$tipo.'_id' => $modelo_id];
                        }
                    @endphp
                    <div class="mb-4">
                        <livewire:asignaciones.trabajadores :presetFiltro="$filtroTrabajador" :ocultarTitulos="true" :key="'trabajadores-'.$tipo.'-'.$modelo_id" />
                    </div>

                    @if($tipo == 'computador')
                        <div class="mt-5 border-top pt-4">
                            <h5 class="mb-4 text-primary"><i class="bi bi-printer me-2"></i>Dispositivos Conectados</h5>
                            <livewire:inventario.dispositivos :presetFiltro="['computador_id' => $modelo_id]" :ocultarTitulos="true" :key="'dispositivos-pc-'.$modelo_id" />
                        </div>
                    @endif
                </div>
                @endif

                @if(in_array($tipo, ['departamento', 'trabajador', 'procesador', 'gpu', 'so', 'marca']))
                <div class="tab-pane fade @if($tipo != 'departamento') show active @endif" id="computadores" role="tabpanel" aria-labelledby="computadores-tab">
                    @php
                        $filtroComputador = [];
                        if($tipo == 'so') $filtroComputador = ['sistema_operativo_id' => $modelo_id];
                        else $filtroComputador = [$tipo.'_id' => $modelo_id];
                    @endphp
                    <livewire:inventario.computadores :presetFiltro="$filtroComputador" :ocultarTitulos="true" :key="'computadores-'.$tipo.'-'.$modelo_id" />
                </div>
                @endif

                @if(in_array($tipo, ['departamento', 'trabajador', 'marca']))
                <div class="tab-pane fade" id="dispositivos" role="tabpanel" aria-labelledby="dispositivos-tab">
                    <livewire:inventario.dispositivos :presetFiltro="[$tipo.'_id' => $modelo_id]" :ocultarTitulos="true" :key="'dispositivos-'.$tipo.'-'.$modelo_id" />
                </div>
                @endif
                
                @if(in_array($tipo, ['marca']))
                <div class="tab-pane fade" id="insumos" role="tabpanel" aria-labelledby="insumos-tab">
                    <livewire:inventario.insumos :presetFiltro="[$tipo.'_id' => $modelo_id]" :ocultarTitulos="true" :key="'insumos-'.$tipo.'-'.$modelo_id" />
                </div>
                @endif

                <!-- Pestaña de Incidencias -->
                <div class="tab-pane fade" id="incidencias" role="tabpanel" aria-labelledby="incidencias-tab">
                    @php
                        $filtroIncidencias = [];
                        if(in_array($tipo, ['computador', 'dispositivo', 'insumo'])) {
                            $modelMap = [
                                'computador' => \App\Models\Computador::class,
                                'dispositivo' => \App\Models\Dispositivo::class,
                                'insumo' => \App\Models\Insumo::class,
                            ];
                            $filtroIncidencias = [
                                'modelo_type' => $modelMap[$tipo],
                                'modelo_id' => $modelo_id
                            ];
                        } else {
                            $filtroIncidencias = [$tipo.'_id' => $modelo_id];
                        }
                    @endphp
                    <livewire:incidencias.gestion :presetFiltro="$filtroIncidencias" :ocultarTitulos="true" :key="'incidencias-'.$tipo.'-'.$modelo_id" />
                </div>

            </div>
        </div>
    </div>
</div>
