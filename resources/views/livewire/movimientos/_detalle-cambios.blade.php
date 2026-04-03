{{--
    Partial: _detalle-cambios.blade.php
    Variables esperadas: $movimiento_detalle (MovimientoComputador | MovimientoDispositivo | MovimientoInsumo)
--}}
@php
    $labels = [
        'activo'               => 'Estado del Sistema',
        'estado_fisico'        => 'Estado Físico',
        'nombre'               => 'Nombre / Modelo',
        'bien_nacional'        => 'Bien Nacional',
        'serial'               => 'Serial',
        'marca_id'             => 'Marca',
        'tipo_dispositivo_id'  => 'Tipo de Dispositivo',
        'sistema_operativo_id' => 'Sistema Operativo',
        'procesador_id'        => 'Procesador',
        'gpu_id'               => 'GPU',
        'categoria_insumo_id'  => 'Categoría',
        'medida_actual'        => 'Stock / Cantidad',
        'medida_minima'        => 'Nivel de Alerta Mínimo',
        'unidad_medida'        => 'Unidad de Medida',
        'reutilizable'         => 'Es Reutilizable',
        'instalable_en_equipo' => 'Instalable en PC',
        'descripcion'          => 'Descripción',
        'departamento_id'      => 'Departamento',
        'trabajador_id'        => 'Trabajador',
        'ip'                   => 'Dirección IP',
        'mac'                  => 'Dirección MAC',
        'tipo_ram'             => 'Tipo de RAM',
        'tipo_conexion'        => 'Tipo de Conexión',
        'estado'               => 'Estado Operativo',
        'unidad_dvd'           => 'Unidad DVD',
        'fuente_poder'         => 'Fuente de Poder',
        'observaciones'        => 'Observaciones',
        'notas'                => 'Notas',
        'codigo'               => 'Código',
        'computador_id'        => 'Computador Asociado',
        'baja'                 => 'Baja del Sistema',
        'discos'               => 'Discos',
        'rams'                 => 'Memorias RAM',
        'puertos'              => 'Puertos',
    ];

    $booleanFields = [
        'activo', 'reutilizable', 'instalable_en_equipo',
        'unidad_dvd', 'fuente_poder', 'baja',
    ];

    $estadosFisicos = [
        'operativo'     => 'Operativo (Bueno)',
        'danado'        => 'Dañado',
        'en_reparacion' => 'En Reparación',
        'indeterminado' => 'Indeterminado',
    ];

    // Formatea un valor individualmente para mostrarlo de forma legible
    $fmtVal = function($val, $field) use ($booleanFields, $estadosFisicos) {
        if (is_null($val)) {
            return '<em class="text-muted">Sin valor</em>';
        }
        // Campos booleanos
        if (in_array($field, $booleanFields)) {
            $truthy = $val === true || $val === 1 || $val === '1';
            return $truthy
                ? '<span class="badge bg-success fw-normal">Sí / Activo</span>'
                : '<span class="badge bg-danger fw-normal">No / Inactivo</span>';
        }
        // Estado físico
        if ($field === 'estado_fisico' || $field === 'estado') {
            return '<span class="badge bg-secondary fw-normal">'
                . e($estadosFisicos[$val] ?? ucfirst($val))
                . '</span>';
        }
        // Arrays (discos, rams, puertos)
        if (is_array($val)) {
            if (empty($val)) return '<em class="text-muted">Sin elementos</em>';
            return '<span class="badge bg-secondary fw-normal">' . count($val) . ' elemento(s)</span>';
        }
        // Texto largo: truncar con tooltip
        $str = (string) $val;
        if (strlen($str) > 120) {
            return '<span title="' . e($str) . '" style="cursor:help">' . e(substr($str, 0, 120)) . '…</span>';
        }
        return e($str);
    };

    $skip   = ['id', 'created_at', 'updated_at', 'deleted_at'];
    $ant    = $movimiento_detalle->payload_anterior ?? [];
    $nuevo  = $movimiento_detalle->payload_nuevo    ?? [];
    $tipo   = $movimiento_detalle->tipo_operacion;

    // Calcular diferencias
    $cambios = [];
    $allKeys = array_unique(array_merge(array_keys($ant), array_keys($nuevo)));
    foreach ($allKeys as $key) {
        if (in_array($key, $skip)) continue;
        $vA = $ant[$key]   ?? null;
        $vN = $nuevo[$key] ?? null;
        // Comparación laxa para manejar 1/true, 0/false desde DB vs PHP
        if ((string)$vA !== (string)$vN) {
            $cambios[$key] = ['anterior' => $vA, 'nuevo' => $vN];
        }
    }
@endphp

{{-- ══ CASO: BAJA ══════════════════════════════════════════════════════════ --}}
@if($tipo === 'baja')
<div class="alert alert-danger d-flex align-items-start gap-3 mb-0">
    <i class="bi bi-exclamation-octagon-fill fs-4 mt-1 flex-shrink-0"></i>
    <div>
        <strong class="d-block mb-1">Solicitud de Baja Definitiva</strong>
        Se propone eliminar este elemento del inventario activo. El registro
        quedará marcado como inactivo y no aparecerá en las consultas operativas.
        <br>
        @if(!empty($ant))
        <small class="text-muted mt-1 d-block">
            El elemento seguirá existiendo en el historial del sistema.
        </small>
        @endif
    </div>
</div>

{{-- ══ CASO: TOGGLE_ACTIVO ════════════════════════════════════════════════ --}}
@elseif($tipo === 'toggle_activo')
@php
    $estadoAntes  = $cambios['activo']['anterior'] ?? ($ant['activo'] ?? null);
    $estadoDespues = $cambios['activo']['nuevo']   ?? ($nuevo['activo'] ?? null);
    $activandose = $estadoDespues === true || $estadoDespues === 1 || $estadoDespues === '1';
@endphp
<div class="alert {{ $activandose ? 'alert-success' : 'alert-secondary' }} d-flex align-items-start gap-3 mb-0">
    <i class="bi bi-toggles fs-4 mt-1 flex-shrink-0"></i>
    <div>
        <strong class="d-block mb-2">Cambio de Estatus</strong>
        <div class="d-flex align-items-center gap-3">
            <div class="text-center">
                <div class="small text-muted mb-1">Estado Actual</div>
                {!! $fmtVal($estadoAntes, 'activo') !!}
            </div>
            <i class="bi bi-arrow-right fs-5"></i>
            <div class="text-center">
                <div class="small text-muted mb-1">Estado Propuesto</div>
                {!! $fmtVal($estadoDespues, 'activo') !!}
            </div>
        </div>
    </div>
</div>

{{-- ══ CASO: ACTUALIZACION DE DATOS CON DIFF ══════════════════════════════ --}}
@else
    @if(empty($cambios))
    <p class="text-muted text-center py-3 mb-0">
        <i class="bi bi-info-circle me-1"></i>
        No se detectaron diferencias entre el estado anterior y el propuesto.
    </p>
    @else
    <p class="text-muted small mb-2">
        <i class="bi bi-pencil-square me-1"></i>
        Se propone modificar <strong>{{ count($cambios) }}</strong> campo(s):
    </p>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:30%">Campo</th>
                    <th style="width:35%"><i class="bi bi-dash-circle text-danger me-1"></i>Valor Anterior</th>
                    <th style="width:35%"><i class="bi bi-plus-circle text-success me-1"></i>Valor Propuesto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cambios as $field => $vals)
                <tr>
                    <td class="fw-semibold text-dark small">
                        {{ $labels[$field] ?? ucwords(str_replace('_', ' ', $field)) }}
                    </td>
                    <td class="small text-danger">{!! $fmtVal($vals['anterior'], $field) !!}</td>
                    <td class="small text-success">{!! $fmtVal($vals['nuevo'], $field) !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endif
