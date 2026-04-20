@extends('layouts.admin')
@section('titulo', 'Nueva propiedad — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/propiedades.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/propiedades-crear.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Nueva propiedad</h1>
        <p>Da de alta una propiedad desde administración</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="crear-wrap">
    <div class="toolbar-admin crear-toolbar">
        <div class="toolbar-izquierda">
            <div class="crear-hint">
                <strong>Alta manual de propiedad</strong>
                <span>Introduce los datos y asígnala a un arrendador existente.</span>
            </div>
        </div>
        <div class="toolbar-derecha">
            <a href="/admin/propiedades" class="btn-exportar">
                <i class="bi bi-arrow-left"></i>
                <span>Volver a propiedades</span>
            </a>
        </div>
    </div>

    <div class="kpi-grid-pequeno crear-kpis">
        <div class="kpi-mini">
            <div class="kpi-mini-icono kpi-mini-azul"><i class="bi bi-house-add"></i></div>
            <div class="kpi-mini-datos">
                <span class="kpi-mini-numero">Nueva alta</span>
                <span class="kpi-mini-label">Registro admin</span>
            </div>
        </div>
        <div class="kpi-mini">
            <div class="kpi-mini-icono kpi-mini-verde"><i class="bi bi-shield-check"></i></div>
            <div class="kpi-mini-datos">
                <span class="kpi-mini-numero">Validación</span>
                <span class="kpi-mini-label">Campos obligatorios</span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="error-lista">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card-admin card-crear">
        <div class="card-header-admin card-header-crear">
            <div class="card-header-title-crear">
                <i class="bi bi-house-add"></i>
                <h2>Formulario de propiedad</h2>
            </div>
            <span class="card-header-sub-crear">Alta manual</span>
        </div>
        <form action="/admin/propiedades/crear" method="POST" class="form-grid">
            @csrf

            <div class="campo-full">
                <label for="titulo">Título</label>
                <input id="titulo" name="titulo" type="text" value="{{ old('titulo') }}" required>
            </div>

            <div>
                <label for="calle">Calle</label>
                <input id="calle" name="calle" type="text" value="{{ old('calle') }}" required>
            </div>

            <div>
                <label for="numero">Número</label>
                <input id="numero" name="numero" type="text" value="{{ old('numero') }}" required>
            </div>

            <div>
                <label for="piso">Piso</label>
                <input id="piso" name="piso" type="text" value="{{ old('piso') }}">
            </div>

            <div>
                <label for="puerta">Puerta</label>
                <input id="puerta" name="puerta" type="text" value="{{ old('puerta') }}">
            </div>

            <div>
                <label for="ciudad">Ciudad</label>
                <input id="ciudad" name="ciudad" type="text" value="{{ old('ciudad') }}" required>
            </div>

            <div>
                <label for="codigo_postal">Código postal</label>
                <input id="codigo_postal" name="codigo_postal" type="text" value="{{ old('codigo_postal') }}" required>
            </div>

            <div>
                <label for="precio">Precio mensual</label>
                <input id="precio" name="precio" type="number" min="0" step="0.01" value="{{ old('precio') }}" required>
            </div>

            <div>
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    <option value="publicada" {{ old('estado') === 'publicada' ? 'selected' : '' }}>Publicada</option>
                    <option value="alquilada" {{ old('estado') === 'alquilada' ? 'selected' : '' }}>Alquilada</option>
                    <option value="borrador" {{ old('estado') === 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="inactiva" {{ old('estado') === 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                </select>
            </div>

            <div class="campo-full">
                <label for="arrendador_email">Email del arrendador</label>
                <input id="arrendador_email" name="arrendador_email" type="email" value="{{ old('arrendador_email') }}" required>
            </div>

            <div class="campo-full">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4">{{ old('descripcion') }}</textarea>
            </div>

            <div class="acciones-form campo-full">
                <a href="/admin/propiedades" class="btn-exportar">Cancelar</a>
                <button type="submit" class="btn-primario">
                    <i class="bi bi-check-lg"></i>
                    <span>Guardar propiedad</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
