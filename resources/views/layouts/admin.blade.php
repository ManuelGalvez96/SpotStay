<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5.3.8 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <title>@yield('titulo', 'Admin — SpotStay')</title>
    
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    
    @yield('css')
</head>
<body>
    <!-- TOPBAR -->
    <div class="topbar">
        <!-- Zona izquierda -->
        <div class="topbar-izq">
            <img src="{{ asset('img/logo.png') }}" alt="SpotStay Logo" class="topbar-logo-img">
        </div>
        
        <!-- Zona central: botones nav -->
        <div class="topbar-central">
            <button class="btn-nav-icon {{ request()->is('admin/dashboard') ? 'activo' : '' }}" data-ruta="/admin/dashboard" title="Panel general">
                <i class="bi bi-grid"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('admin/usuarios*') ? 'activo' : '' }}" data-ruta="/admin/usuarios" title="Usuarios">
                <i class="bi bi-people"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('admin/propiedades*') ? 'activo' : '' }}" data-ruta="/admin/propiedades" title="Propiedades">
                <i class="bi bi-house"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('admin/alquileres*') ? 'activo' : '' }}" data-ruta="/admin/alquileres" title="Alquileres">
                <i class="bi bi-file-text"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('admin/solicitudes*') ? 'activo' : '' }}" data-ruta="/admin/solicitudes" title="Solicitudes">
                <i class="bi bi-inbox"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('admin/incidencias*') ? 'activo' : '' }}" data-ruta="/admin/incidencias" title="Incidencias">
                <i class="bi bi-exclamation-triangle"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('admin/suscripciones*') ? 'activo' : '' }}" data-ruta="/admin/suscripciones" title="Suscripciones">
                <i class="bi bi-credit-card"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('admin/configuracion*') ? 'activo' : '' }}" data-ruta="/admin/configuracion" title="Configuración">
                <i class="bi bi-gear"></i>
            </button>
        </div>
        
        <!-- Zona derecha -->
        <div class="topbar-der">
            <div class="campana-container">
                <i class="bi bi-bell icon-campana" id="iconCampana"></i>
                <span class="badge-campana" id="badgeCampana">9</span>
            </div>
            <div class="admin-container" id="adminContainer">
                <div class="avatar-admin">A</div>
                <span class="admin-nombre">Admin</span>
                <i class="bi bi-chevron-down chevron-admin"></i>
                
                <!-- DROPDOWN MENU -->
                <div class="admin-dropdown" id="adminDropdown">
                    <button class="dropdown-item">Perfil</button>
                    <button class="dropdown-item">Configuración</button>
                    <button class="dropdown-item dropdown-item-logout" id="btnLogout">Cerrar sesión</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- CONTENIDO -->
    <div class="content-wrapper">
        @yield('content')
    </div>
    
    <!-- Bootstrap 5.3.8 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="{{ asset('js/admin/layout.js') }}"></script>
    
    @yield('scripts')
</body>
</html>
