@extends('layouts.gestor')
@section('titulo', 'Dashboard gestor - SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gestor/dashboard.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Panel de gestor</h1>
        <p>Seguimiento operativo de incidencias y propiedades asignadas</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

@if(session('error'))
    <div class="mensaje-estado mensaje-error" data-flash-error="{{ session('error') }}">{{ session('error') }}</div>
@endif

@php
    $tieneIncidencias = collect($permisosDashboard)->contains(fn($p) => $p->incidencias);
    $tieneChat = collect($permisosDashboard)->contains(fn($p) => $p->chat);
@endphp

@if($tieneIncidencias || $tieneChat)
<div class="kpi-grid">
    <div class="kpi-grid-header">
        <h2 class="seccion-titulo-acciones">Acciones necesarias</h2>
    </div>

    @if($tieneIncidencias)
    <a class="kpi-card-link" href="{{ route('gestor.incidencias', ['estado' => 'abierta']) }}">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">INCIDENCIAS ABIERTAS</span>
                <div class="kpi-icon kpi-icon-red"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
            <div class="kpi-numero kpi-numero-red">{{ $incidenciasNuevas }}</div>
            <div class="kpi-sub">Requieren iniciar gestión</div>
        </div>
    </a>
    @endif

    @if($tieneChat)
    <a class="kpi-card-link" href="{{ route('gestor.mensajes.index') }}">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">MENSAJES SIN LEER</span>
                <div class="kpi-icon kpi-icon-blue"><i class="bi bi-chat-dots"></i></div>
            </div>
            <div class="kpi-numero">{{ $mensajesSinLeer }}</div>
            <div class="kpi-sub">Conversaciones pendientes</div>
        </div>
    </a>
    @endif
</div>
@endif

<div class="central-grid">
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
            <span>Incidencias recientes</span>
            <a href="{{ route('gestor.incidencias') }}" class="link-ver-todos">Ver todas →</a>
        </div>

        <!-- Vista desktop: tabla -->
        <div class="incidencias-tabla-desktop">
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
                            <td><a class="link-ver-todos" href="{{ route('gestor.incidencias.show', ['id' => $incidencia->id_incidencia]) }}">Ver</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="tabla-vacia">No hay incidencias registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista mobile: lista compacta -->
        <div class="incidencias-lista-mobile">
            @forelse($incidenciasRecientes as $incidencia)
                @php
                    $iniciales = strtoupper(substr($incidencia->titulo_incidencia, 0, 2));
                    $prioridad = strtolower($incidencia->prioridad_incidencia);
                    $badgeEstado = match($incidencia->estado_incidencia) {
                        'abierta' => 'pendiente',
                        'en_proceso' => 'activo',
                        'resuelta' => 'activo',
                        default => 'rechazado'
                    };
                @endphp
                <div class="solicitud-item">
                    <div class="solicitud-avatar" style="background:#EF4444;">{{ $iniciales }}</div>
                    <div class="solicitud-info">
                        <p class="solicitud-nombre">{{ $incidencia->titulo_incidencia }}</p>
                        <p class="solicitud-ciudad">{{ $incidencia->direccion_propiedad }}</p>
                    </div>
                    <div class="solicitud-meta">
                        <span class="solicitud-tiempo">{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->diffForHumans() }}</span>
                        <a href="{{ route('gestor.incidencias.show', ['id' => $incidencia->id_incidencia]) }}" class="btn-revisar">Abrir →</a>
                    </div>
                </div>
            @empty
                <p class="tarjeta-vacia">No hay incidencias registradas.</p>
            @endforelse
        </div>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
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
                        <a href="{{ route('gestor.incidencias.show', ['id' => $urgente->id_incidencia]) }}" class="btn-revisar">Abrir →</a>
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
        <div class="card-header-admin card-header-gradient">
            <span>Propiedades asignadas</span>
        </div>

        <div class="lista-solicitudes">
            @forelse($propiedadesAsignadas as $propiedad)
                <a class="solicitud-item solicitud-item-link" href="{{ route('gestor.propiedades.show', ['id' => $propiedad->id_propiedad]) }}">
                    <div class="solicitud-avatar" style="background:#035498;">{{ strtoupper(substr($propiedad->titulo_propiedad, 0, 2)) }}</div>
                    <div class="solicitud-info">
                        <p class="solicitud-nombre">{{ $propiedad->titulo_propiedad }}</p>
                        <p class="solicitud-ciudad">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }}</p>
                    </div>
                    <div class="solicitud-meta">
                        <span class="badge-estado badge-pendiente">{{ $propiedad->incidencias_activas }} activas</span>
                    </div>
                </a>
            @empty
                <p class="tarjeta-vacia">No hay propiedades asignadas.</p>
            @endforelse
        </div>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
            <span>Actividad reciente</span>
        </div>

        <div class="timeline">
            <div class="timeline-linea"></div>
            @forelse($notificaciones as $notificacion)
                <div class="timeline-item">
                    <div class="timeline-punto" style="background:{{ $notificacion->color_notificacion ?? '#035498' }};"></div>
                    <div class="timeline-contenido">
                        <p class="timeline-texto">{{ $notificacion->titulo_notificacion ?? 'Actualización operativa' }}</p>
                        <span class="timeline-hora">{{ \Carbon\Carbon::parse($notificacion->creado_notificacion)->diffForHumans() }}</span>
                    </div>
                </div>
            @empty
                <p class="tarjeta-vacia">No hay notificaciones recientes.</p>
            @endforelse
        </div>
    </div>
</div>
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const flashSuccess = document.querySelector('[data-flash-success]');
    const flashError = document.querySelector('[data-flash-error]');
    if (flashSuccess && flashSuccess.dataset.flashSuccess && window.swalSuccess) {
        swalSuccess('Éxito', flashSuccess.dataset.flashSuccess);
    }
    if (flashError && flashError.dataset.flashError && window.swalError) {
        swalError('Error', flashError.dataset.flashError);
    }
});
</script>
@endsection
@endsection
