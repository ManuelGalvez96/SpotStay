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
        <div class="kpi-numero">1.284</div>
        <div class="kpi-sub">usuarios en total</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">PROPIEDADES ACTIVAS</span>
            <div class="kpi-icon kpi-icon-green">
                <i class="bi bi-house"></i>
            </div>
        </div>
        <div class="kpi-numero">347</div>
        <div class="kpi-sub">publicadas actualmente</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">ALQUILERES PENDIENTES</span>
            <div class="kpi-icon kpi-icon-orange">
                <i class="bi bi-clock"></i>
            </div>
        </div>
        <div class="kpi-numero kpi-numero-orange">23</div>
        <div class="kpi-sub">requieren atención</div>
    </div>
    
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">SOLICITUDES NUEVAS</span>
            <div class="kpi-icon kpi-icon-red">
                <i class="bi bi-exclamation-circle"></i>
            </div>
        </div>
        <div class="kpi-numero kpi-numero-red">9</div>
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
                <tr data-id="1">
                    <td>Calle Mayor 14, Madrid</td>
                    <td>Laura Martínez</td>
                    <td><span class="badge-estado badge-pendiente">Pendiente</span></td>
                    <td>
                        <div class="acciones-tabla">
                            <button class="btn-aprobar" data-id="1">✓ Aprobar</button>
                            <button class="btn-rechazar" data-id="1">✕ Rechazar</button>
                        </div>
                    </td>
                </tr>
                <tr data-id="2">
                    <td>Gran Vía 22, Madrid</td>
                    <td>Sofía López</td>
                    <td><span class="badge-estado badge-pendiente">Pendiente</span></td>
                    <td>
                        <div class="acciones-tabla">
                            <button class="btn-aprobar" data-id="2">✓ Aprobar</button>
                            <button class="btn-rechazar" data-id="2">✕ Rechazar</button>
                        </div>
                    </td>
                </tr>
                <tr data-id="3">
                    <td>Av. Diagonal 88, BCN</td>
                    <td>Pedro Ruiz</td>
                    <td><span class="badge-estado badge-activo">Activo</span></td>
                    <td><span class="sin-accion">—</span></td>
                </tr>
                <tr data-id="4">
                    <td>Paseo de Gracia 5</td>
                    <td>Javier Moreno</td>
                    <td><span class="badge-estado badge-rechazado">Rechazado</span></td>
                    <td><span class="sin-accion">—</span></td>
                </tr>
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
            <div class="solicitud-item">
                <div class="solicitud-avatar" style="background: #B8CCE4;">RD</div>
                <div class="solicitud-info">
                    <p class="solicitud-nombre">Roberto Díaz</p>
                    <p class="solicitud-ciudad">Valencia</p>
                </div>
                <div class="solicitud-meta">
                    <span class="solicitud-tiempo">hace 2h</span>
                    <a href="#" class="btn-revisar">Revisar →</a>
                </div>
            </div>
            
            <div class="solicitud-item">
                <div class="solicitud-avatar" style="background: #A8D5BF;">CI</div>
                <div class="solicitud-info">
                    <p class="solicitud-nombre">Carmen Iglesias</p>
                    <p class="solicitud-ciudad">Sevilla</p>
                </div>
                <div class="solicitud-meta">
                    <span class="solicitud-tiempo">hace 5h</span>
                    <a href="#" class="btn-revisar">Revisar →</a>
                </div>
            </div>
            
            <div class="solicitud-item">
                <div class="solicitud-avatar" style="background: #F9E4A0;">AM</div>
                <div class="solicitud-info">
                    <p class="solicitud-nombre">Andrés Molina</p>
                    <p class="solicitud-ciudad">Madrid</p>
                </div>
                <div class="solicitud-meta">
                    <span class="solicitud-tiempo">hace 1 día</span>
                    <a href="#" class="btn-revisar">Revisar →</a>
                </div>
            </div>
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
                    <p class="donut-numero">1.284</p>
                    <p class="donut-label">usuarios</p>
                </div>
            </div>
            
            <div class="donut-leyenda">
                <div class="leyenda-item">
                    <span class="leyenda-punto" style="background: #1AA068;"></span>
                    <span class="leyenda-nombre">Inquilinos</span>
                    <span class="leyenda-numero">687</span>
                </div>
                <div class="leyenda-item">
                    <span class="leyenda-punto" style="background: #035498;"></span>
                    <span class="leyenda-nombre">Arrendadores</span>
                    <span class="leyenda-numero">342</span>
                </div>
                <div class="leyenda-item">
                    <span class="leyenda-punto" style="background: #94A3B8;"></span>
                    <span class="leyenda-nombre">Miembros</span>
                    <span class="leyenda-numero">166</span>
                </div>
                <div class="leyenda-item">
                    <span class="leyenda-punto" style="background: #CBD5E1;"></span>
                    <span class="leyenda-nombre">Gestores</span>
                    <span class="leyenda-numero">89</span>
                </div>
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
            
            <div class="timeline-item">
                <div class="timeline-punto" style="background: #035498;"></div>
                <div class="timeline-contenido">
                    <p class="timeline-texto">Nueva propiedad publicada en Madrid</p>
                    <span class="timeline-hora">hace 5 min</span>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-punto" style="background: #1AA068;"></div>
                <div class="timeline-contenido">
                    <p class="timeline-texto">Alquiler aprobado — Gran Vía 22</p>
                    <span class="timeline-hora">hace 23 min</span>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-punto" style="background: #EF4444;"></div>
                <div class="timeline-contenido">
                    <p class="timeline-texto">Incidencia de fontanería reportada</p>
                    <span class="timeline-hora">hace 1h</span>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-punto" style="background: #035498;"></div>
                <div class="timeline-contenido">
                    <p class="timeline-texto">Nuevo usuario — Marta Gómez</p>
                    <span class="timeline-hora">hace 2h</span>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-punto" style="background: #1AA068;"></div>
                <div class="timeline-contenido">
                    <p class="timeline-texto">Solicitud aprobada — Roberto Díaz</p>
                    <span class="timeline-hora">hace 3h</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endsection
