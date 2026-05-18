@extends('layouts.gestor')
@section('titulo', 'Gestión de gastos - Gestor SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestor/propiedad.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Gastos de {{ $propiedad->titulo_propiedad }}</h1>
        <p>{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }} · CP {{ $propiedad->codigo_postal_propiedad }}</p>
    </div>
    <div class="hero-actions">
        <a href="{{ route('gestor.propiedades.show', ['id' => $propiedad->id_propiedad]) }}" class="btn-volver-propiedades">← Volver al detalle</a>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
</div>

<div class="card-admin card-gastos" id="gastos-propiedad">
    <div class="card-header-admin card-header-acciones">
        <span>Gestión completa de gastos</span>
        <button type="button" class="btn-nuevo-recibo" onclick="abrirModalNuevoGasto()">+ Añadir recibo</button>
    </div>

    @if(session('success'))
        <div class="mensaje-estado mensaje-ok" data-flash-success="{{ session('success') }}">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mensaje-estado mensaje-error" data-flash-error="{{ session('error') }}">{{ session('error') }}</div>
    @endif

    @if(!$gastosHabilitados)
        <div class="mensaje-estado mensaje-info">Para activar esta sección, ejecuta las migraciones pendientes.</div>
    @else
        <div class="pagos-principales-grid">
            @foreach($pagosPrincipales as $clavePago => $pagoPrincipal)
                @php
                    $estadoPagoCard = in_array($pagoPrincipal['estado'], ['pagado', 'pendiente', 'parcial', 'atrasado'], true)
                        ? $pagoPrincipal['estado']
                        : 'sin_dato';
                    $detallePago = $pagoPrincipal['detalle'] ?? null;
                @endphp
                <div class="pago-principal-card pago-principal-{{ $estadoPagoCard }}">
                    <span class="pago-principal-titulo">{{ $pagoPrincipal['label'] }}</span>
                    <strong class="pago-principal-importe">{{ number_format((float) $pagoPrincipal['importe'], 2, ',', '.') }} EUR</strong>
                    <span class="pago-principal-estado estado-{{ $estadoPagoCard }}">
                        {{ ucfirst($estadoPagoCard) }}
                    </span>
                    @if($detallePago)
                        <span class="pago-principal-fecha">
                            {{ $detallePago['texto'] }}@if(!empty($detallePago['fecha'])) · {{ $detallePago['fecha'] }}@endif
                        </span>
                    @elseif($estadoPagoCard === 'atrasado' && !empty($pagoPrincipal['atrasados']))
                        <span class="pago-principal-fecha">{{ $pagoPrincipal['atrasados'] }} recibos atrasados</span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="resumen-gastos">
            <div class="resumen-pill">Total mensual (con alquiler): <strong>{{ number_format((float) $resumenGastos['mensual_total'], 2, ',', '.') }} EUR</strong></div>
            <div class="resumen-pill resumen-pill-pendiente">Total pendiente: <strong>{{ number_format((float) $resumenGastos['total_pendiente_importe'], 2, ',', '.') }} EUR</strong></div>
            <div class="resumen-pill">Pendientes del mes: <strong>{{ $resumenGastos['pendientes_mes'] }}</strong></div>
            <div class="resumen-pill resumen-pill-alerta">Pagos atrasados: <strong>{{ $resumenGastos['atrasados'] }}</strong></div>
            <div class="resumen-pill">Pagados este mes: <strong>{{ $resumenGastos['pagados_mes'] }}</strong></div>
        </div>

        <!-- Resumen visual mensual eliminado por petición del usuario -->

        <div class="card-admin card-con-franja" id="gastosFiltrosCard">
            <div class="card-franja"></div>
            <div class="card-header-admin card-header-gradient"><span>Filtros</span></div>
            <div class="gastos-filtros-form" id="gastosFiltrosForm" data-propiedad-id="{{ $propiedad->id_propiedad }}" data-ruta-gastos-filtrar="{{ route('gestor.propiedades.gastos.filtrar', ['id' => $propiedad->id_propiedad]) }}">
                <select name="categoria" data-filtro-gasto>
                    <option value="">Todas las categorías</option>
                    <option value="luz">Luz</option>
                    <option value="agua">Agua</option>
                    <option value="gas">Gas</option>
                    <option value="internet">Internet</option>
                    <option value="comunidad">Comunidad</option>
                    <option value="otros">Otros</option>
                </select>
                <select name="estado" data-filtro-gasto>
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="pagado">Pagado</option>
                    <option value="atrasado">Atrasado</option>
                    <option value="parcial">Parcial</option>
                </select>
                <span class="filtro-label">Desde</span>
                <input type="date" name="periodo_desde" data-filtro-gasto>
                <span class="filtro-label">Hasta</span>
                <input type="date" name="periodo_hasta" data-filtro-gasto>
                <input type="text" name="concepto" data-filtro-gasto placeholder="Buscar concepto">
                <button type="button" class="btn-limpiar-filtros" onclick="gastosLimpiarFiltros()">Limpiar</button>
            </div>
        </div>

        <table class="tabla-admin tabla-gastos">
            <thead>
                <tr>
                    <th>PERIODO</th>
                    <th>CONCEPTO</th>
                    <th>CATEGORÍA</th>
                    <th>ÁMBITO</th>
                    <th>VENCIMIENTO</th>
                    <th>ESTADO</th>
                    <th>DETALLE DE PAGO</th>
                </tr>
            </thead>
            <tbody id="gastosTableBody">
                @forelse($cuotasGasto as $cuota)
                    @php
                        $esAtrasado = in_array($cuota->estado_cuota, ['pendiente', 'parcial', 'atrasado'], true)
                            && \Carbon\Carbon::parse($cuota->vencimiento_cuota)->lt(\Carbon\Carbon::today());
                        $estadoVisual = $esAtrasado ? 'atrasado' : $cuota->estado_cuota;
                        $detallesCuota = $cuotasDetallePorId->get($cuota->id_gasto_cuota, collect());
                        $categoriaLabel = match ($cuota->categoria_gasto) {
                            'luz' => 'Luz',
                            'agua' => 'Agua',
                            'gas' => 'Gas',
                            'internet' => 'Internet',
                            'comunidad' => 'Comunidad',
                            'otros' => 'Otros',
                            'base_propiedad' => 'Base propiedad',
                            default => $cuota->categoria_gasto ?: 'Sin categoría',
                        };
                    @endphp
                    <tr class="cuota-row" data-gasto-id="{{ $cuota->id_gasto_fk }}" data-propiedad-id="{{ $propiedad->id_propiedad }}" data-importe="{{ $cuota->importe_total_cuota }}" data-mes="{{ $cuota->mes_cuota }}" data-fecha-inicio="{{ $cuota->fecha_inicio_gasto ?? '' }}" data-fecha-fin="{{ $cuota->fecha_fin_gasto ?? '' }}">
                        <td class="display-mes">@if($cuota->fecha_inicio_gasto && $cuota->fecha_fin_gasto){{ \Carbon\Carbon::parse($cuota->fecha_inicio_gasto)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($cuota->fecha_fin_gasto)->format('d/m/Y') }}@else{{ \Carbon\Carbon::parse($cuota->mes_cuota)->translatedFormat('m/Y') }}@endif</td>
                        <td class="display-concepto">{{ $cuota->concepto_gasto ?: 'Sin concepto' }}</td>
                        <td class="display-categoria">{{ $categoriaLabel }}</td>
                        <td class="display-ambito">
                            @if(($cuota->ambito_gasto ?? 'propiedad') === 'contrato')
                                Contrato #{{ $cuota->id_alquiler_fk }}
                            @else
                                Propiedad
                            @endif
                        </td>
                        <td class="display-fecha">{{ \Carbon\Carbon::parse($cuota->vencimiento_cuota)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge-estado badge-gasto-{{ $estadoVisual }}">
                                {{ ucfirst(str_replace('_', ' ', $estadoVisual)) }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
                                <div class="detalle-acciones" style="min-width:160px;">
                                    @if($cuota->estado_cuota !== 'pagado')
                                        <button type="button" class="btn-cuota-edit">Editar</button>
                                        <button type="button" class="btn-cuota-delete">Eliminar</button>
                                    @endif
                                </div>
                                <div class="detalle-pagos-lista">
                                @foreach($detallesCuota as $detalle)
                                    <div class="detalle-pago-item">
                                        <span>
                                            {{ $detalle->nombre_usuario }}: {{ number_format((float) $detalle->importe_detalle, 2, ',', '.') }} EUR
                                            ({{ ucfirst($detalle->estado_detalle) }})
                                        </span>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="tabla-vacia">Todavía no hay gastos creados para esta propiedad.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    <div id="modal-nuevo-gasto" class="gestor-modal">
    <div class="gestor-modal-backdrop" onclick="cerrarModalNuevoGasto()"></div>
    <div class="gestor-modal-content">
        <div class="gestor-modal-header">
            <h2>Nuevo recibo</h2>
            <button class="gestor-modal-close" onclick="cerrarModalNuevoGasto()">✕</button>
        </div>
        <div class="gestor-modal-body">
                <form method="POST" action="{{ route('gestor.propiedades.gastos.store', ['id' => $propiedad->id_propiedad]) }}" class="property-form" data-ajax-nuevo-gasto="true">
                    @csrf
                    <div class="form-grid">
                        <div class="form-section">
                            <h3>Datos del recibo</h3>
                            <div class="form-subsection">
                                <label>
                                    <span>Categoría</span>
                                    <select name="categoria_gasto" required>
                                        <option value="" disabled selected>Selecciona una categoría</option>
                                        <option value="luz">Luz</option>
                                        <option value="agua">Agua</option>
                                        <option value="gas">Gas</option>
                                        <option value="internet">Internet</option>
                                        <option value="comunidad">Comunidad</option>
                                        <option value="otros">Otros</option>
                                    </select>
                                </label>
                                <label>
                                    <span>Concepto (opcional)</span>
                                    <input type="text" name="concepto_gasto" maxlength="200" placeholder="Ej: recibo de electricidad">
                                </label>
                                <label>
                                    <span>Importe (€)</span>
                                    <input type="number" step="0.01" min="0.01" name="importe_estimado" required placeholder="0.00">
                                </label>
                                <label class="date-range-group">
                                    <span>Período</span>
                                    <div class="date-range-inputs">
                                        <input type="date" name="fecha_inicio_gasto" value="{{ now()->startOfMonth()->toDateString() }}" required>
                                        <span class="date-range-sep">→</span>
                                        <input type="date" name="fecha_fin_gasto" value="{{ now()->endOfMonth()->toDateString() }}" required>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mensaje-estado mensaje-error mensaje-error-js" style="display:none;"></div>

                    <button class="btn-primary" type="submit">Añadir recibo</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-editar-gasto" class="gestor-modal">
    <div class="gestor-modal-backdrop" onclick="cerrarModalEditarGasto()"></div>
    <div class="gestor-modal-content">
        <div class="gestor-modal-header">
            <h2>Editar recibo</h2>
            <button class="gestor-modal-close" onclick="cerrarModalEditarGasto()">✕</button>
        </div>
        <div class="gestor-modal-body">
                <form method="POST" class="property-form" data-ajax-editar-gasto="true">
                    @csrf
                    <div class="form-grid">
                        <div class="form-section">
                            <h3>Datos del recibo</h3>
                            <div class="form-subsection">
                                <label>
                                    <span>Categoría</span>
                                    <select name="categoria_gasto" required>
                                        <option value="" disabled selected>Selecciona una categoría</option>
                                        <option value="luz">Luz</option>
                                        <option value="agua">Agua</option>
                                        <option value="gas">Gas</option>
                                        <option value="internet">Internet</option>
                                        <option value="comunidad">Comunidad</option>
                                        <option value="otros">Otros</option>
                                    </select>
                                </label>
                                <label>
                                    <span>Concepto (opcional)</span>
                                    <input type="text" name="concepto_gasto" maxlength="200" placeholder="Ej: recibo de electricidad">
                                </label>
                                <label>
                                    <span>Importe (€)</span>
                                    <input type="number" step="0.01" min="0.01" name="importe_estimado" required placeholder="0.00">
                                </label>
                                <label class="date-range-group">
                                    <span>Período</span>
                                    <div class="date-range-inputs">
                                        <input type="date" name="fecha_inicio_gasto" required>
                                        <span class="date-range-sep">→</span>
                                        <input type="date" name="fecha_fin_gasto" required>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mensaje-estado mensaje-error mensaje-error-js" style="display:none;"></div>

                    <button class="btn-primary" type="submit">Guardar cambios</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection

    @section('scripts')
    <script src="{{ asset('js/gestor/gasto-modal.js') }}"></script>
    <script src="{{ asset('js/gestor/gastos-filtros.js') }}"></script>
    <script src="{{ asset('js/gestor/recibos-inline.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const flashSuccess = document.querySelector('[data-flash-success]');
            const flashError = document.querySelector('[data-flash-error]');
            if (flashSuccess && flashSuccess.dataset.flashSuccess) {
                if (window.swalSuccess) swalSuccess('Éxito', flashSuccess.dataset.flashSuccess);
            }
            if (flashError && flashError.dataset.flashError) {
                if (window.swalError) swalError('Error', flashError.dataset.flashError);
            }
        });
    </script>
    @endsection
