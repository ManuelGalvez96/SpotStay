@extends('layouts.miembro')

@section('title', $alquiler->titulo_propiedad)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/inquilino/ver_propiedad.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/visor_fotos.css') }}?v=2" />
@endsection

@section('content')
    <div class="contenedor-ver-propiedad" data-mensaje-exito="{{ session('success') }}"
        data-mensaje-error="{{ session('error') }}">
        <!-- Botón Volver -->
        <div class="navegacion-superior">
            <a href="{{ route('gestionar_propiedades') }}" class="btn-volver">
                <i class="bi bi-arrow-left"></i> Volver a Gestión
            </a>
        </div>

        <!-- Cabecera de Propiedad -->
        <div class="header-detalle">
            <div class="info-principal">
                <h1>{{ $alquiler->titulo_propiedad }}</h1>
                <p class="ubicacion"><i class="bi bi-geo-alt"></i> {{ $alquiler->calle_propiedad }}
                    {{ $alquiler->numero_propiedad }}{{ $alquiler->piso_propiedad ? ', Piso ' . $alquiler->piso_propiedad : '' }}{{ $alquiler->puerta_propiedad ? ' Pta ' . $alquiler->puerta_propiedad : '' }},
                    {{ $alquiler->ciudad_propiedad }}</p>
                @if (count($companeros) > 0)
                    <p class="companeros-piso">
                        <i class="bi bi-people-fill"></i> Compartes esta propiedad con:
                        <strong>{{ implode(' y ', $companeros) }}</strong>
                    </p>
                @endif
            </div>
            <div class="etiqueta-estado">
                <span class="badge-activo">Alquiler Activo</span>
            </div>
        </div>

        <!-- Grid de Contenido -->
        <div class="grid-ver-propiedad">

            <!-- Columna Izquierda: Info y Fotos -->
            <div class="columna-izquierda">
                <!-- Carrusel de Fotos Bootstrap -->
                <div class="galeria-detalle mb-4">
                    @if ($fotos->count() > 0)
                        <div id="carouselPropiedadInquilino" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                @foreach ($fotos as $index => $foto)
                                    <button type="button" data-bs-target="#carouselPropiedadInquilino"
                                        data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}"
                                        aria-current="{{ $index == 0 ? 'true' : 'false' }}"
                                        aria-label="Slide {{ $index + 1 }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner carrusel-contenedor-inner">
                                @foreach ($fotos as $index => $foto)
                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                        <img src="{{ $foto->url_foto }}" class="d-block w-100 imagen-carrusel-ampliable"
                                            alt="Imagen {{ $index + 1 }} de {{ $alquiler->titulo_propiedad }}"
                                            data-bs-toggle="modal" data-bs-target="#modalVisorFotos-{{ $index }}">
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button"
                                data-bs-target="#carouselPropiedadInquilino" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon carrusel-btn-navegacion" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button"
                                data-bs-target="#carouselPropiedadInquilino" data-bs-slide="next">
                                <span class="carousel-control-next-icon carrusel-btn-navegacion" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>
                    @else
                        <div class="carrusel-estado-vacio">
                            <i class="bi bi-image text-muted"></i>
                            <span>No hay fotos disponibles para esta propiedad.</span>
                        </div>
                    @endif
                </div>

                <!-- Detalles de la Propiedad -->
                <div class="tarjeta-info">
                    <h3><i class="bi bi-info-circle"></i> Información Detallada</h3>
                    <div class="detalles-grid">
                        <div class="detalle-item">
                            <span class="label">Superficie</span>
                            <span class="valor">{{ $alquiler->metros_cuadrados_propiedad }} m²</span>
                        </div>
                        <div class="detalle-item">
                            <span class="label">Habitaciones</span>
                            <span class="valor">{{ $alquiler->habitaciones_propiedad }}</span>
                        </div>
                        <div class="detalle-item">
                            <span class="label">Tipo</span>
                            <span class="valor">{{ $alquiler->tipo_propiedad }}</span>
                        </div>
                        <div class="detalle-item">
                            <span class="label">Precio Renta</span>
                            <span class="valor">{{ number_format($alquiler->precio_propiedad, 0, ',', '.') }} € /
                                mes</span>
                        </div>
                        @if (count($companeros) > 0)
                            <div class="detalle-item full-width">
                                <span class="label">Compañeros de Vivienda</span>
                                <span class="valor">Compartes esta propiedad con
                                    <strong>{{ implode(' y ', $companeros) }}</strong></span>
                            </div>
                        @endif
                    </div>
                    <div class="descripcion-propiedad">
                        <h4>Descripción</h4>
                        <p>{{ $alquiler->descripcion_propiedad }}</p>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Gestión, Contrato y Pagos -->
            <div class="columna-derecha">

                @if ($proximaFinalizacion)
                    {{-- ⚠️ AVISO: Contrato próximo a finalizar (menos de 30 días) --}}
                    <div class="card-gestion fin-contrato" data-id-alquiler="{{ $alquiler->id_alquiler }}">
                        <div class="card-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div class="card-body">
                            <span class="label">CONTRATO PRÓXIMO A FINALIZAR</span>

                            @if ($diasParaFinContrato <= 0)
                                <span class="valor-kpi dias-fin">HOY</span>
                                <p class="nota">Vence en <strong class="js-tiempo-restante"
                                        data-fecha-fin="{{ $alquiler->fecha_fin_alquiler }}">calculando...</strong>.</p>
                            @else
                                <span class="valor-kpi dias-fin">{{ $diasParaFinContrato }} días</span>
                                <p class="nota">Tu contrato vence el <strong>{{ $fechaFinContrato }}</strong>.</p>
                            @endif
                            <p class="nota nota-fin-contrato">Contacta con el propietario para renovar o gestionar la
                                salida.</p>
                            <div id="contenedor-alerta-contrato" class="alerta-semana-exceso-dinamica">
                                <!-- El mensaje se cargará por fetch -->
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Tarjeta Única de Pagos y Gastos --}}
                <div class="card-gestion pago">
                    <div class="card-icon">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="card-body">
                        <span class="label">PAGOS Y SUMINISTROS</span>
                        @if ($totalDeuda > 0 || $totalGastosPendientes > 0)
                            <span
                                class="valor-kpi pago-requerido">{{ number_format($totalDeuda + $totalGastosPendientes, 2, ',', '.') }}€</span>
                            <p class="nota">Tienes pagos pendientes de alquiler o suministros.</p>
                        @else
                            <span class="valor-kpi pago-exito"><i class="bi bi-check-circle-fill"></i></span>
                            <p class="nota pago-exito">Estás al día con todos tus pagos en esta propiedad.</p>
                        @endif

                        <a href="{{ route('inquilino.historial_pagos', ['propiedad_id' => $alquiler->id_propiedad]) }}"
                            class="btn-accion btn-pago mt-3 btn-pago-custom">
                            <i class="bi bi-arrow-right-circle me-2"></i> Gestionar mis Gastos
                        </a>
                    </div>
                </div>

                <!-- Contrato -->
                <div class="card-gestion contrato">
                    <div class="card-icon">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </div>
                    <div class="card-body">
                        <span class="label">TU CONTRATO</span>
                        @if (!empty($alquiler->url_pdf_contrato))
                            <span class="valor-estado">Disponible</span>
                            <p class="nota">Puedes descargar una copia en PDF en cualquier momento.</p>
                            <a href="{{ route('inquilino.descargar_contrato', ['id' => $alquiler->id_propiedad]) }}"
                                class="btn-accion btn-descarga">
                                <i class="bi bi-download"></i> Descargar Contrato
                            </a>
                        @else
                            <span class="valor-estado">No disponible</span>
                            <p class="nota">El propietario aún no ha subido el contrato. Te avisaremos cuando esté
                                disponible.</p>
                            @if ($alquiler->url_pdf_contrato != null)
                                <button class="btn-accion btn-descarga">
                                    <i class="bi bi-download"></i> Descargar Contrato
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Propietario -->
                <div class="card-gestion propietario">
                    <div class="card-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="card-body">
                        <span class="label">PROPIETARIO</span>
                        <span class="valor-estado">{{ $alquiler->nombre_propietario ?? 'No disponible' }}</span>
                        <p class="nota">Si tienes alguna duda o problema, comunícate directamente con él.</p>
                        <form method="POST" action="{{ route('miembro.mensajes.iniciar', $alquiler->id_propiedad) }}"
                            class="m-0 w-100 mt-2">
                            @csrf
                            <button type="submit" class="btn-accion w-100 text-center btn-contacto-limpio">
                                <i class="bi bi-chat-dots"></i> Contactar al Propietario
                            </button>
                        </form>
                    </div>
                </div>

                @if (!empty($alquiler->nombre_gestor) && !empty($alquiler->chat_gestor))
                <!-- Gestor -->
                <div class="card-gestion propietario">
                    <div class="card-icon">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <div class="card-body">
                        <span class="label">GESTOR</span>
                        <span class="valor-estado">{{ $alquiler->nombre_gestor }}</span>
                        <p class="nota">Puedes contactar con el gestor de esta propiedad porque tiene el chat habilitado.</p>
                        <form method="POST" action="{{ route('miembro.mensajes.iniciar_gestor', $alquiler->id_propiedad) }}" class="m-0 w-100 mt-2">
                            @csrf
                            <button type="submit" class="btn-accion w-100 text-center btn-contacto-limpio">
                                <i class="bi bi-chat-dots"></i> Contactar al Gestor
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Incidencias -->
                <div class="card-gestion incidencias">
                    <div class="cabecera-card">
                        <h3><i class="bi bi-tools"></i> Gestor de Incidencias</h3>
                        <button class="btn-reportar" data-bs-toggle="modal" data-bs-target="#modalReportar">
                            <i class="bi bi-plus-lg"></i> Reportar
                        </button>
                    </div>
                    <div class="filtros-incidencias-container">
                        <select id="filtro-autor" class="select-filtro-mini">
                            <option value="todas">Todas las incidencias</option>
                            <option value="mias">Mis reportes</option>
                        </select>
                        <select id="filtro-estado" class="select-filtro-mini">
                            <!-- Se cargará dinámicamente por JS -->
                        </select>
                    </div>
                    <div class="lista-incidencias" id="contenedor-lista-incidencias"
                        data-propiedad-id="{{ $alquiler->id_propiedad }}">
                        <!-- Se cargará dinámicamente por JS -->
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MODALS -->
    @include('inquilino.partials.modal_detalle_incidencia')

    <!-- MODAL DE REPORTE DE INCIDENCIA -->
    <div class="modal fade" id="modalReportar" tabindex="-1" aria-labelledby="modalReportarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="form-reportar-incidencia"
                    action="{{ route('inquilino.reportar_incidencia', $alquiler->id_propiedad) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalReportarLabel">Reportar Nueva Incidencia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="titulo-incidencia" class="form-label">Título de la incidencia</label>
                            <input type="text" class="form-control" id="titulo-incidencia" name="titulo"
                                placeholder="Ej: Gotera en el baño">
                            <span id="error-titulo" class="text-danger small"></span>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="categoria-incidencia" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria-incidencia" name="categoria">
                                    <option value="" selected disabled>Selecciona una categoría</option>
                                    <option value="fontaneria">Fontanería</option>
                                    <option value="electricidad">Electricidad</option>
                                    <option value="limpieza">Limpieza</option>
                                    <option value="climatizacion">Climatización</option>
                                    <option value="otros">Otros</option>
                                </select>
                                <span id="error-categoria" class="text-danger small"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="prioridad-incidencia" class="form-label">Prioridad</label>
                                <select class="form-select" id="prioridad-incidencia" name="prioridad">
                                    <option value="" selected disabled>Selecciona una prioridad</option>
                                    <option value="baja">Baja</option>
                                    <option value="media">Media</option>
                                    <option value="alta">Alta</option>
                                    <option value="urgente">Urgente</option>
                                </select>
                                <span id="error-prioridad" class="text-danger small"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion-incidencia" class="form-label">Descripción detallada</label>
                            <textarea class="form-control" id="descripcion-incidencia" name="descripcion" rows="4"
                                placeholder="Explica el problema aquí..."></textarea>
                            <span id="error-descripcion" class="text-danger small"></span>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="boton-enviar"
                            class="btn btn-primary btn-login-desabilitado btn-enviar-reporte" disabled>Enviar
                            Reporte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODALES VISOR DE FOTOS BOOTSTRAP NATIVO -->
    @if ($fotos->count() > 0)
        @foreach ($fotos as $index => $foto)
            <div class="modal fade modal-visor-fotos" id="modalVisorFotos-{{ $index }}" tabindex="-1"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content">
                        <button type="button" class="btn-cerrar-visor-modal" data-bs-dismiss="modal"
                            aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
                        <div class="modal-body">
                            <img class="imagen-visor-modal" id="imagen-visor-modal-{{ $index }}"
                                src="{{ $foto->url_foto }}" alt="Vista ampliada {{ $index + 1 }}">
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

@endsection

@section('scripts')
    <script src="{{ asset('js/inquilino/validacion_incidencia.js') }}"></script>
    <script src="{{ asset('js/inquilino/comun.js') }}"></script>
    <script src="{{ asset('js/inquilino/vista_ver_propiedad.js') }}"></script>
@endsection
