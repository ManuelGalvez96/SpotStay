@extends('layouts.admin')
@section('titulo', 'Panel general — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/responsive-tablas.css') }}">
@endsection

@section('content')

<!-- BLOQUE HERO -->
<div class="hero-admin">
    <div class="hero-content">
        <h1>Buenos días, {{ auth()->user()->nombre_usuario ?? 'Admin' }} 👋</h1>
        <p>{{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::now()->locale('es')->translatedFormat('l, j \d\e F \d\e Y')) }}</p>
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
    <!-- TARJETA TABLA INCIDENCIAS INACTIVAS -->
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
            <span>Incidencias inactivas</span>
            <div class="card-header-actions">
                <div class="input-busqueda" style="min-width:220px;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="buscadorIncidencias" placeholder="Buscar incidencia...">
                </div>
                <select id="filtroEstadoIncidencias" class="select-filtro" style="margin-left:8px;">
                    <option value="all" selected>Todos los estados</option>
                    <option value="abierta">Abierta</option>
                    <option value="esperando_decision">Esperando decisión</option>
                    <option value="esperando_pago">Esperando pago</option>
                    <option value="solucionada">Solucionada</option>
                </select>
                <a href="/admin/incidencias" class="link-ver-todos">Ver todas →</a>
            </div>
        </div>
        
        <div class="tabla-contenedor-scroll">
            <table class="tabla-admin" id="tablaIncidencias">
                <thead>
                    <tr>
                        <th>PROPIEDAD</th>
                        <th>CATEGORÍA</th>
                        <th>PRIORIDAD</th>
                        <th>ESTADO</th>
                        <th class="col-mobile-hide">ÚLTIMA ACTIVIDAD</th>
                        <th class="col-mobile-hide">ACCIÓN</th>
                    </tr>
                </thead>
                <tbody id="tbodyIncidencias">
                    @forelse($ultimasIncidenciasInactivas as $incidencia)
                    <tr data-id="{{ $incidencia->id_incidencia }}" 
                        data-titulo="{{ $incidencia->titulo_incidencia }}" 
                        data-propiedad="{{ $incidencia->titulo_propiedad }}"
                        data-estado="{{ $incidencia->estado_incidencia }}"
                        data-inquilino="{{ $incidencia->nombre_inquilino }}"
                        data-arrendador="{{ $incidencia->nombre_arrendador }}"
                        data-gestor="{{ $incidencia->nombre_gestor ?? 'Sin asignar' }}"
                        data-encargado-pago="{{ $incidencia->encargado_pago }}">
                        <td>{{ $incidencia->titulo_propiedad }}, {{ $incidencia->ciudad_propiedad }}</td>
                        <td>
                            @if($incidencia->nombre_categoria)
                                <span class="badge bg-info">{{ $incidencia->nombre_categoria }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-prioridad badge-{{ strtolower($incidencia->prioridad_incidencia) }}">
                                {{ ucfirst($incidencia->prioridad_incidencia) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-estado badge-{{ str_replace('_', '-', $incidencia->estado_incidencia) }}">
                                {{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}
                            </span>
                        </td>
                        <td class="col-mobile-hide">
                            <small class="text-muted">{{ \Carbon\Carbon::parse($incidencia->actualizado_incidencia)->diffForHumans() }}</small>
                        </td>
                        <td class="col-mobile-hide">
                            <button class="btn-contactar-inc" data-id="{{ $incidencia->id_incidencia }}" data-toggle="modal" data-target="#modalContactarIncidencia">
                                📧 Contactar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="tabla-vacia-cell">No hay incidencias inactivas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- TARJETA SOLICITUDES NUEVAS -->
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
            <span>Solicitudes nuevas</span>
            <div class="card-header-acciones">
                <input type="text" id="buscadorSolicitudes" placeholder="Buscar por nombre..." class="buscador-input">
                <select id="filtroTipoSolicitudes" class="select-filtro" style="margin-left:8px; min-width: 150px;">
                    <option value="all">Todos los tipos</option>
                    <option value="arrendador">Arrendador</option>
                    <option value="gestor">Gestor</option>
                </select>
                <a href="/admin/solicitudes" class="link-ver-todos">Ver todas →</a>
            </div>
        </div>
        
        <div class="lista-solicitudes-scroll">
            <div class="lista-solicitudes" id="listaSolicitudes">
                @forelse($ultimasSolicitudes as $solicitud)
                @php
                    $partes = explode(' ', $solicitud->nombre_usuario);
                    $iniciales = strtoupper(substr($partes[0], 0, 1)) . 
                                 strtoupper(substr($partes[1] ?? '', 0, 1));
                    $tipoLabel = $solicitud->tipo_label ?? 'Solicitud';
                    $detalle = $solicitud->tipo_solicitud === 'gestor'
                        ? ($solicitud->experiencia_solicitud ?: $solicitud->descripcion_solicitud ?: 'Sin detalle')
                        : ($solicitud->direccion_fiscal_solicitud ?: $solicitud->descripcion_solicitud ?: 'Sin detalle');
                @endphp
                <div class="solicitud-item" data-id="{{ $solicitud->id_solicitud }}" data-nombre="{{ $solicitud->nombre_usuario }}" data-tipo="{{ $solicitud->tipo_solicitud }}">
                    <div class="solicitud-avatar avatar-default">{{ $iniciales }}</div>
                    <div class="solicitud-info">
                        <p class="solicitud-nombre">{{ $solicitud->nombre_usuario }}</p>
                        <p class="solicitud-ciudad">{{ $detalle }}</p>
                        <p class="solicitud-tipo"><span class="badge bg-primary">{{ $tipoLabel }}</span></p>
                    </div>
                    <div class="solicitud-meta">
                        <span class="solicitud-tiempo">{{ \Carbon\Carbon::parse($solicitud->creado_solicitud)->diffForHumans() }}</span>
                        <button class="btn-revisar" data-id="{{ $solicitud->id_solicitud }}" data-tipo="{{ $solicitud->tipo_solicitud }}" type="button">Revisar →</button>
                    </div>
                </div>
                @empty
                <p class="sin-solicitudes">No hay solicitudes nuevas</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- BLOQUE INFERIOR -->
<div class="inferior-grid">
    <!-- TARJETA DONUT -->
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
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
                @php
                    $colorRol = match($rol->nombre_rol) {
                        'Inquilino' => '#1AA068',
                        'Arrendador' => '#035498',
                        'Miembro' => '#94A3B8',
                        default => '#CBD5E1'
                    };
                @endphp
                <div class="leyenda-item">
                    <span class="leyenda-punto" style="background: {{ $colorRol }};"></span>
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
        <div class="card-header-admin card-header-gradient">
            <span>Actividad reciente</span>
        </div>
        
        <div class="timeline">
            <div class="timeline-linea"></div>
            @forelse($actividadReciente as $notif)
            @php
                $colorTipo = $notif->color_notificacion ?? match($notif->tipo_notificacion) {
                    'nueva_solicitud' => '#035498',
                    'alquiler_pendiente' => '#1AA068',
                    default => '#EF4444'
                };
            @endphp
            <div class="timeline-item">
                <div class="timeline-punto" style="background: {{ $colorTipo }};"></div>
                <div class="timeline-contenido">
                    <p class="timeline-texto">{{ $notif->titulo_notificacion ?? 'Actividad' }}</p>
                    <span class="timeline-hora">{{ \Carbon\Carbon::parse($notif->creado_notificacion)->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <p class="sin-actividad">No hay actividad reciente</p>
            @endforelse
        </div>
    </div>
</div>

<!-- MODAL SOLICITUD DASHBOARD (Bootstrap 5) -->
<div class="modal fade" id="modalSolicitudDash" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de solicitud</h5>
                <span class="badge bg-warning" id="modalBadgeEstadoSolicitudDash">Pendiente</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <!-- Header solicitante -->
                <div class="modal-solicitante-dash">
                    <div id="modalAvatarSolicitudDash" class="modal-avatar-dash">UT</div>
                    <div class="modal-solicitante-info-dash">
                        <h6 id="modalNombreSolicitudDash" class="modal-nombre-dash">Usuario Test</h6>
                        <p id="modalEmailSolicitudDash" class="modal-email-dash">test@example.com</p>
                        <p id="modalCiudadSolicitudDash" class="modal-ciudad-dash"><i class="bi bi-geo-alt"></i></p>
                    </div>
                </div>
                
                <hr class="modal-separator-dash">
                
                <!-- Datos personales -->
                <h6 class="modal-seccion-titulo-dash">Datos personales</h6>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Teléfono</label>
                    <p id="modalTelefonoSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Tipo de solicitud</label>
                    <p id="modalTipoSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Fecha de nacimiento</label>
                    <p id="modalFechaNacimientoSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Tipo de documento</label>
                    <p id="modalTipoDocumentoSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Número de documento</label>
                    <p id="modalNumeroDocumentoSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">NIF</label>
                    <p id="modalNifSolicitudDash" class="modal-data-dash">—</p>
                </div>
                
                <hr class="modal-separator-dash">
                
                <!-- Datos bancarios y fiscales -->
                <h6 class="modal-seccion-titulo-dash">Datos bancarios y fiscales</h6>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">IBAN</label>
                    <p id="modalIbanSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Titular de la cuenta</label>
                    <p id="modalTitularCuentaSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Dirección fiscal</label>
                    <p id="modalDireccionFiscalSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Tipo de arrendador</label>
                    <p id="modalTipoArrendadorSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Número de propiedades previstas</label>
                    <p id="modalNumPropiedadesSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Es propietario</label>
                    <p id="modalEsPropietarioSolicitudDash" class="modal-data-dash">—</p>
                </div>
                
                <hr class="modal-separator-dash">
                
                <!-- Declaraciones y descripción -->
                <h6 class="modal-seccion-titulo-dash">Declaraciones y observaciones</h6>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Acepta términos</label>
                    <p id="modalAceptaTerminosSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Acepta veracidad</label>
                    <p id="modalAceptaVeracidadSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Descripción</label>
                    <p id="modalDescripcionSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Experiencia previa</label>
                    <p id="modalExperienciaSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Fecha de aceptación</label>
                    <p id="modalFechaAceptacionSolicitudDash" class="modal-data-dash">—</p>
                </div>
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Estado</label>
                    <p id="modalEstadoSolicitudDash" class="modal-data-dash">—</p>
                </div>
                
                <hr class="modal-separator-dash">
                
                <!-- Notas -->
                <h6 class="modal-seccion-titulo-dash">Notas (Opcional)</h6>
                <textarea id="modalNotasSolicitudDash" class="form-control" rows="4" placeholder="Añade notas..."></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" id="btnRechazarSolicitudDash">Rechazar</button>
                <button type="button" class="btn btn-primary" id="btnAprobarSolicitudDash">Aprobar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONTACTAR INCIDENCIA -->
<div class="modal fade" id="modalContactarIncidencia" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contactar sobre incidencia inactiva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <!-- Info incidencia -->
                <div class="modal-incidencia-info-dash">
                    <p class="modal-incidencia-titulo-dash" id="modalTituloIncidencia">—</p>
                    <small class="modal-incidencia-propiedad-dash" id="modalPropiedadIncidencia">—</small>
                </div>
                
                <hr class="modal-separator-dash">

                <!-- Información de personas involucradas -->
                <h6 class="modal-seccion-titulo-dash">Personas involucradas</h6>
                
                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Inquilino</label>
                    <p class="modal-data-dash" id="modalInquilinoIncidencia">—</p>
                </div>

                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Arrendador</label>
                    <p class="modal-data-dash" id="modalArrendadorIncidencia">—</p>
                </div>

                <div class="modal-seccion-dash">
                    <label class="modal-label-dash">Gestor asignado</label>
                    <p class="modal-data-dash" id="modalGestorIncidencia">—</p>
                </div>

                <div class="modal-seccion-dash" id="modalEncargadoPagoSeccion" style="display: none;">
                    <label class="modal-label-dash">Encargado de pago</label>
                    <p class="modal-data-dash" id="modalEncargadoPagoIncidencia">—</p>
                </div>

                <hr class="modal-separator-dash">

                <!-- Campo destinatario -->
                <div class="modal-seccion-dash">
                    <label for="modalDestinoContacto" class="modal-label-dash">Enviar a:</label>
                    <select id="modalDestinoContacto" class="form-select" required>
                        <option value="">— Selecciona destinatario —</option>
                        <option value="inquilino">Inquilino</option>
                        <option value="arrendador">Arrendador</option>
                        <option value="gestor">Gestor asignado</option>
                    </select>
                </div>

                <!-- Campo asunto -->
                <div class="modal-seccion-dash">
                    <label for="modalAsuntoContacto" class="modal-label-dash">Asunto:</label>
                    <input type="text" id="modalAsuntoContacto" class="form-control" value="Incidencia inactiva — Requiere atención" required>
                </div>

                <!-- Campo mensaje -->
                <div class="modal-seccion-dash">
                    <label for="modalMensajeContacto" class="modal-label-dash">Mensaje:</label>
                    <textarea id="modalMensajeContacto" class="form-control" rows="5" placeholder="Escribe tu mensaje..." required></textarea>
                </div>

                <p class="modal-incidencia-nota-dash">Se enviará un correo con los detalles de la incidencia.</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarContactoIncidencia">
                    <i class="bi bi-send"></i> Enviar correo
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endsection
