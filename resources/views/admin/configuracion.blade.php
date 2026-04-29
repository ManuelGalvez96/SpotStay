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
            <span class="kpi-mini-label">Bloques de apariencia</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-verde">
            <i class="bi bi-person-badge"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero">3</span>
            <span class="kpi-mini-label">Ajustes de cuenta</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-naranja">
            <i class="bi bi-bell"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-naranja">5</span>
            <span class="kpi-mini-label">Reglas de notificación</span>
        </div>
    </div>

    <div class="kpi-mini">
        <div class="kpi-mini-icono kpi-mini-rojo">
            <i class="bi bi-cpu"></i>
        </div>
        <div class="kpi-mini-datos">
            <span class="kpi-mini-numero kpi-mini-numero-rojo">6</span>
            <span class="kpi-mini-label">Parámetros de sistema</span>
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
                    <li>Colores y estilo visual del panel.</li>
                    <li>Comportamiento visual de cabecera y navegación.</li>
                    <li>Consistencia de componentes de interfaz.</li>
                </ul>
            </section>

            <section class="configuracion-card-seccion">
                <div class="configuracion-cabecera-seccion">
                    <span class="configuracion-icono icono-verde"><i class="bi bi-person-badge"></i></span>
                    <h2>Cuenta</h2>
                </div>
                <ul>
                    <li>Información del perfil administrativo.</li>
                    <li>Seguridad básica de acceso y sesión.</li>
                    <li>Preferencias operativas de la cuenta.</li>
                </ul>
            </section>

            <section class="configuracion-card-seccion">
                <div class="configuracion-cabecera-seccion">
                    <span class="configuracion-icono icono-naranja"><i class="bi bi-bell"></i></span>
                    <h2>Notificaciones</h2>
                </div>
                <ul>
                    <li>Alertas internas de solicitudes e incidencias.</li>
                    <li>Canales y frecuencia de avisos.</li>
                    <li>Prioridades y umbrales de comunicación.</li>
                </ul>
            </section>

            <section class="configuracion-card-seccion">
                <div class="configuracion-cabecera-seccion">
                    <span class="configuracion-icono icono-rojo"><i class="bi bi-cpu"></i></span>
                    <h2>Sistema</h2>
                </div>
                <ul>
                    <li>Parámetros generales de operación.</li>
                    <li>Mantenimiento y estado del entorno.</li>
                    <li>Reglas globales del panel administrativo.</li>
                </ul>
            </section>
        </div>
    </div>
</div>
@endsection