@extends('reports.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 20px;">
        <div class="h1" style="margin-bottom: 5px;">Reporte de Auditoría de Logs</div>
        <div style="color: #6c757d; font-size: 12px;">
            Fecha de Emisión: {{ now()->format('d/m/Y h:i A') }}
        </div>
        
        <div style="font-size: 11px; margin-top: 10px; color: #495057;">
            @if(!empty($filters['dateFrom']))
                <span style="margin-right: 15px;"><strong>Desde:</strong> {{ \Carbon\Carbon::parse($filters['dateFrom'])->format('d/m/Y') }}</span>
            @endif
            @if(!empty($filters['dateTo']))
                <span style="margin-right: 15px;"><strong>Hasta:</strong> {{ \Carbon\Carbon::parse($filters['dateTo'])->format('d/m/Y') }}</span>
            @endif
            @if(!empty($filters['searchUser']))
                <span style="margin-right: 15px;"><strong>Usuario/Responsable:</strong> {{ $filters['searchUser'] }}</span>
            @endif
            @if(!empty($filters['searchModule']))
                <span><strong>Módulo/Acción:</strong> {{ $filters['searchModule'] }}</span>
            @endif
        </div>
    </div>

    <table class="table" style="font-size: 10px;">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="width: 5%; text-align: center;">ID</th>
                <th style="width: 25%;">Responsable</th>
                <th style="width: 15%;">Acción</th>
                <th style="width: 25%;">Módulo Afectado</th>
                <th style="width: 15%;">Fecha / Hora</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="text-align: center; font-weight: bold; color: #6c757d;">#{{ $log->id }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $log->causer->name ?? 'Sistema' }}</div>
                        <div style="color: #6c757d; font-size: 9px;">{{ $log->causer->email ?? 'Automático' }}</div>
                    </td>
                    <td>
                        @php
                            $color = match($log->description) {
                                'created' => '#198754',
                                'updated' => '#0dcaf0',
                                'deleted' => '#dc3545',
                                'restored' => '#ffc107',
                                default => '#6c757d'
                            };
                        @endphp
                        <span style="color: {{ $color }}; font-weight: bold;">{{ ucfirst($log->description) }}</span>
                    </td>
                    <td>
                        <span style="font-weight: bold;">{{ class_basename($log->subject_type) }}</span>
                        @if($log->subject_id)
                            <span style="color: #6c757d;"> (ID: {{ $log->subject_id }})</span>
                        @endif
                    </td>
                    <td style="color: #6c757d;">
                        <div>{{ $log->created_at->format('d/m/Y') }}</div>
                        <div>{{ $log->created_at->format('h:i:s A') }}</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #6c757d;">
                        No se encontraron registros de actividad con los filtros proporcionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="margin-top: 20px; font-size: 9px; color: #adb5bd; text-align: right;">
        Reporte generado automáticamente por SIGEINV GDC. Limitado a los primeros 500 registros.
    </div>
@endsection
