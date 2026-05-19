@extends('layouts.arrendador')

@section('titulo', 'Solicitudes - Arrendador')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/arrendador/solicitudes.css') }}" />
@endsection

@section('content')
<div class="pagina" style="padding-top: 0;">
    <header class="cabecera" style="padding-top: 0; padding-bottom: 20px;">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Solicitudes de alquiler</h1>
            <p class="subtitulo">Revisa y decide las solicitudes de tus propiedades.</p>
        </div>
    </header>

    <section class="kpis">
        <article class="kpi"><span>{{ $totales['total'] }}</span><small>Total</small></article>
        <article class="kpi"><span>{{ $totales['pendientes'] }}</span><small>Pendientes</small></article>
        <article class="kpi"><span>{{ $totales['activos'] }}</span><small>Aprobadas</small></article>
        <article class="kpi"><span>{{ $totales['rechazados'] }}</span><small>Rechazadas</small></article>
    </section>

    <section class="panel">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Propiedad</th>
                    <th>Solicitante</th>
                    <th>Periodo</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($solicitudes as $solicitud)
                    @php
                        $estado = strtolower($solicitud->estado_alquiler);
                    @endphp
                    <tr id="fila-{{ $solicitud->id_alquiler }}">
                        <td>
                            <strong>{{ $solicitud->titulo_propiedad }}</strong><br>
                            <span class="muted">{{ $solicitud->direccion_propiedad }}</span>
                        </td>
                        <td>
                            {{ $solicitud->nombre_inquilino }}<br>
                            <span class="muted">{{ $solicitud->email_inquilino }}</span>
                        </td>
                        <td>
                            {{ $solicitud->fecha_inicio_alquiler }}<br>
                            <span class="muted">{{ $solicitud->fecha_fin_alquiler ?? 'Sin fin definido' }}</span>
                        </td>
                        <td>
                            <span class="estado estado-{{ $estado }}" id="estado-{{ $solicitud->id_alquiler }}">{{ ucfirst($estado) }}</span>
                        </td>
                        <td>{{ $solicitud->creado_alquiler ? \Carbon\Carbon::parse($solicitud->creado_alquiler)->format('d/m/Y') : '-' }}</td>
                        <td>
                            <div class="acciones" data-acciones="{{ $solicitud->id_alquiler }}" data-estado="{{ $estado }}" data-arrendador="{{ $arrendadorId }}">
                                @if ($estado === 'activo')
                                    <button class="btn-ver" data-ver="{{ $solicitud->id_alquiler }}">Ver</button>
                                    <button class="btn-eliminar" data-eliminar="{{ $solicitud->id_alquiler }}">Eliminar</button>
                                @else
                                    <button class="btn-ver" data-ver="{{ $solicitud->id_alquiler }}">Ver</button>
                                    <button class="btn-editar" data-editar="{{ $solicitud->id_alquiler }}">Editar</button>
                                    <button class="btn-eliminar" data-eliminar="{{ $solicitud->id_alquiler }}">Eliminar</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No hay solicitudes para este arrendador.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="paginacion">{{ $solicitudes->withQueryString()->links() }}</div>
    </section>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/arrendador/solicitudes.js') }}"></script>
@endsection
