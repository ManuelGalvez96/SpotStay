@extends('layouts.admin')

@section('titulo', 'Configuración — SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/configuracion.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Configuración general</h1>
        <p>Administra los ajustes globales del panel de administración</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="kpi-grid-pequeno">
    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-azul">
            <i class="bi bi-palette"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">4</span>
            <span class="kpi-mini-label">Bloques visuales</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde">
            <i class="bi bi-person-badge"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">2</span>
            <span class="kpi-mini-label">Ajustes de perfil</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-naranja">
            <i class="bi bi-bell"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-naranja">3</span>
            <span class="kpi-mini-label">Reglas de notificación</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-cpu"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo">1</span>
            <span class="kpi-mini-label">Tema activo</span>
        </div>
    </div>
</div>

<div class="card-admin">
    <div class="tabla-header">
        <span class="info-paginacion">Secciones disponibles de configuración</span>
    </div>

    <div class="configuracion-admin-body">
        <div class="configuracion-grid">
            <section class="configuracion-card-seccion">
                <div class="configuracion-cabecera-seccion">
                    <span class="configuracion-icono icono-azul"><i class="bi bi-palette"></i></span>
                    <h2>Apariencia</h2>
                </div>
                <ul>
                    <li>Temas claro y oscuro del panel.</li>
                    <li>Plantilla de color y estilo visual.</li>
                    <li>Componentes y contraste general.</li>
                </ul>
            </section>

            <section class="configuracion-card-seccion">
                <div class="configuracion-cabecera-seccion">
                    <span class="configuracion-icono icono-verde"><i class="bi bi-person-badge"></i></span>
                    <h2>Perfil</h2>
                </div>
                <ul>
                    <li>Datos del perfil administrativo.</li>
                    <li>Correo, contraseña y seguridad.</li>
                    <li>Preferencias de sesión y acceso.</li>
                </ul>
            </section>

            <section class="configuracion-card-seccion">
                <div class="configuracion-cabecera-seccion">
                    <span class="configuracion-icono icono-naranja"><i class="bi bi-bell"></i></span>
                    <h2>Notificaciones</h2>
                </div>
                <ul>
                    <li>Notificaciones predefinidas del sistema.</li>
                    <li>Alertas de incidencias y solicitudes.</li>
                    <li>Plantillas y avisos automáticos.</li>
                </ul>
            </section>

            <section class="configuracion-card-seccion">
                <div class="configuracion-cabecera-seccion">
                    <span class="configuracion-icono icono-rojo"><i class="bi bi-cpu"></i></span>
                    <h2>Sistema</h2>
                </div>
                <ul>
                    <li>Parámetros globales del panel.</li>
                    <li>Mantenimiento y estado técnico.</li>
                    <li>Reglas generales de funcionamiento.</li>
                </ul>
            </section>
        </div>
    </div>
</div>

<div class="card-admin" style="margin-top: 24px;">
    <div class="tabla-header">
        <span class="info-paginacion">Enviar notificación importante</span>
    </div>

    <div style="padding: 22px 24px;">
        <form method="POST" action="{{ route('admin.configuracion.notificaciones.crear') }}" class="row g-3">
            @csrf

            <div class="col-md-4">
                <label class="form-label">Destino</label>
                <select name="destino" class="form-select" required>
                    <option value="todos">Todos los usuarios</option>
                    <option value="rol">Por rol</option>
                    <option value="usuario">Usuario concreto</option>
                </select>
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
            </div>

            <div class="col-md-4">
                <label class="form-label">Usuario concreto</label>
                <select name="usuario_destino" class="form-select">
                    <option value="">Selecciona un usuario</option>
                    @foreach ($usuariosActivos as $usuario)
                        <option value="{{ $usuario->id_usuario }}">{{ $usuario->nombre_usuario }} — {{ $usuario->email_usuario }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Título</label>
                <input type="text" name="titulo_notificacion" class="form-control" maxlength="200" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Enlace opcional</label>
                <input type="text" name="url_notificacion" class="form-control" maxlength="500" placeholder="/admin/... o URL completa">
            </div>

            <div class="col-12">
                <label class="form-label">Mensaje</label>
                <textarea name="mensaje_notificacion" class="form-control" rows="4" maxlength="1000" required></textarea>
            </div>

            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Enviar notificación</button>
            </div>
        </form>
    </div>
</div>
@endsection