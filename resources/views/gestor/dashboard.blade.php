@extends('layouts.gestor')
@section('titulo', 'Dashboard gestor - SpotStay')

@section('content')
<div class="dashboard-shell">
    <section class="cabecera-panel reveal">
        <div>
            <h1>Panel operativo del gestor</h1>
            <p>Prioriza incidencias activas y desbloquea tareas pendientes en tus propiedades asignadas.</p>
        </div>
        <div class="filtros-top">
            <label class="search-wrap" for="busquedaIncidencias">
                <i class="bi bi-search"></i>
                <input id="busquedaIncidencias" type="text" placeholder="Buscar incidencia o propiedad...">
            </label>
            <select id="filtroEstado" aria-label="Filtrar por estado">
                <option value="todos">Todos los estados</option>
                <option value="abierta">Nuevas</option>
                <option value="en_proceso">En proceso</option>
                <option value="esperando">Esperando acción</option>
            </select>
        </div>
    </section>

    <section class="kpi-grid reveal">
        <article class="kpi-card kpi-nuevas">
            <div class="kpi-icono"><i class="bi bi-tools"></i></div>
            <p class="kpi-titulo">Incidencias nuevas</p>
            <p class="kpi-numero">{{ $incidenciasNuevas }}</p>
        </article>

        <article class="kpi-card kpi-proceso">
            <div class="kpi-icono"><i class="bi bi-hourglass-split"></i></div>
            <p class="kpi-titulo">En proceso</p>
            <p class="kpi-numero">{{ $incidenciasEnProceso }}</p>
        </article>

        <article class="kpi-card kpi-espera">
            <div class="kpi-icono"><i class="bi bi-exclamation-octagon"></i></div>
            <p class="kpi-titulo">Esperando acción</p>
            <p class="kpi-numero">{{ $incidenciasEsperandoAccion }}</p>
        </article>
    </section>

    <section class="layout-principal">
        <div class="columna-izquierda">
            <article class="card-saas reveal">
                <header class="card-head">
                    <h2>Incidencias recientes</h2>
                    <span>{{ $incidenciasRecientes->count() }} registros</span>
                </header>

                <div class="tabla-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Propiedad</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="tablaIncidenciasRecientes">
                            @forelse($incidenciasRecientes as $incidencia)
                                @php
                                    $estadoBadge = match($incidencia->estado_incidencia) {
                                        'abierta' => 'badge-estado abierta',
                                        'en_proceso' => 'badge-estado en-proceso',
                                        'resuelta' => 'badge-estado resuelta',
                                        default => 'badge-estado'
                                    };

                                    $prioridadNormalizada = strtolower($incidencia->prioridad_incidencia) === 'urgente' ? 'alta' : strtolower($incidencia->prioridad_incidencia);
                                @endphp
                                <tr data-estado="{{ $incidencia->estado_incidencia }}" data-prioridad="{{ $prioridadNormalizada }}">
                                    <td class="titulo-col">{{ $incidencia->titulo_incidencia }}</td>
                                    <td>{{ $incidencia->direccion_propiedad }}</td>
                                    <td><span class="{{ $estadoBadge }}">{{ str_replace('_', ' ', ucfirst($incidencia->estado_incidencia)) }}</span></td>
                                    <td><span class="badge-prioridad prioridad-{{ $prioridadNormalizada }}">{{ ucfirst($prioridadNormalizada) }}</span></td>
                                    <td>{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</td>
                                    <td>
                                        <a class="btn-ver" href="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia) }}">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="vacio-tabla">No hay incidencias registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="card-saas card-urgentes reveal">
                <header class="card-head card-head-alerta">
                    <h2><i class="bi bi-exclamation-triangle"></i> Incidencias urgentes</h2>
                    <span>Atención prioritaria</span>
                </header>

                <div class="lista-urgentes">
                    @forelse($incidenciasUrgentes as $urgente)
                        @php
                            $diasAbierta = \Carbon\Carbon::parse($urgente->creado_incidencia)->diffInDays(now());
                        @endphp
                        <article class="urgente-item">
                            <div>
                                <p class="urgente-titulo">{{ $urgente->titulo_incidencia }}</p>
                                <p class="urgente-meta">{{ $urgente->direccion_propiedad }} · {{ ucfirst($urgente->prioridad_incidencia) }}</p>
                            </div>
                            <span class="urgente-tiempo">{{ $diasAbierta }} d</span>
                        </article>
                    @empty
                        <p class="vacio-urgentes">No hay incidencias urgentes ahora mismo.</p>
                    @endforelse
                </div>
            </article>
        </div>

        <aside class="columna-derecha">
            <article class="card-saas reveal">
                <header class="card-head">
                    <h2>Propiedades asignadas</h2>
                </header>
                <div class="lista-propiedades">
                    @forelse($propiedadesAsignadas as $propiedad)
                        <div class="propiedad-item">
                            <div>
                                <p class="propiedad-nombre">{{ $propiedad->titulo_propiedad }}</p>
                                <p class="propiedad-dir">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }}</p>
                            </div>
                            <span class="badge-activa">{{ $propiedad->incidencias_activas }} activas</span>
                        </div>
                    @empty
                        <p class="vacio-card">No hay propiedades asignadas.</p>
                    @endforelse
                </div>
            </article>

            <article class="card-saas reveal">
                <header class="card-head">
                    <h2>Esperando acción (detalle)</h2>
                </header>

                <div class="detalle-espera">
                    <div class="fila-espera">
                        <div class="fila-texto">
                            <span>Esperando arrendador</span>
                            <strong>{{ $esperandoArrendador }}</strong>
                        </div>
                        <div class="barra"><span class="progress-fill" data-value="{{ round(($esperandoArrendador / $totalEsperandoDetalle) * 100) }}"></span></div>
                    </div>

                    <div class="fila-espera">
                        <div class="fila-texto">
                            <span>Esperando empresa</span>
                            <strong>{{ $esperandoEmpresa }}</strong>
                        </div>
                        <div class="barra"><span class="progress-fill" data-value="{{ round(($esperandoEmpresa / $totalEsperandoDetalle) * 100) }}"></span></div>
                    </div>

                    <div class="fila-espera">
                        <div class="fila-texto">
                            <span>Esperando inquilino</span>
                            <strong>{{ $esperandoInquilino }}</strong>
                        </div>
                        <div class="barra"><span class="progress-fill" data-value="{{ round(($esperandoInquilino / $totalEsperandoDetalle) * 100) }}"></span></div>
                    </div>
                </div>
            </article>

            <article class="card-saas reveal">
                <header class="card-head">
                    <h2>Incidencias por estado</h2>
                </header>
                <div class="mini-chart">
                    @php
                        $maxEstado = max(1, max($resumenEstados));
                    @endphp
                    <div class="estado-barra">
                        <span>Nuevas</span>
                        <div class="barra"><span class="progress-fill" data-value="{{ round(($resumenEstados['abierta'] / $maxEstado) * 100) }}"></span></div>
                        <strong>{{ $resumenEstados['abierta'] }}</strong>
                    </div>
                    <div class="estado-barra">
                        <span>En proceso</span>
                        <div class="barra"><span class="progress-fill" data-value="{{ round(($resumenEstados['en_proceso'] / $maxEstado) * 100) }}"></span></div>
                        <strong>{{ $resumenEstados['en_proceso'] }}</strong>
                    </div>
                    <div class="estado-barra">
                        <span>Resueltas</span>
                        <div class="barra"><span class="progress-fill" data-value="{{ round(($resumenEstados['resuelta'] / $maxEstado) * 100) }}"></span></div>
                        <strong>{{ $resumenEstados['resuelta'] }}</strong>
                    </div>
                </div>
            </article>

            <article class="card-saas reveal">
                <header class="card-head">
                    <h2>Notificaciones</h2>
                </header>
                <div class="lista-notificaciones">
                    @forelse($notificaciones as $notificacion)
                        @php
                            $datos = json_decode($notificacion->datos_notificacion);
                            $etiqueta = match($notificacion->tipo_notificacion) {
                                'nueva_incidencia' => 'Nueva incidencia',
                                'mensaje_nuevo' => 'Mensaje recibido',
                                'alquiler_pendiente' => 'Presupuesto pendiente',
                                'incidencia_actualizada' => 'Incidencia actualizada',
                                default => 'Evento'
                            };
                        @endphp
                        <div class="notificacion-item">
                            <i class="bi bi-dot"></i>
                            <div>
                                <p class="notif-titulo">{{ $etiqueta }}</p>
                                <p class="notif-detalle">{{ $datos->titulo ?? 'Actualización operativa' }}</p>
                            </div>
                            <span>{{ \Carbon\Carbon::parse($notificacion->creado_notificacion)->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="vacio-card">No hay notificaciones recientes.</p>
                    @endforelse
                </div>
            </article>
        </aside>
    </section>
</div>
@endsection
