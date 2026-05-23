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
    </div>
    <div class="table-responsive">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Nombre</th>
                    <th>Enlace</th>
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

@endsection
