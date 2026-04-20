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

<div class="modal-overlay" id="modalOverlay"></div>
<div class="modal-admin modal-ancho" id="modalIncidencia">
    <div class="modal-header-admin">
        <div class="modal-titulo-grupo">
            <span class="modal-titulo">Detalle de incidencia</span>
            <span id="modalBadgePrioridad" class="badge-prioridad"></span>
            <span id="modalBadgeCategoria" class="badge-categoria-modal"></span>
        </div>
        <button id="btnCerrarModal" class="btn-cerrar-modal">
            <i class="bi bi-x"></i>
        </button>
    </div>

    <div class="modal-imagen-inc" id="modalImagenInc">
        <div class="modal-imagen-texto" id="modalImagenTexto"></div>
    </div>

    <div class="modal-cuerpo">
        <span class="seccion-label">DESCRIPCIÓN</span>
        <h3 id="modalTituloInc" class="modal-titulo-inc"></h3>
        <p id="modalDescInc" class="modal-desc-inc"></p>

        <div class="modal-separador"></div>

        <div class="modal-grid-2">
            <div class="dato-item">
                <span class="dato-label">Propiedad</span>
                <span class="dato-valor" id="modalPropiedadInc"></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Reportada por</span>
                <span class="dato-valor" id="modalInquilinoInc"></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Fecha reporte</span>
                <span class="dato-valor" id="modalFechaInc"></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Categoría</span>
                <span class="dato-valor" id="modalCategoriaInc"></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Prioridad</span>
                <span id="modalPrioridadInc"></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Estado actual</span>
                <span id="modalEstadoInc"></span>
            </div>
        </div>

        <div class="modal-separador"></div>

        <span class="seccion-label">ASIGNAR GESTOR</span>
        <div class="asignacion-fila">
            <select id="selectGestorModal" class="select-filtro select-flex">
                <option value="">Sin asignar</option>
                @foreach($gestores as $gestor)
                    <option value="{{ $gestor->id_usuario }}">{{ $gestor->nombre_usuario }}</option>
                @endforeach
            </select>
            <button id="btnAsignar" class="btn-primario btn-asignar">Asignar</button>
        </div>

        <div class="modal-separador"></div>

        <span class="seccion-label">CAMBIAR ESTADO</span>
        <div class="estados-fila">
            <button class="btn-estado" data-estado="abierta">Abierta</button>
            <button class="btn-estado" data-estado="en_proceso">En proceso</button>
            <button class="btn-estado" data-estado="resuelta">Resuelta</button>
            <button class="btn-estado" data-estado="cerrada">Cerrada</button>
        </div>

        <div class="modal-separador"></div>

        <span class="seccion-label">HISTORIAL DE CAMBIOS</span>
        <div class="timeline-inc" id="timelineHistorial">
            <div class="timeline-linea-v"></div>
        </div>

        <div class="modal-separador"></div>

        <span class="seccion-label">NOTAS INTERNAS</span>
        <textarea id="modalNotasInc" class="textarea-admin" placeholder="Añade notas sobre esta incidencia..."></textarea>
    </div>

    <div class="modal-footer-admin">
        <button id="btnCerrarInc" class="btn-exportar">Marcar como cerrada</button>
        <div class="modal-footer-derecha">
            <button id="btnContactarInquilino" class="btn-exportar">
                <i class="bi bi-chat"></i>
                <span>Contactar inquilino</span>
            </button>
            <button id="btnGuardarCambios" class="btn-primario">Guardar cambios</button>
        </div>
    </div>
</div>

<div class="modal-overlay-nueva" id="modalOverlayNueva"></div>
<div class="modal-admin" id="modalNuevaIncidencia">
    <div class="modal-header-admin">
        <div class="modal-titulo-grupo">
            <span class="modal-titulo">Nueva incidencia</span>
        </div>
        <button id="btnCerrarModalNueva" class="btn-cerrar-modal">
            <i class="bi bi-x"></i>
        </button>
    </div>

    <div class="modal-cuerpo">
        <span class="seccion-label">PROPIEDAD</span>
        <select id="nuevaPropiedadId" class="select-filtro select-full">
            <option value="">Selecciona una propiedad...</option>
            @foreach($propiedades as $prop)
                <option value="{{ $prop->id_propiedad }}">{{ $prop->titulo_propiedad }} — {{ $prop->ciudad_propiedad }}</option>
            @endforeach
        </select>

        <div class="modal-separador"></div>

        <span class="seccion-label">INQUILINO QUE REPORTA</span>
        <select id="nuevaInquilinoId" class="select-filtro select-full">
            <option value="">Selecciona un inquilino...</option>
            @foreach($inquilinos as $inq)
                <option value="{{ $inq->id_usuario }}">{{ $inq->nombre_usuario }}</option>
            @endforeach
        </select>

        <div class="modal-separador"></div>

        <span class="seccion-label">TÍTULO</span>
        <input type="text" id="nuevaTitulo" class="input-full" placeholder="Describe brevemente el problema...">

        <div class="modal-separador"></div>

        <span class="seccion-label">DESCRIPCIÓN</span>
        <textarea id="nuevaDescripcion" class="textarea-admin" placeholder="Explica el problema con detalle..."></textarea>

        <div class="modal-separador"></div>

        <div class="nueva-inc-grid">
            <div>
                <span class="seccion-label">CATEGORÍA</span>
                <select id="nuevaCategoria" class="select-filtro select-full">
                    <option value="fontaneria">Fontanería</option>
                    <option value="electricidad">Electricidad</option>
                    <option value="calefaccion">Calefacción</option>
                    <option value="climatizacion">Climatización</option>
                    <option value="humedades">Humedades</option>
                    <option value="cerrajeria">Cerrajería</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div>
                <span class="seccion-label">PRIORIDAD</span>
                <select id="nuevaPrioridad" class="select-filtro select-full">
                    <option value="baja">Baja</option>
                    <option value="media">Media</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
        </div>
    </div>

    <div class="modal-footer-admin">
        <button id="btnCancelarNueva" class="btn-exportar">Cancelar</button>
        <button id="btnGuardarNueva" class="btn-primario">
            <i class="bi bi-plus"></i>
            <span>Crear incidencia</span>
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin/incidencias.js') }}"></script>
@endsection
