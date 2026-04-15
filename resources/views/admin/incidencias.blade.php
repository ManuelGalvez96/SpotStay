@extends('layouts.admin')

@section('titulo', 'Incidencias — SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/incidencias.css') }}">
@endsection

@section('content')

<div class="hero-admin">
    <h1>Gestión de incidencias</h1>
    <p>Supervisa y resuelve los problemas reportados</p>
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
            <i class="bi bi-hourglass-split"></i>
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
            <span class="kpi-mini-label">Resueltas</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo-intenso">
            <i class="bi bi-fire"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo">{{ $urgentes }}</span>
            <span class="kpi-mini-label">Urgentes</span>
            <div class="badge-urgente-pulsante"></div>
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
            <option value="otro">Otro</option>
        </select>
        <select id="selectPrioridad" class="select-filtro">
            <option value="">Todas las prioridades</option>
            <option value="urgente">Urgente</option>
            <option value="alta">Alta</option>
            <option value="media">Media</option>
            <option value="baja">Baja</option>
        </select>
    </div>
</div>

<div class="kanban-board">
    <div class="kanban-col kanban-col-abierta">
        <div class="kanban-col-header">
            <span class="kanban-punto kanban-punto-rojo"></span>
            <span>Abierta</span>
            <span class="badge-kanban badge-kanban-rojo">{{ $totalAbiertas }}</span>
        </div>
        <div class="kanban-col-body" id="colAbierta">
            @forelse($abiertas as $inc)
                @php
                    $bordeColor = match($inc->prioridad_incidencia) {
                        'urgente' => '#EF4444',
                        'alta' => '#D97706',
                        'media' => '#6B7280',
                        'baja' => '#1AA068',
                        default => '#6B7280'
                    };
                    $partesInc = explode(' ', $inc->nombre_inquilino);
                    $inicialesInc = strtoupper(substr($partesInc[0],0,1)) . strtoupper(substr($partesInc[1]??'',0,1));
                @endphp
                <div class="tarjeta-inc" data-id="{{ $inc->id_incidencia }}" style="border-left: 3px solid {{ $bordeColor }}">
                    <div class="tarjeta-inc-top">
                        <span class="badge-prioridad badge-prioridad-{{ $inc->prioridad_incidencia }}">{{ ucfirst($inc->prioridad_incidencia) }}</span>
                        <span class="tarjeta-tiempo">{{ \Carbon\Carbon::parse($inc->creado_incidencia)->diffForHumans() }}</span>
                    </div>
                    <p class="tarjeta-titulo">{{ $inc->titulo_incidencia }}</p>
                    <p class="tarjeta-desc">{{ \Illuminate\Support\Str::limit($inc->descripcion_incidencia, 60) }}</p>
                    <div class="tarjeta-inc-bottom">
                        <span class="tarjeta-propiedad">{{ \Illuminate\Support\Str::limit($inc->direccion_propiedad, 15) }}</span>
                        <div class="tarjeta-inquilino">
                            <div class="avatar-mini">{{ $inicialesInc }}</div>
                            <span>{{ explode(' ',$inc->nombre_inquilino)[0] }} {{ strtoupper(substr(explode(' ',$inc->nombre_inquilino)[1]??'',0,1)) }}.</span>
                        </div>
                    </div>
                    <div class="tarjeta-categoria">
                        <i class="bi bi-droplet"></i>
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
        <div class="kanban-col-body" id="colProceso">
            @forelse($enProceso as $inc)
                @php
                    $bordeColor = match($inc->prioridad_incidencia) {
                        'urgente' => '#EF4444',
                        'alta' => '#D97706',
                        'media' => '#6B7280',
                        'baja' => '#1AA068',
                        default => '#6B7280'
                    };
                    $partesInc = explode(' ', $inc->nombre_inquilino);
                    $inicialesInc = strtoupper(substr($partesInc[0],0,1)) . strtoupper(substr($partesInc[1]??'',0,1));
                @endphp
                <div class="tarjeta-inc" data-id="{{ $inc->id_incidencia }}" style="border-left: 3px solid {{ $bordeColor }}">
                    <div class="tarjeta-inc-top">
                        <span class="badge-prioridad badge-prioridad-{{ $inc->prioridad_incidencia }}">{{ ucfirst($inc->prioridad_incidencia) }}</span>
                        <span class="tarjeta-tiempo">{{ \Carbon\Carbon::parse($inc->creado_incidencia)->diffForHumans() }}</span>
                    </div>
                    <p class="tarjeta-titulo">{{ $inc->titulo_incidencia }}</p>
                    <p class="tarjeta-desc">{{ \Illuminate\Support\Str::limit($inc->descripcion_incidencia, 60) }}</p>
                    @if($inc->nombre_gestor)
                        <div class="tarjeta-gestor">
                            <span class="gestor-label">Asignado a:</span>
                            <span class="gestor-nombre">{{ $inc->nombre_gestor }}</span>
                        </div>
                    @endif
                    <div class="tarjeta-inc-bottom">
                        <span class="tarjeta-propiedad">{{ \Illuminate\Support\Str::limit($inc->direccion_propiedad, 15) }}</span>
                    </div>
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
        <div class="kanban-col-body" id="colResuelta">
            @forelse($resueltas as $inc)
                @php
                    $partesInc = explode(' ', $inc->nombre_inquilino);
                    $inicialesInc = strtoupper(substr($partesInc[0],0,1)) . strtoupper(substr($partesInc[1]??'',0,1));
                @endphp
                <div class="tarjeta-inc tarjeta-inc-resuelta" data-id="{{ $inc->id_incidencia }}">
                    <i class="bi bi-check-circle-fill check-resuelta"></i>
                    <div class="tarjeta-inc-top">
                        <span class="badge-prioridad badge-prioridad-{{ $inc->prioridad_incidencia }}">{{ ucfirst($inc->prioridad_incidencia) }}</span>
                        <span class="tarjeta-tiempo">{{ \Carbon\Carbon::parse($inc->creado_incidencia)->diffForHumans() }}</span>
                    </div>
                    <p class="tarjeta-titulo">{{ $inc->titulo_incidencia }}</p>
                    <p class="tarjeta-desc">{{ \Illuminate\Support\Str::limit($inc->descripcion_incidencia, 60) }}</p>
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
        <div class="kanban-col-body" id="colCerrada">
            @forelse($cerradas as $inc)
                <div class="tarjeta-inc tarjeta-inc-cerrada" data-id="{{ $inc->id_incidencia }}">
                    <p class="tarjeta-titulo">{{ $inc->titulo_incidencia }}</p>
                    <span class="tarjeta-tiempo">{{ \Carbon\Carbon::parse($inc->creado_incidencia)->diffForHumans() }}</span>
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
            <span>Detalle de incidencia</span>
            <span id="modalBadgePrioridad" class="badge-prioridad"></span>
            <span id="modalBadgeCategoria" class="badge-categoria"></span>
        </div>
        <button id="btnCerrarModal" class="btn-cerrar-modal"><i class="bi bi-x"></i></button>
    </div>

    <div class="modal-imagen-inc" id="modalImagenInc">
        <div class="modal-imagen-texto" id="modalImagenTexto"></div>
    </div>

    <div class="modal-cuerpo">
        <span class="seccion-label">DESCRIPCIÓN</span>
        <h3 id="modalTituloInc"></h3>
        <p id="modalDescInc"></p>

        <div class="modal-separador"></div>

        <div class="modal-grid-2">
            <div>
                <span class="seccion-label">PROPIEDAD</span>
                <p id="modalPropiedadInc"></p>
            </div>
            <div>
                <span class="seccion-label">REPORTADA POR</span>
                <p id="modalInquilinoInc"></p>
            </div>
            <div>
                <span class="seccion-label">FECHA REPORTE</span>
                <p id="modalFechaInc"></p>
            </div>
            <div>
                <span class="seccion-label">CATEGORÍA</span>
                <p id="modalCategoriaInc"></p>
            </div>
            <div>
                <span class="seccion-label">PRIORIDAD</span>
                <p id="modalPrioridadInc"></p>
            </div>
            <div>
                <span class="seccion-label">ESTADO</span>
                <p id="modalEstadoInc"></p>
            </div>
        </div>

        <div class="modal-separador"></div>

        <span class="seccion-label">ASIGNAR GESTOR</span>
        <div class="asignacion-fila">
            <select id="selectGestorModal" class="select-filtro">
                <option value="">Sin asignar</option>
                @foreach($gestores as $gestor)
                    <option value="{{ $gestor->id_usuario }}">{{ $gestor->nombre_usuario }}</option>
                @endforeach
            </select>
            <button id="btnAsignar" class="btn-primario">Asignar</button>
        </div>

        <div class="modal-separador"></div>

        <span class="seccion-label">CAMBIAR ESTADO</span>
        <div class="estados-fila" id="estadosFila">
            <button class="btn-estado" data-estado="abierta">Abierta</button>
            <button class="btn-estado" data-estado="en_proceso">En proceso</button>
            <button class="btn-estado" data-estado="resuelta">Resuelta</button>
            <button class="btn-estado" data-estado="cerrada">Cerrada</button>
        </div>

        <div class="modal-separador"></div>

        <span class="seccion-label">HISTORIAL DE CAMBIOS</span>
        <div class="timeline" id="timelineHistorial"></div>

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

@endsection

@section('scripts')
<script src="{{ asset('js/admin/incidencias.js') }}"></script>
@endsection
