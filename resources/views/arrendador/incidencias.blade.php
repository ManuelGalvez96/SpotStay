<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidencias - Arrendador</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/arrendador/incidencias.css') }}" />
</head>
<body>
<div class="pagina">
    <header class="cabecera">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Incidencias de tus propiedades</h1>
            <p class="subtitulo">Revisa el estado de cada incidencia y decide quién paga cuando el presupuesto espera decisión.</p>
        </div>
        <div class="acciones-cabecera">
            <a class="btn-volver" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">Volver al dashboard</a>
            <a class="btn-volver" href="{{ route('logout') }}">Cerrar sesion</a>
        </div>
    </header>

    @if(session('ok'))
        <div class="alerta ok">{{ session('ok') }}</div>
    @endif

    @if(session('error'))
        <div class="alerta error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alerta error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="kpis">
        <article class="kpi"><span>{{ $totalIncidencias }}</span><small>Total</small></article>
        <article class="kpi"><span>{{ $esperandoDecision }}</span><small>Esperando decisión</small></article>
        <article class="kpi"><span>{{ $esperandoPago }}</span><small>Esperando pago</small></article>
        <article class="kpi"><span>{{ $resueltas }}</span><small>Resueltas / cerradas</small></article>
    </section>

    <section class="filtros">
        <form method="GET" action="{{ route('arrendador.incidencias') }}" class="form-filtros">
            <input type="hidden" name="arrendador_id" value="{{ $arrendadorId }}">
            <input type="text" name="titulo" value="{{ $titulo }}" placeholder="Filtrar por título">
            <input type="text" name="propiedad" value="{{ $propiedad }}" placeholder="Filtrar por propiedad">
            <select name="estado">
                <option value="">Todos los estados</option>
                <option value="abierta" {{ $estado === 'abierta' ? 'selected' : '' }}>Abiertas</option>
                <option value="en_proceso" {{ $estado === 'en_proceso' ? 'selected' : '' }}>En proceso</option>
                <option value="esperando_decision" {{ $estado === 'esperando_decision' ? 'selected' : '' }}>Esperando decisión</option>
                <option value="esperando_pago" {{ $estado === 'esperando_pago' ? 'selected' : '' }}>Esperando pago</option>
                <option value="resuelta" {{ $estado === 'resuelta' ? 'selected' : '' }}>Resueltas</option>
                <option value="cerrada" {{ $estado === 'cerrada' ? 'selected' : '' }}>Cerradas</option>
            </select>
            <select name="prioridad">
                <option value="">Todas las prioridades</option>
                <option value="alta" {{ $prioridad === 'alta' ? 'selected' : '' }}>Alta</option>
                <option value="media" {{ $prioridad === 'media' ? 'selected' : '' }}>Media</option>
                <option value="baja" {{ $prioridad === 'baja' ? 'selected' : '' }}>Baja</option>
                <option value="urgente" {{ $prioridad === 'urgente' ? 'selected' : '' }}>Urgente</option>
            </select>
            <input type="date" name="fecha" value="{{ $fecha }}">
            <button type="submit" class="btn-aplicar">Aplicar filtros</button>
        </form>
    </section>

    <section class="panel">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Propiedad</th>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Presupuesto</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidencias as $incidencia)
                    @php
                        $prioridad = strtolower($incidencia->prioridad_incidencia);
                        $badgePrioridad = $prioridad === 'urgente' ? 'alta' : $prioridad;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $incidencia->titulo_propiedad }}</strong>
                            <div class="muted">{{ $incidencia->direccion_propiedad }}, {{ $incidencia->ciudad_propiedad }}</div>
                        </td>
                        <td>
                            <strong>{{ $incidencia->titulo_incidencia }}</strong>
                            <div class="muted">Reporta: {{ $incidencia->nombre_reporta }}</div>
                        </td>
                        <td>
                            <span class="estado estado-{{ str_replace('_', '-', $incidencia->estado_incidencia) }}">{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</span>
                            <div class="muted">Prioridad: {{ ucfirst($badgePrioridad) }}</div>
                        </td>
                        <td>
                            @if(!is_null($incidencia->presupuesto_importe_incidencia))
                                <strong>{{ number_format((float) $incidencia->presupuesto_importe_incidencia, 2, ',', '.') }} €</strong>
                                <div class="muted">Responsable: {{ $incidencia->responsable_pago_incidencia ?: 'Sin definir' }}</div>
                            @else
                                <span class="muted">Sin presupuesto</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</td>
                        <td>
                            <a class="btn-ver" href="{{ route('arrendador.incidencias.show', ['id' => $incidencia->id_incidencia, 'arrendador_id' => $arrendadorId]) }}">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No hay incidencias para este arrendador.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="paginacion">{{ $incidencias->withQueryString()->links() }}</div>
    </section>
</div>
</body>
</html>