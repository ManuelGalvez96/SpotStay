<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Gestor - SpotStay')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/gestor/dashboard.css') }}">
    @yield('css')
</head>
<body>
    <header class="topbar-gestor">
        <div class="brand-wrap">
            <span class="brand-dot"></span>
            <div>
                <p class="brand-title">SpotStay</p>
                <p class="brand-sub">Gestor de Propiedades</p>
            </div>
        </div>

        <nav class="gestor-nav">
            <a href="{{ url('/gestor/dashboard') }}" class="item-nav {{ request()->is('gestor/dashboard') ? 'activo' : '' }}">Dashboard</a>
            <a href="{{ url('/admin/incidencias') }}" class="item-nav">Incidencias</a>
            <a href="{{ url('/admin/propiedades') }}" class="item-nav">Propiedades</a>
        </nav>

        <div class="topbar-actions">
            <button class="icon-btn" type="button" aria-label="Notificaciones">
                <i class="bi bi-bell"></i>
            </button>
            <div class="avatar-gestor">GP</div>
        </div>
    </header>

    <main class="contenedor-gestor">
        @yield('content')
    </main>

    <script src="{{ asset('js/gestor/dashboard.js') }}"></script>
    @yield('scripts')
</body>
</html>
