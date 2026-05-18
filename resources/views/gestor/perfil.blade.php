@extends('layouts.gestor')
@section('titulo', 'Mi perfil - SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gestor/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gestor/perfil.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Mi perfil</h1>
        <p>Gestiona tus datos personales y contraseña</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

@if(session('success'))
    <div class="mensaje-estado mensaje-exito" data-flash-success="{{ session('success') }}">{{ session('success') }}</div>
@endif

<div class="perfil-grid">
    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
            <span>Datos personales</span>
        </div>
        <form method="POST" action="{{ route('gestor.perfil.update') }}" enctype="multipart/form-data" class="perfil-form">
            @csrf

            <div class="perfil-avatar-section">
                <div class="perfil-avatar-preview">
                    @if($gestor->avatar_usuario)
                        <img src="{{ asset('storage/' . $gestor->avatar_usuario) }}" alt="Avatar">
                    @else
                        <div class="perfil-avatar-placeholder">{{ strtoupper(substr($gestor->nombre_usuario, 0, 1)) }}</div>
                    @endif
                </div>
                <div class="perfil-avatar-upload">
                    <label class="btn-avatar">Cambiar foto
                        <input type="file" name="avatar_usuario" id="avatar_usuario" accept="image/*" hidden>
                    </label>
                    <span class="avatar-filename" id="avatar-filename"></span>
                    <p class="perfil-avatar-hint">JPEG, PNG, WebP. Máx 2MB</p>
                    @error('avatar_usuario') <span class="error-mensaje">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="campo-grupo">
                <label for="nombre_usuario">Nombre completo</label>
                <input type="text" name="nombre_usuario" id="nombre_usuario" value="{{ old('nombre_usuario', $gestor->nombre_usuario) }}" required>
                @error('nombre_usuario') <span class="error-mensaje">{{ $message }}</span> @enderror
            </div>

            <div class="campo-grupo">
                <label for="email_usuario">Correo electrónico</label>
                <input type="email" name="email_usuario" id="email_usuario" value="{{ old('email_usuario', $gestor->email_usuario) }}" required>
                @error('email_usuario') <span class="error-mensaje">{{ $message }}</span> @enderror
            </div>

            <div class="campo-grupo">
                <label for="telefono_usuario">Teléfono</label>
                <input type="text" name="telefono_usuario" id="telefono_usuario" value="{{ old('telefono_usuario', $gestor->telefono_usuario) }}">
                @error('telefono_usuario') <span class="error-mensaje">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn-submit">Guardar cambios</button>
        </form>
    </div>

    <div class="card-admin card-con-franja">
        <div class="card-franja"></div>
        <div class="card-header-admin card-header-gradient">
            <span>Cambiar contraseña</span>
        </div>
        <form method="POST" action="{{ route('gestor.perfil.update') }}" class="perfil-form">
            @csrf

            <div class="campo-grupo">
                <label for="contrasena_actual">Contraseña actual</label>
                <input type="password" name="contrasena_actual" id="contrasena_actual">
                @error('contrasena_actual') <span class="error-mensaje">{{ $message }}</span> @enderror
            </div>

            <div class="campo-grupo">
                <label for="contrasena_usuario">Nueva contraseña</label>
                <input type="password" name="contrasena_usuario" id="contrasena_usuario">
                @error('contrasena_usuario') <span class="error-mensaje">{{ $message }}</span> @enderror
            </div>

            <div class="campo-grupo">
                <label for="contrasena_usuario_confirmation">Confirmar nueva contraseña</label>
                <input type="password" name="contrasena_usuario_confirmation" id="contrasena_usuario_confirmation">
            </div>

            <button type="submit" class="btn-submit">Actualizar contraseña</button>
        </form>
    </div>
</div>
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const flashSuccess = document.querySelector('[data-flash-success]');
    if (flashSuccess && flashSuccess.dataset.flashSuccess && window.swalSuccess) {
        swalSuccess('Éxito', flashSuccess.dataset.flashSuccess);
    }

    const fileInput = document.getElementById('avatar_usuario');
    const filenameSpan = document.getElementById('avatar-filename');
    const preview = document.querySelector('.perfil-avatar-preview');

    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            filenameSpan.textContent = this.files[0].name;

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Avatar">';
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            filenameSpan.textContent = '';
        }
    });
});
</script>
@endsection
@endsection
