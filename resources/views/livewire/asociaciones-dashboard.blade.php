<div>
    <!-- Header Especial -->
    <div class="row mb-4 align-items-center">
        <div class="col-12 d-flex align-items-center">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary me-3 shadow-sm border-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary border shadow-sm">
                <i class="bi bi-diagram-3 fs-3"></i>
            </div>
            <div>
                <h2 class="fw-bold mb-0 text-dark">{{ $titulo }}</h2>
                @if($subtitulo)
                    <p class="text-muted mb-0">{{ $subtitulo }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Pestañas de Asociaciones -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pb-0 pt-3">
            <ul class="nav nav-tabs card-header-tabs" id="asociacionesTabs" role="tablist">
                <!-- Pestaña de Trabajadores (Solo para Departamentos, Marcas, Computadores o Dispositivos) -->
                @if(in_array($tipo, ['departamento', 'marca', 'computador', 'dispositivo', 'insumo']))
                @can('ver-trabajadores')
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if(in_array($tipo, ['departamento', 'computador', 'dispositivo', 'insumo'])) active @endif" id="trabajadores-tab" data-bs-toggle="tab" data-bs-target="#trabajadores" type="button" role="tab" aria-controls="trabajadores" aria-selected="true">
                        @if($tipo == 'computador' || $tipo == 'dispositivo' || $tipo == 'insumo')
                            <i class="bi bi-people-fill"></i> Responsable y Ubicación
                        @else
                            <i class="bi bi-person-badge"></i> Trabajador / Responsable
                        @endif
                    </button>
                </li>
                @endcan
                @endif

                <!-- Pestaña de Computadores (Casi todos) -->
                @if(in_array($tipo, ['departamento', 'trabajador', 'procesador', 'gpu', 'so', 'marca', 'dispositivo']))
                @can('ver-computadores')
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if($tipo != 'departamento' && $tipo != 'dispositivo') active @endif" id="computadores-tab" data-bs-toggle="tab" data-bs-target="#computadores" type="button" role="tab" aria-controls="computadores" aria-selected="false">
                        <i class="bi bi-pc-display"></i> @if($tipo == 'dispositivo') Computador Asociado @else Computadores @endif
                    </button>
                </li>
                @endcan
                @endif

                <!-- Pestaña de Dispositivos (Departamentos, Trabajadores, Marcas, Insumos, Computadores) -->
                @if(in_array($tipo, ['departamento', 'trabajador', 'marca', 'insumo', 'computador']))
                @can('ver-dispositivos')
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="dispositivos-tab" data-bs-toggle="tab" data-bs-target="#dispositivos" type="button" role="tab" aria-controls="dispositivos" aria-selected="false">
                        <i class="bi bi-printer"></i> @if($tipo == 'insumo') Computador y Dispositivo Asociado @elseif($tipo == 'computador') Dispositivos Conectados @else Dispositivos Adjuntos @endif
                    </button>
                </li>
                @endcan
                @endif
                
                <!-- Pestaña de Insumos -->
                @if(in_array($tipo, ['marca', 'departamento', 'trabajador', 'dispositivo', 'computador']))
                @can('ver-insumos')
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="insumos-tab" data-bs-toggle="tab" data-bs-target="#insumos" type="button" role="tab" aria-controls="insumos" aria-selected="false">
                        <i class="bi bi-box-seam"></i> Insumos
                    </button>
                </li>
                @endcan
                @endif

                <!-- Pestaña de Incidencias (PARA TODOS) -->
                @can('ver-incidencias')
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="incidencias-tab" data-bs-toggle="tab" data-bs-target="#incidencias" type="button" role="tab" aria-controls="incidencias" aria-selected="false">
                        <i class="bi bi-exclamation-triangle"></i> Historial de Incidencias
                    </button>
                </li>
                @endcan
            </ul>
        </div>
        <div class="card-body bg-light p-4">
            <div class="tab-content" id="asociacionesTabsContent">
                
                @if(in_array($tipo, ['departamento', 'marca', 'computador', 'dispositivo', 'insumo']))
                <div class="tab-pane fade @if(in_array($tipo, ['departamento', 'computador', 'dispositivo', 'insumo'])) show active @endif" id="trabajadores" role="tabpanel" aria-labelledby="trabajadores-tab">
                    @php
                        $filtroTrabajador = [];
                        if($tipo == 'computador' || $tipo == 'dispositivo' || $tipo == 'insumo') {
                            $filtroTrabajador = ['id' => $modelo->trabajador_id ?? 0];
                        } else {
                            $filtroTrabajador = [$tipo.'_id' => $modelo_id];
                        }
                    @endphp
                    <div class="mb-4">
                        <livewire:asignaciones.trabajadores :presetFiltro="$filtroTrabajador" :ocultarTitulos="true" :key="'trabajadores-'.$tipo.'-'.$modelo_id" />
                    </div>

                    @if($tipo == 'insumo' || $tipo == 'dispositivo' || $tipo == 'computador')
                        <div class="mt-5 border-top pt-4">
                            <h5 class="mb-4 text-primary"><i class="bi bi-building me-2"></i>Departamento Asociado (Ubicación)</h5>
                            <livewire:asignaciones.departamentos :presetFiltro="['id' => $modelo->departamento_id ?? 0]" :ocultarTitulos="true" :key="'departamentos-'.$tipo.'-'.$modelo_id" />
                        </div>
                    @endif

                    @if($tipo == 'computador')
                        {{-- Movido a la pestaña de Dispositivos para estandarización --}}
                    @endif

                    @if($tipo == 'dispositivo')
                        {{-- Movido a la pestaña de Computadores para estandarización --}}
                    @endif
                </div>
                @endif

                @if(in_array($tipo, ['departamento', 'trabajador', 'procesador', 'gpu', 'so', 'marca', 'dispositivo']))
                <div class="tab-pane fade @if($tipo != 'departamento' && $tipo != 'dispositivo') show active @endif" id="computadores" role="tabpanel" aria-labelledby="computadores-tab">
                    @php
                        $filtroComputador = [];
                        if($tipo == 'so') $filtroComputador = ['sistema_operativo_id' => $modelo_id];
                        elseif($tipo == 'dispositivo') $filtroComputador = ['id' => $modelo->computador_id ?? 0];
                        else $filtroComputador = [$tipo.'_id' => $modelo_id];
                    @endphp
                    <livewire:inventario.computadores :presetFiltro="$filtroComputador" :ocultarTitulos="true" :key="'computadores-'.$tipo.'-'.$modelo_id" />
                </div>
                @endif

                @if(in_array($tipo, ['departamento', 'trabajador', 'marca', 'insumo', 'computador']))
                <div class="tab-pane fade" id="dispositivos" role="tabpanel" aria-labelledby="dispositivos-tab">
                    @if($tipo == 'insumo')
                        <div class="mb-4">
                            <h5 class="mb-4 text-primary"><i class="bi bi-pc-display me-2"></i>Computador Asociado</h5>
                            <livewire:inventario.computadores :presetFiltro="['id' => $modelo->computador_id ?? 0]" :ocultarTitulos="true" :key="'computadores-ins-tab-'.$modelo_id" />
                        </div>
                        
                        <div class="mt-5 border-top pt-4">
                            <h5 class="mb-4 text-primary"><i class="bi bi-printer me-2"></i>Dispositivo Asociado</h5>
                            @if($modelo->dispositivo_id)
                                <livewire:inventario.dispositivos :presetFiltro="['id' => $modelo->dispositivo_id]" :ocultarTitulos="true" :key="'dispositivos-ins-'.$modelo_id" />
                            @else
                                <div class="alert alert-info border-0 shadow-sm">
                                    <i class="bi bi-info-circle me-2"></i>Este insumo no está asociado a ningún dispositivo.
                                </div>
                            @endif
                        </div>
                    @elseif($tipo == 'computador')
                        <livewire:inventario.dispositivos :presetFiltro="['computador_id' => $modelo_id]" :ocultarTitulos="true" :key="'dispositivos-pc-'.$modelo_id" />
                    @else
                        <livewire:inventario.dispositivos :presetFiltro="[$tipo.'_id' => $modelo_id]" :ocultarTitulos="true" :key="'dispositivos-'.$tipo.'-'.$modelo_id" />
                    @endif
                </div>
                @endif
                
                @if(in_array($tipo, ['marca', 'departamento', 'trabajador', 'dispositivo', 'computador']))
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
