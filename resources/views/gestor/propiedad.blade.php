@extends('layouts.gestor')
@section('titulo', 'Detalle de propiedad - Gestor SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestor/propiedad.css') }}">
@endsection

@section('content')
@if(session('error'))
    <div class="mensaje-estado mensaje-error" data-flash-error="{{ session('error') }}">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="mensaje-estado mensaje-ok" data-flash-success="{{ session('success') }}">{{ session('success') }}</div>
@endif

<div class="hero-admin">
    <div class="hero-content">
        <h1>{{ $propiedad->titulo_propiedad }}</h1>
        <p>{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }} · CP {{ $propiedad->codigo_postal_propiedad }}</p>
        <div class="permisos-badges" style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
            @if($permisos->gastos) <span class="badge-estado badge-activo" style="font-size:11px;">Gastos ✓</span>
            @else <span class="badge-estado badge-inactiva" style="font-size:11px;">Gastos ✗</span> @endif
            @if($permisos->incidencias) <span class="badge-estado badge-activo" style="font-size:11px;">Incidencias ✓</span>
            @else <span class="badge-estado badge-inactiva" style="font-size:11px;">Incidencias ✗</span> @endif
            @if($permisos->chat) <span class="badge-estado badge-activo" style="font-size:11px;">Chat ✓</span>
            @else <span class="badge-estado badge-inactiva" style="font-size:11px;">Chat ✗</span> @endif
            @if($permisos->editar_propiedad) <span class="badge-estado badge-activo" style="font-size:11px;">Editar ✓</span>
            @else <span class="badge-estado badge-inactiva" style="font-size:11px;">Editar ✗</span> @endif
        </div>
    </div>
    <div class="hero-actions">
        <a href="{{ route('gestor.propiedades') }}" class="btn-volver-propiedades">← Volver a propiedades</a>
        @if($permisos->editar_propiedad)
            <button type="button" class="btn-editar-propiedad" onclick="abrirModalEditar({{ $propiedad->id_propiedad }})">
                <i class="bi bi-pencil"></i> Editar propiedad
            </button>
        @endif
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
</div>

@if($permisos->gastos)
<div class="card-admin card-gastos" id="gastos-propiedad">
    <div class="card-franja"></div>
    <div class="card-header-admin card-header-gradient card-header-acciones">
        <span>Pagos principales del mes</span>
        <a href="{{ route('gestor.propiedades.gastos', ['id' => $propiedad->id_propiedad]) }}" class="link-ver-todos">Gestionar gastos</a>
    </div>

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
            <div class="total-pendiente-box">
                Total pendiente: <strong>{{ number_format((float) $resumenGastos['total_pendiente_importe'], 2, ',', '.') }} EUR</strong>
            </div>
    @endif
</div>
@endif

<div class="central-grid detalle-grid">
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient"><span>Información de la propiedad</span></div>
        <div class="detalle-cuerpo">
            <div class="detalle-dato"><span class="label">Estado</span><span>{{ ucfirst($propiedad->estado_propiedad) }}</span></div>
            <div class="detalle-dato"><span class="label">Precio</span><span>{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} EUR/mes</span></div>
            <div class="detalle-dato"><span class="label">Gestor asignado</span><span>{{ $propiedad->nombre_gestor }}</span></div>
            <div class="detalle-dato"><span class="label">Descripción</span><span>{{ $propiedad->descripcion_propiedad ?: 'Sin descripción' }}</span></div>
        </div>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient"><span>Arrendador</span></div>
        <div class="detalle-cuerpo">
            <div class="detalle-dato"><span class="label">Nombre</span><span>{{ $propiedad->nombre_arrendador }}</span></div>
            <div class="detalle-dato"><span class="label">Email</span><span>{{ $propiedad->email_arrendador }}</span></div>
            <div class="detalle-dato"><span class="label">Teléfono</span><span>{{ $propiedad->telefono_arrendador ?: 'No disponible' }}</span></div>
        </div>
    </div>
</div>

<div class="inferior-grid detalle-grid-inferior">
    <div class="card-admin card-con-franja" id="alquileres-activos">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient"><span>Alquileres activos</span></div>
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

    @if($permisos->incidencias)
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient"><span>Incidencias recientes</span></div>
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
                        <td><a href="{{ route('gestor.incidencias.show', ['id' => $incidencia->id_incidencia]) }}" class="link-ver-todos">Abrir</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="tabla-vacia">No hay incidencias registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>

<div id="modal-editar-propiedad" class="modal">
    <div class="modal-backdrop" onclick="cerrarModalEditar()"></div>
    <div class="modal-content modal-formulario">
        <div class="modal-header">
            <h2 id="modal-editar-titulo">Editar propiedad</h2>
            <button class="modal-close" type="button" onclick="cerrarModalEditar()">✕</button>
        </div>
        <div class="modal-body">
            <form method="POST" class="property-form" data-ajax-editar="true">
                @csrf
                <input type="hidden" name="id_propiedad" id="edit-id-propiedad" value="">

                <div class="form-grid">
                    <div class="form-section">
                        <h3>Información Básica</h3>
                        <div class="form-subsection">
                            <label>
                                <span>Título</span>
                                <input type="text" name="titulo_propiedad" id="edit-form-titulo" required>
                            </label>
                            <label>
                                <span>Tipo de propiedad</span>
                                <select name="tipo_propiedad" id="edit-form-tipo" required>
                                    <option value="">Selecciona un tipo</option>
                                    <option value="piso">Piso</option>
                                    <option value="casa">Casa</option>
                                    <option value="estudio">Estudio</option>
                                    <option value="habitacion">Habitación</option>
                                </select>
                            </label>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Ubicación</h3>
                        <div class="form-subsection">
                            <label>
                                <span>Calle</span>
                                <input type="text" name="calle_propiedad" id="edit-form-calle" required>
                            </label>
                            <label>
                                <span>Número</span>
                                <input type="text" name="numero_propiedad" id="edit-form-numero" required>
                            </label>
                            <label>
                                <span>Piso</span>
                                <input type="text" name="piso_propiedad" id="edit-form-piso">
                            </label>
                            <label>
                                <span>Puerta</span>
                                <input type="text" name="puerta_propiedad" id="edit-form-puerta">
                            </label>
                            <label>
                                <span>Código postal</span>
                                <input type="text" name="codigo_postal_propiedad" id="edit-form-codigo-postal" required>
                            </label>
                            <label class="full-width">
                                <span>Ciudad</span>
                                <input type="text" name="ciudad_propiedad" id="edit-form-ciudad" required>
                            </label>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Características</h3>
                        <div class="form-subsection">
                            <label>
                                <span>Habitaciones</span>
                                <input type="text" name="habitaciones_propiedad" id="edit-form-habitaciones">
                            </label>
                            <label>
                                <span>Baños</span>
                                <input type="number" name="banos_propiedad" id="edit-form-banos" min="0">
                            </label>
                            <label>
                                <span>Metros cuadrados</span>
                                <input type="number" name="metros_cuadrados_propiedad" id="edit-form-metros" min="0">
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="ascensor_propiedad" id="edit-form-ascensor" value="1">
                                <span>Ascensor</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="amueblado_propiedad" id="edit-form-amueblado" value="1">
                                <span>Amueblado</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="piscina_propiedad" id="edit-form-piscina" value="1">
                                <span>Piscina</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="terraza_propiedad" id="edit-form-terraza" value="1">
                                <span>Terraza</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="garaje_propiedad" id="edit-form-garaje" value="1">
                                <span>Garaje</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="aire_acondicionado_propiedad" id="edit-form-aire-acondicionado" value="1">
                                <span>Aire acondicionado</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="calefaccion_propiedad" id="edit-form-calefaccion" value="1">
                                <span>Calefacción</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="trastero_propiedad" id="edit-form-trastero" value="1">
                                <span>Trastero</span>
                            </label>
                            <label class="full-width">
                                <span>Adicional</span>
                                <input type="text" name="adicional_propiedad" id="edit-form-adicional" maxlength="255">
                            </label>
                        </div>
                    </div>

                    <div class="form-section" id="edit-seccion-precio">
                        <h3>Precio</h3>
                        <div class="form-subsection">
                            <label>
                                <span>Alquiler mensual (€)</span>
                                <input type="number" step="0.01" name="precio_propiedad" id="edit-form-precio" required>
                            </label>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Descripción</h3>
                        <div class="form-subsection">
                            <label class="full-width">
                                <span>Descripción</span>
                                <textarea name="descripcion_propiedad" id="edit-form-descripcion" rows="5"></textarea>
                            </label>
                        </div>
                    </div>
                </div>

                <button class="btn-primary" type="submit" id="btn-submit-editar">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script src="{{ asset('js/gestor/propiedad-editar.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const flashSuccess = document.querySelector('[data-flash-success]');
    const flashError = document.querySelector('[data-flash-error]');
    if (flashSuccess && flashSuccess.dataset.flashSuccess && window.swalSuccess) {
        swalSuccess('Éxito', flashSuccess.dataset.flashSuccess);
    }
    if (flashError && flashError.dataset.flashError && window.swalError) {
        swalError('Error', flashError.dataset.flashError);
    }
});
</script>
@endsection
@endsection
