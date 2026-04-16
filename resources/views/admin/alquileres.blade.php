@extends('layouts.admin')

@section('titulo', 'Alquileres — SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/alquileres.css') }}">
@endsection

@section('content')

<!-- HERO AZUL -->
<div class="hero-admin">
    <h1>Gestión de alquileres</h1>
    <p>Supervisa y aprueba todas las relaciones de alquiler</p>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<!-- KPI RÁPIDOS -->
<div class="kpi-grid-pequeno">
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde">
            <i class="bi bi-house-check"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $activos }}</span>
            <span class="kpi-mini-label">Alquileres activos</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-naranja">
            <i class="bi bi-clock"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-naranja">{{ $pendientes }}</span>
            <span class="kpi-mini-label">Pendientes de aprobación</span>
            @if($pendientes > 0)
                <span class="badge-atencion">Requiere atención</span>
            @endif
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-x-circle"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo">{{ $rechazados }}</span>
            <span class="kpi-mini-label">Rechazados este mes</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-azul">
            <i class="bi bi-calendar"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">{{ $finalizanMes }}</span>
            <span class="kpi-mini-label">Finalizan este mes</span>
        </div>
    </div>
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
        <button id="btnExportarAlq" class="btn-exportar">
            <i class="bi bi-download"></i>
            <span>Exportar</span>
        </button>
        <button id="btnNuevoAlquiler" class="btn-primario">
            <i class="bi bi-plus"></i>
            <span>Nuevo alquiler</span>
        </button>
    </div>
</div>

<!-- TABLA PRINCIPAL -->
<div class="card-admin">

    <div class="tabla-header">
        <span id="contadorAlquileres">{{ $alquileres->total() }} alquileres encontrados</span>
        <div class="paginacion">
            <button id="btnAnteriorAlq" class="btn-pag">← Anterior</button>
            <span id="paginasAlq">
                @for($i = 1; $i <= min($alquileres->lastPage(), 3); $i++)
                    <span class="pag-numero {{ $i === 1 ? 'activo' : '' }}" data-pagina="{{ $i }}">{{ $i }}</span>
                @endfor
            </span>
            <button id="btnSiguienteAlq" class="btn-pag">Siguiente →</button>
        </div>
    </div>

    <table class="tabla-admin" id="tablaAlquileres">
        <thead>
            <tr>
                <th>PROPIEDAD</th>
                <th>INQUILINO</th>
                <th>ARRENDADOR</th>
                <th>INICIO</th>
                <th>FIN</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
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

                    <td>
                        <div class="propiedad-celda">
                            <div class="thumb-propiedad" style="background:{{ $colorProp }}"></div>
                            <div>
                                <p class="propiedad-nombre">{{ $alquiler->titulo_propiedad }}</p>
                                <p class="propiedad-ciudad">{{ $alquiler->ciudad_propiedad }}</p>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="usuario-celda-mini">
                            <div class="avatar-tabla avatar-sm" style="background:{{ $colorInq }}">{{ $inicialesInq }}</div>
                            <span class="nombre-mini">{{ $alquiler->nombre_inquilino }}</span>
                        </div>
                    </td>

                    <td>
                        <div class="usuario-celda-mini">
                            <div class="avatar-tabla avatar-sm" style="background:{{ $colorArr }}">{{ $inicialesArr }}</div>
                            <span class="nombre-mini">{{ $alquiler->nombre_arrendador }}</span>
                        </div>
                    </td>

                    <td>
                        <span class="texto-fecha">{{ \Carbon\Carbon::parse($alquiler->fecha_inicio_alquiler)->format('d M Y') }}</span>
                    </td>

                    <td>
                        <span class="texto-fecha">
                            @if($alquiler->fecha_fin_alquiler)
                                {{ \Carbon\Carbon::parse($alquiler->fecha_fin_alquiler)->format('d M Y') }}
                            @else
                                —
                            @endif
                        </span>
                    </td>

                    <td>
                        <span class="badge-estado badge-estado-{{ $alquiler->estado_alquiler }}">{{ ucfirst($alquiler->estado_alquiler) }}</span>
                    </td>

                    <td>
                        <div class="acciones-tabla">
                            <button class="btn-accion btn-ver-alq" data-id="{{ $alquiler->id_alquiler }}">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if($alquiler->estado_alquiler === 'pendiente')
                                <button class="btn-aprobar-alq" data-id="{{ $alquiler->id_alquiler }}">✓ Aprobar</button>
                                <button class="btn-rechazar-alq" data-id="{{ $alquiler->id_alquiler }}">✕ Rechazar</button>
                            @endif
                        </div>
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

<!-- MODAL DETALLE ALQUILER -->
<div class="modal-overlay" id="modalOverlay"></div>
<div class="modal-admin modal-ancho" id="modalAlquiler">

    <div class="modal-header-admin">
        <div class="modal-titulo-grupo">
            <span class="modal-titulo">Detalle de alquiler</span>
            <span id="modalBadgeEstadoAlq" class="badge-estado"></span>
        </div>
        <button id="btnCerrarModal" class="btn-cerrar-modal">
            <i class="bi bi-x"></i>
        </button>
    </div>

    <div class="modal-imagen-alq" id="modalImagenAlq">
        <div class="modal-imagen-texto" id="modalImagenTexto"></div>
    </div>

    <div class="modal-cuerpo">

        <!-- PARTES IMPLICADAS -->
        <span class="seccion-label">PARTES IMPLICADAS</span>
        <div class="modal-partes-grid">

            <div class="parte-bloque">
                <span class="parte-label">ARRENDADOR</span>
                <div class="parte-persona" id="bloqueArrendador">
                    <div class="modal-avatar" id="avatarArrendador"></div>
                    <div>
                        <p id="nombreArrendador" class="parte-nombre"></p>
                        <p id="emailArrendador" class="parte-email"></p>
                        <p id="telefonoArrendador" class="parte-email"></p>
                    </div>
                </div>
                <a id="linkArrendador" href="#" class="link-accion">Ver perfil →</a>
            </div>

            <div class="parte-bloque">
                <span class="parte-label">INQUILINO</span>
                <div class="parte-persona" id="bloqueInquilino">
                    <div class="modal-avatar" id="avatarInquilino"></div>
                    <div>
                        <p id="nombreInquilino" class="parte-nombre"></p>
                        <p id="emailInquilino" class="parte-email"></p>
                        <p id="telefonoInquilino" class="parte-email"></p>
                    </div>
                </div>
                <a id="linkInquilino" href="#" class="link-accion">Ver perfil →</a>
            </div>
        </div>

        <div class="modal-separador"></div>

        <!-- PROPIEDAD -->
        <span class="seccion-label">PROPIEDAD</span>
        <div class="propiedad-modal-fila">
            <div class="thumb-propiedad thumb-modal" id="thumbPropiedadModal"></div>
            <div>
                <p id="nombrePropiedadModal" class="propiedad-nombre"></p>
                <p id="ciudadPropiedadModal" class="propiedad-ciudad"></p>
                <p id="precioPropiedadModal" class="precio-modal"></p>
            </div>
        </div>
        <a id="linkPropiedad" href="#" class="link-accion">Ver propiedad →</a>

        <div class="modal-separador"></div>

        <!-- FECHAS Y CONDICIONES -->
        <span class="seccion-label">FECHAS Y CONDICIONES</span>
        <div class="modal-grid-3">
            <div class="dato-item">
                <span class="dato-label">Inicio</span>
                <span class="dato-valor" id="dataInicioAlq"></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Fin</span>
                <span class="dato-valor" id="dataFinAlq"></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Duración</span>
                <span class="dato-valor" id="dataDuracionAlq"></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Alquiler</span>
                <span class="dato-valor" id="dataPrecioAlq"></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Fianza</span>
                <span class="dato-valor" id="dataFianzaAlq"></span>
            </div>
            <div class="dato-item">
                <span class="dato-label">Total anual</span>
                <span class="dato-valor" id="dataTotalAnual"></span>
            </div>
        </div>

        <div class="modal-separador"></div>

        <!-- ESTADO DEL CONTRATO -->
        <span class="seccion-label">ESTADO DEL CONTRATO</span>
        <div class="contrato-estado-grid">

            <div class="contrato-bloque">
                <div class="contrato-fila">
                    <span>Firma arrendador</span>
                    <span id="firmaArrendador" class="badge-estado"></span>
                </div>
                <div class="contrato-fila">
                    <span>Firma inquilino</span>
                    <span id="firmaInquilino" class="badge-estado"></span>
                </div>
                <div class="contrato-fila">
                    <span>Estado contrato</span>
                    <span id="estadoContrato" class="badge-estado"></span>
                </div>
            </div>

            <div class="contrato-bloque">
                <div class="contrato-fila">
                    <span>Primer pago (fianza)</span>
                    <span id="estadoPago" class="badge-estado"></span>
                </div>
                <p id="importePago" class="pago-importe"></p>
                <p id="referenciaPago" class="pago-referencia"></p>
            </div>
        </div>

        <div class="modal-separador"></div>

        <!-- HISTORIAL -->
        <span class="seccion-label">HISTORIAL</span>
        <div class="timeline-alq" id="timelineAlquiler">
            <div class="timeline-linea-v"></div>
        </div>

        <div class="modal-separador"></div>

        <!-- NOTAS -->
        <span class="seccion-label">NOTAS DEL ADMIN</span>
        <textarea id="modalNotasAlq" class="textarea-admin" placeholder="Añade notas sobre este alquiler..."></textarea>

    </div>

    <div class="modal-footer-admin">
        <button id="btnRechazarModal" class="btn-desactivar">Rechazar alquiler</button>
        <div class="modal-footer-derecha">
            <button id="btnVerContrato" class="btn-exportar">
                <i class="bi bi-file-text"></i>
                <span>Ver contrato</span>
            </button>
            <button id="btnAprobarModal" class="btn-aprobar-verde">Aprobar alquiler</button>
        </div>
    </div>
</div>

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
        <div id="propiedadSeleccionada" class="propiedad-preview" style="display:none">
            <div class="thumb-propiedad" id="thumbNuevaProp"></div>
            <div>
                <p id="nombreNuevaProp" class="propiedad-nombre"></p>
                <p id="ciudadNuevaProp" class="propiedad-ciudad"></p>
                <p id="precioNuevaProp" class="precio-modal"></p>
            </div>
        </div>
    </div>

    <!-- PASO 2 -->
    <div class="paso-contenido" id="paso2" style="display:none">
        <span class="seccion-label">SELECCIONA EL INQUILINO</span>
        <select id="nuevoInquilinoId" class="select-filtro select-full">
            <option value="">Selecciona un inquilino...</option>
            @foreach($inquilinos as $inq)
                <option value="{{ $inq->id_usuario }}" data-email="{{ $inq->email_usuario }}">
                    {{ $inq->nombre_usuario }} — {{ $inq->email_usuario }}
                </option>
            @endforeach
        </select>
        <div id="inquilinoSeleccionado" class="persona-preview" style="display:none">
            <div class="modal-avatar" id="avatarNuevoInq"></div>
            <div>
                <p id="nombreNuevoInq" class="parte-nombre"></p>
                <p id="emailNuevoInq" class="parte-email"></p>
            </div>
        </div>
    </div>

    <!-- PASO 3 -->
    <div class="paso-contenido" id="paso3" style="display:none">
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
    <div class="paso-contenido" id="paso4" style="display:none">
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
        <button id="btnPasoAnterior" class="btn-exportar" style="display:none">← Anterior</button>
        <div class="modal-footer-derecha">
            <button id="btnCancelarNuevo" class="btn-exportar">Cancelar</button>
            <button id="btnPasoSiguiente" class="btn-primario">Siguiente →</button>
            <button id="btnCrearAlquiler" class="btn-aprobar-verde" style="display:none">
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
