@extends('reports.layout')

@section('content')
    <div class="h1">Ficha Técnica de Dispositivo ({{ $disp->bien_nacional ?? 'S/BN' }})</div>


    <div class="h2">Información del Activo</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Bien Nacional:</th>
            <td style="width: 25%">{{ $disp->bien_nacional ?? 'N/A' }}</td>
            <th style="width: 25%">Serial:</th>
            <td style="width: 25%">{{ $disp->serial ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Marca:</th>
            <td>{{ $disp->marca->nombre ?? 'N/A' }}</td>
            <th>Tipo:</th>
            <td>{{ $disp->tipoDispositivo->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Modelo/Nombre:</th>
            <td colspan="3">{{ $disp->nombre }}</td>
        </tr>
        <tr>
            <th>Departamento:</th>
            <td>
                {{ $disp->departamento->nombre ?? 'STOCK / ALMACÉN' }}
                @if($disp->dependencia)
                    <br><span style="color: #666; font-size: 0.9em;">&#8627; {{ $disp->dependencia->nombre }}</span>
                @endif
            </td>
            <th>Responsable:</th>
            <td>{{ $disp->trabajador ? ($disp->trabajador->nombres . ' ' . $disp->trabajador->apellidos) : 'Sin Asignar' }}</td>
        </tr>
        <tr>
            <th>Estado Físico:</th>
            <td>{{ strtoupper(str_replace('_', ' ', $disp->estado)) }}</td>
            <th>Estatus:</th>
            <td>{{ $disp->activo ? 'ACTIVO' : 'INACTIVO' }}</td>
        </tr>
    </table>

    <div class="h2" style="margin-top: 30px;">Especificaciones y Conectividad</div>
    <table class="table">
        <tr>
            <th style="width: 25%">IP Address:</th>
            <td>{{ $disp->ip ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Conectado a:</th>
            <td>
                @if($disp->computador)
                    [BN: {{ $disp->computador->bien_nacional }}] {{ $disp->computador->nombre }}
                @else
                    Uso Independiente / Red
                @endif
            </td>
        </tr>
        <tr>
            <th>Puertos Disponibles:</th>
            <td>{{ $disp->puertos->pluck('nombre')->implode(', ') ?: 'Estándar' }}</td>
        </tr>
    </table>

    @if($disp->notas)
    <div class="h2" style="margin-top: 30px;">Notas Adicionales</div>
    <div style="font-size: 11px; color: #555; background: #f9f9f9; padding: 10px; border-radius: 5px;">
        {{ $disp->notas }}
    </div>
    @endif

    <div class="h2" style="margin-top: 30px;">Últimos Movimientos</div>
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Aprobador</th>
                <th>Estado</th>
                <th>Incidencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($disp->movimientos->take(5) as $mov)
                <tr>
                    <td>{{ $mov->created_at->format('d/m/Y') }}</td>
                    <td>{{ str_replace('_', ' ', strtoupper($mov->tipo_operacion)) }}</td>
                    <td>{{ $mov->aprobador->name ?? 'Sistema' }}</td>
                    <td>{{ ucfirst($mov->estado_workflow) }}</td>
                    <td>{{ $mov->incidencia_id ? '#' . str_pad($mov->incidencia_id, 5, '0', STR_PAD_LEFT) : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center">No hay movimientos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-box">
        <center>
            <div class="signature-line"></div>
            <div style="font-size: 10px;">Firma del Responsable de TI</div>
        </center>
    </div>
@endsection
