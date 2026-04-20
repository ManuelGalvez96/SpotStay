@extends('layouts.gestor')
@section('titulo', 'Dashboard gestor - SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gestor/dashboard.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Panel del gestor</h1>
        <p>Seguimiento operativo de incidencias y propiedades asignadas</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="kpi-grid">
    <a class="kpi-card-link" href="{{ url('/gestor/incidencias?estado=abierta') }}">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">INCIDENCIAS NUEVAS</span>
                <div class="kpi-icon kpi-icon-red"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
            <div class="kpi-numero kpi-numero-red">{{ $incidenciasNuevas }}</div>
            <div class="kpi-sub">pendientes de iniciar</div>
        </div>
    </a>

    <a class="kpi-card-link" href="{{ url('/gestor/incidencias?estado=en_proceso') }}">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">EN PROCESO</span>
                <div class="kpi-icon kpi-icon-orange"><i class="bi bi-hourglass-split"></i></div>
            </div>
            <div class="kpi-numero kpi-numero-orange">{{ $incidenciasEnProceso }}</div>
            <div class="kpi-sub">actualmente en gestión</div>
        </div>
    </a>

    <a class="kpi-card-link" href="{{ url('/gestor/incidencias?estado=esperando') }}">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">EN ESPERA</span>
                <div class="kpi-icon kpi-icon-blue"><i class="bi bi-pause-circle"></i></div>
            </div>
            <div class="kpi-numero">{{ $incidenciasEsperandoAccion }}</div>
            <div class="kpi-sub">bloqueadas por terceros</div>
        </div>
    </a>

    <a class="kpi-card-link" href="{{ url('/gestor/incidencias') }}">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">URGENTES</span>
                <div class="kpi-icon kpi-icon-green"><i class="bi bi-lightning-charge"></i></div>
            </div>
            <div class="kpi-numero">{{ $incidenciasUrgentes->count() }}</div>
            <div class="kpi-sub">requieren prioridad alta</div>
        </div>
    </a>
</div>

<div class="central-grid">
    <div class="card-admin">
        <div class="card-header-admin">
            <span>Incidencias recientes</span>
            <a href="{{ url('/gestor/incidencias') }}" class="link-ver-todos">Ver todas →</a>
        </div>

        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>TÍTULO</th>
                    <th>PROPIEDAD</th>
                    <th>ESTADO</th>
                    <th>PRIORIDAD</th>
                    <th>FECHA</th>
                    <th>ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidenciasRecientes as $incidencia)
                    @php
                        $prioridad = strtolower($incidencia->prioridad_incidencia);
                        $badgePrioridad = $prioridad === 'urgente' ? 'alta' : $prioridad;
                        $badgeEstado = match($incidencia->estado_incidencia) {
                            'abierta' => 'pendiente',
                            'en_proceso' => 'activo',
                            'resuelta' => 'activo',
                            default => 'rechazado'
                        };
                    @endphp
                    <tr>
                        <td>{{ $incidencia->titulo_incidencia }}</td>
                        <td>{{ $incidencia->direccion_propiedad }}</td>
                        <td><span class="badge-estado badge-{{ $badgeEstado }}">{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</span></td>
                        <td><span class="badge-prioridad badge-prioridad-{{ $badgePrioridad }}">{{ ucfirst($prioridad) }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</td>
                        <td><a class="link-ver-todos" href="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia) }}">Ver</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="tabla-vacia">No hay incidencias registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin">
            <span>Incidencias urgentes</span>
            <span class="badge-contador">{{ $incidenciasUrgentes->count() }}</span>
        </div>

        <div class="lista-solicitudes">
            @forelse($incidenciasUrgentes as $urgente)
                @php
                    $iniciales = strtoupper(substr($urgente->titulo_incidencia, 0, 2));
                @endphp
                <div class="solicitud-item">
                    <div class="solicitud-avatar" style="background:#EF4444;">{{ $iniciales }}</div>
                    <div class="solicitud-info">
                        <p class="solicitud-nombre">{{ $urgente->titulo_incidencia }}</p>
                        <p class="solicitud-ciudad">{{ $urgente->direccion_propiedad }}</p>
                    </div>
                    <div class="solicitud-meta">
                        <span class="solicitud-tiempo">{{ \Carbon\Carbon::parse($urgente->creado_incidencia)->diffForHumans() }}</span>
                        <a href="{{ url('/gestor/incidencias/' . $urgente->id_incidencia) }}" class="btn-revisar">Abrir →</a>
                    </div>
                </div>
            @empty
                <p class="tarjeta-vacia">No hay incidencias urgentes.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="inferior-grid">
    <div class="card-admin card-con-franja" id="propiedades-asignadas">
        <div class="card-franja"></div>
        <div class="card-header-admin">
            <span>Propiedades asignadas</span>
        </div>

        <div class="lista-solicitudes">
            @forelse($propiedadesAsignadas as $propiedad)
                <div class="solicitud-item">
                    <div class="solicitud-avatar" style="background:#035498;">{{ strtoupper(substr($propiedad->titulo_propiedad, 0, 2)) }}</div>
                    <div class="solicitud-info">
                        <p class="solicitud-nombre">{{ $propiedad->titulo_propiedad }}</p>
                        <p class="solicitud-ciudad">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }}</p>
                    </div>
                    <div class="solicitud-meta">
                        <span class="badge-estado badge-pendiente">{{ $propiedad->incidencias_activas }} activas</span>
                    </div>
                </div>
            @empty
                <p class="tarjeta-vacia">No hay propiedades asignadas.</p>
            @endforelse
        </div>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin">
            <span>Actividad reciente</span>
        </div>

        <div class="timeline">
            <div class="timeline-linea"></div>
            @forelse($notificaciones as $notificacion)
                @php
                    $datos = json_decode($notificacion->datos_notificacion);
                @endphp
                <div class="timeline-item">
                    <div class="timeline-punto" style="background:#035498;"></div>
                    <div class="timeline-contenido">
                        <p class="timeline-texto">{{ $datos->titulo ?? 'Actualización operativa' }}</p>
                        <span class="timeline-hora">{{ \Carbon\Carbon::parse($notificacion->creado_notificacion)->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <p class="tarjeta-vacia">No hay notificaciones recientes.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
