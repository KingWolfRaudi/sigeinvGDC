<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Solicitudes de Perfil</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #000; text-transform: uppercase; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f2f2f2; border: 1px solid #ccc; padding: 8px; text-align: left; }
        td { border: 1px solid #ccc; padding: 8px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9px; border-top: 1px solid #ccc; padding-top: 5px; }
        .badge { padding: 3px 7px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-pendiente { background-color: #fff3cd; color: #856404; }
        .badge-aprobado { background-color: #d4edda; color: #155724; }
        .badge-rechazado { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Solicitudes de Perfil</h2>
        <p>Sistema de Gestión de Inventario (SIGEINV)</p>
    </div>

    <div class="info">
        <strong>Filtro aplicado:</strong> {{ ucfirst($filtro) }}<br>
        <strong>Fecha de generación:</strong> {{ now()->format('d/m/Y h:i A') }}<br>
        <strong>Total registros:</strong> {{ count($solicitudes) }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Usuario</th>
                <th style="width: 10%">Tipo</th>
                <th style="width: 25%">Cambio Solicitado</th>
                <th style="width: 10%">Estado</th>
                <th style="width: 15%">Fecha Solicitud</th>
                <th style="width: 25%">Revisión / Auditoría</th>
            </tr>
        </thead>
        <tbody>
            @foreach($solicitudes as $sol)
                <tr>
                    <td>
                        <strong>{{ $sol->user->name }}</strong><br>
                        <small>@ {{ $sol->user->username }}</small>
                    </td>
                    <td>{{ ucfirst($sol->tipo) }}</td>
                    <td>
                        @if($sol->tipo === 'password')
                            (Hash de Nueva Contraseña)
                        @else
                            {{ $sol->valor_nuevo }}
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $sol->estado }}">
                            {{ $sol->estado }}
                        </span>
                    </td>
                    <td>{{ $sol->created_at->format('d/m/Y h:i A') }}</td>
                    <td>
                        @if($sol->estado !== 'pendiente')
                            <strong>Por:</strong> {{ $sol->revisor->name ?? 'N/A' }}<br>
                            @if($sol->estado === 'rechazado')
                                <strong>Motivo:</strong> {{ $sol->motivo_rechazo }}
                            @endif
                        @else
                            <em>En espera de revisión</em>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado automáticamente por SIGEINV el {{ now()->format('d/m/Y H:i') }} - Página 1
    </div>
</body>
</html>
