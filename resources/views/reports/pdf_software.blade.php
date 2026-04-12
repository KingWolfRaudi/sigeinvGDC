<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Software</title>
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
        .badge-libre { background-color: #d4edda; color: #155724; }
        .badge-privativo { background-color: #fff3cd; color: #856404; }
        .badge-activo { background-color: #d4edda; color: #155724; }
        .badge-inactivo { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Catálogo de Software</h2>
        <p>Sistema de Gestión de Inventario (SIGEINV)</p>
    </div>

    <div class="info">
        <strong>Filtro aplicado:</strong> {{ ucfirst($filtro) }}<br>
        <strong>Fecha de generación:</strong> {{ now()->format('d/m/Y h:i A') }}<br>
        <strong>Total registros:</strong> {{ count($softwares) }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%">Nombre del Programa</th>
                <th style="width: 10%">Arquitectura</th>
                <th style="width: 15%">Licencia</th>
                <th style="width: 20%">Serial / Clave</th>
                <th style="width: 25%">Descripción</th>
                <th style="width: 10%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($softwares as $soft)
                <tr>
                    <td><strong>{{ $soft->nombre_programa }}</strong></td>
                    <td>{{ $soft->arquitectura_programa ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower($soft->tipo_licencia) }}">
                            {{ $soft->tipo_licencia }}
                        </span>
                    </td>
                    <td>{{ $soft->serial ?? 'No especificado' }}</td>
                    <td>{{ $soft->descripcion_programa ?? 'Sin descripción' }}</td>
                    <td>
                        <span class="badge badge-{{ $soft->activo ? 'activo' : 'inactivo' }}">
                            {{ $soft->activo ? 'Activo' : 'Inactivo' }}
                        </span>
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
