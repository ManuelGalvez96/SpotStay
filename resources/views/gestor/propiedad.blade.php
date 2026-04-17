@extends('layouts.gestor')
@section('titulo', 'Detalle de propiedad - Gestor SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestor/propiedad.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>{{ $propiedad->titulo_propiedad }}</h1>
        <p>{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }} · CP {{ $propiedad->codigo_postal_propiedad }}</p>
    </div>
    <div class="hero-actions">
        <a href="{{ url('/gestor/propiedades') }}" class="btn-volver-propiedades">← Volver a propiedades</a>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
</div>

<div class="central-grid detalle-grid">
    <div class="card-admin">
        <div class="card-header-admin"><span>Información de la propiedad</span></div>
        <div class="detalle-cuerpo">
            <div class="detalle-dato"><span class="label">Estado</span><span>{{ ucfirst($propiedad->estado_propiedad) }}</span></div>
            <div class="detalle-dato"><span class="label">Precio</span><span>{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} EUR/mes</span></div>
            <div class="detalle-dato"><span class="label">Gestor asignado</span><span>{{ $propiedad->nombre_gestor }}</span></div>
            <div class="detalle-dato"><span class="label">Descripción</span><span>{{ $propiedad->descripcion_propiedad ?: 'Sin descripción' }}</span></div>
        </div>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin"><span>Arrendador</span></div>
        <div class="detalle-cuerpo">
            <div class="detalle-dato"><span class="label">Nombre</span><span>{{ $propiedad->nombre_arrendador }}</span></div>
            <div class="detalle-dato"><span class="label">Email</span><span>{{ $propiedad->email_arrendador }}</span></div>
            <div class="detalle-dato"><span class="label">Teléfono</span><span>{{ $propiedad->telefono_arrendador ?: 'No disponible' }}</span></div>
        </div>
    </div>
</div>

<div class="inferior-grid detalle-grid-inferior">
    <div class="card-admin" id="alquileres-activos">
        <div class="card-header-admin"><span>Alquileres activos</span></div>
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>INQUILINO</th>
                    <th>EMAIL</th>
                    <th>INICIO</th>
                    <th>FIN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alquileresActivos as $alquiler)
                    <tr>
                        <td>{{ $alquiler->nombre_inquilino }}</td>
                        <td>{{ $alquiler->email_inquilino }}</td>
                        <td>{{ \Carbon\Carbon::parse($alquiler->fecha_inicio_alquiler)->format('d/m/Y') }}</td>
                        <td>{{ $alquiler->fecha_fin_alquiler ? \Carbon\Carbon::parse($alquiler->fecha_fin_alquiler)->format('d/m/Y') : 'Indefinido' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="tabla-vacia">No hay alquileres activos para esta propiedad.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin"><span>Incidencias recientes</span></div>
        <div class="resumen-incidencias">
            <div class="resumen-pill">Abiertas: <strong>{{ $totalesIncidencia['abiertas'] }}</strong></div>
            <div class="resumen-pill">En proceso: <strong>{{ $totalesIncidencia['en_proceso'] }}</strong></div>
            <div class="resumen-pill">Resueltas: <strong>{{ $totalesIncidencia['resueltas'] }}</strong></div>
        </div>
        <table class="tabla-admin tabla-incidencias-detalle">
            <thead>
                <tr>
                    <th>TÍTULO</th>
                    <th>ESTADO</th>
                    <th>PRIORIDAD</th>
                    <th>FECHA</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($incidenciasRecientes as $incidencia)
                    <tr>
                        <td>{{ $incidencia->titulo_incidencia }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $incidencia->estado_incidencia)) }}</td>
                        <td>{{ ucfirst($incidencia->prioridad_incidencia) }}</td>
                        <td>{{ \Carbon\Carbon::parse($incidencia->creado_incidencia)->format('d/m/Y') }}</td>
                        <td><a href="{{ url('/gestor/incidencias/' . $incidencia->id_incidencia) }}" class="link-ver-todos">Abrir</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="tabla-vacia">No hay incidencias registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-admin card-gastos" id="gastos-propiedad">
    <div class="card-header-admin"><span>Gestión de gastos</span></div>

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
                @endphp
                <div class="pago-principal-card pago-principal-{{ $estadoPagoCard }}">
                    <span class="pago-principal-titulo">{{ $pagoPrincipal['label'] }}</span>
                    <strong class="pago-principal-importe">{{ number_format((float) $pagoPrincipal['importe'], 2, ',', '.') }} EUR</strong>
                    <span class="pago-principal-estado estado-{{ $estadoPagoCard }}">
                        {{ $estadoPagoCard === 'sin_dato' ? 'Sin dato este mes' : ucfirst($estadoPagoCard) }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="resumen-gastos">
            <div class="resumen-pill">Mensual estimado: <strong>{{ number_format((float) $resumenGastos['mensual_estimado'], 2, ',', '.') }} EUR</strong></div>
            <div class="resumen-pill">Pendientes del mes: <strong>{{ $resumenGastos['pendientes_mes'] }}</strong></div>
            <div class="resumen-pill resumen-pill-alerta">Pagos atrasados: <strong>{{ $resumenGastos['atrasados'] }}</strong></div>
            <div class="resumen-pill">Pagados este mes: <strong>{{ $resumenGastos['pagados_mes'] }}</strong></div>
        </div>

        <form method="POST" action="{{ url('/gestor/propiedades/' . $propiedad->id_propiedad . '/gastos') }}" class="form-gasto">
            @csrf
            <div class="fila-form-gasto">
                <label>
                    Concepto
                    <input type="text" name="concepto_gasto" value="{{ old('concepto_gasto') }}" required maxlength="200" placeholder="Ej: Comunidad, Internet, Seguro" />
                </label>
                <label>
                    Categoría
                    <input type="text" name="categoria_gasto" value="{{ old('categoria_gasto') }}" maxlength="50" placeholder="Ej: suministros" />
                </label>
                <label>
                    Importe mensual (EUR)
                    <input type="number" step="0.01" min="0.01" name="importe_gasto" value="{{ old('importe_gasto') }}" required />
                </label>
            </div>

            <div class="fila-form-gasto">
                <label>
                    Quién paga
                    <select name="pagador_gasto" required>
                        <option value="gestor" {{ old('pagador_gasto') === 'gestor' ? 'selected' : '' }}>Gestor</option>
                        <option value="inquilinos" {{ old('pagador_gasto') === 'inquilinos' ? 'selected' : '' }}>Inquilinos (reparto automático)</option>
                    </select>
                </label>
                <label>
                    Día de vencimiento
                    <input type="number" name="dia_vencimiento" min="1" max="28" value="{{ old('dia_vencimiento', 5) }}" required />
                </label>
                <label>
                    Mes de inicio
                    <input type="date" name="fecha_inicio_gasto" value="{{ old('fecha_inicio_gasto', now()->startOfMonth()->toDateString()) }}" required />
                </label>
            </div>

            @if($errors->any())
                <div class="mensaje-estado mensaje-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="acciones-form-gasto">
                <button type="submit" class="btn-principal-admin">Añadir gasto mensual</button>
            </div>
        </form>

        <table class="tabla-admin tabla-gastos">
            <thead>
                <tr>
                    <th>MES</th>
                    <th>CONCEPTO</th>
                    <th>CATEGORÍA</th>
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
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($cuota->mes_cuota)->translatedFormat('m/Y') }}</td>
                        <td>{{ $cuota->concepto_gasto }}</td>
                        <td>{{ $cuota->categoria_gasto === 'base_propiedad' ? 'Base propiedad' : ($cuota->categoria_gasto ?: 'Sin categoría') }}</td>
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
                        <td colspan="6" class="tabla-vacia">Todavía no hay gastos creados para esta propiedad.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</div>
@endsection
