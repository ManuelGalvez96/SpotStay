<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propiedades del Arrendador - SpotStay</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/arrendador/propiedades.css') }}" />
</head>
<body>
<div class="page-shell">
    <header class="page-hero">
        <div>
            <p class="eyebrow">Arrendador</p>
            <h1>Gestiona tus propiedades</h1>
            <p class="hero-copy">Crea, edita y publica inmuebles de tu portafolio.</p>
        </div>
        <div class="hero-lateral">
            <div class="hero-avatar">{{ $avatarInicial }}</div>
            <a class="btn-volver" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">← Volver al dashboard</a>
            <a class="btn-volver" href="{{ route('logout') }}">Cerrar sesion</a>
        </div>
    </header>

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
        <button class="stat-card btn-nueva-propiedad" type="button" onclick="abrirModalFormulario({{ $arrendadorId }})" style="background: #0066cc; color: white; border: none; cursor: pointer; font-size: 16px; font-weight: 600;">
            <span style="font-size: 24px;">+</span>
            <small>Nueva propiedad</small>
        </button>
    </section>

    <section class="content-grid">
        <div class="panel list-panel">
            <div class="panel-header">
                <h2>Mis propiedades</h2>
                <a class="link-secondary" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">Volver al dashboard</a>
            </div>

            <div class="property-list">
                @forelse ($propiedades as $propiedad)
                    <article class="property-card">
                        <div>
                            <p class="property-title">{{ $propiedad->titulo_propiedad }}</p>
                            <p class="property-meta">{{ $propiedad->direccion_propiedad }}, {{ $propiedad->ciudad_propiedad }} · {{ $propiedad->codigo_postal_propiedad }}</p>
                            <p class="property-meta">{{ number_format((float) $propiedad->precio_propiedad, 2, ',', '.') }} €/mes · {{ $propiedad->total_inquilinos ?? 0 }} inquilinos activos</p>
                        </div>
                        <div class="property-actions">
                            <span class="badge badge-{{ $propiedad->estado_propiedad }}">{{ ucfirst($propiedad->estado_propiedad) }}</span>
                            <a class="mini-link" href="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorId, 'editar' => $propiedad->id_propiedad]) }}">Editar</a>
                            <button class="mini-link" type="button" onclick="abrirModalPropiedad({{ $propiedad->id_propiedad }}, {{ $arrendadorId }})">Ver</button>
                            <form method="POST" action="{{ route('arrendador.propiedades.estado', $propiedad->id_propiedad) }}" data-ajax-state-form="true">
                                @csrf
                                <input type="hidden" name="arrendador_id" value="{{ $arrendadorId }}" />
                                <button class="mini-button" type="submit" data-state-button="true">
                                    {{ $propiedad->estado_propiedad === 'publicada' ? 'Inactivar' : 'Publicar' }}
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">Aún no tienes propiedades creadas.</div>
                @endforelse
            </div>

            <div class="pagination-wrap">{{ $propiedades->withQueryString()->links() }}</div>
        </div>
    </section>
</div>

<div id="modal-propiedad" class="modal" style="display: none;">
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

<div id="modal-formulario" class="modal" style="display: none;">
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
                    <div class="form-section" style="grid-column: 1 / -1;">
                        <h3 style="margin-top: 0; margin-bottom: 15px; color: #0066cc; font-size: 14px; text-transform: uppercase; font-weight: 600; border-bottom: 2px solid #0066cc; padding-bottom: 10px;">Información Básica</h3>
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
                    <div class="form-section" style="grid-column: 1 / -1;">
                        <h3 style="margin-top: 0; margin-bottom: 15px; color: #0066cc; font-size: 14px; text-transform: uppercase; font-weight: 600; border-bottom: 2px solid #0066cc; padding-bottom: 10px;">Ubicación</h3>
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
                            <label style="grid-column: 1 / -1;">
                                <span>Ciudad</span>
                                <input type="text" name="ciudad_propiedad" id="form-ciudad" value="" required>
                            </label>
                        </div>
                    </div>

                    <!-- Características -->
                    <div class="form-section" style="grid-column: 1 / -1;">
                        <h3 style="margin-top: 0; margin-bottom: 15px; color: #0066cc; font-size: 14px; text-transform: uppercase; font-weight: 600; border-bottom: 2px solid #0066cc; padding-bottom: 10px;">Características</h3>
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
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="ascensor_propiedad" id="form-ascensor" value="1">
                                <span>Ascensor</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="amueblado_propiedad" id="form-amueblado" value="1">
                                <span>Amueblado</span>
                            </label>
                        </div>
                    </div>

                    <!-- Precio -->
                    <div class="form-section" style="grid-column: 1 / -1;">
                        <h3 style="margin-top: 0; margin-bottom: 15px; color: #0066cc; font-size: 14px; text-transform: uppercase; font-weight: 600; border-bottom: 2px solid #0066cc; padding-bottom: 10px;">Precio</h3>
                        <div class="form-subsection">
                            <label>
                                <span>Alquiler mensual (€)</span>
                                <input type="number" step="0.01" name="precio_propiedad" id="form-precio" value="" required>
                            </label>
                        </div>
                    </div>

                    <!-- Descripción e Imágenes -->
                    <div class="form-section" style="grid-column: 1 / -1;">
                        <h3 style="margin-top: 0; margin-bottom: 15px; color: #0066cc; font-size: 14px; text-transform: uppercase; font-weight: 600; border-bottom: 2px solid #0066cc; padding-bottom: 10px;">Descripción e Imágenes</h3>
                        <div class="form-subsection">
                            <label style="grid-column: 1 / -1;">
                                <span>Descripción</span>
                                <textarea name="descripcion_propiedad" id="form-descripcion" rows="5"></textarea>
                            </label>
                            <label style="grid-column: 1 / -1;">
                                <span>Imágenes de la propiedad (máximo 10)</span>
                                <input type="file" name="imagenes_propiedad[]" id="imagenes-propiedad" accept="image/jpeg,image/png,image/webp" multiple>
                                <small class="input-help">Puedes subir hasta 10 imágenes (JPG, PNG, WEBP). Solo puedes anclar una como principal.</small>
                            </label>
                            <div id="contenedor-previa-imagenes" style="display: none; margin-top: 15px; grid-column: 1 / -1;">
                                <p class="input-help"><strong>Vista previa y seleccionar principal:</strong></p>
                                <div id="lista-previa-imagenes" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; margin-top: 10px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn-primary" type="submit" id="btn-submit-formulario">Publicarpropiedad</button>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    display: flex;
    align-items: center;
    justify-content: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    cursor: pointer;
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    z-index: 1001;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #000;
}

.modal-body {
    padding: 20px;
}

.spinner {
    text-align: center;
    color: #999;
    padding: 40px;
}

.badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-borrador { background: #fff3cd; color: #856404; }
.badge-publicada { background: #d4edda; color: #155724; }
.badge-alquilada { background: #cce5ff; color: #004085; }
.badge-inactiva { background: #f8d7da; color: #721c24; }

.error {
    color: #d32f2f;
    text-align: center;
    padding: 20px;
}

.modal-formulario {
    max-width: 700px;
}

.btn-nueva-propiedad {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.btn-nueva-propiedad:hover {
    background: #0052a3 !important;
    transform: scale(1.05);
}

.property-form .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.property-form .form-grid .wide {
    grid-column: 1 / -1;
}

.form-section {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
}

.form-subsection {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-subsection label {
    display: flex;
    flex-direction: column;
}

.form-subsection label span {
    margin-bottom: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #333;
}

.form-subsection input[type="text"],
.form-subsection input[type="number"],
.form-subsection input[type="file"],
.form-subsection select,
.form-subsection textarea {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
}

.form-subsection input[type="text"]:focus,
.form-subsection input[type="number"]:focus,
.form-subsection input[type="file"]:focus,
.form-subsection select:focus,
.form-subsection textarea:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}
</style>

<script src="{{ asset('js/arrendador/propiedades.js') }}"></script>
</body>
</html>
