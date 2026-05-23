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
    <a href="#" class="asesoria-admin-tab">
        <i class="bi bi-file-text"></i> Artículos
    </a>
    <a href="#" class="asesoria-admin-tab">
        <i class="bi bi-question-circle"></i> Preguntas frecuentes
    </a>
</div>

<div class="card-admin">
    <div class="tabla-header">
        <span class="info-paginacion">{{ $categorias->count() }} categoría(s)</span>
        <button type="button" class="btn-nuevo-recibo" onclick="abrirModalNuevaCategoria()">+ Nueva categoría</button>
    </div>
    <div class="table-responsive">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Nombre</th>
                    <th>Enlace <span class="info-tooltip" data-tooltip="Identificador único para la URL de esta categoría. Se genera automáticamente a partir del nombre.">i</span></th>
                    <th>Icono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categorias as $categoria)
                    @php
                        $activo = $categoria->estado ? '1' : '0';
                        $estadoLabel = $categoria->estado ? 'Activo' : 'Inactivo';
                        $estadoClass = $categoria->estado ? 'activo' : 'inactivo';
                        $inactivaClass = $activo === '0' ? 'class="fila-inactiva"' : '';
                    @endphp
                    <tr data-id="{{ $categoria->id }}" data-activo="{{ $activo }}" {{ $inactivaClass }}>
                        <td data-label="ORDEN">{{ $categoria->orden }}</td>
                        <td data-label="NOMBRE">{{ $categoria->nombre }}</td>
                        <td data-label="ENLACE"><code>{{ $categoria->slug }}</code></td>
                        <td data-label="ICONO"><i class="bi {{ $categoria->icono }}"></i></td>
                        <td data-label="ESTADO"><span class="badge-estado badge-{{ $estadoClass }}">{{ $estadoLabel }}</span></td>
                        <td data-label="ACCIONES">
                            <div class="acciones-tabla">
                                <button class="btn-accion btn-editar" data-id="{{ $categoria->id }}" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-accion btn-eliminar" data-id="{{ $categoria->id }}" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <div class="toggle-switch {{ $activo === '1' ? 'activo' : '' }}" data-id="{{ $categoria->id }}">
                                    <div class="toggle-circulo"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #999; padding: 20px;">No hay categorías para mostrar</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Nueva Categoría --}}
<div id="modal-nueva-categoria" class="gestor-modal">
    <div class="gestor-modal-backdrop" onclick="cerrarModalNuevaCategoria()"></div>
    <div class="gestor-modal-content gestor-modal-content--med">
        <div class="gestor-modal-header">
            <h2>Nueva categoría</h2>
            <button class="gestor-modal-close" onclick="cerrarModalNuevaCategoria()">&times;</button>
        </div>
        <div class="gestor-modal-body">
            <form class="property-form" data-ajax-nueva-categoria="true">
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
                        </div>
                        <div class="form-subsection">
                            <label class="icono-selector-label">
                                <span>Icono</span>
                                <div class="icono-selector">
                                    <div class="icono-preview" id="icono-preview">
                                        <i class="bi bi-question-circle"></i>
                                    </div>
                                    <button type="button" class="btn-secundario" onclick="abrirSelectorIconos()">Seleccionar icono</button>
                                    <input type="hidden" name="icono" value="bi bi-question-circle">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mensaje-estado mensaje-error mensaje-error-js" style="display:none;"></div>
                <div class="modal-acciones">
                    <button type="button" class="btn-cancelar" onclick="cerrarModalNuevaCategoria()">Cancelar</button>
                    <button type="submit" class="btn-primary">Crear categoría</button>
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
    document.addEventListener('DOMContentLoaded', function () {
        poblarSelectOrden({{ $nextOrden }});
        cargarIconos();
    });
</script>
@endsection
