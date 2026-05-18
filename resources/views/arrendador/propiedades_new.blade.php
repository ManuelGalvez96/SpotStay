@extends('layouts.arrendador')

@section('titulo', 'Gestiona tus propiedades')
@section('titulo-cabecera', 'Gestiona tus propiedades')
@section('subtitulo', 'Crea, edita y publica inmuebles de tu portafolio.')
@section('avatar', $avatarInicial)

@section('css')
<link rel="stylesheet" href="{{ asset('css/arrendador/propiedades.css') }}" />
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="stats-grid">
        <div class="stat-card"><span>{{ $totales['totalPropiedades'] }}</span><small>Total</small></div>
        <div class="stat-card"><span>{{ $totales['publicadas'] }}</span><small>Publicadas</small></div>
        <div class="stat-card"><span>{{ $totales['alquiladas'] }}</span><small>Alquiladas</small></div>
        <div class="stat-card"><span>{{ $totales['inactivas'] }}</span><small>Inactivas</small></div>
        <button class="stat-card btn-nueva-propiedad" type="button" data-arrendador-id="{{ $arrendadorId }}" onclick="abrirModalFormulario(this.dataset.arrendadorId)">
            <span>+</span>
            <small>Nueva propiedad</small>
        </button>
    </section>

    <section class="content-grid">
        <div class="panel list-panel">
            <div class="panel-header">
                <h2>Mis propiedades</h2>
                <a class="link-secondary" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">Volver al dashboard</a>
            </div>

            <table class="properties-table">
                <thead>
                    <tr>
                        <th>Propiedad</th>
                        <th>Estado</th>
                        <th>Incidencias</th>
                        <th>Pagos</th>
                        <th>Gestor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($propiedades as $propiedad)
                        <tr>
                            <td>
                                <div class="property-info">
                                    <p class="property-title">{{ $propiedad->titulo_propiedad }}</p>
                                    <p class="property-meta">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }} {{ $propiedad->codigo_postal_propiedad }}</p>
                                    <p class="property-meta">{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} €/mes</p>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $propiedad->estado_propiedad }}">{{ ucfirst($propiedad->estado_propiedad) }}</span>
                            </td>
                            <td class="text-center">
                                {{ $propiedad->total_incidencias ?? 'Sin incidencias' }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $propiedad->estado_pagos ?? 'al-dia' }}">{{ $propiedad->estado_pagos_label ?? 'Al día' }}</span>
                            </td>
                            <td>
                                {{ $propiedad->gestor_nombre ?? 'Sin gestor' }}
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a class="action-link" href="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorId, 'editar' => $propiedad->id_propiedad]) }}">Editar</a>
                                    <button class="action-link" type="button" data-propiedad-id="{{ $propiedad->id_propiedad }}" data-arrendador-id="{{ $arrendadorId }}" onclick="abrirModalPropiedad(this.dataset.propiedadId, this.dataset.arrendadorId)">Ver</button>
                                    <form method="POST" action="{{ route('arrendador.propiedades.estado', $propiedad->id_propiedad) }}" data-ajax-state-form="true" class="inline-form">
                                        @csrf
                                        <input type="hidden" name="arrendador_id" value="{{ $arrendadorId }}" />
                                        <button class="action-link" type="submit" data-state-button="true">{{ $propiedad->estado_propiedad === 'publicada' ? 'Inactivar' : 'Publicar' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Aún no tienes propiedades creadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="pagination-wrap">{{ $propiedades->withQueryString()->links() }}</div>
        </div>
    </section>

    <div id="modal-propiedad" class="modal" hidden>
        <div class="modal-backdrop" onclick="cerrarModalPropiedad()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-titulo">Detalles de la propiedad</h2>
                <button class="modal-close" type="button" onclick="cerrarModalPropiedad()">✕</button>
            </div>
            <div class="modal-body" id="modal-body">
                <div class="spinner">Cargando...</div>
            </div>
        </div>
    </div>

    <div id="modal-formulario" class="modal" hidden>
        <div class="modal-backdrop" onclick="cerrarModalFormulario()"></div>
        <div class="modal-content modal-formulario">
            <div class="modal-header">
                <h2 id="modal-formulario-titulo">Nueva propiedad</h2>
                <button class="modal-close" type="button" onclick="cerrarModalFormulario()">✕</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('arrendador.propiedades.store') }}" class="property-form" data-ajax-form="true" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id_propiedad" id="form-id-propiedad" value="" />
                    <input type="hidden" name="arrendador_id" id="form-arrendador-id" value="{{ $arrendadorId }}" />
                    <input type="hidden" name="imagen-principal-indice" id="imagen-principal-indice" value="-1" />

                    <div class="form-grid">
                        <!-- Información Básica -->
                        <div class="form-section">
                            <h3>Información Básica</h3>
                            <div class="form-subsection">
                                <label>
                                    <span>Título</span>
                                    <input type="text" name="titulo_propiedad" id="form-titulo" value="" required>
                                </label>
                                <label>
                                    <span>Tipo de propiedad</span>
                                    <select name="tipo_propiedad" id="form-tipo" required>
                                        <option value="">Selecciona un tipo</option>
                                        <option value="piso">Piso</option>
                                        <option value="casa">Casa</option>
                                        <option value="estudio">Estudio</option>
                                        <option value="habitacion">Habitación</option>
                                    </select>
                                </label>
                                <label>
                                    <span>Estado</span>
                                    <select name="estado_propiedad" id="form-estado" required>
                                        <option value="borrador">Borrador</option>
                                        <option value="publicada">Publicada</option>
                                    </select>
                                </label>
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="form-section">
                            <h3>Ubicación</h3>
                            <div class="form-subsection">
                                <label>
                                    <span>Calle</span>
                                    <input type="text" name="calle_propiedad" id="form-calle" value="" required>
                                </label>
                                <label>
                                    <span>Número</span>
                                    <input type="text" name="numero_propiedad" id="form-numero" value="" required>
                                </label>
                                <label>
                                    <span>Piso</span>
                                    <input type="text" name="piso_propiedad" id="form-piso" value="">
                                </label>
                                <label>
                                    <span>Puerta</span>
                                    <input type="text" name="puerta_propiedad" id="form-puerta" value="">
                                </label>
                                <label>
                                    <span>Código postal</span>
                                    <input type="text" name="codigo_postal_propiedad" id="form-codigo-postal" value="" required>
                                </label>
                                <label class="full-width">
                                    <span>Ciudad</span>
                                    <input type="text" name="ciudad_propiedad" id="form-ciudad" value="" required>
                                </label>
                            </div>
                        </div>

                        <!-- Características -->
                        <div class="form-section">
                            <h3>Características</h3>
                            <div class="form-subsection">
                                <label>
                                    <span>Habitaciones</span>
                                    <input type="text" name="habitaciones_propiedad" id="form-habitaciones" value="">
                                </label>
                                <label>
                                    <span>Baños</span>
                                    <input type="number" name="banos_propiedad" id="form-banos" value="" min="0">
                                </label>
                                <label>
                                    <span>Metros cuadrados</span>
                                    <input type="number" name="metros_cuadrados_propiedad" id="form-metros" value="" min="0">
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="ascensor_propiedad" id="form-ascensor" value="1">
                                    <span>Ascensor</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="amueblado_propiedad" id="form-amueblado" value="1">
                                    <span>Amueblado</span>
                                </label>
                            </div>
                        </div>

                        <!-- Precio -->
                        <div class="form-section">
                            <h3>Precio</h3>
                            <div class="form-subsection">
                                <label>
                                    <span>Alquiler mensual (€)</span>
                                    <input type="number" step="0.01" name="precio_propiedad" id="form-precio" value="" required>
                                </label>
                            </div>
                        </div>

                        <!-- Descripción e Imágenes -->
                        <div class="form-section">
                            <h3>Descripción e Imágenes</h3>
                            <div class="form-subsection">
                                <label class="full-width">
                                    <span>Descripción</span>
                                    <textarea name="descripcion_propiedad" id="form-descripcion" rows="5"></textarea>
                                </label>
                                <label class="full-width">
                                    <span>Imágenes de la propiedad (máximo 10)</span>
                                    <input type="file" name="imagenes_propiedad[]" id="imagenes-propiedad" accept="image/jpeg,image/png,image/webp" multiple>
                                    <small class="input-help">Puedes subir hasta 10 imágenes (JPG, PNG, WEBP). Solo puedes anclar una como principal.</small>
                                </label>
                                <div id="contenedor-previa-imagenes" hidden>
                                    <p class="input-help"><strong>Vista previa y seleccionar principal:</strong></p>
                                    <div id="lista-previa-imagenes"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn-primary" type="submit" id="btn-submit-formulario">Publicar propiedad</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/arrendador/propiedades.js') }}"></script>
@endsection
