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
        <a href="{{ url('/gestor/propiedades/' . $propiedad->id_propiedad) }}" class="btn-volver-propiedades">← Volver al detalle</a>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
</div>

<div class="card-admin card-gastos" id="gastos-propiedad">
    <div class="card-header-admin"><span>Gestión completa de gastos</span></div>

    @if(session('success'))
        <div class="mensaje-estado mensaje-ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mensaje-estado mensaje-error">{{ session('error') }}</div>
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

        <form method="POST" action="{{ url('/gestor/propiedades/' . $propiedad->id_propiedad . '/gastos') }}" class="form-gasto">
            @csrf
            <div class="fila-form-gasto">
                <label>
                    Categoría
                    <select name="categoria_gasto" required>
                        <option value="" disabled {{ old('categoria_gasto') ? '' : 'selected' }}>Selecciona una categoría</option>
                        <option value="luz" {{ old('categoria_gasto') === 'luz' ? 'selected' : '' }}>Luz</option>
                        <option value="agua" {{ old('categoria_gasto') === 'agua' ? 'selected' : '' }}>Agua</option>
                        <option value="gas" {{ old('categoria_gasto') === 'gas' ? 'selected' : '' }}>Gas</option>
                        <option value="internet" {{ old('categoria_gasto') === 'internet' ? 'selected' : '' }}>Internet</option>
                        <option value="comunidad" {{ old('categoria_gasto') === 'comunidad' ? 'selected' : '' }}>Comunidad</option>
                        <option value="otros" {{ old('categoria_gasto') === 'otros' ? 'selected' : '' }}>Otros</option>
                    </select>
                </label>
                <label>
                    Concepto <span class="texto-opcional">(opcional)</span>
                    <input type="text" name="concepto_gasto" value="{{ old('concepto_gasto') }}" maxlength="200" placeholder="Ej: recibo de electricidad" />
                </label>
                <label>
                    Importe
                    <input type="number" step="0.01" min="0.01" name="importe_estimado" value="{{ old('importe_estimado') }}" required placeholder="Importe del recibo" />
                </label>
            </div>

            <div class="fila-form-gasto">
                <label>
                    Fecha inicio
                    <input type="date" name="fecha_inicio_gasto" value="{{ old('fecha_inicio_gasto', now()->startOfMonth()->toDateString()) }}" required />
                </label>
                <label>
                    Fecha fin
                    <input type="date" name="fecha_fin_gasto" value="{{ old('fecha_fin_gasto', now()->endOfMonth()->toDateString()) }}" required />
                </label>
            </div>

            @if($errors->any())
                <div class="mensaje-estado mensaje-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="acciones-form-gasto">
                <button type="submit" class="btn-principal-admin">Añadir recibo</button>
            </div>
        </form>

        <table class="tabla-admin tabla-gastos">
            <thead>
                <tr>
                    <th>MES</th>
                    <th>CONCEPTO</th>
                    <th>CATEGORÍA</th>
                    <th>ÁMBITO</th>
                    <th>VENCIMIENTO</th>
                    <th>ESTADO</th>
                    <th>DETALLE DE PAGO</th>
                </tr>
            </thead>
            <tbody>
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
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($cuota->mes_cuota)->translatedFormat('m/Y') }}</td>
                        <td>{{ $cuota->concepto_gasto ?: 'Sin concepto' }}</td>
                        <td>{{ $categoriaLabel }}</td>
                        <td>
                            @if(($cuota->ambito_gasto ?? 'propiedad') === 'contrato')
                                Contrato #{{ $cuota->id_alquiler_fk }}
                            @else
                                Propiedad
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($cuota->vencimiento_cuota)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge-estado badge-gasto-{{ $estadoVisual }}">
                                {{ ucfirst(str_replace('_', ' ', $estadoVisual)) }}
                            </span>
                        </td>
                        <td>
                            <div class="detalle-pagos-lista">
                                @foreach($detallesCuota as $detalle)
                                    <div class="detalle-pago-item">
                                        <span>
                                            {{ $detalle->nombre_usuario }}: {{ number_format((float) $detalle->importe_detalle, 2, ',', '.') }} EUR
                                            ({{ ucfirst($detalle->estado_detalle) }})
                                        </span>
                                        @if($detalle->estado_detalle !== 'pagado')
                                            <form method="POST" action="{{ url('/gestor/propiedades/' . $propiedad->id_propiedad . '/gastos/cuotas/' . $cuota->id_gasto_cuota . '/pagos/' . $detalle->id_gasto_cuota_detalle) }}">
                                                @csrf
                                                <button type="submit" class="link-ver-todos">Marcar pagado</button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
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
    @endif
</div>
@endsection
