@extends('layouts.admin')

@section('titulo', 'Alquileres — SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/alquileres.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/responsive-tablas.css') }}">
@endsection

@section('content')

<!-- HERO AZUL -->
<div class="hero-admin">
    <div class="hero-content">
        <h1>Gestión de alquileres</h1>
        <p>Supervisa y aprueba todas las relaciones de alquiler</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<!-- BARRA DE HERRAMIENTAS -->
<div class="toolbar-admin">
    <div class="toolbar-izquierda">
        <div class="input-busqueda">
            <i class="bi bi-search"></i>
            <input type="text" id="buscadorAlq" placeholder="Buscar por propiedad o inquilino...">
        </div>
        <select id="selectEstadoAlq" class="select-filtro">
            <option value="">Todos los estados</option>
            <option value="activo">Activo</option>
            <option value="pendiente">Pendiente</option>
            <option value="finalizado">Finalizado</option>
            <option value="rechazado">Rechazado</option>
        </select>
        <select id="selectPropiedadAlq" class="select-filtro">
            <option value="">Todas las propiedades</option>
            @foreach($propiedades as $prop)
                <option value="{{ $prop->id_propiedad }}">{{ $prop->titulo_propiedad }}</option>
            @endforeach
        </select>
        <select id="selectMesAlq" class="select-filtro">
            <option value="">Todos los meses</option>
            <option value="1">Enero</option>
            <option value="2">Febrero</option>
            <option value="3">Marzo</option>
            <option value="4">Abril</option>
            <option value="5">Mayo</option>
            <option value="6">Junio</option>
            <option value="7">Julio</option>
            <option value="8">Agosto</option>
            <option value="9">Septiembre</option>
            <option value="10">Octubre</option>
            <option value="11">Noviembre</option>
            <option value="12">Diciembre</option>
        </select>
    </div>
    <div class="toolbar-derecha">
        {{-- El admin sólo puede ver el contrato PDF; no se permiten acciones de creación/gestión desde aquí --}}
    </div>
</div>

<!-- TABLA PRINCIPAL -->
<div class="card-admin">

    <div class="tabla-header">
        <span id="contadorAlquileres" class="info-paginacion">{{ $alquileres->total() }} alquileres encontrados</span>
        <nav aria-label="Paginación de alquileres">
            <ul class="pagination pagination-sm mb-0" id="paginasAlq">
                @for($i = 1; $i <= min($alquileres->lastPage(), 3); $i++)
                    <li class="page-item {{ $i === 1 ? 'active' : '' }}"><button type="button" class="page-link" data-pagina="{{ $i }}">{{ $i }}</button></li>
                @endfor
            </ul>
        </nav>
    </div>

    <table class="tabla-admin" id="tablaAlquileres">
        <thead>
            <tr>
                <th>PROPIEDAD</th>
                <th>INQUILINO</th>
                <th class="col-mobile-hide">ARRENDADOR</th>
                <th class="col-tablet-hide">INICIO</th>
                <th class="col-tablet-hide">FIN</th>
                <th>ESTADO</th>
                <th>CONTRATO</th>
            </tr>
        </thead>
        <tbody id="tbodyAlquileres">
            @forelse($alquileres as $alquiler)
                @php
                    $colores = ['#B8CCE4','#A8D5BF','#F9E4A0','#FFD5CC','#D7EAF9','#EDE7F6','#D5F5E3','#FAD7D7','#CCE5FF','#FDE8C8'];
                    $colorProp = $colores[$alquiler->id_propiedad_fk % 10];
                    $partesInq = explode(' ', $alquiler->nombre_inquilino ?? '');
                    $inicialesInq = strtoupper(substr($partesInq[0] ?? '', 0, 1)) . strtoupper(substr($partesInq[1] ?? '', 0, 1));
                    $partesArr = explode(' ', $alquiler->nombre_arrendador ?? '');
                    $inicialesArr = strtoupper(substr($partesArr[0] ?? '', 0, 1)) . strtoupper(substr($partesArr[1] ?? '', 0, 1));
                    $colorInq = $colores[$alquiler->id_inquilino_fk % 10];
                    $colorArr = $colores[$alquiler->id_arrendador % 10];
                    $filaInactiva = in_array($alquiler->estado_alquiler, ['finalizado','rechazado']) ? 'fila-inactiva' : '';
                @endphp
                <tr data-id="{{ $alquiler->id_alquiler }}" class="{{ $filaInactiva }}">

                    <td data-label="PROPIEDAD">
                        <div class="propiedad-celda">
                            <div class="thumb-propiedad" style="background:{{ $colorProp }}"></div>
                            <div>
                                <p class="propiedad-nombre">{{ $alquiler->titulo_propiedad }}</p>
                                <p class="propiedad-ciudad">{{ $alquiler->ciudad_propiedad }}</p>
                            </div>
                        </div>
                    </td>

                    <td data-label="INQUILINO">
                        <div class="usuario-celda-mini">
                            @php
                                $avatarInq = $alquiler->avatar_inquilino ?? null;
                                $avatarUrlInq = '';
                                if ($avatarInq) {
                                    if (str_starts_with($avatarInq, 'http')) {
                                        $avatarUrlInq = $avatarInq;
                                    } elseif (str_starts_with($avatarInq, 'img/')) {
                                        $avatarUrlInq = asset($avatarInq);
                                    } else {
                                        $avatarUrlInq = asset('storage/' . ltrim($avatarInq, '/'));
                                    }
                                }
                            @endphp
                            @if ($avatarUrlInq)
                                <img class="avatar-tabla avatar-sm" src="{{ $avatarUrlInq }}" alt="">
                            @else
                                <div class="avatar-tabla avatar-sm" style="background:{{ $colorInq }}">{{ $inicialesInq }}</div>
                            @endif
                            <span class="nombre-mini">{{ $alquiler->nombre_inquilino }}</span>
                        </div>
                    </td>

                    <td data-label="ARRENDADOR" class="col-mobile-hide">
                        <div class="usuario-celda-mini">
                            @php
                                $avatarArr = $alquiler->avatar_arrendador ?? null;
                                $avatarUrlArr = '';
                                if ($avatarArr) {
                                    if (str_starts_with($avatarArr, 'http')) {
                                        $avatarUrlArr = $avatarArr;
                                    } elseif (str_starts_with($avatarArr, 'img/')) {
                                        $avatarUrlArr = asset($avatarArr);
                                    } else {
                                        $avatarUrlArr = asset('storage/' . ltrim($avatarArr, '/'));
                                    }
                                }
                            @endphp
                            @if ($avatarUrlArr)
                                <img class="avatar-tabla avatar-sm" src="{{ $avatarUrlArr }}" alt="">
                            @else
                                <div class="avatar-tabla avatar-sm" style="background:{{ $colorArr }}">{{ $inicialesArr }}</div>
                            @endif
                            <span class="nombre-mini">{{ $alquiler->nombre_arrendador }}</span>
                        </div>
                    </td>

                    <td data-label="INICIO" class="col-tablet-hide">
                        <span class="texto-fecha">{{ \Carbon\Carbon::parse($alquiler->fecha_inicio_alquiler)->format('d M Y') }}</span>
                    </td>

                    <td data-label="FIN" class="col-tablet-hide">
                        <span class="texto-fecha">
                            @if($alquiler->fecha_fin_alquiler)
                                {{ \Carbon\Carbon::parse($alquiler->fecha_fin_alquiler)->format('d M Y') }}
                            @else
                                —
                            @endif
                        </span>
                    </td>

                    <td data-label="ESTADO">
                        <span class="badge-estado badge-estado-{{ $alquiler->estado_alquiler }}">{{ ucfirst($alquiler->estado_alquiler) }}</span>
                    </td>

                    <td data-label="CONTRATO">
                        @if(!empty($alquiler->url_pdf_contrato) || (isset($alquiler->pdf_disponible) && $alquiler->pdf_disponible))
                            <a href="{{ route('admin.alquileres.descargar-contrato', ['id' => $alquiler->id_alquiler]) }}" class="btn-exportar">
                                <i class="bi bi-file-earmark-pdf"></i>
                                <span>Ver Contrato</span>
                            </a>
                        @else
                            <span class="text-muted">Sin contrato</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="sin-resultados">No hay alquileres registrados</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="tabla-footer">
        Mostrando {{ $alquileres->firstItem() ?? 0 }}-{{ $alquileres->lastItem() ?? 0 }} de {{ $alquileres->total() }} alquileres
    </div>
</div>

<!-- Modal de detalle eliminado: el admin verá el PDF directamente desde la tabla -->

<!-- MODAL NUEVO ALQUILER — 4 PASOS -->
<div class="modal-overlay-nuevo" id="modalOverlayNuevo"></div>
<div class="modal-admin modal-ancho" id="modalNuevoAlquiler">

    <div class="modal-header-admin">
        <div class="modal-titulo-grupo">
            <span class="modal-titulo">Nuevo alquiler</span>
            <span id="labelPasoActual" class="badge-paso">Paso 1 de 4</span>
        </div>
        <button id="btnCerrarModalNuevo" class="btn-cerrar-modal">
            <i class="bi bi-x"></i>
        </button>
    </div>

    <!-- INDICADOR DE PASOS -->
    <div class="pasos-indicador">
        <div class="paso-item paso-activo" id="paso-ind-1">
            <div class="paso-circulo">1</div>
            <span>Propiedad</span>
        </div>
        <div class="paso-linea"></div>
        <div class="paso-item" id="paso-ind-2">
            <div class="paso-circulo">2</div>
            <span>Inquilino</span>
        </div>
        <div class="paso-linea"></div>
        <div class="paso-item" id="paso-ind-3">
            <div class="paso-circulo">3</div>
            <span>Fechas</span>
        </div>
        <div class="paso-linea"></div>
        <div class="paso-item" id="paso-ind-4">
            <div class="paso-circulo">4</div>
            <span>Confirmar</span>
        </div>
    </div>

    <!-- PASO 1 -->
    <div class="paso-contenido" id="paso1">
        <span class="seccion-label">SELECCIONA LA PROPIEDAD</span>
        <select id="nuevoPropiedadId" class="select-filtro select-full">
            <option value="">Selecciona una propiedad publicada...</option>
            @foreach($propiedadesPublicadas as $prop)
                <option value="{{ $prop->id_propiedad }}" data-precio="{{ $prop->precio_propiedad }}" data-ciudad="{{ $prop->ciudad_propiedad }}">
                    {{ $prop->titulo_propiedad }} — {{ $prop->ciudad_propiedad }} — ${{ number_format($prop->precio_propiedad, 2) }}/mes
                </option>
            @endforeach
        </select>
        <div id="propiedadSeleccionada" class="propiedad-preview d-none">
            <div class="thumb-propiedad" id="thumbNuevaProp"></div>
            <div>
                <p id="nombreNuevaProp" class="propiedad-nombre"></p>
                <p id="ciudadNuevaProp" class="propiedad-ciudad"></p>
                <p id="precioNuevaProp" class="precio-modal"></p>
            </div>
        </div>
    </div>

    <!-- PASO 2 -->
    <div class="paso-contenido d-none" id="paso2">
        <span class="seccion-label">SELECCIONA EL INQUILINO</span>
        <select id="nuevoInquilinoId" class="select-filtro select-full">
            <option value="">Selecciona un inquilino...</option>
            @foreach($inquilinos as $inq)
                <option value="{{ $inq->id_usuario }}" data-email="{{ $inq->email_usuario }}">
                    {{ $inq->nombre_usuario }} — {{ $inq->email_usuario }}
                </option>
            @endforeach
        </select>
        <div id="inquilinoSeleccionado" class="persona-preview d-none">
            <div class="modal-avatar" id="avatarNuevoInq"></div>
            <div>
                <p id="nombreNuevoInq" class="parte-nombre"></p>
                <p id="emailNuevoInq" class="parte-email"></p>
            </div>
        </div>
    </div>

    <!-- PASO 3 -->
    <div class="paso-contenido d-none" id="paso3">
        <span class="seccion-label">FECHAS DEL ALQUILER</span>
        <div class="fechas-grid">
            <div>
                <label class="input-label">Fecha de inicio</label>
                <input type="date" id="nuevoFechaInicio" class="input-full">
            </div>
            <div>
                <label class="input-label">Fecha de fin (opcional)</label>
                <input type="date" id="nuevoFechaFin" class="input-full">
            </div>
        </div>
        <div class="modal-separador"></div>
        <span class="seccion-label">PRECIO MENSUAL</span>
        <input type="number" id="nuevoPrecio" class="input-full" placeholder="Precio en €" step="0.01" min="0">
        <p id="precioSugerido" class="texto-sugerido">Precio sugerido según la propiedad: —</p>
    </div>

    <!-- PASO 4 -->
    <div class="paso-contenido d-none" id="paso4">
        <span class="seccion-label">RESUMEN DEL ALQUILER</span>
        <div class="resumen-alquiler">
            <div class="resumen-fila">
                <span class="resumen-label">Propiedad</span>
                <span class="resumen-valor" id="resumenPropiedad"></span>
            </div>
            <div class="resumen-fila">
                <span class="resumen-label">Inquilino</span>
                <span class="resumen-valor" id="resumenInquilino"></span>
            </div>
            <div class="resumen-fila">
                <span class="resumen-label">Fecha inicio</span>
                <span class="resumen-valor" id="resumenInicio"></span>
            </div>
            <div class="resumen-fila">
                <span class="resumen-label">Fecha fin</span>
                <span class="resumen-valor" id="resumenFin"></span>
            </div>
            <div class="resumen-fila">
                <span class="resumen-label">Precio mensual</span>
                <span class="resumen-valor resumen-precio" id="resumenPrecio"></span>
            </div>
            <div class="resumen-fila">
                <span class="resumen-label">Fianza (2 meses)</span>
                <span class="resumen-valor" id="resumenFianza"></span>
            </div>
            <div class="resumen-fila">
                <span class="resumen-label">Estado inicial</span>
                <span class="badge-estado badge-estado-pendiente">Pendiente de aprobación</span>
            </div>
        </div>
        <div class="aviso-alquiler">
            <i class="bi bi-info-circle"></i>
            <span>El alquiler quedará en estado pendiente hasta que ambas partes firmen el contrato y se confirme el primer pago.</span>
        </div>
    </div>

    <div class="modal-footer-admin">
        <button id="btnPasoAnterior" class="btn-exportar d-none">← Anterior</button>
        <div class="modal-footer-derecha">
            <button id="btnCancelarNuevo" class="btn-exportar">Cancelar</button>
            <button id="btnPasoSiguiente" class="btn-primario">Siguiente →</button>
            <button id="btnCrearAlquiler" class="btn-aprobar-verde d-none">
                <i class="bi bi-check"></i>
                <span>Crear alquiler</span>
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin/alquileres.js') }}"></script>
@endsection
