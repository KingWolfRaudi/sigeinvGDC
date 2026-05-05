@extends('reports.layout')

@section('content')
    <div style="text-align: center; margin-bottom: 20px;">
        <div class="h1" style="margin-bottom: 5px;">Auditoría de Usuario</div>
        <div style="color: #6c757d; font-size: 12px;">
            Fecha de Emisión: {{ now()->format('d/m/Y h:i A') }}
        </div>
    </div>

    <div class="h2">Perfil del Usuario</div>
    <table class="table" style="margin-bottom: 30px;">
        <tr>
            <th style="width: 25%">Nombre:</th>
            <td style="width: 25%; font-weight: bold; color: #0d6efd;">{{ $user->name }}</td>
            <th style="width: 25%">Usuario:</th>
            <td style="width: 25%">{{ $user->username }}</td>
        </tr>
        <tr>
            <th>Email:</th>
            <td>{{ $user->email }}</td>
            <th>Estado:</th>
            <td>{{ $user->activo ? 'Activo' : 'Inactivo' }}</td>
        </tr>
        <tr>
            <th>Roles:</th>
            <td colspan="3">
                @foreach($user->roles as $role)
                    {{ $role->name }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </td>
        </tr>
        @if($user->especialidad)
        <tr>
            <th>Especialidad Técnica:</th>
            <td colspan="3">{{ $user->especialidad->nombre }}</td>
        </tr>
        @endif
    </table>

    <div class="h2">
        Historial de Actividad
        <span style="font-size: 12px; font-weight: normal; color: #6c757d;">
            (Desde: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Inicio' }} 
            Hasta: {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'Hoy' }})
        </span>
    </div>

    <table class="table" style="font-size: 10px;">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="width: 20%;">Fecha / Hora</th>
                <th style="width: 20%;">Acción</th>
                <th style="width: 30%;">Módulo Afectado</th>
                <th style="width: 30%;">Detalles</th>
            </tr>
        </thead>
        <tbody>
            @forelse($actividades as $act)
                <tr>
                    <td style="color: #6c757d;">
                        <div>{{ $act->created_at->format('d/m/Y') }}</div>
                        <div>{{ $act->created_at->format('h:i:s A') }}</div>
                    </td>
                    <td>
                        @php
                            $color = match($act->description) {
                                'created' => '#198754',
                                'updated' => '#0dcaf0',
                                'deleted' => '#dc3545',
                                'Inicio de sesión exitoso' => '#0d6efd',
                                'Intento fallido de inicio de sesión' => '#dc3545',
                                'Cierre de sesión' => '#6c757d',
                                default => '#6c757d'
                            };
                        @endphp
                        <span style="color: {{ $color }}; font-weight: bold;">{{ ucfirst($act->description) }}</span>
                    </td>
                    <td style="font-weight: bold;">
                        {{ $act->subject_type ? class_basename($act->subject_type) : 'Sistema' }}
                        @if($act->subject_id)
                            <span style="color: #6c757d; font-weight: normal;">(ID: {{ $act->subject_id }})</span>
                        @endif
                    </td>
                    <td style="color: #6c757d; font-size: 9px;">
                        @php
                            $props = $act->properties;
                            $hasChanges = false;
                        @endphp
                        
                        @if(isset($props['attributes']) && count($props['attributes']) > 0)
                            @php $hasChanges = true; @endphp
                            <ul style="margin: 0; padding-left: 15px; list-style-type: square;">
                            @foreach($props['attributes'] as $key => $newValue)
                                @php
                                    if ($key === 'updated_at') continue;
                                    $oldValue = $props['old'][$key] ?? null;
                                    
                                    $formatValue = function($k, $v) {
                                        if ($v === null || $v === '') return 'N/A';
                                        if (is_array($v)) return json_encode($v);
                                        if (is_bool($v)) return $v ? 'Sí' : 'No';
                                        $lk = strtolower($k);
                                        if (in_array($lk, ['activo', 'solventado', 'disponible'])) return $v == 1 ? 'Sí' : 'No';
                                        if (str_ends_with($lk, '_id') || str_ends_with($lk, '_by')) return 'ID ' . $v;
                                        return (string)$v;
                                    };

                                    $oldStr = $formatValue($key, $oldValue);
                                    $newStr = $formatValue($key, $newValue);
                                    $showOld = isset($props['old']) && array_key_exists($key, $props['old']);
                                @endphp
                                <li style="margin-bottom: 2px;">
                                    <strong style="color: #495057;">{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> 
                                    @if($showOld)
                                        <span style="color: #dc3545; text-decoration: line-through;">{{ $oldStr }}</span> 
                                        <span style="color: #6c757d; margin: 0 4px;">&rarr;</span> 
                                    @endif
                                    <span style="color: #198754; font-weight: bold;">{{ $newStr }}</span>
                                </li>
                            @endforeach
                            </ul>
                        @elseif(isset($props['old']) && count($props['old']) > 0 && $act->description === 'deleted')
                            @php $hasChanges = true; @endphp
                            <span style="color: #dc3545; font-weight: bold;">Registro eliminado</span>
                        @elseif(isset($props['ip']))
                            @php $hasChanges = true; @endphp
                            IP: {{ $props['ip'] }}
                        @endif

                        @if(!$hasChanges)
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; color: #6c757d;">
                        No hay actividad en el rango seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="margin-top: 20px; font-size: 9px; color: #adb5bd; text-align: right;">
        Reporte generado automáticamente por SIGEINV GDC. Limitado a un máximo de 200 registros recientes.
    </div>
@endsection
