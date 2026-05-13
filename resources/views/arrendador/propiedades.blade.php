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
                        <th>Propiedades</th>
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
                            <td>
                                {{ $propiedad->total_incidencias ?? 'Sin incidencias' }}
                            </td>
                            @php
                                //Cálculo de las cuotas
                                $atrasadas = $propiedad->cuotas_atrasadas ?? 0;
                                $pendientes = $propiedad->cuotas_pendientes ?? 0;

                                if($atrasadas > 0) {
                                    $estado = 'atrasado';
                                    $label = 'Atrasado';
                                } elseif($pendientes > 0) {
                                    $estado = 'pendiente';
                                    $label = 'Pendiente';
                                } else {
                                    $estado = 'al-dia';
                                    $label = 'Al día';
                                }
                            @endphp
                            <td>
                                <span class="badge badge-{{ $estado }}">{{ $label}}</span>
                            </td>
                            <td>
                                <span class="gestor-nombre">{{ $propiedad->nombre_gestor ?? 'Sin gestor asignado' }}</span>
                                <button type="button" class="btn-gear" aria-label="Configurar gestor" data-propiedad-id="{{ $propiedad->id_propiedad }}" onclick="abrirModalGestor(this.dataset.propiedadId)">
                                    <i class="bi bi-gear"></i>
                                </button>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a class="action-link" href="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorId, 'editar' => $propiedad->id_propiedad]) }}">Editar</a>
                                    <button class="action-link" type="button" data-propiedad-id="{{ $propiedad->id_propiedad }}" data-arrendador-id="{{ $arrendadorId }}" onclick="abrirModalPropiedad(this.dataset.propiedadId, this.dataset.arrendadorId)">Previsualizar</button>
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

<!-- RUTA PARA OBTENER PERMISOS EN EL FETCH -->
 <script>
    const rutaPermisosGestor = "{{ route('arrendador.permisos.get', ':propiedad') }}";
 </script>

@if ($propiedadEditando)
    <div id="propiedad-editando-data"
         hidden
         data-arrendador-id="{{ $arrendadorId }}"
         data-propiedad='@json($propiedadEditando)'>
    </div>
@endif

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
                        <h3>Asignar gestor</h3>
                        <p class="gestor-panel-help">Selecciona quién administrará esta propiedad.</p>

                        <div class="gestor-selector-wrap">
                            <span class="gestor-selector-label">Gestor seleccionado</span>
                            <div class="gestor-selector-box" aria-live="polite">
                                <div class="gestor-avatar" id="gestor_avatar_inicial">-</div>
                                <div class="gestor-selector-info">
                                    <span class="gestor-selector-name" id="nombre_gestor">Sin gestor asignado</span>
                                    <span class="gestor-selector-email" id="email_gestor">Sin email disponible</span>
                                </div>
                                <i class="bi bi-chevron-down"></i>
                            </div>
                        </div>

                        <button type="button" class="btn-gestor-profile" id="btnVerPerfilGestor" disabled>Ver perfil del gestor</button>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
