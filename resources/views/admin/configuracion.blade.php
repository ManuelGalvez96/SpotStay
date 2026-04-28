@extends('layouts.admin')

@section('titulo', 'Configuración — SpotStay')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/configuracion.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Configuración general</h1>
        <p>Ajustes visibles del panel administrativo organizados por secciones</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<div class="configuracion-admin">
    <div class="configuracion-resumen">
        <div class="configuracion-bloque">
            <small>Apariencia</small>
            <strong>Identidad visual</strong>
            <span>Colores, iconos, cabecera y coherencia visual del panel.</span>
        </div>
        <div class="configuracion-bloque">
            <small>Cuenta</small>
            <strong>Sesión y perfil</strong>
            <span>Datos del administrador, acceso y seguridad básica.</span>
        </div>
        <div class="configuracion-bloque">
            <small>Notificaciones</small>
            <strong>Alertas internas</strong>
            <span>Mensajes, avisos y preferencias de comunicación.</span>
        </div>
        <div class="configuracion-bloque">
            <small>Sistema</small>
            <strong>Estado general</strong>
            <span>Parámetros técnicos, mantenimiento y configuración base.</span>
        </div>
    </div>

    <div class="configuracion-grid">
        <section class="configuracion-card">
            <h2>Apariencia</h2>
            <p>Define cómo se ve el panel para mantener una experiencia consistente.</p>
            <div class="configuracion-lista">
                <div class="configuracion-item">
                    <h3>Paleta principal</h3>
                    <span>Colores corporativos, contraste y estados visuales del admin.</span>
                </div>
                <div class="configuracion-item">
                    <h3>Cabecera y navegación</h3>
                    <span>Estado activo de iconos, fondos y jerarquía de navegación.</span>
                </div>
            </div>
        </section>

        <section class="configuracion-card">
            <h2>Cuenta</h2>
            <p>Centraliza los ajustes personales y de seguridad del administrador.</p>
            <div class="configuracion-lista">
                <div class="configuracion-item">
                    <h3>Perfil</h3>
                    <span>Nombre, correo, avatar y estado del usuario administrador.</span>
                </div>
                <div class="configuracion-item">
                    <h3>Acceso</h3>
                    <span>Control de sesión y cierre seguro de la cuenta activa.</span>
                </div>
            </div>
        </section>

        <section class="configuracion-card">
            <h2>Notificaciones</h2>
            <p>Agrupa avisos operativos y mensajes que requieren revisión.</p>
            <div class="configuracion-lista">
                <div class="configuracion-item">
                    <h3>Alertas del sistema</h3>
                    <span>Notificaciones de solicitudes, incidencias y eventos críticos.</span>
                </div>
                <div class="configuracion-item">
                    <h3>Alertas visuales</h3>
                    <span>Uso del oso, modales y mensajes coherentes con el panel.</span>
                </div>
            </div>
        </section>

        <section class="configuracion-card">
            <h2>Sistema</h2>
            <p>Describe ajustes técnicos y de mantenimiento de la plataforma.</p>
            <div class="configuracion-lista">
                <div class="configuracion-item">
                    <h3>Estado general</h3>
                    <span>Control de versión, mantenimiento y parámetros base.</span>
                </div>
                <div class="configuracion-item">
                    <h3>Operativa interna</h3>
                    <span>Configuración de procesos y reglas administrativas globales.</span>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection