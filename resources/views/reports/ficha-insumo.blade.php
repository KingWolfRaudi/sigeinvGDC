@extends('reports.layout')

@section('content')
    <div class="h1">Ficha Técnica de Insumo / Herramienta</div>


    <div class="h2">Información General</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Nombre:</th>
            <td style="width: 25%">{{ $insumo->nombre }}</td>
            <th style="width: 25%">BN / Código:</th>
            <td style="width: 25%">{{ $insumo->bien_nacional }}</td>
        </tr>
        <tr>
            <th>Categoría:</th>
            <td>{{ $insumo->categoriaInsumo->nombre ?? 'N/A' }}</td>
            <th>Marca:</th>
            <td>{{ $insumo->marca->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Serial:</th>
            <td>{{ $insumo->serial ?? 'No Aplica' }}</td>
            <th>Estatus:</th>
            <td>{{ $insumo->activo ? 'ACTIVO' : 'INACTIVO' }}</td>
        </tr>
        <tr>
            <th>Departamento:</th>
            <td colspan="3">{{ $insumo->departamento->nombre ?? 'Sin Asignar' }}</td>
        </tr>
    </table>

    <div class="h2" style="margin-top: 30px;">Estado de Stock</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Stock Actual:</th>
            <td style="width: 25%">{{ floatval($insumo->medida_actual) }} {{ $insumo->unidad_medida }}</td>
            <th style="width: 25%">Stock Mínimo:</th>
            <td style="width: 25%">{{ floatval($insumo->medida_minima) }} {{ $insumo->unidad_medida }}</td>
        </tr>
        <tr>
            <th>Estado Físico:</th>
            <td>{{ strtoupper(str_replace('_', ' ', $insumo->estado_fisico)) }}</td>
            <th>Consición Alertable:</th>
            <td>{{ $insumo->medida_actual <= $insumo->medida_minima ? '¡BAJO STOCK!' : 'CRÍTICO NORMAL' }}</td>
        </tr>
    </table>

    <div class="h2" style="margin-top: 30px;">Propiedades y Configuración</div>
    <table class="table">
        <tr>
            <th style="width: 25%">¿Reutilizable?</th>
            <td style="width: 25%">{{ $insumo->reutilizable ? 'SÍ' : 'NO' }}</td>
            <th style="width: 25%">¿Instalable en PC?</th>
            <td style="width: 25%">{{ $insumo->instalable_en_equipo ? 'SÍ' : 'NO' }}</td>
        </tr>
        <tr>
            <th>Observaciones:</th>
            <td colspan="3">{{ $insumo->observaciones ?? 'Sin observaciones adicionales.' }}</td>
        </tr>
    </table>

    <div class="h2" style="margin-top: 30px;">Últimos Movimientos de Almacén</div>
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Aprobado por</th>
                <th>Incidencia</th>
                <th>Justificación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($insumo->movimientos()->where('estado_workflow', 'aprobado')->orWhere('estado_workflow', 'ejecutado_directo')->latest()->take(5)->get() as $mov)
                <tr>
                    <td>{{ $mov->created_at->format('d/m/Y') }}</td>
                    <td>{{ str_replace('_', ' ', strtoupper($mov->tipo_operacion)) }}</td>
                    <td>{{ $mov->cantidad_movida ?? '—' }}</td>
                    <td>{{ $mov->aprobador->name ?? 'Sistema' }}</td>
                    <td>{{ $mov->incidencia_id ? '#' . str_pad($mov->incidencia_id, 5, '0', STR_PAD_LEFT) : 'N/A' }}</td>
                    <td>{{ \Str::limit($mov->justificacion, 40) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-box" style="margin-top: 50px;">
        <center>
            <div class="signature-line"></div>
            <div style="font-size: 10px;">Firma del Responsable del Almacén TI</div>
        </center>
    </div>
@endsection
