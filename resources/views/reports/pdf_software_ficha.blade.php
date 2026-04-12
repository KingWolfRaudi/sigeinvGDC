<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ficha del Software</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #000; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; width: 30%; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; border-top: 1px solid #ccc; padding-top: 10px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .badge-libre { color: #155724; }
        .badge-privativo { color: #856404; }
        .badge-activo { color: #155724; }
        .badge-inactivo { color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Ficha de Software Individual</h2>
        <p>Sistema de Gestión de Inventario Tecnológico (SIGEINV)</p>
    </div>

    <table>
        <tbody>

            <tr>
                <th>Nombre del Programa</th>
                <td><strong>{{ $software->nombre_programa }}</strong></td>
            </tr>
            <tr>
                <th>Arquitectura</th>
                <td>{{ $software->arquitectura_programa ?? 'Universal / No Aplica' }}</td>
            </tr>
            <tr>
                <th>Tipo de Licencia</th>
                <td>
                    <span class="badge badge-{{ strtolower($software->tipo_licencia) }}">
                        {{ $software->tipo_licencia }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Serial / Clave de Activación</th>
                <td>
                    @if($software->serial)
                        <code>{{ $software->serial }}</code>
                    @else
                        <em>No especificado / No requiere</em>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Estado Actual</th>
                <td>
                    <span class="badge badge-{{ $software->activo ? 'activo' : 'inactivo' }}">
                        {{ $software->activo ? 'Activo (En Operación)' : 'Inactivo (Deprecado)' }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Descripción / Notas</th>
                <td>{{ $software->descripcion_programa ?? 'No existen notas registradas para este software.' }}</td>
            </tr>
            <tr>
                <th>Fecha de Integración al Sistema</th>
                <td>{{ $software->created_at->format('d/m/Y - h:i A') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
