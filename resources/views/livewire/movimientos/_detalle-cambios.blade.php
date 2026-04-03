{{--
    Partial: _detalle-cambios.blade.php
    Variable esperada: $movimiento_detalle

    Estrategia: independientemente de si payload_nuevo es el diff puro (registros nuevos)
    o el payload completo (registros anteriores al fix), siempre se compara contra
    payload_anterior y solo se muestran los campos que REALMENTE cambiaron.
--}}
@php
    $labels = [
        'activo'               => 'Estado del Sistema',
        'estado_fisico'        => 'Estado Físico',
        'estado'               => 'Estado Operativo',
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

    // Campos que deben compararse como booleanos
    $boolFields = [
        'activo', 'reutilizable', 'instalable_en_equipo',
        'unidad_dvd', 'fuente_poder', 'baja',
    ];

    // Campos numéricos decimales (para comparar con (float) y evitar "1.0000" ≠ "1.00")
    $numericFields = ['medida_actual', 'medida_minima'];

    // Función de comparación por tipo — robusta ante distintos formatos
    $sonIguales = function($a, $b, string $field) use ($boolFields, $numericFields): bool {
        // Nulls: ambos nulos → iguales
        if (is_null($a) && is_null($b)) return true;

        // Booleanos (true/false vs 1/0 vs "1"/"0")
        if (in_array($field, $boolFields)) {
            return (bool)$a === (bool)$b;
        }

        // Decimales/numéricos (evita "1.0000" ≠ "1.00")
        if (in_array($field, $numericFields)) {
            if (is_null($a) || is_null($b)) return false;
            return abs((float)$a - (float)$b) < 0.0001;
        }

        // Arrays (discos, rams, puertos)
        if (is_array($a) && is_array($b)) {
            return json_encode($a) === json_encode($b);
        }
        if (is_array($a) || is_array($b)) return false;

        // Todo lo demás: comparación de strings (null → cadena vacía)
        return trim((string)($a ?? '')) === trim((string)($b ?? ''));
    };

    // Formatea un valor para mostrarlo de forma legible al usuario
    $fmt = function($val, string $field) use ($boolFields): string {
        if (is_null($val)) return '<em class="text-muted">—</em>';

        if (in_array($field, $boolFields)) {
            $v = $val === true || $val === 1 || $val === '1';
            return $v
                ? '<span class="badge bg-success fw-normal">Sí / Activo</span>'
                : '<span class="badge bg-danger fw-normal">No / Inactivo</span>';
        }

        if ($field === 'estado_fisico' || $field === 'estado') {
            $map = ['operativo' => 'Operativo', 'danado' => 'Dañado',
                    'en_reparacion' => 'En Reparación', 'indeterminado' => 'Indeterminado'];
            return '<span class="badge bg-secondary fw-normal">'
                . e($map[$val] ?? ucfirst(str_replace('_', ' ', $val))) . '</span>';
        }

        if (is_array($val)) {
            return empty($val)
                ? '<em class="text-muted">Sin elementos</em>'
                : '<span class="badge bg-secondary fw-normal">' . count($val) . ' elemento(s)</span>';
        }

        $str = (string)$val;
        return strlen($str) > 120
            ? '<span title="' . e($str) . '" style="cursor:help">' . e(substr($str, 0, 120)) . '…</span>'
            : e($str);
    };

    // ── Payload ────────────────────────────────────────────────────────────
    $tipo  = $movimiento_detalle->tipo_operacion;
    $ant   = $movimiento_detalle->payload_anterior ?? [];
    $nuevo = $movimiento_detalle->payload_nuevo    ?? [];

    // ── Calcular diff real ─────────────────────────────────────────────────
    // Funciona para borradores nuevos (payload_nuevo = diff puro) Y para
    // borradores antiguos (payload_nuevo = registro completo). En ambos casos
    // comparamos cada campo contra payload_anterior y solo mostramos cambios reales.
    $camposAMostrar = [];
    if (!empty($ant)) {
        foreach ($nuevo as $field => $valNuevo) {
            $valAnterior = $ant[$field] ?? null;
            if (!$sonIguales($valAnterior, $valNuevo, $field)) {
                $camposAMostrar[$field] = ['anterior' => $valAnterior, 'nuevo' => $valNuevo];
            }
        }
    } else {
        // Sin snapshot anterior: mostrar todo lo que hay en payload_nuevo
        foreach ($nuevo as $field => $valNuevo) {
            $camposAMostrar[$field] = ['anterior' => null, 'nuevo' => $valNuevo];
        }
    }
@endphp

{{-- ════ BAJA ════════════════════════════════════════════════════════════ --}}
@if($tipo === 'baja')
<div class="alert alert-danger d-flex align-items-start gap-3 mb-0">
    <i class="bi bi-exclamation-octagon-fill fs-4 mt-1 flex-shrink-0"></i>
    <div>
        <strong class="d-block mb-1">Solicitud de Baja Definitiva</strong>
        Se propone retirar este elemento del inventario activo. El registro
        quedará marcado como inactivo y no aparecerá en las consultas operativas.
        <small class="text-muted d-block mt-1">El historial de trazabilidad se conservará.</small>
    </div>
</div>

{{-- ════ TOGGLE ACTIVO ════════════════════════════════════════════════════ --}}
@elseif($tipo === 'toggle_activo')
@php
    $estadoAntes   = $ant['activo']   ?? null;
    $estadoDespues = $nuevo['activo'] ?? null;
    $activando     = $estadoDespues === true || $estadoDespues === 1 || $estadoDespues === '1';
@endphp
<div class="alert {{ $activando ? 'alert-success' : 'alert-secondary' }} d-flex align-items-start gap-3 mb-0">
    <i class="bi bi-toggles fs-4 mt-1 flex-shrink-0"></i>
    <div>
        <strong class="d-block mb-2">Cambio de Estatus Propuesto</strong>
        <div class="d-flex align-items-center gap-3">
            <div class="text-center">
                <div class="small text-muted mb-1">Estado Actual</div>
                {!! $fmt($estadoAntes, 'activo') !!}
            </div>
            <i class="bi bi-arrow-right fs-5"></i>
            <div class="text-center">
                <div class="small text-muted mb-1">Estado Propuesto</div>
                {!! $fmt($estadoDespues, 'activo') !!}
            </div>
        </div>
    </div>
</div>

{{-- ════ ACTUALIZACION (DIFF FILTRADO) ════════════════════════════════════ --}}
@else
    @if(empty($camposAMostrar))
    <div class="alert alert-secondary mb-0 py-2">
        <i class="bi bi-info-circle me-1"></i>
        No se detectaron diferencias entre el estado actual y el propuesto.
    </div>
    @else
    <p class="text-muted small mb-2">
        <i class="bi bi-pencil-square me-1"></i>
        Se propone modificar <strong>{{ count($camposAMostrar) }}</strong> campo(s):
    </p>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:30%">Campo</th>
                    <th style="width:35%">
                        <i class="bi bi-dash-circle text-danger me-1"></i>Valor Anterior
                    </th>
                    <th style="width:35%">
                        <i class="bi bi-plus-circle text-success me-1"></i>Valor Propuesto
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($camposAMostrar as $field => $vals)
                <tr>
                    <td class="fw-semibold text-dark small">
                        {{ $labels[$field] ?? ucwords(str_replace('_', ' ', $field)) }}
                    </td>
                    <td class="small text-danger">
                        {!! $fmt($vals['anterior'], $field) !!}
                    </td>
                    <td class="small text-success">
                        {!! $fmt($vals['nuevo'], $field) !!}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endif
