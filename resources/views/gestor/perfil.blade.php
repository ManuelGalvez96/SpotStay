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
    <div class="mensaje-estado mensaje-exito" data-flash-success="{{ session('success') }}"></div>
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
                        <img src="{{ $gestor->avatar_url }}" alt="Avatar">
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
                <div class="perfil-codigo-gestor">
                    <span class="codigo-gestor-label">Código de gestor:</span>
                    <input type="text" class="codigo-gestor-input" value="{{ $codigoGestor ?? '—' }}" readonly>
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

    <div class="perfil-info-lectura">
        Miembro desde {{ \Carbon\Carbon::parse($gestor->creado_usuario)->format('d/m/Y') }}
        · Última actualización: {{ \Carbon\Carbon::parse($gestor->actualizado_usuario)->format('d/m/Y') }}
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

    function mostrarError(el, msg) {
        el.textContent = msg;
    }

    function limpiarErrores() {
        document.querySelectorAll('.error-mensaje-cliente').forEach(function(el) {
            el.textContent = '';
        });
    }

    function crearError(input, name) {
        var span = document.createElement('span');
        span.className = 'error-mensaje error-mensaje-cliente';
        span.id = 'error-' + name;
        input.parentNode.appendChild(span);
        return span;
    }

    var forms = document.querySelectorAll('.perfil-form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            limpiarErrores();
            var valido = true;
            var inputs = form.querySelectorAll('input');
            inputs.forEach(function(inp) { inp.classList.remove('input-error'); });

            var nombre = form.querySelector('[name="nombre_usuario"]');
            if (nombre) {
                var err = document.getElementById('error-nombre_usuario') || crearError(nombre, 'nombre_usuario');
                if (!nombre.value.trim()) {
                    mostrarError(err, 'El nombre es obligatorio.');
                    nombre.classList.add('input-error');
                    valido = false;
                }
            }

            var email = form.querySelector('[name="email_usuario"]');
            if (email) {
                var err = document.getElementById('error-email_usuario') || crearError(email, 'email_usuario');
                if (!email.value.trim()) {
                    mostrarError(err, 'El correo electrónico es obligatorio.');
                    email.classList.add('input-error');
                    valido = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                    mostrarError(err, 'El correo electrónico no es válido.');
                    email.classList.add('input-error');
                    valido = false;
                }
            }

            var telefono = form.querySelector('[name="telefono_usuario"]');
            if (telefono) {
                var err = document.getElementById('error-telefono_usuario') || crearError(telefono, 'telefono_usuario');
                var telLimpio = telefono.value.trim().replace(/\s/g, '');
                if (telefono.value.trim() && !/^(\+\d{2})?\d{9}$/.test(telLimpio)) {
                    mostrarError(err, 'El teléfono debe tener 9 dígitos y opcionalmente el prefijo +34.');
                    telefono.classList.add('input-error');
                    valido = false;
                }
            }

            var contrasenaActual = form.querySelector('[name="contrasena_actual"]');
            if (contrasenaActual) {
                var err = document.getElementById('error-contrasena_actual') || crearError(contrasenaActual, 'contrasena_actual');
                if (!contrasenaActual.value.trim()) {
                    mostrarError(err, 'La contraseña actual es obligatoria.');
                    contrasenaActual.classList.add('input-error');
                    valido = false;
                }
            }

            var contrasenaNueva = form.querySelector('[name="contrasena_usuario"]');
            if (contrasenaNueva) {
                var err = document.getElementById('error-contrasena_usuario') || crearError(contrasenaNueva, 'contrasena_usuario');
                if (!contrasenaNueva.value.trim()) {
                    mostrarError(err, 'La nueva contraseña es obligatoria.');
                    contrasenaNueva.classList.add('input-error');
                    valido = false;
                } else if (contrasenaNueva.value.length < 6) {
                    mostrarError(err, 'La contraseña debe tener al menos 6 caracteres.');
                    contrasenaNueva.classList.add('input-error');
                    valido = false;
                } else if (!/[0-9]/.test(contrasenaNueva.value)) {
                    mostrarError(err, 'La contraseña debe contener al menos un número.');
                    contrasenaNueva.classList.add('input-error');
                    valido = false;
                }
            }

            var confirmacion = form.querySelector('[name="contrasena_usuario_confirmation"]');
            if (confirmacion && contrasenaNueva && contrasenaNueva.value !== confirmacion.value) {
                var err = document.getElementById('error-contrasena_usuario_confirmation') || crearError(confirmacion, 'contrasena_usuario_confirmation');
                mostrarError(err, 'Las contraseñas no coinciden.');
                confirmacion.classList.add('input-error');
                valido = false;
            }

            if (!valido) e.preventDefault();
        });
    });
});
</script>
@endsection
@endsection
