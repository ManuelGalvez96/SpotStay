@extends('layouts.admin')
@section('titulo', 'Artículos — Asesoría Legal — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/asesoria.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/responsive-tablas.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.9/tinymce.min.js"></script>
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
    <a href="{{ route('admin.asesoria') }}" class="asesoria-admin-tab">
        <i class="bi bi-folder"></i> Categorías
    </a>
    <a href="{{ route('admin.asesoria.articulos') }}" class="asesoria-admin-tab activo">
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
            <span class="filtro-label">Categoría:</span>
            <select id="filtro-categoria">
                <option value="">Todas</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                @endforeach
            </select>
            <input type="text" id="filtro-busqueda" placeholder="Título">
            <span class="filtro-label">Estado:</span>
            <select id="filtro-estado">
                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
            <span class="filtro-label">Destacado:</span>
            <select id="filtro-destacado">
                <option value="">Todos</option>
                <option value="1">Solo destacados</option>
                <option value="0">No destacados</option>
            </select>
            <span class="filtro-label">Núm. resultados:</span>
            <select id="filtro-paginacion">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="0">Todos</option>
            </select>
            <button type="button" class="btn-limpiar-filtros" id="btn-limpiar-filtros">Limpiar</button>
        </div>
        <button type="button" class="btn-nuevo-recibo" onclick="abrirModalNuevoArticulo()">+ Nuevo artículo</button>
    </div>
    <div class="tabla-body-wrap">
    <div class="table-responsive">
        <table class="tabla-admin" id="tabla-articulos-admin">
            <thead>
                <tr>
                    <th data-sort="categoria" class="sortable">Categoría <span class="sort-arrow"></span></th>
                    <th>Orden <span class="info-tooltip" data-tooltip="El orden que tienen los artículos dentro de sus categorías">i</span></th>
                    <th data-sort="titulo" class="sortable">Título <span class="sort-arrow"></span></th>
                    <th>Contenido</th>
                    <th data-sort="estado" class="sortable">Estado <span class="sort-arrow"></span></th>
                    <th data-sort="destacado" class="sortable">Destacado <span class="info-tooltip" data-tooltip="Los artículos destacados aparecen en Preguntas frecuentes">i</span> <span class="sort-arrow"></span></th>
                    <th>Orden Destacado <span class="info-tooltip" data-tooltip="Orden que tienen los artículos en el panel de Preguntas frecuentes">i</span></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-articulos-body">
                <tr>
                    <td colspan="8" style="text-align: center; color: #999; padding: 20px;">Cargando...</td>
                </tr>
            </tbody>
        </table>
    </div>
    </div>
</div>
<div id="paginacion-articulos" class="paginacion"></div>

{{-- Modal Nuevo Artículo --}}
<div id="modal-nuevo-articulo" class="gestor-modal">
    <div class="gestor-modal-backdrop" onclick="cerrarModalNuevoArticulo()"></div>
    <div class="gestor-modal-content">
        <div class="gestor-modal-header">
            <h2 id="modal-articulo-titulo">Nuevo artículo</h2>
            <button class="gestor-modal-close" onclick="cerrarModalNuevoArticulo()">&times;</button>
        </div>
        <div class="gestor-modal-body">
            <form class="property-form" data-ajax-form-articulo="true" data-create-url="{{ route('admin.asesoria.articulos.crear') }}" action="{{ route('admin.asesoria.articulos.crear') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-section">
                        <h3>Datos del artículo</h3>
                        <div class="form-subsection">
                            <label>
                                <span>Título</span>
                                <input type="text" name="titulo" maxlength="255" placeholder="Ej: ¿Qué hacer ante un impago?" oninput="generarSlugArticulo()" required>
                            </label>
                            <label>
                                <span>
                                    Enlace
                                    <span class="info-tooltip" data-tooltip="Identificador único para la URL de este artículo. Se genera automáticamente a partir del título.">i</span>
                                </span>
                                <input type="text" name="slug" readonly placeholder="Se genera automáticamente">
                            </label>
                            <label>
                                <span>Categoría</span>
                                <select name="id_categoria_fk" required>
                                    @foreach($categorias as $i => $cat)
                                        <option value="{{ $cat->id }}" {{ $i === 0 ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                <span>Orden</span>
                                <select name="orden" required></select>
                            </label>
                        </div>
                    </div>
                    <div class="form-section">
                        <h3>Contenido</h3>
                        <textarea name="contenido" id="contenido-articulo" rows="12" placeholder="Escribe aquí el contenido del artículo..."></textarea>
                    </div>
                    <div class="form-section">
                        <h3>Opciones</h3>
                        <div class="form-subsection">
                            <label style="flex-direction:row;align-items:center;gap:8px;">
                                <input type="checkbox" name="destacado" value="1">
                                <span style="margin:0;font-weight:500;font-size:14px;color:#333;">Marcar como artículo destacado</span>
                            </label>
                            <label>
                                <span>Orden en FAQ</span>
                                <input type="number" name="orden_faq" min="1" placeholder="Vacío si no es FAQ">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mensaje-estado mensaje-error mensaje-error-js" style="display:none;"></div>
                <div class="modal-acciones">
                    <button type="button" class="btn-cancelar" onclick="cerrarModalNuevoArticulo()">Cancelar</button>
                    <button type="submit" id="modal-articulo-boton" class="btn-primary">Crear artículo</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin/asesoria-articulos.js') }}"></script>
<script>
    var filtrarUrl = "{{ route('admin.asesoria.articulos.filtrar') }}";
    document.addEventListener('DOMContentLoaded', function () {
        asignarEventosFiltrosArticulos();
        actualizarOrdenPorCategoria();
        asignarEventosPaginacionArticulos();
        var th = document.querySelector('th[data-sort="categoria"]');
        if (th) {
            th.classList.add('active');
            var arrow = th.querySelector('.sort-arrow');
            if (arrow) arrow.textContent = '\u25B2';
        }
        filtrarArticulos();
    });
</script>
@endsection
