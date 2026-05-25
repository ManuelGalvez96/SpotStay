@extends('layouts.admin')

@section('titulo', 'Notificaciones — SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/configuracion.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('js/admin/configuracion.js') }}"></script>
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Notificaciones</h1>
        <p>Gestiona y envía notificaciones a los usuarios de la plataforma</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="card-admin" style="margin-top: 24px;">
    <div class="tabla-header">
        <span class="info-paginacion">Enviar notificación importante</span>
    </div>

    <div style="padding: 22px 24px;">
        <form method="POST" action="{{ route('admin.configuracion.notificaciones.crear') }}" class="row g-3" id="form-notificacion-admin">
            @csrf

            <div class="col-md-4">
                <label class="form-label">Destino</label>
                <select name="destino" class="form-select">
                    <option value="todos">Todos los usuarios</option>
                    <option value="rol">Por rol</option>
                    <option value="usuario">Usuario concreto</option>
                </select>
                <small class="error-mensaje" id="errorDestinoNotificacion"></small>
            </div>

            <div class="col-md-4">
                <label class="form-label">Rol de destino</label>
                <select name="rol_destino" class="form-select">
                    <option value="">Selecciona un rol</option>
                    <option value="miembro">Miembro</option>
                    <option value="inquilino">Inquilino</option>
                    <option value="arrendador">Arrendador</option>
                    <option value="gestor">Gestor</option>
                </select>
                <small class="error-mensaje" id="errorRolNotificacion"></small>
            </div>

            <div class="col-md-4">
                <label class="form-label">Usuario concreto</label>
                <select name="usuario_destino" class="form-select">
                    <option value="">Selecciona un usuario</option>
                    @foreach ($usuariosActivos as $usuario)
                        <option value="{{ $usuario->id_usuario }}">{{ $usuario->nombre_usuario }} — {{ $usuario->email_usuario }}</option>
                    @endforeach
                </select>
                <small class="error-mensaje" id="errorUsuarioNotificacion"></small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Título</label>
                <input type="text" name="titulo_notificacion" class="form-control" maxlength="200">
                <small class="error-mensaje" id="errorTituloNotificacion"></small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Enlace opcional</label>
                <input type="text" name="url_notificacion" class="form-control" maxlength="500" placeholder="/admin/... o URL completa">
            </div>

            <div class="col-12">
                <label class="form-label">Mensaje</label>
                <textarea name="mensaje_notificacion" class="form-control" rows="4" maxlength="1000"></textarea>
                <small class="error-mensaje" id="errorMensajeNotificacion"></small>
            </div>

            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Enviar notificación</button>
            </div>
        </form>
    </div>
</div>
@endsection