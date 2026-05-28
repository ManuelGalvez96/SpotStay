@extends('layouts.arrendador')

@section('titulo', 'Gestiona tus propiedades')
@section('titulo-cabecera', 'Gestiona tus propiedades')
@section('subtitulo', 'Crea, edita y publica inmuebles de tu portafolio.')
@section('avatar', $avatarInicial)

@section('css')
<link rel="stylesheet" href="{{ asset('css/arrendador/propiedades.css') }}" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')
@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif



<section class="stats-grid">
    @if ($limiteAlcanzado)
    <div class="alert alert-warning alert-limite" style="display: flex; flex-direction: column; gap: 10px; background: #fffcf4; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); box-sizing: border-box;">
        <div>
            <strong style="color: #856404; font-size: 16px;">💳 ¡Límite de Suscripción Alcanzado!</strong>
            <p style="margin: 5px 0 0 0; color: #664d03; line-height: 1.5; font-size: 14px;">
                Has alcanzado el límite de tu plan actual <strong>{{ $nombrePlan }}</strong> (máximo de <strong>{{ $maxPropiedades }}</strong> propiedades registradas). Para registrar un nuevo inmueble en SpotStay, debes inactivar o eliminar alguna propiedad existente, o mejorar tu nivel de suscripción para ampliar la capacidad.
            </p>
        </div>
    </div>
    @endif
    <div class="stat-card"><span>{{ $totales['totalPropiedades'] }}</span><small>Total</small></div>
    <div class="stat-card"><span>{{ $totales['publicadas'] }}</span><small>Publicadas</small></div>
    <div class="stat-card"><span>{{ $totales['alquiladas'] }}</span><small>Alquiladas</small></div>
    <div class="stat-card"><span>{{ $totales['inactivas'] }}</span><small>Inactivas</small></div>
    @if ($limiteAlcanzado)
        <div class="stat-card btn-nueva-propiedad" style="background: #fce8e6 !important; border: 1px solid #f5c2c2 !important; cursor: not-allowed; opacity: 0.85; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;" title="Has alcanzado el límite de tu suscripción actual.">
            <span style="color: #ea4335 !important; font-size: 24px;">✕</span>
            <small style="color: #c5221f !important; font-weight: bold;">Límite alcanzado</small>
        </div>
    @else
        <button class="stat-card btn-nueva-propiedad" type="button" data-arrendador-id="{{ $arrendadorId }}" onclick="abrirModalFormulario(this.dataset.arrendadorId)">
            <span>+</span>
            <small>Nueva propiedad</small>
        </button>
    @endif
</section>

<section class="content-grid">
    <div class="panel list-panel">
        <div class="panel-header">
            <h2>Mis propiedades</h2>
        </div>

        <table class="properties-table">
            <thead>
                <th>Propiedades</th>
                <th>Estado</th>
                <th>Incidencias</th>
                <th>Pagos</th>
                <th>Gestor</th>
                <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="spinner">Cargando...</div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="pagination-wrap"></div>
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

<!-- RUTA PARA OBTENER PERMISOS EN EL FETCH -->
<script>
    const rutaPermisosGestor = "{{ route('arrendador.permisos.get', ':propiedad') }}";
    const rutaDatosEdicionPropiedad = "{{ route('arrendador.propiedades.edit-data', ':id') }}";
</script>
<!-- MODAL GESTOR -->
<div id="modal-gestor-config" class="modal" hidden>
    <div class="modal-backdrop" onclick="cerrarModalGestor()"></div>
    <div class="modal-content modal-gestor-panel">
        <div class="modal-header">
            <h2>Gestión del gestor</h2>
            <button class="modal-close" id="btnCerrarModalGestor" type="button" onclick="cerrarModalGestor()">✕</button>
        </div>
        <div class="modal-body gestor-config-body">
            <section class="propiedad-context-card" id="propiedad_contexto_modal" aria-live="polite">
                <div class="propiedad-context-main">
                    <span class="propiedad-context-label">Propiedad seleccionada</span>
                    <h4 class="propiedad-context-title" id="modal_propiedad_titulo">Cargando propiedad...</h4>
                    <p class="propiedad-context-address" id="modal_propiedad_direccion">-</p>
                </div>
                <div class="propiedad-context-meta">
                    <span class="propiedad-context-chip" id="modal_propiedad_precio">Precio no disponible</span>
                    <span class="propiedad-context-chip" id="modal_propiedad_estado">Estado no disponible</span>
                </div>
            </section>

            <div class="gestor-config-grid">
                <aside class="gestor-left-panel">
                    <div class="gestor-section-header">
                        <h3 id="gestor-titulo">Asignar gestor</h3>
                        <p class="gestor-panel-help" id="gestor-descripcion">Introduce el código del gestor para asignarlo a esta propiedad. El código tiene formato GES-XXXX-XXXX.</p>
                    </div>

                    <div class="gestor-section-bottom" id="gestor-section-asignar">
                        <div class="gestor-selector-wrap">
                            <span class="gestor-selector-label">Código del gestor</span>
                            <div class="gestor-input-row">
                                <input type="text" id="codigo-gestor-input" class="codigo-gestor-input" placeholder="GES-XXXX-XXXX" maxlength="13" aria-label="Código del gestor" aria-describedby="codigo-gestor-error">
                                <button type="button" class="btn-validar-codigo" id="btn-validar-codigo">Asignar</button>
                            </div>
                            <span class="codigo-gestor-validacion" id="codigo-gestor-validacion"></span>
                            <div id="codigo-gestor-error" class="codigo-gestor-error" style="display:none;"></div>
                        </div>

                        <div class="gestor-info-container" id="gestor-info-container" style="display:none;margin-top:12px;padding:12px;background:#F5F5F5;border-radius:8px;">
                            <span class="gestor-selector-label">Gestor identificado</span>
                            <div class="gestor-info-display">
                                <div class="gestor-avatar" id="gestor_avatar_inicial">-</div>
                                <div class="gestor-selector-info">
                                    <span class="gestor-selector-name" id="nombre_gestor">—</span>
                                    <span class="gestor-selector-email" id="email_gestor">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="gestor-section-bottom" id="gestor-section-actual" style="display:none;">
                        <div class="gestor-actual-card">
                            <div class="gestor-avatar" id="gestor-actual-avatar-inicial">-</div>
                            <div class="gestor-actual-info">
                                <span class="gestor-actual-nombre" id="gestor-actual-nombre">—</span>
                                <span class="gestor-actual-email" id="gestor-actual-email">—</span>
                            </div>
                        </div>
                        <button type="button" class="btn-desasignar-gestor" id="btnDesasignarGestor" onclick="desasignarGestorPropiedad()">Quitar gestor</button>
                    </div>
                </aside>

                <section class="gestor-right-panel">
                    <h3>Permisos de administración</h3>
                    <p class="gestor-panel-help">Define exactamente qué acciones puede realizar <strong id="nombre_gestor_subtitulo">el gestor</strong> sobre esta propiedad.</p>

                    <div class="permissions-grid">
                        <label class="permission-card" for="permiso-gastos">
                            <input type="checkbox" id="permiso-gastos" value="1">
                            <div>
                                <span class="permission-title">Gestión de gastos</span>
                                <span class="permission-desc">Puede registrar y gestionar gastos y recibos.</span>
                            </div>
                        </label>

                        <label class="permission-card" for="permiso-incidencias">
                            <input type="checkbox" id="permiso-incidencias" value="1">
                            <div>
                                <span class="permission-title">Gestión de incidencias</span>
                                <span class="permission-desc">Coordina incidencias y reparaciones con los inquilinos.</span>
                            </div>
                        </label>

                        <label class="permission-card" for="permiso-editar">
                            <input type="checkbox" id="permiso-editar" value="1">
                            <div>
                                <span class="permission-title">Edición de propiedad</span>
                                <span class="permission-desc">Puede actualizar datos y contenido del anuncio.</span>
                            </div>
                        </label>

                        <label class="permission-card" for="permiso-chat">
                            <input type="checkbox" id="permiso-chat" value="1">
                            <div>
                                <span class="permission-title">Atención al inquilino</span>
                                <span class="permission-desc">Gestiona conversaciones y consultas desde el chat.</span>
                            </div>
                        </label>
                    </div>
                </section>
            </div>

            <div class="modal-footer">
                <button class="btn-modal-secondary" type="button" onclick="cerrarModalGestor()">Descartar</button>
                <button class="btn-primary" id="btnGuardarPermisosGestor" type="button">Guardar permisos</button>
            </div>
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
            <form id="form-registrar-propiedad" method="POST" action="{{ route('arrendador.propiedades.store') }}" class="property-form" data-ajax-form="true" enctype="multipart/form-data">
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
                            <label class="full-width">
                                <span>Seleccionar en mapa</span>
                                <div id="mapa-registro" class="mapa-registro-propiedad" style="height:300px;margin-top:8px;border:1px solid #ddd;border-radius:6px"></div>
                            </label>

                            <label class="full-width">
                                <span>Latitud</span>
                                <input type="number" step="0.0000001" name="latitud_propiedad" id="latitud_propiedad" value="" readonly required>
                            </label>

                            <label class="full-width">
                                <span>Longitud</span>
                                <input type="number" step="0.0000001" name="longitud_propiedad" id="longitud_propiedad" value="" readonly required>
                            </label>

                            <label class="full-width">
                                <span>Dirección (autocompletada)</span>
                                <input type="text" name="direccion_propiedad" id="direccion_propiedad" value="" readonly required>
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
                        </div>
                        <div class="form-subsection-divider"></div>
                        <div class="form-subsection form-subsection-checkboxes">
                            <label class="checkbox-label">
                                <input type="checkbox" name="ascensor_propiedad" id="form-ascensor" value="1">
                                <span>Ascensor</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="amueblado_propiedad" id="form-amueblado" value="1">
                                <span>Amueblado</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="piscina_propiedad" id="form-piscina" value="1">
                                <span>Piscina</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="terraza_propiedad" id="form-terraza" value="1">
                                <span>Terraza</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="garaje_propiedad" id="form-garaje" value="1">
                                <span>Garaje</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="aire_acondicionado_propiedad" id="form-aire-acondicionado" value="1">
                                <span>Aire acondicionado</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="calefaccion_propiedad" id="form-calefaccion" value="1">
                                <span>Calefacción</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="trastero_propiedad" id="form-trastero" value="1">
                                <span>Trastero</span>
                            </label>
                        </div>
                        <div class="form-subsection" style="margin-top: 10px;">
                            <label class="full-width">
                                <span>Adicional</span>
                                <input type="text" name="adicional_propiedad" id="form-adicional" value="" placeholder="Otras características...">
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
                            
                            <div id="contenedor-imagenes-existentes" class="full-width" hidden style="margin-top: 15px; margin-bottom: 15px;">
                                <p class="input-help"><strong>Imágenes actuales:</strong></p>
                                <div id="lista-imagenes-existentes" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-top: 10px;"></div>
                                <input type="hidden" name="eliminar_fotos" id="eliminar-fotos-input" value="" />
                            </div>

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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/arrendador/registrar_propiedad.js') }}"></script>
<script src="{{ asset('js/arrendador/propiedades.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var contenedor = document.getElementById('propiedad-editando-data');
        if (!contenedor || !window.abrirModalFormulario) {
            return;
        }

        try {
            var datosPropiedad = JSON.parse(contenedor.dataset.propiedad || 'null');
            var arrendadorId = contenedor.dataset.arrendadorId;
            if (datosPropiedad && arrendadorId) {
                abrirModalFormulario(arrendadorId, datosPropiedad);
            }
        } catch (error) {
            console.error(error);
        }
    });
</script>
@endsection