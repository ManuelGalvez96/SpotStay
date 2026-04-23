<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SpotStay | {{ $alquiler->titulo_propiedad }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/inquilino/ver_propiedad.css') }}" />
</head>

<body class="pagina-miembro">

    <!-- HEADER IDÉNTICO A MIEMBRO -->
    <header class="encabezado-miembro" id="encabezado-miembro">
        <div class="contenedor-encabezado-miembro">
            <!-- Bloque de Mensajes de Feedback -->
            @if(session('success'))
            <div class="header-error-msg header-feedback-msg">
                <div class="alert alert-success-feedback">
                    {!! session('success') !!}
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="header-error-msg header-feedback-msg">
                <div class="alert alert-error">
                    {!! session('error') !!}
                </div>
            </div>
            @endif

            <div class="marca-miembro">
                <img src="/img/logo.png" alt="SpotStay Logo" />
            </div>
            <div class="acciones-miembro">
                <button class="boton-icono" type="button" aria-label="Notificaciones">
                    <i class="bi bi-bell" aria-hidden="true"></i>
                </button>
                <div class="perfil-miembro" id="boton-perfil">
                    <span class="nombre-miembro">{{ $nombreUsuario }}</span>
                    @if ($tieneFoto)
                    <img class="foto-perfil" src="{{ $fotoUsuario }}" alt="Foto de perfil" />
                    @else
                    <div class="inicial-perfil" aria-hidden="true">{{ $inicialUsuario }}</div>
                    @endif

                    <div class="submenu-perfil" id="submenu-perfil">
                        <a href="#" class="item-submenu"><i class="bi bi-person"></i> Mi Perfil</a>
                        <a href="#" class="item-submenu"><i class="bi bi-gear"></i> Configuración</a>
                        <div class="separador-submenu"></div>
                        <a href="{{ route('logout') }}" class="item-submenu item-submenu-danger"><i class="bi bi-box-arrow-right icon-danger"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- NAVBAR HORIZONTAL IDÉNTICO A MIEMBRO -->
    <nav class="navegacion-horizontal">
        <div class="contenedor-nav">
            <ul class="lista-nav">
                <li><a href="/miembro/inicio" class="enlace-nav"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li><a href="#" class="enlace-nav"><i class="bi bi-plus-circle"></i> Registra tus Propiedades</a></li>
                <li><a href="#" class="enlace-nav"><i class="bi bi-journal-text"></i> Alquileres</a></li>
                <li><a href="#" class="enlace-nav"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
                @if ($esInquilino)
                <li><a href="{{ route('gestionar_propiedades') }}" class="enlace-nav activo"><i class="bi bi-building-gear"></i> Gestionar</a></li>
                @endif
            </ul>
        </div>
    </nav>

    <main class="contenido-miembro">
        <div class="contenedor-ver-propiedad">
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
                    <p class="ubicacion"><i class="bi bi-geo-alt"></i> {{ $alquiler->calle_propiedad }} {{ $alquiler->numero_propiedad }}{{ $alquiler->piso_propiedad ? ', Piso '.$alquiler->piso_propiedad : '' }}{{ $alquiler->puerta_propiedad ? ' Pta '.$alquiler->puerta_propiedad : '' }}, {{ $alquiler->ciudad_propiedad }}</p>
                </div>
                <div class="etiqueta-estado">
                    <span class="badge-activo">Alquiler Activo</span>
                </div>
            </div>

            <!-- Grid de Contenido -->
            <div class="grid-ver-propiedad">

                <!-- Columna Izquierda: Info y Fotos -->
                <div class="columna-izquierda">
                    <!-- Galería Simple -->
                    <div class="galeria-detalle">
                        @if ($fotos->count() > 0)
                        <div class="foto-principal">
                            <img src="{{ $fotoPrincipal }}" alt="Imagen principal de {{ $alquiler->titulo_propiedad }}" class="foto-principal-imagen">
                        </div>
                        <div class="miniaturas">
                            @foreach ($fotos as $foto)
                            <div class="miniatura">
                                <img src="{{ $foto->url_foto }}" alt="Miniatura de {{ $alquiler->titulo_propiedad }}" class="miniatura-imagen">
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="foto-principal placeholder">No hay fotos disponibles</div>
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
                                <span class="valor">{{ number_format($alquiler->precio_propiedad, 0, ',', '.') }} € / mes</span>
                            </div>
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
                    <div class="card-gestion fin-contrato">
                        <div class="card-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div class="card-body">
                            <span class="label">CONTRATO PRÓXIMO A FINALIZAR</span>

                            @if ($diasParaFinContrato <= 0)
                                <span class="valor-kpi dias-fin">HOY</span>
                                <p class="nota">Vence en <strong class="js-tiempo-restante" data-fecha-fin="{{ $alquiler->fecha_fin_alquiler }}">calculando...</strong>.</p>
                                @else
                                <span class="valor-kpi dias-fin">{{ $diasParaFinContrato }} días</span>
                                <p class="nota">Tu contrato vence el <strong>{{ $fechaFinContrato }}</strong>.</p>
                                @endif
                                <p class="nota nota-fin-contrato">Contacta con el propietario para renovar o gestionar la salida.</p>
                                <a href="mailto:" class="btn-accion btn-contactar">
                                    <i class="bi bi-envelope"></i> Contactar al Propietario
                                </a>
                        </div>
                    </div>
                    @elseif ($esIndefinido)
                    {{-- KPI Pagos Indefinido --}}
                    <div class="card-gestion pago">
                        <div class="card-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="card-body">
                            @if ($diasRestantesMes === 0 && $estadoPagoActual === 'pendiente')
                            <span class="label pago-requerido">¡PAGO REQUERIDO!</span>
                            <span class="valor-kpi pago-requerido">HOY</span>
                            @if ($numPagosAtrasados > 0)
                            <p class="nota pago-requerido"><strong>¡Paga ya!</strong> Tienes <strong>{{ $numPagosAtrasados }} meses</strong> de retraso más este mes (Total: <strong>{{ number_format($totalDeuda, 2, ',', '.') }}€</strong>).</p>
                            @else
                            <p class="nota pago-requerido"><strong>¡Paga ya!</strong> El plazo de este mes vence hoy.</p>
                            @endif
                            <button class="btn-accion btn-pago bg-pago-requerido">Pagar Cuota Ahora</button>
                            @elseif ($estadoPagoActual === 'pagado')
                            <span class="label pago-exito">PAGO REALIZADO CON ÉXITO</span>
                            <span class="valor-kpi pago-exito"><i class="bi bi-check-circle-fill icono-check-pago"></i></span>
                            <p class="nota pago-exito">Espera al siguiente mes.</p>
                            <p class="nota mt-10">El siguiente pago empieza en: <strong>{{ $diasRestantesMes + 1 }} días</strong> para el mes que viene.</p>
                            @else
                            <span class="label">PRÓXIMO PAGO EN</span>
                            <span class="valor-kpi">{{ $diasRestantesMes }} días</span>
                            @if ($numPagosAtrasados > 0)
                            <p class="nota pago-aviso">⚠️ Tienes <strong>{{ $numPagosAtrasados }} meses</strong> de retraso.<br> El total a pagar (incluyendo este mes) es de <strong>{{ number_format($totalDeuda, 2, ',', '.') }}€</strong>.</p>
                            @else
                            <p class="nota">Vence a final de mes.</p>
                            @endif
                            <button class="btn-accion btn-pago">Pagar Ahora</button>
                            @endif
                        </div>
                    </div>
                    @else
                    {{-- KPI Pagos normal --}}
                    <div class="card-gestion pago">
                        <div class="card-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="card-body">
                            @if ($diasParaPago === 0 && $estadoPagoActual === 'pendiente')
                                <span class="label pago-requerido">¡PAGO REQUERIDO!</span>
                                <span class="valor-kpi pago-requerido">HOY</span>
                                @if ($numPagosAtrasados > 0)
                                    <p class="nota pago-requerido"><strong>¡Paga ya!</strong> Tienes <strong>{{ $numPagosAtrasados }} meses</strong> de retraso más este mes (Total: <strong>{{ number_format($totalDeuda, 2, ',', '.') }}€</strong>).</p>
                                @else
                                    <p class="nota pago-requerido"><strong>¡Paga ya!</strong> El plazo vence hoy.</p>
                                @endif
                                <button class="btn-accion btn-pago bg-pago-requerido">Pagar Cuota Ahora</button>
                            @elseif ($estadoPagoActual === 'pagado')
                                <span class="label pago-exito">PAGO REALIZADO CON ÉXITO</span>
                                <span class="valor-kpi pago-exito"><i class="bi bi-check-circle-fill icono-check-pago"></i></span>
                                <p class="nota pago-exito">Vence el {{ $fechaProximoPago }}</p>
                                <p class="nota mt-10">Estado de cuenta: <strong>Al día</strong></p>
                            @else
                                <span class="label">PRÓXIMO PAGO EN</span>
                                <span class="valor-kpi">{{ $diasParaPago }} días</span>
                                @if ($numPagosAtrasados > 0)
                                    <p class="nota pago-aviso">⚠️ Tienes <strong>{{ $numPagosAtrasados }} meses</strong> de retraso. Se acumulará un total de <strong>{{ number_format($totalDeuda, 2, ',', '.') }}€</strong>.</p>
                                @else
                                    <p class="nota">Vence el {{ $fechaProximoPago }}</p>
                                @endif
                                
                                @if (!empty($cuotaPendienteId))
                                    <form method="POST" action="{{ route('inquilino.pagar_cuota', $cuotaPendienteId) }}" class="form-pago-detalle">
                                        @csrf
                                        <button class="btn-accion btn-pago" type="submit">Pagar Cuota Ahora</button>
                                    </form>
                                @else
                                    <button class="btn-accion btn-pago" type="button" disabled>Sin cuotas pendientes</button>
                                @endif
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Contrato -->
                    <div class="card-gestion contrato">
                        <div class="card-icon">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </div>
                        <div class="card-body">
                            <span class="label">TU CONTRATO</span>
                            <span class="valor-estado">{{ ucfirst($alquiler->estado_contrato_pdf ?? 'No disponible') }}</span>
                            <p class="nota">Puedes descargar una copia en PDF en cualquier momento.</p>
                            <a href="{{ $pdfEjemplo }}" target="_blank" class="btn-accion btn-descarga">
                                <i class="bi bi-download"></i> Descargar Contrato
                            </a>
                        </div>
                    </div>

                    <!-- Incidencias -->
                    <div class="card-gestion incidencias">
                        <div class="cabecera-card">
                            <h3><i class="bi bi-tools"></i> Gestor de Incidencias</h3>
                            <button class="btn-reportar" data-bs-toggle="modal" data-bs-target="#modalReportar">
                                <i class="bi bi-plus-lg"></i> Reportar
                            </button>
                        </div>
                        <div class="lista-incidencias">
                            @forelse ($incidencias as $incidencia)
                            <div class="item-incidencia">
                                <div class="incidencia-info">
                                    <span class="titulo">{{ $incidencia->titulo_incidencia }}</span>
                                    <span class="fecha">{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</span>
                                </div>
                                <div class="incidencia-acciones">
                                    <span class="estado-tag {{ $incidencia->estado_incidencia }}">{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</span>

                                    @if($incidencia->id_reporta_fk == auth()->id() && $incidencia->estado_incidencia != 'resuelta')
                                    <form action="{{ route('inquilino.cerrar_incidencia', $incidencia->id_incidencia) }}" method="POST" class="form-inline-incidencia">
                                        @csrf
                                        <button type="submit" class="btn-resolver" title="Marcar como resuelta">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="aviso-vacio">
                                <p>No hay incidencias registradas.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- MODAL DE REPORTE DE INCIDENCIA -->
    <div class="modal fade" id="modalReportar" tabindex="-1" aria-labelledby="modalReportarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('inquilino.reportar_incidencia', $alquiler->id_propiedad) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalReportarLabel">Reportar Nueva Incidencia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="titulo-incidencia" class="form-label">Título de la incidencia</label>
                            <input type="text" class="form-control" id="titulo-incidencia" name="titulo" placeholder="Ej: Gotera en el baño">
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
                            <textarea class="form-control" id="descripcion-incidencia" name="descripcion" rows="4" placeholder="Explica el problema aquí..."></textarea>
                            <span id="error-descripcion" class="text-danger small"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="boton-enviar" class="btn btn-primary btn-login-desabilitado btn-enviar-reporte" disabled>Enviar Reporte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/miembro/miembro.js') }}"></script>
    <script src="{{ asset('js/inquilino/validacion_incidencia.js') }}"></script>
    <script src="{{ asset('js/inquilino/inquilino.js') }}"></script>
</body>

</html>