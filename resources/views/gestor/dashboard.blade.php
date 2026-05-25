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
        @if(!empty($esArrendador))
            <div class="hero-actions">
                <a href="{{ route('arrendador.dashboard') }}" class="btn btn-outline btn-sm">Volver al Panel de Arrendador</a>
            </div>
        @endif
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

@if($tieneIncidencias || $tieneChat || $pagosPendientesTotal > 0 || $contratosPorVencer > 0)
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

    <a class="kpi-card-link" href="{{ route('gestor.propiedades', ['estado_pagos' => 'pendiente']) }}">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">PAGOS PENDIENTES</span>
                <div class="kpi-icon kpi-icon-orange"><i class="bi bi-credit-card"></i></div>
            </div>
            <div class="kpi-numero kpi-numero-orange">{{ $pagosPendientesTotal }}</div>
            <div class="kpi-sub">Recibos pendientes de cobro</div>
        </div>
    </a>

    <a class="kpi-card-link" href="{{ route('gestor.propiedades', ['proximo_vencimiento' => 30]) }}">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-label">CONTRATOS POR VENCER</span>
                <div class="kpi-icon kpi-icon-red"><i class="bi bi-calendar-event"></i></div>
            </div>
            <div class="kpi-numero kpi-numero-red">{{ $contratosPorVencer }}</div>
            <div class="kpi-sub">Finalizan en menos de 30 días</div>
        </div>
    </a>
</div>
@endif

@php
    $tienePermisos = collect($permisosDashboard)->contains(fn($p) => $p->incidencias || $p->chat);
@endphp

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
                        $badgeEstado = str_replace('_', '-', $incidencia->estado_incidencia);
                    @endphp
                    <tr>
                        <td>{{ $incidencia->titulo_incidencia }}</td>
                        <td>{{ $incidencia->direccion_propiedad }}</td>
                        <td><span class="badge-estado badge-estado-{{ $badgeEstado }}">{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</span></td>
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

        <!-- Vista mobile: cards -->
        <div class="incidencias-lista-mobile">
            @forelse($incidenciasRecientes as $incidencia)
                @php
                    $prioridad = strtolower($incidencia->prioridad_incidencia);
                $badgeEstado = str_replace('_', '-', $incidencia->estado_incidencia);
            @endphp
            <a href="{{ route('gestor.incidencias.show', ['id' => $incidencia->id_incidencia]) }}" class="incidencia-card">
                <div class="incidencia-card-header">
                    <span class="incidencia-card-titulo">{{ $incidencia->titulo_incidencia }}</span>
                    <span class="badge-estado badge-estado-{{ $badgeEstado }}">{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</span>
                </div>
                    <p class="incidencia-card-dir">{{ $incidencia->direccion_propiedad }}</p>
                    <div class="incidencia-card-divider"></div>
                    <div class="incidencia-card-footer">
                        <div class="incidencia-card-meta">
                            <span class="incidencia-card-prioridad">Prioridad: <strong>{{ ucfirst($prioridad) }}</strong></span>
                            <span class="incidencia-card-fecha">{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</span>
                        </div>
                        <span class="btn-revisar">Abrir →</span>
                    </div>
                </a>
            @empty
                <p class="tarjeta-vacia">No hay incidencias registradas.</p>
            @endforelse
        </div>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
            <span>Incidencias urgentes</span>
            <div class="card-header-right">
                <span class="badge-contador">{{ $incidenciasUrgentes->count() }}</span>
                <a href="{{ route('gestor.incidencias') }}" class="link-ver-todos">Ver todas →</a>
            </div>
        </div>

        <!-- Vista desktop: mini-cards -->
        <div class="urgentes-grid lista-solicitudes-desktop">
            @forelse($incidenciasUrgentes as $urgente)
                @php
                    $prioridad = strtolower($urgente->prioridad_incidencia);
                    $badgeEstado = str_replace('_', '-', $urgente->estado_incidencia);
                @endphp
                <a href="{{ route('gestor.incidencias.show', ['id' => $urgente->id_incidencia]) }}" class="incidencia-card">
                    <div class="incidencia-card-header">
                        <span class="incidencia-card-titulo">{{ $urgente->titulo_incidencia }}</span>
                        <span class="badge-estado badge-estado-{{ $badgeEstado }}">{{ ucfirst(str_replace('_', ' ', $urgente->estado_incidencia)) }}</span>
                    </div>
                    <p class="incidencia-card-dir">{{ $urgente->direccion_propiedad }}</p>
                    <div class="incidencia-card-footer">
                        <span class="incidencia-card-fecha">{{ \Carbon\Carbon::parse($urgente->creado_incidencia)->format('d/m/Y') }}</span>
                        <span class="btn-revisar">Abrir →</span>
                    </div>
                </a>
            @empty
                <p class="tarjeta-vacia">No hay incidencias urgentes.</p>
            @endforelse
        </div>

        <!-- Vista mobile: cards -->
        <div class="incidencias-lista-mobile">
            @forelse($incidenciasUrgentes as $urgente)
                @php
                    $prioridad = strtolower($urgente->prioridad_incidencia);
                    $badgeEstado = str_replace('_', '-', $urgente->estado_incidencia);
                @endphp
                <a href="{{ route('gestor.incidencias.show', ['id' => $urgente->id_incidencia]) }}" class="incidencia-card">
                    <div class="incidencia-card-header">
                        <span class="incidencia-card-titulo">{{ $urgente->titulo_incidencia }}</span>
                        <span class="badge-estado badge-estado-{{ $badgeEstado }}">{{ ucfirst(str_replace('_', ' ', $urgente->estado_incidencia)) }}</span>
                    </div>
                    <p class="incidencia-card-dir">{{ $urgente->direccion_propiedad }}</p>
                    <div class="incidencia-card-divider"></div>
                    <div class="incidencia-card-footer">
                        <div class="incidencia-card-meta">
                            <span class="incidencia-card-prioridad">Prioridad: <strong>{{ ucfirst($prioridad) }}</strong></span>
                            <span class="incidencia-card-fecha">{{ \Carbon\Carbon::parse($urgente->creado_incidencia)->format('d/m/Y') }}</span>
                        </div>
                        <span class="btn-revisar">Abrir →</span>
                    </div>
                </a>
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
            <div class="card-header-right">
                <span class="badge-contador">{{ $totalesPropiedades->total }} propiedades</span>
                <a href="{{ route('gestor.propiedades') }}" class="link-ver-todos">Ver todas →</a>
            </div>
        </div>

        @php
            $conAtrasados = collect($propiedadesAsignadas)->filter(fn($p) => $p->pagos_atrasados > 0)->count();
        @endphp
        <div class="propiedades-resumen-pills">
            <span class="resumen-pill">{{ $totalesPropiedades->total }} propiedades</span>
            <span class="resumen-pill resumen-pill-verde">{{ $totalesPropiedades->con_alquiler }} alquiladas</span>
            @if($conAtrasados)
                <span class="resumen-pill resumen-pill-rojo">{{ $conAtrasados }} con pagos atrasados</span>
            @endif
        </div>

        <!-- Vista desktop: tabla -->
        <div class="propiedades-grid-desktop">
            <table class="propiedades-table">
                <thead>
                    <tr>
                        <th>Propiedad</th>
                        <th>Dirección</th>
                        <th>Estado</th>
                        <th>Detalles</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($propiedadesAsignadas as $propiedad)
                        @php
                            $tieneAlquiler = !is_null($propiedad->fecha_inicio_alquiler);
                        @endphp
                        <tr class="propiedad-table-row {{ $tieneAlquiler ? '' : 'fila-inactiva' }}">
                            <td class="td-titulo">{{ $propiedad->titulo_propiedad }}</td>
                            <td class="td-dir">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }}</td>
                            <td>
                                @if($tieneAlquiler)
                                    <span class="badge-estado badge-activo">Alquilado</span>
                                @else
                                    <span class="badge-estado badge-rechazado">Sin alquiler</span>
                                @endif
                            </td>
                            <td class="td-detalles">
                                @if($tieneAlquiler && $propiedad->nombre_inquilino)
                                    <span>👤 {{ $propiedad->nombre_inquilino }}</span>
                                @endif
                                @if($propiedad->incidencias_activas > 0)
                                    <span>⚠️ {{ $propiedad->incidencias_activas }} incidencias</span>
                                @endif
                                @if($propiedad->pagos_pendientes > 0)
                                    <span>💰 {{ $propiedad->pagos_pendientes }} pend.</span>
                                @endif
                                @if($propiedad->pagos_atrasados > 0)
                                    <span class="td-atrasados">🔴 {{ $propiedad->pagos_atrasados }} atrasados</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('gestor.propiedades.show', ['id' => $propiedad->id_propiedad]) }}" class="btn-revisar">Ver →</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="tarjeta-vacia" style="text-align:center;padding:24px;">No hay propiedades asignadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista mobile: cards -->
        <div class="propiedades-lista-mobile">
            @forelse($propiedadesAsignadas as $propiedad)
                @php
                    $tieneAlquiler = !is_null($propiedad->fecha_inicio_alquiler);
                @endphp
                <a href="{{ route('gestor.propiedades.show', ['id' => $propiedad->id_propiedad]) }}" class="propiedad-card">
                    <div class="propiedad-card-header">
                        <span class="propiedad-card-titulo">{{ $propiedad->titulo_propiedad }}</span>
                        @if($tieneAlquiler)
                            <span class="badge-estado badge-activo">Alquilado</span>
                        @else
                            <span class="badge-estado badge-rechazado">Sin alquiler</span>
                        @endif
                    </div>
                    <p class="propiedad-card-dir">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }}</p>
                    <div class="propiedad-card-divider"></div>
                    <div class="propiedad-card-footer">
                        <div class="propiedad-card-meta">
                            @if($propiedad->incidencias_activas > 0)
                                <span>{{ $propiedad->incidencias_activas }} incidencias</span>
                            @endif
                            @if($propiedad->pagos_pendientes > 0)
                                <span>{{ $propiedad->pagos_pendientes }} pagos pend.</span>
                            @endif
                            @if($propiedad->pagos_atrasados > 0)
                                <span style="color:#991B1B;">{{ $propiedad->pagos_atrasados }} atrasados</span>
                            @endif
                        </div>
                        <span class="btn-revisar">Ver →</span>
                    </div>
                </a>
            @empty
                <p class="tarjeta-vacia">No hay propiedades asignadas.</p>
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
