@extends('layouts.miembro')

@section('title', $alquiler->titulo_propiedad)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/inquilino/ver_propiedad.css') }}" />
@endsection

@section('content')
<div class="contenedor-ver-propiedad" 
     data-mensaje-exito="{{ session('success') }}" 
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
            <p class="ubicacion"><i class="bi bi-geo-alt"></i> {{ $alquiler->calle_propiedad }} {{ $alquiler->numero_propiedad }}{{ $alquiler->piso_propiedad ? ', Piso '.$alquiler->piso_propiedad : '' }}{{ $alquiler->puerta_propiedad ? ' Pta '.$alquiler->puerta_propiedad : '' }}, {{ $alquiler->ciudad_propiedad }}</p>
            @if(count($companeros) > 0)
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
                    @if(count($companeros) > 0)
                    <div class="detalle-item full-width">
                        <span class="label">Compañeros de Vivienda</span>
                        <span class="valor">Compartes esta propiedad con <strong>{{ implode(' y ', $companeros) }}</strong></span>
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
                        <p class="nota">Vence en <strong class="js-tiempo-restante" data-fecha-fin="{{ $alquiler->fecha_fin_alquiler }}">calculando...</strong>.</p>
                        @else
                        <span class="valor-kpi dias-fin">{{ $diasParaFinContrato }} días</span>
                        <p class="nota">Tu contrato vence el <strong>{{ $fechaFinContrato }}</strong>.</p>
                        @endif
                        <p class="nota nota-fin-contrato">Contacta con el propietario para renovar o gestionar la salida.</p>
                        <div id="contenedor-alerta-contrato" class="alerta-semana-exceso-dinamica">
                            <!-- El mensaje se cargará por fetch -->
                        </div>
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
                    <p class="nota pago-requerido"><strong>¡Paga ya!</strong> Tienes <strong>{{ $numPagosAtrasados }} meses</strong> de retraso más este mes (Cuota: <strong>{{ number_format($montoCuotaActual, 2, ',', '.') }}€</strong>).</p>
                    @else
                    <p class="nota pago-requerido"><strong>¡Paga ya!</strong> El plazo de este mes vence hoy.</p>
                    @endif
                    <form method="POST" action="{{ route('inquilino.pagar_cuota', $cuotaPendienteId ?? 0) }}?tipo=alquiler" class="form-pago-cuota" data-monto="{{ number_format($montoCuotaActual, 2, ',', '.') }}€">
                        @csrf
                        <button type="button" id="boton-pagar" class="btn-accion btn-pago bg-pago-requerido" {{ empty($cuotaPendienteId) ? 'disabled' : '' }}>Pagar Cuota Ahora</button>
                    </form>
                    @elseif ($estadoPagoActual === 'pagado')
                    <span class="label pago-exito">¡ESTÁS AL DÍA!</span>
                    <span class="valor-kpi pago-exito"><i class="bi bi-check-circle-fill icono-check-pago"></i></span>
                    <p class="nota pago-exito">Pago del mes actual confirmado.<br>Próximo recibo: <strong>{{ \Carbon\Carbon::parse($fechaProximoPago)->format('d/m/Y') }}</strong></p>
                    <p class="nota mt-10 pago-exito">Estado de cuenta: <strong>Excelente</strong></p>
                    @else
                    <span class="label">PRÓXIMO PAGO EN</span>
                    <span class="valor-kpi">{{ $diasRestantesMes }} días</span>
                    @if ($numPagosAtrasados > 0)
                    <p class="nota pago-aviso">⚠️ Tienes <strong>{{ $numPagosAtrasados }} meses</strong> de retraso.<br> El pago pendiente es de <strong>{{ number_format($montoCuotaActual, 2, ',', '.') }}€</strong>.</p>
                    @else
                    <p class="nota pago-aviso">Vence a final de mes.</p>
                    @endif
                    <form method="POST" action="{{ route('inquilino.pagar_cuota', $cuotaPendienteId ?? 0) }}?tipo=alquiler" class="form-pago-cuota" data-monto="{{ number_format($montoCuotaActual, 2, ',', '.') }}€">
                        @csrf
                        <button type="button" id="boton-pagar" class="btn-accion btn-pago" {{ empty($cuotaPendienteId) ? 'disabled' : '' }}>Pagar Ahora</button>
                    </form>
                    @endif
                    <button type="button" class="btn-accion w-100 mt-3 btn-ver-historial" data-bs-toggle="modal" data-bs-target="#modalHistorialPagos">
                        <i class="bi bi-clock-history"></i> Ver Historial de Pagos
                    </button>
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
                    <p class="nota pago-requerido"><strong>¡Paga ya!</strong> Tienes <strong>{{ $numPagosAtrasados }} meses</strong> de retraso más este mes (Cuota: <strong>{{ number_format($montoCuotaActual, 2, ',', '.') }}€</strong>).</p>
                    @else
                    <p class="nota pago-requerido"><strong>¡Paga ya!</strong> El plazo vence hoy.</p>
                    @endif
                    <form method="POST" action="{{ route('inquilino.pagar_cuota', $cuotaPendienteId ?? 0) }}?tipo=alquiler" class="form-pago-cuota" data-monto="{{ number_format($montoCuotaActual, 2, ',', '.') }}€">
                        @csrf
                        <button type="button" id="boton-pagar" class="btn-accion btn-pago bg-pago-requerido" {{ empty($cuotaPendienteId) ? 'disabled' : '' }}>Pagar Cuota Ahora</button>
                    </form>
                    @elseif ($estadoPagoActual === 'pagado')
                    <span class="label pago-exito">¡ESTÁS AL DÍA!</span>
                    <span class="valor-kpi pago-exito"><i class="bi bi-check-circle-fill icono-check-pago"></i></span>
                    <p class="nota pago-exito">Pago confirmado. No tienes deudas pendientes.<br>Próximo recibo: <strong>{{ \Carbon\Carbon::parse($fechaProximoPago)->format('d/m/Y') }}</strong></p>
                    <p class="nota mt-10 pago-exito">Estado de cuenta: <strong>Excelente</strong></p>
                    @else
                    <span class="label">PRÓXIMO PAGO EN</span>
                    <span class="valor-kpi">{{ $diasParaPago }} días</span>
                    @if ($numPagosAtrasados > 0)
                    <p class="nota pago-aviso">⚠️ Tienes <strong>{{ $numPagosAtrasados }} meses</strong> de retraso. Se acumulará un total de <strong>{{ number_format($totalDeuda, 2, ',', '.') }}€</strong>.</p>
                    @else
                    <p class="nota">Vence el {{ \Carbon\Carbon::parse($fechaProximoPago)->format('d/m/Y') }}</p>
                    @endif

                    @if (!empty($cuotaPendienteId))
                    <form method="POST" action="{{ route('inquilino.pagar_cuota', $cuotaPendienteId) }}?tipo=alquiler" class="form-pago-cuota" data-monto="{{ number_format($montoCuotaActual, 2, ',', '.') }}€">
                        @csrf
                        <button class="btn-accion btn-pago" type="button">Pagar Cuota Ahora</button>
                    </form>
                    @else
                    <button class="btn-accion btn-pago" type="button" disabled>Sin cuotas pendientes</button>
                    @endif
                    @endif
                    <button type="button" class="btn-accion w-100 mt-3 btn-ver-historial" data-bs-toggle="modal" data-bs-target="#modalHistorialPagos">
                        <i class="bi bi-clock-history"></i> Ver Historial de Pagos
                    </button>
                </div>
            </div>
            @endif

            {{-- Tarjeta de Pagos Extras (Suministros) --}}
            <div class="card-gestion pago suministros">
                <div class="card-icon">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div class="card-body">
                    <span class="label">PAGOS EXTRAS / SUMINISTROS</span>
                    @if ($numGastosPendientes > 0)
                    <span class="valor-kpi">{{ number_format($totalGastosPendientes, 2, ',', '.') }}€</span>
                    <p class="nota">Tienes <strong>{{ $numGastosPendientes }} suministros</strong> pendientes:</p>
                    <ul class="lista-suministros-limpia">
                        @foreach($listaGastos as $gasto)
                        <li class="item-suministro-pendiente-limpio">
                            <span><i class="bi bi-dot"></i> {{ ucfirst($gasto->categoria_gasto) }}{{ !empty($gasto->concepto_gasto) ? ' - ' . $gasto->concepto_gasto : '' }}</span>
                            @php
                            $importeInd = $numInquilinos > 1 ? ($gasto->importe_detalle / $numInquilinos) : $gasto->importe_detalle;
                            @endphp
                            <strong>{{ number_format($importeInd, 2, ',', '.') }}€</strong>
                        </li>
                        @endforeach
                    </ul>
                    <form method="POST" action="{{ route('inquilino.pagar_cuota', $cuotaPendienteId ?? 0) }}?tipo=gasto" class="form-pago-cuota" data-monto="{{ number_format($totalGastosPendientes, 2, ',', '.') }}€">
                        @csrf
                        <button type="button" class="btn-accion btn-pago">Pagar Gastos Ahora</button>
                    </form>
                    @else
                    <span class="valor-kpi pago-exito"><i class="bi bi-check-circle-fill"></i></span>
                    <p class="nota pago-exito">No tienes suministros pendientes. ¡Al día!</p>
                    @endif
                </div>
            </div>

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

            <!-- Propietario -->
            <div class="card-gestion propietario">
                <div class="card-icon">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div class="card-body">
                    <span class="label">PROPIETARIO</span>
                    <span class="valor-estado">{{ $alquiler->nombre_propietario ?? 'No disponible' }}</span>
                    <p class="nota">Si tienes alguna duda o problema, comunícate directamente con él.</p>
                    <form method="POST" action="{{ route('miembro.mensajes.iniciar', $alquiler->id_propiedad) }}" class="m-0 w-100 mt-2">
                        @csrf
                        <button type="submit" class="btn-accion w-100 text-center btn-contacto-limpio">
                            <i class="bi bi-chat-dots"></i> Contactar al Propietario
                        </button>
                    </form>
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
                <div class="filtros-incidencias-container">
                    <select id="filtro-autor" class="select-filtro-mini">
                        <option value="todas">Todas las incidencias</option>
                        <option value="mias">Mis reportes</option>
                    </select>
                    <select id="filtro-estado" class="select-filtro-mini">
                        <!-- Se cargará dinámicamente por JS -->
                    </select>
                </div>
                <div class="lista-incidencias" id="contenedor-lista-incidencias" data-propiedad-id="{{ $alquiler->id_propiedad }}">
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
            <form id="form-reportar-incidencia" action="{{ route('inquilino.reportar_incidencia', $alquiler->id_propiedad) }}" method="POST">
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
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="boton-enviar" class="btn btn-primary btn-login-desabilitado btn-enviar-reporte" disabled>Enviar Reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL HISTORIAL DE PAGOS -->
<div class="modal fade" id="modalHistorialPagos" tabindex="-1" aria-labelledby="modalHistorialPagosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHistorialPagosLabel"><i class="bi bi-clock-history"></i> Historial de Pagos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs nav-fill" id="tabHistorial" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active tab-historial-btn" id="alquiler-tab" data-bs-toggle="tab" data-bs-target="#alquiler-history" type="button" role="tab" aria-controls="alquiler-history" aria-selected="true">
                            <i class="bi bi-house-door"></i> Alquiler
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link tab-historial-btn" id="gastos-tab" data-bs-toggle="tab" data-bs-target="#gastos-history" type="button" role="tab" aria-controls="gastos-history" aria-selected="false">
                            <i class="bi bi-lightning-charge"></i> Suministros
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="tabHistorialContent" data-id-alquiler="{{ $alquiler->id_alquiler }}">
                    {{-- Historial Alquiler --}}
                    <div class="tab-pane fade show active" id="alquiler-history" role="tabpanel" aria-labelledby="alquiler-tab">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Fecha</th>
                                        <th>Concepto</th>
                                        <th class="text-end">Importe</th>
                                        <th class="text-center pe-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Se carga vía fetch --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Historial Gastos --}}
                    <div class="tab-pane fade" id="gastos-history" role="tabpanel" aria-labelledby="gastos-tab">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Fecha</th>
                                        <th>Categoria(Concepto)</th>
                                        <th class="text-end">Importe</th>
                                        <th class="text-center pe-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Se carga vía fetch --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar Historial</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/inquilino/validacion_incidencia.js') }}"></script>
<script src="{{ asset('js/inquilino/incidencias.js') }}"></script>
<script src="{{ asset('js/inquilino/inquilino.js') }}"></script>
@endsection