@extends('layouts.admin')
@section('titulo', 'Nuevo alquiler — SpotStay')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/alquileres.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/alquileres-crear.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <h1>Nuevo alquiler</h1>
    <p>Crea un alquiler desde administración</p>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="crear-wrap">
    <div class="toolbar-admin crear-toolbar">
        <div class="toolbar-izquierda">
            <div class="crear-hint">
                <strong>Formulario de alquiler</strong>
                <span>Selecciona propiedad, inquilino y condiciones iniciales.</span>
            </div>
        </div>
        <div class="toolbar-derecha">
            <a href="/admin/alquileres" class="btn-exportar">
                <i class="bi bi-arrow-left"></i>
                <span>Volver a alquileres</span>
            </a>
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
                <h2>Formulario de alquiler</h2>
            </div>
            <span class="card-header-sub-crear">Alta manual</span>
        </div>

        <form action="/admin/alquileres/crear" method="POST" class="form-grid">
            @csrf

            <div class="campo-full">
                <label for="id_propiedad">Propiedad</label>
                <select id="id_propiedad" name="id_propiedad" required>
                    <option value="">Selecciona una propiedad publicada...</option>
                    @foreach($propiedadesPublicadas as $propiedad)
                        <option value="{{ $propiedad->id_propiedad }}" data-precio="{{ $propiedad->precio_propiedad }}" {{ old('id_propiedad') == $propiedad->id_propiedad ? 'selected' : '' }}>
                            {{ $propiedad->titulo_propiedad }} - {{ $propiedad->ciudad_propiedad }} - €{{ number_format($propiedad->precio_propiedad, 2) }}/mes
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="campo-full">
                <label for="id_inquilino">Inquilino</label>
                <select id="id_inquilino" name="id_inquilino" required>
                    <option value="">Selecciona un inquilino...</option>
                    @foreach($inquilinos as $inquilino)
                        <option value="{{ $inquilino->id_usuario }}" {{ old('id_inquilino') == $inquilino->id_usuario ? 'selected' : '' }}>
                            {{ $inquilino->nombre_usuario }} - {{ $inquilino->email_usuario }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="fecha_inicio">Fecha de inicio</label>
                <input id="fecha_inicio" name="fecha_inicio" type="date" value="{{ old('fecha_inicio') }}" required>
            </div>

            <div>
                <label for="fecha_fin">Fecha de fin (opcional)</label>
                <input id="fecha_fin" name="fecha_fin" type="date" value="{{ old('fecha_fin') }}">
            </div>

            <div class="campo-full">
                <label for="precio">Precio mensual</label>
                <input id="precio" name="precio" type="number" min="0" step="0.01" value="{{ old('precio') }}" required>
                <small class="texto-ayuda">Puedes usar el precio de la propiedad o indicar uno distinto.</small>
            </div>

            <div class="acciones-form campo-full">
                <a href="/admin/alquileres" class="btn-exportar">Cancelar</a>
                <button type="submit" class="btn-primario">
                    <i class="bi bi-check-lg"></i>
                    <span>Crear alquiler</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/admin/alquileres-crear.js') }}"></script>
@endsection
