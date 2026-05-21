@extends('layouts.admin')

@section('titulo', 'Solicitudes — SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/solicitudes.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/responsive-tablas.css') }}">
@endsection

@section('content')

@php
    $colores = ['#B8CCE4','#A8D5BF','#F9E4A0','#FFD5CC','#D7EAF9','#EDE7F6','#D5F5E3','#FAD7D7'];
@endphp

<div class="hero-admin">
    <div class="hero-content">
        <h1>Gestión de solicitudes</h1>
        <p>Revisa y aprueba solicitudes de arrendador y gestor desde un único panel</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="kpi-grid-pequeno">
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-naranja">
            <i class="bi bi-clock"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-naranja" id="kpiPendientesSolicitudes">{{ $solicitudesPendientes->total() }}</span>
            <span class="kpi-mini-label">Pendientes este mes</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero" id="kpiAprobadasSolicitudes">{{ $aprobadas }}</span>
            <span class="kpi-mini-label">Aprobadas este mes</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo" id="kpiRechazadasSolicitudes">{{ $rechazadas }}</span>
            <span class="kpi-mini-label">Rechazadas este mes</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-azul">
            <i class="bi bi-inbox"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero" id="kpiTotalSolicitudes">{{ $totalSolicitudes }}</span>
            <span class="kpi-mini-label">Total solicitudes</span>
        </div>
    </div>
</div>

<div class="toolbar-admin">
    <div class="toolbar-izquierda">
        <div class="input-busqueda">
            <i class="bi bi-search"></i>
            <input type="text" id="buscadorSolicitudes" placeholder="Buscar por nombre, email o detalle...">
        </div>
        <select id="selectRangoSol" class="select-filtro">
            <option value="mes">Este mes</option>
            <option value="3meses">Últimos 3 meses</option>
            <option value="anio">Este año</option>
            <option value="all">Todas</option>
        </select>
        <select id="selectTipoSol" class="select-filtro">
            <option value="all">Todos los tipos</option>
            <option value="arrendador">Arrendador</option>
            <option value="gestor">Gestor</option>
        </select>
        <select id="selectEstadoSol" class="select-filtro">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="aprobada">Aprobada</option>
            <option value="rechazada">Rechazada</option>
        </select>
        <select id="selectCiudadSol" class="select-filtro">
            <option value="">Todas las ubicaciones</option>
            <option value="Madrid">Madrid</option>
            <option value="Barcelona">Barcelona</option>
            <option value="Valencia">Valencia</option>
            <option value="Sevilla">Sevilla</option>
            <option value="Bilbao">Bilbao</option>
        </select>
    </div>
    <div class="toolbar-derecha">
        <span class="texto-pendientes">{{ $solicitudesPendientes->total() }} pendientes de revisión este mes</span>
    </div>
</div>

<div class="solicitudes-grid">
    <div class="columna-izquierda-sol">
        <div class="card-admin">
            <div class="card-header-admin">
                <span>Solicitudes</span>
            </div>

            <div class="tabla-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="tabla-header-info">
                    <span class="tabla-header-titulo">Solicitudes</span>
                    <span id="contadorResultados" class="info-paginacion">Mostrando 0-0 de 0 solicitudes</span>
                </div>

                <nav aria-label="Paginación de solicitudes">
                    <ul class="pagination pagination-sm mb-0" id="paginacionSolicitudes">
                    </ul>
                </nav>
            </div>

            <div class="tabla-contenedor">
                <table class="tabla-admin tabla-solicitudes">
                    <thead>
                        <tr>
                            <th>SOLICITANTE</th>
                            <th class="col-tablet-hide">TIPO</th>
                            <th class="col-mobile-hide">DETALLE</th>
                            <th class="col-tablet-hide">FECHA</th>
                            <th>ESTADO</th>
                            <th>PAGO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tablaSolicitudes">
                        @forelse($solicitudesPendientes as $solicitud)
                            @php
                                $partes = explode(' ', $solicitud->nombre_usuario);
                                $iniciales = strtoupper(substr($partes[0], 0, 1)) . strtoupper(substr($partes[1] ?? '', 0, 1));
                                $color = $colores[($solicitud->id_solicitud ?? 0) % 8];
                                $fecha = \Carbon\Carbon::parse($solicitud->creado_solicitud)->format('d/m/Y');
                                $tipoLabel = $solicitud->tipo_label ?? 'Solicitud';
                                $esGestor = ($solicitud->tipo_solicitud ?? '') === 'gestor';
                                $detallePrincipal = $esGestor
                                    ? ($solicitud->experiencia_solicitud ?: $solicitud->descripcion_solicitud ?: '—')
                                    : ($solicitud->tipo_arrendador_solicitud ?: $solicitud->descripcion_solicitud ?: '—');
                                $detalleSecundario = $esGestor
                                    ? ($solicitud->descripcion_solicitud ?: '—')
                                    : ($solicitud->direccion_fiscal_solicitud ?: '—');
                            @endphp
                            <tr class="fila-solicitud" data-id="{{ $solicitud->id_solicitud }}" data-tipo="{{ $solicitud->tipo_solicitud }}">
                                <td data-label="SOLICITANTE">
                                    <div class="usuario-celda">
                                        <div class="avatar-tabla" style="background:{{ $color }}">{{ $iniciales }}</div>
                                        <div class="usuario-info-tabla">
                                            <span class="usuario-nombre-tabla">{{ $solicitud->nombre_usuario }}</span>
                                            <span class="usuario-email-tabla">{{ $solicitud->email_usuario }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="TIPO" class="col-tablet-hide">
                                    <span class="badge-estado badge-activo" style="background: rgba(52, 152, 219, 0.1); color: #3498db; border: 1px solid #3498db;">{{ $tipoLabel }}</span>
                                </td>
                                <td data-label="DETALLE" class="col-mobile-hide">
                                    <div class="usuario-info-tabla">
                                        <span class="usuario-nombre-tabla">{{ $detallePrincipal }}</span>
                                        <span class="usuario-email-tabla">{{ $detalleSecundario }}</span>
                                    </div>
                                </td>
                                <td data-label="FECHA" class="col-tablet-hide">{{ $fecha }}</td>
                                <td data-label="ESTADO">
                                    <span class="badge-estado badge-pendiente">Pendiente</span>
                                </td>
                                <td data-label="PAGO">
                                    @if($esGestor)
                                        <span class="badge-estado badge-activo" style="background: rgba(52, 152, 219, 0.1); color: #3498db; border: 1px solid #3498db;">No aplica</span>
                                    @elseif($solicitud->stripe_status === 'active')
                                        <span class="badge-estado badge-activo" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71;">Pagado</span>
                                    @else
                                        <span class="badge-estado badge-pendiente" style="background: rgba(243, 156, 18, 0.1); color: #f39c12; border: 1px solid #f39c12;">Pendiente</span>
                                    @endif
                                </td>
                                <td data-label="ACCIONES">
                                    <div class="acciones-tabla">
                                        <button class="btn-icono btn-ver-sol" data-id="{{ $solicitud->id_solicitud }}" data-tipo="{{ $solicitud->tipo_solicitud }}" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn-icono btn-aprobar-sol" data-id="{{ $solicitud->id_solicitud }}" data-tipo="{{ $solicitud->tipo_solicitud }}" title="Aprobar">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button class="btn-icono btn-rechazar-sol" data-id="{{ $solicitud->id_solicitud }}" data-tipo="{{ $solicitud->tipo_solicitud }}" title="Rechazar">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="sin-resultados">No hay solicitudes pendientes</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="columna-derecha-sol">
        <div class="card-admin card-estadisticas">
            <div class="card-header-admin">
                <span>Aprobadas este mes</span>
                <span class="badge-contador-verde" id="badgeAprobadasDetalles">{{ $aprobadas }}</span>
            </div>
            <div class="historial-lista">
                @forelse($ultimasAprobadas as $aprobada)
                    @php
                        $partesA = explode(' ', $aprobada->nombre_usuario);
                        $inicialesA = strtoupper(substr($partesA[0], 0, 1)) . strtoupper(substr($partesA[1] ?? '', 0, 1));
                        $colorA = $colores[($aprobada->id_solicitud ?? 0) % 8];
                    @endphp
                    <div class="historial-item">
                        <div class="solicitud-avatar-mini" style="background:{{ $colorA }}">{{ $inicialesA }}</div>
                        <div class="historial-info">
                            <span class="historial-nombre">{{ $aprobada->nombre_usuario }}</span>
                            <span class="historial-ciudad">{{ $aprobada->tipo_label }} · {{ \Illuminate\Support\Str::limit($aprobada->direccion_fiscal_solicitud ?: $aprobada->experiencia_solicitud ?: 'Sin detalle', 35) }}</span>
                        </div>
                        <span class="badge-estado badge-activo">Aprobada</span>
                    </div>
                @empty
                    <div class="sin-items">No hay solicitudes aprobadas aún</div>
                @endforelse
            </div>
        </div>

        <div class="card-admin card-estadisticas">
            <div class="card-header-admin">
                <span>Rechazadas este mes</span>
                <span class="badge-contador-rojo" id="badgeRechazadasDetalles">{{ $rechazadas }}</span>
            </div>
            <div class="historial-lista">
                @forelse($ultimasRechazadas as $rechazada)
                    @php
                        $partesR = explode(' ', $rechazada->nombre_usuario);
                        $inicialesR = strtoupper(substr($partesR[0], 0, 1)) . strtoupper(substr($partesR[1] ?? '', 0, 1));
                        $colorR = $colores[($rechazada->id_solicitud ?? 0) % 8];
                    @endphp
                    <div class="historial-item">
                        <div class="solicitud-avatar-mini" style="background:{{ $colorR }}">{{ $inicialesR }}</div>
                        <div class="historial-info">
                            <span class="historial-nombre">{{ $rechazada->nombre_usuario }}</span>
                            <span class="historial-motivo">{{ \Illuminate\Support\Str::limit($rechazada->notas_solicitud ?? 'Sin motivo', 35) }}</span>
                        </div>
                        <span class="badge-estado badge-inactivo">Rechazada</span>
                    </div>
                @empty
                    <div class="sin-items">No hay solicitudes rechazadas aún</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSolicitud" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de solicitud</h5>
                <span class="badge bg-warning" id="modalBadgeEstado">Pendiente</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="modal-solicitante">
                    <div class="avatar-modal" id="modalAvatar">UT</div>
                    <div class="modal-solicitante-info">
                        <h6 class="modal-nombre" id="modalNombre">Usuario Test</h6>
                        <p class="modal-email" id="modalEmail">test@example.com</p>
                        <p class="modal-ciudad" id="modalTipoSolicitud"><i class="bi bi-briefcase"></i></p>
                    </div>
                </div>

                <hr class="modal-separator">

                <h6 class="modal-seccion-titulo">Datos de contacto</h6>
                <div class="modal-seccion row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <p class="modal-data" id="modalTelefono">—</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado de la solicitud</label>
                        <p class="modal-data" id="modalEstadoTexto">—</p>
                    </div>
                </div>

                <div id="bloque-arrendador-solicitud">
                    <hr class="modal-separator">

                    <h6 class="modal-seccion-titulo">Datos personales</h6>
                    <div class="modal-seccion row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <p class="modal-data" id="modalFechaNacimiento">—</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Documento</label>
                            <p class="modal-data" id="modalTipoDocumento">—</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Número de Documento</label>
                            <p class="modal-data" id="modalNumeroDocumento">—</p>
                        </div>
                    </div>

                    <hr class="modal-separator">

                    <h6 class="modal-seccion-titulo">Datos bancarios</h6>
                    <div class="modal-seccion row g-3">
                        <div class="col-md-6">
                            <label class="form-label">IBAN</label>
                            <p class="modal-data" id="modalIban">—</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Titular de la Cuenta</label>
                            <p class="modal-data" id="modalTitularCuenta">—</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIF</label>
                            <p class="modal-data" id="modalNif">—</p>
                        </div>
                    </div>

                    <hr class="modal-separator">

                    <h6 class="modal-seccion-titulo">Información como Arrendador</h6>
                    <div class="modal-seccion row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Dirección Fiscal</label>
                            <p class="modal-data" id="modalDireccionFiscal">—</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Arrendador</label>
                            <p class="modal-data" id="modalTipoArrendador">—</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Número de Propiedades Previstas</label>
                            <p class="modal-data" id="modalNumPropiedades">—</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Es Propietario</label>
                            <p class="modal-data" id="modalEsPropietario">—</p>
                        </div>
                    </div>
                </div>

                <div id="bloque-gestor-solicitud" style="display:none;">
                    <hr class="modal-separator">

                    <h6 class="modal-seccion-titulo">Datos de la solicitud de Gestor</h6>
                    <div class="modal-seccion row g-3">
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <p class="modal-data" id="modalDescripcionGestor">—</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Experiencia previa</label>
                            <p class="modal-data" id="modalExperienciaGestor">—</p>
                        </div>
                    </div>
                </div>

                <hr class="modal-separator">

                <h6 class="modal-seccion-titulo">Descripción de la Solicitud</h6>
                <div class="modal-seccion">
                    <p class="modal-data" id="modalDescripcion">—</p>
                </div>

                <hr class="modal-separator">

                <h6 class="modal-seccion-titulo">Aceptaciones</h6>
                <div class="modal-seccion row g-3">
                    <div class="col-12">
                        <label class="form-label">Acepta Términos y Condiciones</label>
                        <p class="modal-data" id="modalAceptaTerminos">—</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Declara Veracidad de Datos</label>
                        <p class="modal-data" id="modalAceptaVeracidad">—</p>
                    </div>
                </div>

                <hr class="modal-separator">

                <h6 class="modal-seccion-titulo">Notas (Opcional)</h6>
                <textarea id="modalNotas" class="form-control" rows="4" placeholder="Añade notas o motivo de rechazo..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn-danger" id="btnRechazarModal">Rechazar solicitud</button>
                <button type="button" class="btn-primary" id="btnAprobarModal">Aprobar solicitud</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin/solicitudes.js') }}"></script>
@endsection
