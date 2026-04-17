@extends('layouts.admin')

@section('titulo', 'Solicitudes — SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/solicitudes.css') }}">
@endsection

@section('content')

<div class="hero-admin">
    <h1>Gestión de solicitudes</h1>
    <p>Revisa y aprueba las solicitudes de nuevos arrendadores</p>
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
            <span class="kpi-mini-numero kpi-mini-numero-naranja">{{ $solicitudesPendientes->total() }}</span>
            <span class="kpi-mini-label">Pendientes</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $aprobadas }}</span>
            <span class="kpi-mini-label">Aprobadas este mes</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo">{{ $rechazadas }}</span>
            <span class="kpi-mini-label">Rechazadas este mes</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-azul">
            <i class="bi bi-inbox"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $totalSolicitudes }}</span>
            <span class="kpi-mini-label">Total solicitudes</span>
        </div>
    </div>
</div>

<div class="toolbar-admin">
    <div class="toolbar-izquierda">
        <div class="input-busqueda">
            <i class="bi bi-search"></i>
            <input type="text" id="buscadorSolicitudes" placeholder="Buscar por nombre o ciudad...">
        </div>
        <select id="selectEstadoSol" class="select-filtro">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="aprobada">Aprobada</option>
            <option value="rechazada">Rechazada</option>
        </select>
        <select id="selectCiudadSol" class="select-filtro">
            <option value="">Todas las ciudades</option>
            <option value="Madrid">Madrid</option>
            <option value="Barcelona">Barcelona</option>
            <option value="Valencia">Valencia</option>
            <option value="Sevilla">Sevilla</option>
            <option value="Bilbao">Bilbao</option>
        </select>
    </div>
    <div class="toolbar-derecha">
        <span class="texto-pendientes">{{ $solicitudesPendientes->total() }} pendientes de revisión</span>
    </div>
</div>

<div class="solicitudes-grid">
    
    <div class="columna-izquierda-sol">
        <div class="card-admin">
            <div class="card-header-admin">
                <span>Solicitudes</span>
                <span class="badge-contador">{{ $solicitudesPendientes->total() }}</span>
            </div>

            <div class="tabla-contenedor">
                <table class="tabla-admin tabla-solicitudes">
                    <thead>
                        <tr>
                            <th>SOLICITANTE</th>
                            <th>CIUDAD</th>
                            <th>PROPIEDAD</th>
                            <th>FECHA</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tablaSolicitudes">
                        @forelse($solicitudesPendientes as $solicitud)
                            @php
                                $datos = json_decode($solicitud->datos_solicitud_arrendador);
                                $partes = explode(' ', $solicitud->nombre_usuario);
                                $iniciales = strtoupper(substr($partes[0],0,1)) . strtoupper(substr($partes[1]??'',0,1));
                                $colores = ['#B8CCE4','#A8D5BF','#F9E4A0','#FFD5CC','#D7EAF9','#EDE7F6','#D5F5E3','#FAD7D7'];
                                $color = $colores[$solicitud->id_solicitud_arrendador % 8];
                                $fecha = \Carbon\Carbon::parse($solicitud->creado_solicitud_arrendador)->format('d/m/Y');
                            @endphp
                            <tr class="fila-solicitud" data-id="{{ $solicitud->id_solicitud_arrendador }}">
                                <td>
                                    <div class="usuario-celda">
                                        <div class="avatar-tabla" style="background:{{ $color }}">{{ $iniciales }}</div>
                                        <div class="usuario-info-tabla">
                                            <span class="usuario-nombre-tabla">{{ $solicitud->nombre_usuario }}</span>
                                            <span class="usuario-email-tabla">{{ $solicitud->email_usuario }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $datos->ciudad ?? '—' }}</td>
                                <td>{{ $datos->direccion ?? '—' }}</td>
                                <td>{{ $fecha }}</td>
                                <td>
                                    <span class="badge-estado badge-pendiente">Pendiente</span>
                                </td>
                                <td>
                                    <div class="acciones-tabla">
                                        <button class="btn-icono btn-ver-sol" data-id="{{ $solicitud->id_solicitud_arrendador }}" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn-icono btn-aprobar-sol" data-id="{{ $solicitud->id_solicitud_arrendador }}" title="Aprobar">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button class="btn-icono btn-rechazar-sol" data-id="{{ $solicitud->id_solicitud_arrendador }}" title="Rechazar">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="sin-resultados">No hay solicitudes pendientes</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="tabla-footer">
                <span class="info-paginacion">Mostrando {{ $solicitudesPendientes->firstItem() ?? 0 }}-{{ $solicitudesPendientes->lastItem() ?? 0 }} de {{ $solicitudesPendientes->total() }} solicitudes</span>
                <div class="paginacion-links" id="paginacionSolicitudes">
                    @if ($solicitudesPendientes->onFirstPage())
                        <span class="btn-paginacion deshabilitado"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <button class="btn-paginacion" data-page="{{ $solicitudesPendientes->currentPage() - 1 }}"><i class="bi bi-chevron-left"></i></button>
                    @endif

                    @foreach ($solicitudesPendientes->getUrlRange(1, $solicitudesPendientes->lastPage()) as $page => $url)
                        @if ($page == $solicitudesPendientes->currentPage())
                            <span class="btn-paginacion activo">{{ $page }}</span>
                        @else
                            <button class="btn-paginacion" data-page="{{ $page }}">{{ $page }}</button>
                        @endif
                    @endforeach

                    @if ($solicitudesPendientes->hasMorePages())
                        <button class="btn-paginacion" data-page="{{ $solicitudesPendientes->currentPage() + 1 }}"><i class="bi bi-chevron-right"></i></button>
                    @else
                        <span class="btn-paginacion deshabilitado"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="columna-derecha-sol">
        
        <div class="card-admin card-estadisticas">
            <div class="card-header-admin">
                <span>Aprobadas este mes</span>
                <span class="badge-contador-verde">{{ $aprobadas }}</span>
            </div>
            <div class="historial-lista">
                @forelse($ultimasAprobadas as $aprobada)
                    @php
                        $partesA = explode(' ', $aprobada->nombre_usuario);
                        $inicialesA = strtoupper(substr($partesA[0],0,1)) . strtoupper(substr($partesA[1]??'',0,1));
                        $colorA = $colores[$aprobada->id_solicitud_arrendador % 8];
                        $datosA = json_decode($aprobada->datos_solicitud_arrendador);
                    @endphp
                    <div class="historial-item">
                        <div class="solicitud-avatar-mini" style="background:{{ $colorA }}">{{ $inicialesA }}</div>
                        <div class="historial-info">
                            <span class="historial-nombre">{{ $aprobada->nombre_usuario }}</span>
                            <span class="historial-ciudad">{{ $datosA->ciudad ?? '' }}</span>
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
                <span class="badge-contador-rojo">{{ $rechazadas }}</span>
            </div>
            <div class="historial-lista">
                @forelse($ultimasRechazadas as $rechazada)
                    @php
                        $partesR = explode(' ', $rechazada->nombre_usuario);
                        $inicialesR = strtoupper(substr($partesR[0],0,1)) . strtoupper(substr($partesR[1]??'',0,1));
                        $colorR = $colores[$rechazada->id_solicitud_arrendador % 8];
                    @endphp
                    <div class="historial-item">
                        <div class="solicitud-avatar-mini" style="background:{{ $colorR }}">{{ $inicialesR }}</div>
                        <div class="historial-info">
                            <span class="historial-nombre">{{ $rechazada->nombre_usuario }}</span>
                            <span class="historial-motivo">{{ Str::limit($rechazada->notas_solicitud_arrendador ?? 'Sin motivo', 30) }}</span>
                        </div>
                        <span class="badge-estado badge-inactivo">Rechazada</span>
                    </div>
                @empty
                    <div class="sin-items">No hay solicitudes rechazadas aún</div>
                @endforelse
            </div>
        </div>

        <div class="card-admin card-tiempo-medio">
            <div class="tiempo-medio-centro">
                <span class="tiempo-medio-numero">{{ $tiempoMedio }}</span>
                <span class="tiempo-medio-unit">horas</span>
                <span class="tiempo-medio-label">tiempo medio de aprobación</span>
            </div>
            <div class="tiempo-medio-stats">
                <div class="stat-item">
                    <span class="stat-numero">{{ $solicitudesPendientes->total() }}</span>
                    <span class="stat-label">Pendientes</span>
                </div>
                <div class="stat-item">
                    <span class="stat-numero">{{ $totalSolicitudes }}</span>
                    <span class="stat-label">Total</span>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal-overlay" id="modalOverlay"></div>
<div class="modal-admin" id="modalSolicitud">
    <div class="modal-header-admin">
        <div class="modal-titulo-grupo">
            <span>Detalle de solicitud</span>
            <span class="badge-estado badge-pendiente" id="modalBadgeEstado">Pendiente</span>
        </div>
        <button id="btnCerrarModal" class="btn-cerrar-modal"><i class="bi bi-x"></i></button>
    </div>

    <div class="modal-cuerpo">
        <span class="seccion-label">SOLICITANTE</span>
        <div class="modal-persona-header">
            <div class="modal-avatar" id="modalAvatar"></div>
            <div class="modal-persona-info">
                <h2 id="modalNombre"></h2>
                <p id="modalEmail"></p>
                <p id="modalCiudad"><i class="bi bi-geo-alt"></i></p>
            </div>
        </div>

        <div class="modal-separador"></div>

        <span class="seccion-label">PROPIEDAD SOLICITADA</span>
        <div class="modal-grid-datos" id="modalDatosPropiedad"></div>

        <div class="modal-separador"></div>

        <span class="seccion-label">NOTAS (opcional)</span>
        <textarea id="modalNotas" class="textarea-admin" placeholder="Añade notas o motivo de rechazo..."></textarea>
    </div>

    <div class="modal-footer-admin">
        <button id="btnRechazarModal" class="btn-desactivar">Rechazar solicitud</button>
        <button id="btnAprobarModal" class="btn-primario">Aprobar solicitud</button>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin/solicitudes.js') }}"></script>
@endsection
