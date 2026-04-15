@extends('layouts.admin')
@section('titulo', 'Panel general — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endsection

@section('content')

<!-- BLOQUE HERO -->
<div class="hero-admin">
    <div class="hero-content">
        <h1>Buenos días, Admin 👋</h1>
        <p>Miércoles, 14 de abril de 2025</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<!-- BLOQUE KPI -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">USUARIOS REGISTRADOS</span>
            <div class="kpi-icon kpi-icon-blue">
                <i class="bi bi-people"></i>
            </div>
        </div>
        <div class="kpi-numero">{{ number_format($totalUsuarios) }}</div>
        <div class="kpi-sub">usuarios en total</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">PROPIEDADES ACTIVAS</span>
            <div class="kpi-icon kpi-icon-green">
                <i class="bi bi-house"></i>
            </div>
        </div>
        <div class="kpi-numero">{{ $propiedadesActivas }}</div>
        <div class="kpi-sub">publicadas actualmente</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">ALQUILERES PENDIENTES</span>
            <div class="kpi-icon kpi-icon-orange">
                <i class="bi bi-clock"></i>
            </div>
        </div>
        <div class="kpi-numero kpi-numero-orange">{{ $alquileresPendientes }}</div>
        <div class="kpi-sub">requieren atención</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">SOLICITUDES NUEVAS</span>
            <div class="kpi-icon kpi-icon-red">
                <i class="bi bi-exclamation-circle"></i>
            </div>
        </div>
        <div class="kpi-numero kpi-numero-red">{{ $solicitudesNuevas }}</div>
        <div class="kpi-sub">pendientes de revisión</div>
    </div>
</div>

<!-- BLOQUE CENTRAL -->
<div class="central-grid">
    <!-- TARJETA TABLA ALQUILERES -->
    <div class="card-admin">
        <div class="card-header-admin">
            <span>Alquileres pendientes</span>
            <div style="display: flex; gap: 12px; align-items: center;">
                <input type="text" id="buscadorTabla" placeholder="Buscar..." class="buscador-input">
                <a href="#" class="link-ver-todos">Ver todos →</a>
            </div>
        </div>
        
        <table class="tabla-admin" id="tablaAlquileres">
            <thead>
                <tr>
                    <th>PROPIEDAD</th>
                    <th>INQUILINO</th>
                    <th>ESTADO</th>
                    <th>ACCIÓN</th>
                </tr>
            </thead>
            <tbody id="tbodyAlquileres">
                @forelse($ultimosAlquileres as $alquiler)
                <tr data-id="{{ $alquiler->id_alquiler }}">
                    <td>{{ $alquiler->titulo_propiedad }}, {{ $alquiler->ciudad_propiedad }}</td>
                    <td>{{ $alquiler->nombre_inquilino }}</td>
                    <td><span class="badge-estado badge-{{ str_replace('_', '-', $alquiler->estado_alquiler) }}">{{ ucfirst($alquiler->estado_alquiler) }}</span></td>
                    <td>
                        @if($alquiler->estado_alquiler === 'pendiente')
                        <div class="acciones-tabla">
                            <button class="btn-aprobar" data-id="{{ $alquiler->id_alquiler }}">✓ Aprobar</button>
                            <button class="btn-rechazar" data-id="{{ $alquiler->id_alquiler }}">✕ Rechazar</button>
                        </div>
                        @else
                        <span class="sin-accion">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">No hay alquileres pendientes</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- TARJETA SOLICITUDES NUEVAS -->
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin">
            <span>Solicitudes nuevas</span>
            <span class="badge-contador">3</span>
        </div>
        
        <div class="lista-solicitudes">
            @forelse($ultimasSolicitudes as $solicitud)
            @php
                $datos = json_decode($solicitud->datos_solicitud_arrendador);
                $partes = explode(' ', $solicitud->nombre_usuario);
                $iniciales = strtoupper(substr($partes[0], 0, 1)) . 
                             strtoupper(substr($partes[1] ?? '', 0, 1));
            @endphp
            <div class="solicitud-item">
                <div class="solicitud-avatar" style="background: #B8CCE4;">{{ $iniciales }}</div>
                <div class="solicitud-info">
                    <p class="solicitud-nombre">{{ $solicitud->nombre_usuario }}</p>
                    <p class="solicitud-ciudad">{{ $datos->ciudad ?? 'N/A' }}</p>
                </div>
                <div class="solicitud-meta">
                    <span class="solicitud-tiempo">{{ \Carbon\Carbon::parse($solicitud->creado_solicitud_arrendador)->diffForHumans() }}</span>
                    <a href="#" class="btn-revisar">Revisar →</a>
                </div>
            </div>
            @empty
            <p style="text-align: center; color: #999;">No hay solicitudes nuevas</p>
            @endforelse
        </div>
        
        <div class="card-footer-admin">
            <a href="#">Ver todas las solicitudes →</a>
        </div>
    </div>
</div>

<!-- BLOQUE INFERIOR -->
<div class="inferior-grid">
    <!-- TARJETA DONUT -->
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin">
            <span>Distribución de usuarios</span>
        </div>
        
        <div class="donut-container">
            <div class="donut-wrapper">
                <canvas id="donutChart" width="180" height="180"></canvas>
                <div class="donut-centro">
                    <p class="donut-numero">{{ number_format($totalUsuarios) }}</p>
                    <p class="donut-label">usuarios</p>
                </div>
            </div>
            
            <div class="donut-leyenda">
                @forelse($usuariosPorRol as $rol)
                <div class="leyenda-item">
                    <span class="leyenda-punto" style="background: @switch($rol->nombre_rol)@case('Inquilino')#1AA068@break@case('Arrendador')#035498@break@case('Miembro')#94A3B8@break@default#CBD5E1@endswitch;"></span>
                    <span class="leyenda-nombre">{{ $rol->nombre_rol }}</span>
                    <span class="leyenda-numero">{{ $rol->total }}</span>
                </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- TARJETA TIMELINE ACTIVIDAD -->
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin">
            <span>Actividad reciente</span>
        </div>
        
        <div class="timeline">
            <div class="timeline-linea"></div>
            @forelse($actividadReciente as $notif)
            @php
                $datos = json_decode($notif->datos_notificacion);
                $colorTipo = match($notif->tipo_notificacion) {
                    'nueva_solicitud' => '#035498',
                    'alquiler_pendiente' => '#1AA068',
                    default => '#EF4444'
                };
            @endphp
            <div class="timeline-item">
                <div class="timeline-punto" style="background: {{ $colorTipo }};"></div>
                <div class="timeline-contenido">
                    <p class="timeline-texto">{{ $datos->titulo ?? 'Actividad' }}</p>
                    <span class="timeline-hora">{{ \Carbon\Carbon::parse($notif->creado_notificacion)->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <p style="text-align: center; color: #999;">No hay actividad reciente</p>
            @endforelse
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endsection
