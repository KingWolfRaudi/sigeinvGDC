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
        'tipo_computador'      => 'Tipo de Computador',
        'tipo_dispositivo_id'  => 'Tipo de Dispositivo (ID)',
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
        'codigo'               => 'Bien Nacional',
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
        // Nulls/Empty logic
        $isEmpty = function($val) {
            return is_null($val) || $val === '' || (is_array($val) && count($val) === 0);
        };

        if ($isEmpty($a) && $isEmpty($b)) return true;

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
        if (is_array($a) || is_array($b)) {
            // Si uno no es array, ya comparamos arriba si eran "ambos vacíos".
            // Si llegamos aquí y uno no es array, son diferentes.
            if (!is_array($a) || !is_array($b)) return false;

            if ($field === 'discos' || $field === 'rams') {
                 $normalize = function($list, $f) {
                     return array_map(function($item) use ($f) {
                         $c = str_replace(['GB', ' '], '', (string)($item['capacidad'] ?? ''));
                         return $f === 'discos' 
                            ? ['capacidad' => $c, 'tipo' => $item['tipo'] ?? '']
                            : ['capacidad' => $c, 'slot' => (int)($item['slot'] ?? 0)];
                     }, $list);
                 };
                 $normA = $normalize($a, $field);
                 $normB = $normalize($b, $field);
                 usort($normA, fn($x, $y) => json_encode($x) <=> json_encode($y));
                 usort($normB, fn($x, $y) => json_encode($x) <=> json_encode($y));
                 return json_encode($normA) === json_encode($normB);
            }

            if ($field === 'puertos') {
                $vA = array_values($a); sort($vA);
                $vB = array_values($b); sort($vB);
                return $vA == $vB;
            }

            return json_encode($a) === json_encode($b);
        }

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
            if (empty($val)) return '<em class="text-muted">Sin elementos</em>';
            
            if ($field === 'rams') {
                return '<div class="small">' . implode('<br>', array_map(fn($r) => "Slot ".($r['slot'] ?? '?').": ".($r['capacidad'] ?? '0')."GB", $val)) . '</div>';
            }
            if ($field === 'discos') {
                return '<div class="small">' . implode('<br>', array_map(fn($d) => ($d['capacidad'] ?? '0')."GB (".($d['tipo'] ?? 'Desconocido').")", $val)) . '</div>';
            }
            if ($field === 'puertos') {
                // Si es solo un array de IDs, no podemos mostrar los nombres aquí fácilmente sin consulta extra.
                // Pero en el panel de movimientos usualmente se guarda el array de IDs.
                return '<span class="badge bg-secondary fw-normal">' . count($val) . ' puertos seleccionados</span>';
            }

            return '<span class="badge bg-secondary fw-normal">' . count($val) . ' elemento(s)</span>';
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

    // ── Nombres para IDs (Departamento, Trabajador, etc.) ──────────────────
    $resolverNombre = function($id, string $field) {
        if (!$id) return null;
        try {
            return match($field) {
                'departamento_id' => \App\Models\Departamento::find($id)?->nombre,
                'trabajador_id'   => \App\Models\Trabajador::find($id)?->nombres_apellidos,
                'marca_id'        => \App\Models\Marca::find($id)?->nombre,
                'categoria_insumo_id' => \App\Models\CategoriaInsumo::find($id)?->nombre,
                'dispositivo_id'  => \App\Models\Dispositivo::find($id)?->nombre,
                'computador_id'   => \App\Models\Computador::find($id)?->nombre_equipo,
                default => null,
            };
        } catch (\Exception $e) { return "ID: $id"; }
    };

    // ── Calcular diff real ─────────────────────────────────────────────────
    $camposAMostrar = [];
    if (!empty($ant)) {
        foreach ($nuevo as $field => $valNuevo) {
            $valAnterior = $ant[$field] ?? null;
            if (!$sonIguales($valAnterior, $valNuevo, $field)) {
                
                // Intentar resolver nombres para IDs si aplica
                if (str_ends_with($field, '_id')) {
                    $valAnterior = $resolverNombre($valAnterior, $field) ?? $valAnterior;
                    $valNuevo    = $resolverNombre($valNuevo, $field) ?? $valNuevo;
                }

                $camposAMostrar[$field] = ['anterior' => $valAnterior, 'nuevo' => $valNuevo];
            }
        }
    } else {
        foreach ($nuevo as $field => $valNuevo) {
            $valFinal = str_ends_with($field, '_id') ? ($resolverNombre($valNuevo, $field) ?? $valNuevo) : $valNuevo;
            $camposAMostrar[$field] = ['anterior' => null, 'nuevo' => $valFinal];
        }
    }
@endphp

{{-- ════ CAMBIO DE STOCK (ALERTA VISUAL) ══════════════════════════════════ --}}
@if(in_array($tipo, ['entrada_stock', 'salida_consumo', 'prestamo', 'devolucion']) && $movimiento_detalle->cantidad_movida > 0)
@php
    $stockAntes = (int)($ant['medida_actual'] ?? $movimiento_detalle->insumo->medida_actual);
    $cantidad   = (int)$movimiento_detalle->cantidad_movida;
    $stockDespues = match($tipo) {
        'entrada_stock', 'devolucion' => $stockAntes + $cantidad,
        'salida_consumo', 'prestamo' => max(0, $stockAntes - $cantidad),
        default => $stockAntes
    };
    $esAumento = in_array($tipo, ['entrada_stock', 'devolucion']);
@endphp
<div class="card border-{{ $esAumento ? 'success' : 'warning' }} mb-3 bg-light shadow-sm">
    <div class="card-body py-2 px-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.65rem;">Impacto en Existencias</small>
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5 fw-bold text-dark">{{ $stockAntes }}</span>
                    <i class="bi bi-arrow-right text-muted"></i>
                    <span class="fs-4 fw-bold text-{{ $esAumento ? 'success' : 'danger' }}">{{ $stockDespues }}</span>
                    <span class="badge bg-{{ $esAumento ? 'success' : 'danger' }} ms-2">
                        {{ $esAumento ? '+' : '-' }}{{ $cantidad }}
                    </span>
                </div>
            </div>
            <div class="text-end">
                <i class="bi bi-box-seam fs-2 opacity-25"></i>
            </div>
        </div>
    </div>
</div>
@endif

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

{{-- ════ CAMBIO DE ESTADO ════════════════════════════════════════════════════ --}}
@elseif($tipo === 'toggle_activo')
@php
    $estadoAntes   = $ant['activo']   ?? null;
    $estadoDespues = $nuevo['activo'] ?? null;
    $activando     = $estadoDespues === true || $estadoDespues === 1 || $estadoDespues === '1';
@endphp
<div class="alert {{ $activando ? 'alert-success' : 'alert-secondary' }} d-flex align-items-start gap-3 mb-0">
    <i class="bi bi-toggles fs-4 mt-1 flex-shrink-0"></i>
    <div>
        <strong class="d-block mb-2">Cambio de Estado Propuesto</strong>
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
