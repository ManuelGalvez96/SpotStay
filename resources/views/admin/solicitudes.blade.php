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

    <div class="card-admin">
        <div class="card-header-admin">
            <span>Solicitudes pendientes</span>
            <span class="badge-contador">{{ $solicitudesPendientes->total() }}</span>
        </div>

        <div id="listaSolicitudes">
            @forelse($solicitudesPendientes as $solicitud)
                @php
                    $datos = json_decode($solicitud->datos_solicitud_arrendador);
                    $partes = explode(' ', $solicitud->nombre_usuario);
                    $iniciales = strtoupper(substr($partes[0],0,1)) . strtoupper(substr($partes[1]??'',0,1));
                    $colores = ['#B8CCE4','#A8D5BF','#F9E4A0','#FFD5CC','#D7EAF9','#EDE7F6','#D5F5E3','#FAD7D7'];
                    $color = $colores[$solicitud->id_solicitud_arrendador % 8];
                @endphp

                <div class="solicitud-card" data-id="{{ $solicitud->id_solicitud_arrendador }}">
                    <div class="solicitud-card-top">
                        <div class="solicitud-persona">
                            <div class="solicitud-avatar" style="background:{{ $color }}">{{ $iniciales }}</div>
                            <div class="solicitud-info">
                                <p class="solicitud-nombre">{{ $solicitud->nombre_usuario }}</p>
                                <p class="solicitud-email">{{ $solicitud->email_usuario }}</p>
                                <p class="solicitud-ciudad">
                                    <i class="bi bi-geo-alt"></i>
                                    {{ $datos->ciudad ?? '' }}
                                </p>
                            </div>
                        </div>
                        <div class="solicitud-meta-derecha">
                            <span class="solicitud-tiempo">{{ \Carbon\Carbon::parse($solicitud->creado_solicitud_arrendador)->diffForHumans() }}</span>
                            <span class="badge-estado badge-pendiente">Pendiente</span>
                        </div>
                    </div>

                    <div class="propiedad-solicitada">
                        <span class="seccion-label">PROPIEDAD SOLICITADA</span>
                        <div class="propiedad-resumen-grid">
                            <div class="dato-resumen">
                                <span class="dato-resumen-label">Dirección</span>
                                <span class="dato-resumen-valor">{{ $datos->direccion ?? '' }}</span>
                            </div>
                            <div class="dato-resumen">
                                <span class="dato-resumen-label">Tipo</span>
                                <span class="dato-resumen-valor">{{ $datos->tipo ?? '' }}</span>
                            </div>
                            <div class="dato-resumen">
                                <span class="dato-resumen-label">Precio est.</span>
                                <span class="dato-resumen-valor">${{ $datos->precio_estimado ?? '' }}/mes</span>
                            </div>
                            <div class="dato-resumen">
                                <span class="dato-resumen-label">Habitaciones</span>
                                <span class="dato-resumen-valor">{{ $datos->habitaciones ?? '' }}</span>
                            </div>
                            <div class="dato-resumen">
                                <span class="dato-resumen-label">Baños</span>
                                <span class="dato-resumen-valor">{{ $datos->banos ?? '' }}</span>
                            </div>
                            <div class="dato-resumen">
                                <span class="dato-resumen-label">Tamaño</span>
                                <span class="dato-resumen-valor">{{ $datos->tamano ?? '' }} m²</span>
                            </div>
                        </div>
                    </div>

                    <div class="solicitud-acciones">
                        <button class="btn-aprobar-sol" data-id="{{ $solicitud->id_solicitud_arrendador }}">
                            <i class="bi bi-check-circle"></i>
                            <span>Aprobar solicitud</span>
                        </button>
                        <button class="btn-rechazar-sol" data-id="{{ $solicitud->id_solicitud_arrendador }}">
                            <i class="bi bi-x-circle"></i>
                            <span>Rechazar</span>
                        </button>
                        <button class="btn-ver-sol" data-id="{{ $solicitud->id_solicitud_arrendador }}">
                            <i class="bi bi-eye"></i>
                            <span>Ver detalles</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="sin-resultados">No hay solicitudes pendientes</div>
            @endforelse
        </div>

        <div class="tabla-footer">
            Mostrando {{ $solicitudesPendientes->firstItem() ?? 0 }}-{{ $solicitudesPendientes->lastItem() ?? 0 }} de {{ $solicitudesPendientes->total() }} solicitudes
        </div>
    </div>

    <div>
        <div class="card-admin card-con-franja">
            <div class="card-franja"></div>
            <div class="card-header-admin">
                <span>Aprobadas recientemente</span>
                <span class="badge-contador-verde">{{ $aprobadas }}</span>
            </div>
            @foreach($ultimasAprobadas as $aprobada)
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
                    <div class="historial-meta">
                        <span class="badge-estado badge-activo">Aprobada</span>
                        <span class="historial-tiempo">{{ \Carbon\Carbon::parse($aprobada->actualizado_solicitud_arrendador)->diffForHumans() }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card-admin card-con-franja card-franja-roja">
            <div class="card-franja-roja-el"></div>
            <div class="card-header-admin">
                <span>Rechazadas recientemente</span>
                <span class="badge-contador-rojo">{{ $rechazadas }}</span>
            </div>
            @foreach($ultimasRechazadas as $rechazada)
                @php
                    $partesR = explode(' ', $rechazada->nombre_usuario);
                    $inicialesR = strtoupper(substr($partesR[0],0,1)) . strtoupper(substr($partesR[1]??'',0,1));
                @endphp
                <div class="historial-item">
                    <div class="solicitud-avatar-mini">{{ $inicialesR }}</div>
                    <div class="historial-info">
                        <span class="historial-nombre">{{ $rechazada->nombre_usuario }}</span>
                        <span class="historial-motivo">{{ $rechazada->notas_solicitud_arrendador ?? '' }}</span>
                    </div>
                    <div class="historial-meta">
                        <span class="badge-estado badge-inactivo">Rechazada</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card-admin card-con-franja">
            <div class="card-franja"></div>
            <div class="tiempo-medio-centro">
                <span class="tiempo-medio-numero">{{ $tiempoMedio }}h</span>
                <span class="tiempo-medio-label">tiempo medio de aprobación</span>
            </div>
            <div class="tiempo-medio-stats">
                <div class="stat-item">
                    <span class="stat-numero">{{ $solicitudesPendientes->total() }}</span>
                    <span class="stat-label">Pendientes</span>
                </div>
                <div class="stat-item">
                    <span class="stat-numero">{{ $aprobadas }}</span>
                    <span class="stat-label">Este mes</span>
                </div>
                <div class="stat-item">
                    <span class="stat-numero">94%</span>
                    <span class="stat-label">Tasa aprobación</span>
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
                <p id="modalTelefono"></p>
                <p id="modalCiudad"><i class="bi bi-geo-alt"></i></p>
            </div>
        </div>

        <div class="modal-separador"></div>

        <span class="seccion-label">PROPIEDAD SOLICITADA</span>
        <div class="modal-grid-2" id="modalDatosPropiedad"></div>

        <div class="modal-separador"></div>

        <span class="seccion-label">DOCUMENTACIÓN APORTADA</span>
        <div class="docs-lista">
            <div class="doc-item">
                <i class="bi bi-file-pdf"></i>
                <span>DNI escaneado.pdf</span>
                <a href="#" class="link-accion">Descargar</a>
            </div>
            <div class="doc-item">
                <i class="bi bi-file-pdf"></i>
                <span>Nómina marzo 2025.pdf</span>
                <a href="#" class="link-accion">Descargar</a>
            </div>
            <div class="doc-item">
                <i class="bi bi-file-image"></i>
                <span>Foto propiedad.jpg</span>
                <a href="#" class="link-accion">Ver</a>
            </div>
        </div>

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
