@extends('reports.layout')

@section('content')
    <div class="h1">Ficha Técnica de Equipo ({{ $pc->bien_nacional }})</div>


    <div class="h2">Información del Activo</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Nombre Equipo:</th>
            <td colspan="3"><span style="color: #0d6efd; font-weight: bold;">{{ $pc->nombre_equipo }}</span></td>
        </tr>
        <tr>
            <th style="width: 25%">Bien Nacional:</th>
            <td style="width: 25%">{{ $pc->bien_nacional }}</td>
            <th style="width: 25%">Serial:</th>
            <td style="width: 25%">{{ $pc->serial }}</td>
        </tr>
        <tr>
            <th>Marca:</th>
            <td>{{ $pc->marca->nombre ?? 'N/A' }}</td>
            <th>Tipo de Computador:</th>
            <td>{{ $pc->tipo_computador ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Departamento:</th>
            <td>{{ $pc->departamento->nombre ?? 'N/A' }}</td>
            <th>Custodio Actual:</th>
            <td>{{ $pc->trabajador->nombre ?? 'Sin Asignar' }}</td>
        </tr>
        <tr>
            <th>Estado Físico:</th>
            <td>{{ $pc->estado_fisico ?? 'N/A' }}</td>
            <th>Estatus:</th>
            <td>{{ $pc->activo ? 'Operativo' : 'Inactivo' }}</td>
        </tr>
    </table>

    <div class="h2" style="margin-top: 30px;">Especificaciones de Hardware</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Procesador:</th>
            <td>{{ $pc->procesador->nombre_completo ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>GPU / Gráficos:</th>
            <td>{{ $pc->gpu->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Total RAM:</th>
            <td>{{ $pc->total_ram }} ({{ $pc->tipo_ram }})</td>
        </tr>
        <tr>
            <th>Almacenamiento:</th>
            <td>{{ $pc->total_almacenamiento }}</td>
        </tr>
        <tr>
            <th>Sistema Operativo:</th>
            <td>{{ $pc->sistemaOperativo->nombre ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="h2" style="margin-top: 30px;">Configuración de Red</div>
    <table class="table">
        <tr>
            <th style="width: 25%">MAC Address:</th>
            <td style="width: 25%">{{ $pc->mac ?? 'N/A' }}</td>
            <th style="width: 25%">IP Address:</th>
            <td style="width: 25%">{{ $pc->ip ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="h2" style="margin-top: 30px;">Últimos Movimientos</div>
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Aprobador</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pc->movimientos->take(5) as $mov)
                <tr>
                    <td>{{ $mov->created_at->format('d/m/Y') }}</td>
                    <td>{{ str_replace('_', ' ', strtoupper($mov->tipo_operacion)) }}</td>
                    <td>{{ $mov->aprobador->name ?? 'Sistema' }}</td>
                    <td>{{ ucfirst($mov->estado_workflow) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-box">
        <center>
            <div class="signature-line"></div>
            <div style="font-size: 10px;">Firma del Responsable de TI</div>
        </center>
    </div>
@endsection
