<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes - Arrendador</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/arrendador/solicitudes.css') }}" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="pagina">
    <header class="cabecera">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Solicitudes de alquiler</h1>
            <p class="subtitulo">Revisa y decide las solicitudes de tus propiedades.</p>
        </div>
        <div class="acciones-cabecera">
            <a class="btn-volver" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">Volver al dashboard</a>
            <a class="btn-volver" href="{{ route('logout') }}">Cerrar sesion</a>
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
                        $estado = strtolower($solicitud->estado_solicitud_alquiler);
                    @endphp
                    <tr id="fila-{{ $solicitud->id_solicitud_alquiler }}">
                        <td>
                            <strong>{{ $solicitud->titulo_propiedad }}</strong><br>
                            <span class="muted">{{ $solicitud->direccion_propiedad }}</span>
                        </td>
                        <td>
                            {{ $solicitud->nombre_inquilino }}<br>
                            <span class="muted">{{ $solicitud->email_inquilino }}</span>
                        </td>
                        <td>
                            {{ $solicitud->fecha_inicio_solicitud_alquiler }}<br>
                            <span class="muted">Sin fin definido</span>
                        </td>
                        <td>
                            <span class="estado estado-{{ $estado }}" id="estado-{{ $solicitud->id_solicitud_alquiler }}">{{ ucfirst($estado) }}</span>
                        </td>
                        <td>{{ $solicitud->creado_solicitud_alquiler ? \Carbon\Carbon::parse($solicitud->creado_solicitud_alquiler)->format('d/m/Y') : '-' }}</td>
                        <td>
                            <div class="acciones" data-acciones="{{ $solicitud->id_solicitud_alquiler }}" data-estado="{{ $estado }}" data-arrendador="{{ $arrendadorId }}">
                                @if ($estado === 'activo')
                                    <button class="btn-ver" data-ver="{{ $solicitud->id_solicitud_alquiler }}">Ver</button>
                                @elseif ($estado === 'pendiente')
                                    <button class="btn-ver" data-ver="{{ $solicitud->id_solicitud_alquiler }}">Ver</button>
                                    <button class="btn-editar" data-editar="{{ $solicitud->id_solicitud_alquiler }}">Editar</button>
                                    <button class="btn-aprobar" data-aprobar="{{ $solicitud->id_solicitud_alquiler }}">Aceptar</button>
                                    <button class="btn-rechazar" data-rechazar="{{ $solicitud->id_solicitud_alquiler }}">Rechazar</button>
                                @elseif ($estado === 'rechazado')
                                    <button class="btn-ver" data-ver="{{ $solicitud->id_solicitud_alquiler }}">Ver</button>
                                    <button class="btn-aprobar" data-aprobar="{{ $solicitud->id_solicitud_alquiler }}">Aceptar</button>
                                @else
                                    <button class="btn-ver" data-ver="{{ $solicitud->id_solicitud_alquiler }}">Ver</button>
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

<script src="{{ asset('js/arrendador/solicitudes.js') }}"></script>
</body>
</html>
