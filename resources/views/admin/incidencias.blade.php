@extends('layouts.admin')

@section('titulo', 'Incidencias — SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/incidencias.css') }}">
@endsection

@section('content')

<div class="hero-admin">
    <h1>Gestión de incidencias</h1>
    <p>Supervisa y resuelve los problemas reportados por los inquilinos</p>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="kpi-grid-pequeno">
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo">{{ $totalAbiertas }}</span>
            <span class="kpi-mini-label">Abiertas</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-naranja">
            <i class="bi bi-tools"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-naranja">{{ $totalEnProceso }}</span>
            <span class="kpi-mini-label">En proceso</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $totalResueltas }}</span>
            <span class="kpi-mini-label">Resueltas este mes</span>
        </div>
    </div>

    <div class="kpi-mini kpi-mini-urgente">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-exclamation-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <div class="kpi-urgente-fila">
                <span class="kpi-mini-numero kpi-mini-numero-rojo">{{ $urgentes }}</span>
                <span class="badge-pulsante"></span>
            </div>
            <span class="kpi-mini-label">Urgentes</span>
        </div>
    </div>
</div>

<div class="toolbar-admin">
    <div class="toolbar-izquierda">
        <div class="input-busqueda">
            <i class="bi bi-search"></i>
            <input type="text" id="buscadorInc" placeholder="Buscar incidencia...">
        </div>
        <select id="selectCategoria" class="select-filtro">
            <option value="">Todas las categorías</option>
            <option value="fontaneria">Fontanería</option>
            <option value="electricidad">Electricidad</option>
            <option value="calefaccion">Calefacción</option>
            <option value="climatizacion">Climatización</option>
            <option value="humedades">Humedades</option>
            <option value="cerrajeria">Cerrajería</option>
            <option value="otro">Otro</option>
        </select>
        <select id="selectPrioridad" class="select-filtro">
            <option value="">Todas las prioridades</option>
            <option value="urgente">Urgente</option>
            <option value="alta">Alta</option>
            <option value="media">Media</option>
            <option value="baja">Baja</option>
        </select>
        <select id="selectPropiedad" class="select-filtro">
            <option value="">Todas las propiedades</option>
            @foreach($propiedades as $prop)
                <option value="{{ $prop->id_propiedad }}">{{ $prop->titulo_propiedad }}</option>
            @endforeach
        </select>
    </div>
    <div class="toolbar-derecha">
        <div class="btns-vista">
            <button id="btnVistaKanban" class="btn-vista activo">
                <i class="bi bi-kanban"></i>
            </button>
            <button id="btnVistaLista" class="btn-vista">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>
        <button id="btnNuevaIncidencia" class="btn-primario">
            <i class="bi bi-plus"></i>
            <span>Nueva incidencia</span>
        </button>
    </div>
</div>

<div class="kanban-board" id="kanbanBoard">

    <div class="kanban-col kanban-col-abierta">
        <div class="kanban-col-header">
            <span class="kanban-punto kanban-punto-rojo"></span>
            <span>Abierta</span>
            <span class="badge-kanban badge-kanban-rojo">{{ $totalAbiertas }}</span>
        </div>
        <div class="kanban-col-body">
            @forelse($abiertas as $inc)
                @php
                    $bordeColor = match($inc->prioridad_incidencia) {
                        'urgente' => '#EF4444',
                        'alta'    => '#D97706',
                        'media'   => '#6B7280',
                        'baja'    => '#1AA068',
                        default   => '#6B7280'
                    };
                    $partesInc = explode(' ', $inc->nombre_inquilino ?? '');
                    $inicialesInc = strtoupper(substr($partesInc[0] ?? '', 0, 1)) . strtoupper(substr($partesInc[1] ?? '', 0, 1));
                    $iconoCat = match($inc->categoria_incidencia) {
                        'fontaneria'   => 'bi-droplet',
                        'electricidad' => 'bi-lightning',
                        'calefaccion'  => 'bi-thermometer',
                        'climatizacion' => 'bi-fan',
                        'humedades'    => 'bi-cloud-rain',
                        'cerrajeria'   => 'bi-key',
                        default        => 'bi-wrench'
                    };
                @endphp
                <div class="tarjeta-inc" data-id="{{ $inc->id_incidencia }}" style="border-left: 3px solid {{ $bordeColor }}">
                    <div class="tarjeta-inc-top">
                        <span class="badge-prioridad badge-prioridad-{{ $inc->prioridad_incidencia }}">{{ ucfirst($inc->prioridad_incidencia) }}</span>
                        <span class="tarjeta-tiempo">{{ \Carbon\Carbon::parse($inc->creado_incidencia)->diffForHumans() }}</span>
                    </div>
                    <p class="tarjeta-titulo">{{ $inc->titulo_incidencia }}</p>
                    <p class="tarjeta-desc">{{ \Illuminate\Support\Str::limit($inc->descripcion_incidencia, 60) }}</p>
                    <div class="tarjeta-inc-bottom">
                        <span class="tarjeta-propiedad">{{ \Illuminate\Support\Str::limit($inc->direccion_propiedad ?? '', 15) }}</span>
                        <div class="tarjeta-inquilino">
                            <div class="avatar-mini">{{ $inicialesInc }}</div>
                            <span>{{ explode(' ', $inc->nombre_inquilino ?? '')[0] }} {{ strtoupper(substr(explode(' ', $inc->nombre_inquilino ?? '')[1] ?? '', 0, 1)) }}.</span>
                        </div>
                    </div>
                    <div class="tarjeta-categoria">
                        <i class="bi {{ $iconoCat }}"></i>
                        <span>{{ ucfirst($inc->categoria_incidencia) }}</span>
                    </div>
                </div>
            @empty
                <p class="kanban-vacio">Sin incidencias abiertas</p>
            @endforelse
        </div>
    </div>

    <div class="kanban-col kanban-col-proceso">
        <div class="kanban-col-header">
            <span class="kanban-punto kanban-punto-naranja"></span>
            <span>En proceso</span>
            <span class="badge-kanban badge-kanban-naranja">{{ $totalEnProceso }}</span>
        </div>
        <div class="kanban-col-body">
            @forelse($enProceso as $inc)
                @php
                    $bordeColor = match($inc->prioridad_incidencia) {
                        'urgente' => '#EF4444',
                        'alta'    => '#D97706',
                        'media'   => '#6B7280',
                        'baja'    => '#1AA068',
                        default   => '#6B7280'
                    };
                    $partesInc = explode(' ', $inc->nombre_inquilino ?? '');
                    $inicialesInc = strtoupper(substr($partesInc[0] ?? '', 0, 1)) . strtoupper(substr($partesInc[1] ?? '', 0, 1));
                    $iconoCat = match($inc->categoria_incidencia) {
                        'fontaneria'   => 'bi-droplet',
                        'electricidad' => 'bi-lightning',
                        'calefaccion'  => 'bi-thermometer',
                        'climatizacion' => 'bi-fan',
                        'humedades'    => 'bi-cloud-rain',
                        'cerrajeria'   => 'bi-key',
                        default        => 'bi-wrench'
                    };
                @endphp
                <div class="tarjeta-inc" data-id="{{ $inc->id_incidencia }}" style="border-left: 3px solid {{ $bordeColor }}">
                    <div class="tarjeta-inc-top">
                        <span class="badge-prioridad badge-prioridad-{{ $inc->prioridad_incidencia }}">{{ ucfirst($inc->prioridad_incidencia) }}</span>
                        <span class="tarjeta-tiempo">{{ \Carbon\Carbon::parse($inc->creado_incidencia)->diffForHumans() }}</span>
                    </div>
                    <p class="tarjeta-titulo">{{ $inc->titulo_incidencia }}</p>
                    <p class="tarjeta-desc">{{ \Illuminate\Support\Str::limit($inc->descripcion_incidencia, 60) }}</p>
                    <div class="tarjeta-inc-bottom">
                        <span class="tarjeta-propiedad">{{ \Illuminate\Support\Str::limit($inc->direccion_propiedad ?? '', 15) }}</span>
                        <div class="tarjeta-inquilino">
                            <div class="avatar-mini">{{ $inicialesInc }}</div>
                            <span>{{ explode(' ', $inc->nombre_inquilino ?? '')[0] }} {{ strtoupper(substr(explode(' ', $inc->nombre_inquilino ?? '')[1] ?? '', 0, 1)) }}.</span>
                        </div>
                    </div>
                    <div class="tarjeta-categoria">
                        <i class="bi {{ $iconoCat }}"></i>
                        <span>{{ ucfirst($inc->categoria_incidencia) }}</span>
                    </div>
                    @if($inc->nombre_gestor)
                        <div class="tarjeta-gestor">
                            <span class="gestor-label">Asignado a:</span>
                            <div class="avatar-mini avatar-mini-gestor">MG</div>
                            <span class="gestor-nombre">{{ $inc->nombre_gestor }} (gestor)</span>
                        </div>
                    @endif
                </div>
            @empty
                <p class="kanban-vacio">Sin incidencias en proceso</p>
            @endforelse
        </div>
    </div>

    <div class="kanban-col kanban-col-resuelta">
        <div class="kanban-col-header">
            <span class="kanban-punto kanban-punto-verde"></span>
            <span>Resuelta</span>
            <span class="badge-kanban badge-kanban-verde">{{ $totalResueltas }}</span>
        </div>
        <div class="kanban-col-body">
            @forelse($resueltas as $inc)
                <div class="tarjeta-inc tarjeta-inc-resuelta" data-id="{{ $inc->id_incidencia }}" style="position:relative">
                    <i class="bi bi-check-circle-fill check-resuelta"></i>
                    <p class="tarjeta-titulo">{{ $inc->titulo_incidencia }}</p>
                    <div class="tarjeta-inc-bottom">
                        <span class="tarjeta-propiedad">{{ \Illuminate\Support\Str::limit($inc->direccion_propiedad ?? '', 15) }}</span>
                    </div>
                    <p class="tarjeta-tiempo-resolucion">Resuelto · {{ \Carbon\Carbon::parse($inc->creado_incidencia)->diffForHumans() }}</p>
                </div>
            @empty
                <p class="kanban-vacio">Sin incidencias resueltas</p>
            @endforelse
        </div>
    </div>

    <div class="kanban-col kanban-col-cerrada">
        <div class="kanban-col-header">
            <span class="kanban-punto kanban-punto-gris"></span>
            <span>Cerrada</span>
            <span class="badge-kanban badge-kanban-gris">{{ $totalCerradas }}</span>
        </div>
        <div class="kanban-col-body">
            @forelse($cerradas as $inc)
                <div class="tarjeta-inc tarjeta-inc-cerrada" data-id="{{ $inc->id_incidencia }}" style="position:relative">
                    <i class="bi bi-lock-fill lock-cerrada"></i>
                    <p class="tarjeta-titulo tarjeta-titulo-cerrada">{{ $inc->titulo_incidencia }}</p>
                    <p class="tarjeta-propiedad">{{ \Illuminate\Support\Str::limit($inc->direccion_propiedad ?? '', 20) }}</p>
                    <p class="tarjeta-tiempo">Cerrada · {{ \Carbon\Carbon::parse($inc->creado_incidencia)->diffForHumans() }}</p>
                </div>
            @empty
                <p class="kanban-vacio">Sin incidencias cerradas</p>
            @endforelse
        </div>
    </div>

</div>

<!-- Vista de Lista -->
<div class="card-admin" id="vistaLista" style="display: none;">
    <div class="tabla-header">
        <span id="contadorResultados">0 incidencias encontradas</span>
        <div class="paginacion">
            <button id="btnAnteriorInc" class="btn-pag">← Anterior</button>
            <span id="paginasInc">
            </span>
            <button id="btnSiguienteInc" class="btn-pag">Siguiente →</button>
        </div>
    </div>
    <table class="tabla-admin" id="tablaIncidencias">
        <thead>
            <tr>
                <th>TÍTULO</th>
                <th>PROPIEDAD</th>
                <th>CATEGORÍA</th>
                <th>PRIORIDAD</th>
                <th>ESTADO</th>
                <th>REPORTADA POR</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody id="tbodyIncidencias">
        </tbody>
    </table>
</div>

<!-- MODAL DETALLE INCIDENCIA (Bootstrap 5) -->
<div class="modal fade" id="modalIncidencia" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3" style="flex: 1;">
                    <h5 class="modal-title mb-0">Detalle de incidencia</h5>
                    <span id="modalBadgePrioridad" class="badge"></span>
                    <span id="modalBadgeCategoria" class="badge bg-secondary"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <span class="seccion-label">DESCRIPCIÓN</span>
                <h6 id="modalTituloInc" class="fw-bold mb-2"></h6>
                <p id="modalDescInc" class="text-muted mb-3"></p>

                <hr>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Propiedad</small>
                        <p id="modalPropiedadInc" class="fw-500"></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Reportada por</small>
                        <p id="modalInquilinoInc" class="fw-500"></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Fecha reporte</small>
                        <p id="modalFechaInc" class="fw-500"></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Categoría</small>
                        <p id="modalCategoriaInc" class="fw-500"></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Prioridad</small>
                        <span id="modalPrioridadInc"></span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Estado actual</small>
                        <span id="modalEstadoInc"></span>
                    </div>
                </div>

                <hr>

                <span class="seccion-label">ASIGNAR GESTOR</span>
                <div class="d-flex gap-2 mb-3">
                    <select id="selectGestorModal" class="form-select form-select-sm">
                        <option value="">Sin asignar</option>
                        @foreach($gestores as $gestor)
                            <option value="{{ $gestor->id_usuario }}">{{ $gestor->nombre_usuario }}</option>
                        @endforeach
                    </select>
                    <button id="btnAsignar" class="btn btn-sm btn-primary">Asignar</button>
                </div>

                <hr>

                <span class="seccion-label">CAMBIAR ESTADO</span>
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <button class="btn btn-sm btn-outline-secondary btn-estado" data-estado="abierta">Abierta</button>
                    <button class="btn btn-sm btn-outline-secondary btn-estado" data-estado="en_proceso">En proceso</button>
                    <button class="btn btn-sm btn-outline-secondary btn-estado" data-estado="resuelta">Resuelta</button>
                    <button class="btn btn-sm btn-outline-secondary btn-estado" data-estado="cerrada">Cerrada</button>
                </div>

                <hr>

                <span class="seccion-label">HISTORIAL DE CAMBIOS</span>
                <div id="timelineHistorial" class="mb-3"></div>

                <hr>

                <span class="seccion-label">NOTAS INTERNAS</span>
                <textarea id="modalNotasInc" class="form-control" rows="3" placeholder="Añade notas sobre esta incidencia..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCerrarInc">Marcar como cerrada</button>
                <button type="button" class="btn btn-outline-secondary" id="btnContactarInquilino">
                    <i class="bi bi-chat"></i> Contactar
                </button>
                <button type="button" class="btn btn-primary" id="btnGuardarCambios">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay-nueva" id="modalOverlayNueva"></div>
<!-- MODAL NUEVA INCIDENCIA (Bootstrap 5) -->
<div class="modal fade" id="modalNuevaIncidencia" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva incidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="formNuevaIncidencia">
                <span class="seccion-label">PROPIEDAD</span>
                <select id="nuevaPropiedadId" class="form-select form-select-sm mb-3">
                    <option value="">Selecciona una propiedad...</option>
                    @foreach($propiedades as $prop)
                        <option value="{{ $prop->id_propiedad }}">{{ $prop->titulo_propiedad }} — {{ $prop->ciudad_propiedad }}</option>
                    @endforeach
                </select>

                <span class="seccion-label">INQUILINO QUE REPORTA</span>
                <select id="nuevaInquilinoId" class="form-select form-select-sm mb-3">
                    <option value="">Selecciona un inquilino...</option>
                    @foreach($inquilinos as $inq)
                        <option value="{{ $inq->id_usuario }}">{{ $inq->nombre_usuario }}</option>
                    @endforeach
                </select>

                <span class="seccion-label">TÍTULO</span>
                <input type="text" id="nuevaTitulo" class="form-control form-control-sm mb-3" placeholder="Describe brevemente el problema...">

                <span class="seccion-label">DESCRIPCIÓN</span>
                <textarea id="nuevaDescripcion" class="form-control form-control-sm mb-3" rows="3" placeholder="Explica el problema con detalle..."></textarea>

                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="seccion-label">CATEGORÍA</span>
                        <select id="nuevaCategoria" class="form-select form-select-sm">
                            <option value="fontaneria">Fontanería</option>
                            <option value="electricidad">Electricidad</option>
                            <option value="calefaccion">Calefacción</option>
                            <option value="climatizacion">Climatización</option>
                            <option value="humedades">Humedades</option>
                            <option value="cerrajeria">Cerrajería</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <span class="seccion-label">PRIORIDAD</span>
                        <select id="nuevaPrioridad" class="form-select form-select-sm">
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelarNueva" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarNueva">
                    <i class="bi bi-plus"></i> Crear incidencia
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin/incidencias.js') }}"></script>
@endsection
