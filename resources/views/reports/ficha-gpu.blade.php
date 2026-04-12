@extends('reports.layout')

@section('content')
    <div class="h1">Ficha Técnica de Componente: GPU</div>


    <div class="h2">Información de la Tarjeta de Video</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Marca:</th>
            <td style="width: 25%">{{ $gpu->marca->nombre ?? 'N/A' }}</td>
            <th style="width: 25%">Modelo:</th>
            <td style="width: 25%">{{ $gpu->modelo }}</td>
        </tr>
        <tr>
            <th>Memoria:</th>
            <td>{{ $gpu->memoria ?? 'N/A' }} {{ $gpu->tipo_memoria ?? '' }}</td>
            <th>Ancho de Bus:</th>
            <td>{{ $gpu->bus ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Frecuencia:</th>
            <td>{{ $gpu->frecuencia ?? 'N/A' }}</td>
            <th>Estado:</th>
            <td>{{ $gpu->activo ? 'Activo' : 'Inactivo' }}</td>
        </tr>
    </table>

    <div class="h2" style="margin-top: 30px;">Puertos de Conexión Disponibles</div>
    <div style="margin-top: 10px;">
        @forelse($gpu->puertos as $puerto)
            <span style="display: inline-block; padding: 5px 10px; background-color: #f0f0f0; border-radius: 4px; margin-right: 5px; margin-bottom: 5px; font-size: 12px;">
                {{ $puerto->nombre }}
            </span>
        @empty
            <p class="text-muted">No se han registrado puertos específicos para este modelo.</p>
        @endforelse
    </div>

    <div class="h2" style="margin-top: 30px;">Fechas de Registro</div>
    <table class="table">
        <tr>
            <th style="width: 25%">Creado el:</th>
            <td>{{ $gpu->created_at->format('d/m/Y H:i A') }}</td>
        </tr>
        <tr>
            <th>Última actualización:</th>
            <td>{{ $gpu->updated_at->format('d/m/Y H:i A') }}</td>
        </tr>
    </table>

    <div class="signature-box" style="margin-top: 50px;">
        <center>
            <div class="signature-line"></div>
            <div style="font-size: 10px;">Validación de Inventario GDC</div>
        </center>
    </div>
@endsection
