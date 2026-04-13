@extends('reports.layout')

@section('content')
    <div class="h1">Ficha Técnica de Incidencia (Folio #{{ str_pad($incidencia->id, 5, '0', STR_PAD_LEFT) }})</div>

    <div class="h2">Información de la Solicitud</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Folio Interno:</th>
            <td style="width: 25%">#{{ str_pad($incidencia->id, 5, '0', STR_PAD_LEFT) }}</td>
            <th style="width: 25%">Fecha Reporte:</th>
            <td style="width: 25%">{{ $incidencia->created_at->format('d/m/Y h:i A') }}</td>
        </tr>
        <tr>
            <th>Departamento:</th>
            <td>{{ $incidencia->departamento->nombre ?? 'Sin Departamento' }}</td>
            <th>Estatus Final:</th>
            <td>
                @if($incidencia->cerrado)
                    <span style="color: #333; font-weight: bold;">Cerrado</span>
                @elseif($incidencia->solventado)
                    <span style="color: #155724; font-weight: bold;">Solventado</span>
                @else
                    <span style="color: #856404; font-weight: bold;">En Curso</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Solicitante:</th>
            <td colspan="3">
                @if($incidencia->trabajador)
                    {{ $incidencia->trabajador->nombres }} {{ $incidencia->trabajador->apellidos }}
                @else
                    {{ $incidencia->creator->name ?? 'Usuario Externo' }} (Reportado vía Portal)
                @endif
            </td>
        </tr>
    </table>

    <div class="h2" style="margin-top: 30px;">Detalles del Problema</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Tipo / Categoría:</th>
            <td>{{ $incidencia->problema->nombre }}</td>
        </tr>
        <tr>
            <th>Descripción:</th>
            <td style="background-color: #fcfcfc; min-height: 100px; padding: 15px; border: 1px solid #eee;">
                {{ $incidencia->descripcion }}
            </td>
        </tr>
    </table>

    @if($incidencia->modelo_id)
    <div class="h2" style="margin-top: 30px;">Equipo / Activo Relacionado</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Activo:</th>
            <td>
                @if($incidencia->modelo)
                    {{ class_basename($incidencia->modelo_type) }}: {{ $incidencia->modelo->nombre ?? $incidencia->modelo->bien_nacional }}
                @else
                    N/A
                @endif
            </td>
        </tr>
    </table>
    @endif

    <div class="h2" style="margin-top: 30px;">Dictamen Técnico y Resolución</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Técnico Asignado:</th>
            <td>{{ $incidencia->tecnico->name ?? 'Pendiente por Asignar' }}</td>
        </tr>
        <tr>
            <th>Fecha Resolución:</th>
            <td>{{ $incidencia->updated_at->format('d/m/Y h:i A') }}</td>
        </tr>
        <tr>
            <th>¿Amerita Movimiento?:</th>
            <td>
                @if($incidencia->amerita_movimiento)
                    <span style="color: #d39e00; font-weight: bold;">SÍ (Pendiente de trámite administrativo)</span>
                @else
                    No
                @endif
            </td>
        </tr>
        <tr>
            <th>Nota de Resolución:</th>
            <td style="background-color: #fcfcfc; min-height: 100px; padding: 15px; border: 1px solid #eee;">
                {{ $incidencia->nota_resolucion ?: 'No se ha registrado nota de resolución aún.' }}
            </td>
        </tr>
    </table>

    {{-- Nueva Sección: Trámite Administrativo Relacionado --}}
    @php
        $movRelacionado = $incidencia->movimientoComputador 
                         ?? $incidencia->movimientoDispositivo 
                         ?? $incidencia->movimientoInsumo;
    @endphp

    @if($movRelacionado)
    <div class="h2" style="margin-top: 30px;">Resolución Administrativa (Movimiento de Inventario)</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Folio Movimiento:</th>
            <td style="width: 25%">#{{ str_pad($movRelacionado->id, 5, '0', STR_PAD_LEFT) }}</td>
            <th style="width: 25%">Tipo Operación:</th>
            <td style="width: 25%">{{ str_replace('_', ' ', strtoupper($movRelacionado->tipo_operacion)) }}</td>
        </tr>
        <tr>
            <th>Estatus Movimiento:</th>
            <td>{{ strtoupper($movRelacionado->estado_workflow) }}</td>
            <th>Fecha Registro:</th>
            <td>{{ $movRelacionado->created_at->format('d/m/Y h:i A') }}</td>
        </tr>
        <tr>
            <th>Justificación:</th>
            <td colspan="3" style="font-size: 10px; color: #555;">
                {{ $movRelacionado->justificacion }}
            </td>
        </tr>
    </table>
    @endif

    <div class="signature-box" style="margin-top: 100px;">
        <table style="width: 100%; border: 0;">
            <tr>
                <td style="width: 45%; border: 0;">
                    <center>
                        <div class="signature-line"></div>
                        <div style="font-size: 10px;">{{ $incidencia->tecnico->name ?? 'Firma del Técnico' }}</div>
                        <div style="font-size: 8px;">Personal de TI / Resolutor</div>
                    </center>
                </td>
                <td style="width: 10%; border: 0;"></td>
                <td style="width: 45%; border: 0;">
                    <center>
                        <div class="signature-line"></div>
                        <div style="font-size: 10px;">Firma del Solicitante</div>
                        <div style="font-size: 8px;">Conformidad del Servicio</div>
                    </center>
                </td>
            </tr>
        </table>
    </div>
@endsection
