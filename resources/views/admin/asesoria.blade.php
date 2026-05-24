@extends('layouts.admin')
@section('titulo', 'Asesoría Legal — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/asesoria.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/responsive-tablas.css') }}">
@endsection

@section('content')

<div class="hero-admin">
    <div class="hero-content">
        <h1>Asesoría Legal</h1>
        <p>Administra las categorías, artículos y preguntas frecuentes de la sección de asesoría</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="asesoria-admin-submenu">
    <a href="{{ route('admin.asesoria') }}" class="asesoria-admin-tab activo">
        <i class="bi bi-folder"></i> Categorías
    </a>
    <a href="{{ route('admin.asesoria.articulos') }}" class="asesoria-admin-tab">
        <i class="bi bi-file-text"></i> Artículos
    </a>
    <a href="#" class="asesoria-admin-tab">
        <i class="bi bi-question-circle"></i> Preguntas frecuentes
    </a>
</div>

<div class="card-admin">
    <div class="tabla-header">
        <div class="filtros-categorias">
            <span class="filtro-label">Filtrar por:</span>
            <input type="text" id="filtro-busqueda" placeholder="Nombre">
            <span class="filtro-label">Estado:</span>
            <select id="filtro-estado">
                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
            <span class="filtro-label">Número de resultados:</span>
            <select id="filtro-paginacion">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="0">Todos</option>
            </select>
            <button type="button" class="btn-limpiar-filtros" id="btn-limpiar-filtros">Limpiar</button>
        </div>
        <button type="button" class="btn-nuevo-recibo" onclick="abrirModalNuevaCategoria()">+ Nueva categoría</button>
    </div>
    <div class="tabla-body-wrap">
    <div class="table-responsive">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th data-sort="orden" class="sortable">Orden <span class="sort-arrow"></span></th>
                    <th data-sort="nombre" class="sortable">Nombre <span class="sort-arrow"></span></th>
                    <th data-sort="slug" class="sortable">Enlace <span class="info-tooltip" data-tooltip="Identificador único para la URL de esta categoría. Se genera automáticamente a partir del nombre.">i</span> <span class="sort-arrow"></span></th>
                    <th data-sort="articulos" class="sortable">Artículos <span class="sort-arrow"></span></th>
                    <th>Icono</th>
                    <th data-sort="estado" class="sortable">Estado <span class="sort-arrow"></span></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-categorias-body">
                <tr>
                    <td colspan="7" style="text-align: center; color: #999; padding: 20px;">Cargando...</td>
                </tr>
            </tbody>
        </table>
    </div>
    </div>
</div>
<div id="paginacion-categorias" class="paginacion"></div>

{{-- Modal Nueva Categoría --}}
<div id="modal-nueva-categoria" class="gestor-modal">
    <div class="gestor-modal-backdrop" onclick="cerrarModalNuevaCategoria()"></div>
    <div class="gestor-modal-content gestor-modal-content--med">
        <div class="gestor-modal-header">
            <h2 id="modal-categoria-titulo">Nueva categoría</h2>
            <button class="gestor-modal-close" onclick="cerrarModalNuevaCategoria()">&times;</button>
        </div>
        <div class="gestor-modal-body">
            <form class="property-form" data-ajax-form="true" data-create-url="{{ route('admin.asesoria.categoria.crear') }}" action="{{ route('admin.asesoria.categoria.crear') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-section">
                        <h3>Datos de la categoría</h3>
                        <div class="form-subsection">
                            <label>
                                <span>Nombre</span>
                                <input type="text" name="nombre" maxlength="255" placeholder="Ej: Obras y reformas" oninput="generarSlug()" required>
                            </label>
                            <label>
                                <span>
                                    Enlace
                                    <span class="info-tooltip" data-tooltip="Identificador único para la URL de esta categoría. Se genera automáticamente a partir del nombre.">i</span>
                                </span>
                                <input type="text" name="slug" readonly placeholder="Se genera automáticamente">
                            </label>
                            <label>
                                <span>Orden</span>
                                <select name="orden" required></select>
                            </label>
                            <div class="icono-selector-label">
                                <span>Icono</span>
                                <div class="icono-selector">
                                    <div class="icono-preview" id="icono-preview">
                                        <i class="bi bi-question-circle"></i>
                                    </div>
                                    <button type="button" class="btn-secundario" onclick="abrirSelectorIconos()">Seleccionar icono</button>
                                    <input type="hidden" name="icono" value="bi bi-question-circle">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mensaje-estado mensaje-error mensaje-error-js" style="display:none;"></div>
                <div class="modal-acciones">
                    <button type="button" class="btn-cancelar" onclick="cerrarModalNuevaCategoria()">Cancelar</button>
                    <button type="submit" id="modal-categoria-boton" class="btn-primary">Crear categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Selector de iconos --}}
<div id="modal-selector-iconos" class="gestor-modal">
    <div class="gestor-modal-backdrop" onclick="cerrarSelectorIconos()"></div>
    <div class="gestor-modal-content gestor-modal-content--sm">
        <div class="gestor-modal-header">
            <h2>Seleccionar icono</h2>
            <button class="gestor-modal-close" onclick="cerrarSelectorIconos()">&times;</button>
        </div>
        <div class="gestor-modal-body">
            <div class="icono-picker-grid" id="icono-picker-grid"></div>
            <div class="modal-acciones" style="margin-top:16px;">
                <button type="button" class="btn-cancelar" onclick="cerrarSelectorIconos()">Cancelar</button>
                <button type="button" class="btn-primary" onclick="guardarIconoSeleccionado()">Guardar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin/asesoria-categorias.js') }}"></script>
<script>
    var filtrarUrl = "{{ route('admin.asesoria.filtrar') }}";
    document.addEventListener('DOMContentLoaded', function () {
        poblarSelectOrden({{ $nextOrden }});
        cargarIconos();
        asignarEventosFiltros();
        asignarEventosPaginacion();
        var th = document.querySelector('th[data-sort="orden"]');
        if (th) {
            th.classList.add('active');
            var arrow = th.querySelector('.sort-arrow');
            if (arrow) arrow.textContent = '\u25B2';
        }
        filtrarCategorias();
    });
</script>
@endsection
