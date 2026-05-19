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

        <div class="configuracion-accion-planes">
            <a href="{{ route('admin.planes') }}" class="btn-guardar-plan btn-ver-planes">
                Ver y editar planes
            </a>
        </div>
    </div>
</div>
@endsection