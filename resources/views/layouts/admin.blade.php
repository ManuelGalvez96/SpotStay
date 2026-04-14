<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5.3.8 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-pi9qg5Dvprt5r+gZsxslCbWUUcc2/djiCCwYinnBJlcgkYR5LAWaxkulGLmQ40SP" crossorigin="anonymous">
    
    <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <title>@yield('titulo', 'Admin — SpotStay')</title>
    
    @yield('css')
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .topbar {
            height: 56px;
            background: white;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar-izq {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .topbar-logo {
            color: #035498;
            font-weight: 700;
            font-size: 16px;
        }
        .topbar-central {
            display: flex;
            gap: 4px;
        }
        .btn-nav-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            color: #8C93A0;
            font-size: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.2s ease;
        }
        .btn-nav-icon:hover {
            background: #F1F5F9;
            color: #111827;
        }
        .btn-nav-icon.activo {
            background: #EEF4FF;
            color: #035498;
        }
        .btn-nav-icon.activo::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #035498;
        }
        .topbar-der {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .campana-container {
            position: relative;
            cursor: pointer;
        }
        .icon-campana {
            font-size: 18px;
            color: #6B7280;
        }
        .badge-campana {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #EF4444;
            color: white;
            border-radius: 50%;
            font-size: 10px;
            padding: 1px 5px;
            font-weight: 600;
            min-width: 18px;
            text-align: center;
        }
        .avatar-admin {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #035498;
            color: white;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-nombre {
            font-size: 13px;
            color: #6B7280;
        }
        .chevron-admin {
            font-size: 12px;
            color: #9CA3AF;
        }
        .content-wrapper {
            background: #F0F4F8;
            min-height: calc(100vh - 56px);
        }
    </style>
</head>
<body>
    <!-- TOPBAR -->
    <div class="topbar">
        <!-- Zona izquierda -->
        <div class="topbar-izq">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 4C2.44772 4 2 4.44772 2 5V16C2 17.1046 2.89543 18 4 18H16C17.1046 18 18 17.1046 18 16V5C18 4.44772 17.5523 4 17 4H3Z" fill="#035498"/>
                <path d="M6 2V6M14 2V6" stroke="#035498" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="topbar-logo">SpotStay</span>
        </div>
        
        <!-- Zona central: botones nav -->
        <div class="topbar-central">
            <button class="btn-nav-icon activo" data-ruta="/admin/dashboard" title="Panel general">
                <i class="bi bi-grid"></i>
            </button>
            <button class="btn-nav-icon" data-ruta="/admin/usuarios" title="Usuarios">
                <i class="bi bi-people"></i>
            </button>
            <button class="btn-nav-icon" data-ruta="/admin/propiedades" title="Propiedades">
                <i class="bi bi-house"></i>
            </button>
            <button class="btn-nav-icon" data-ruta="/admin/alquileres" title="Alquileres">
                <i class="bi bi-file-text"></i>
            </button>
            <button class="btn-nav-icon" data-ruta="/admin/solicitudes" title="Solicitudes">
                <i class="bi bi-inbox"></i>
            </button>
            <button class="btn-nav-icon" data-ruta="/admin/incidencias" title="Incidencias">
                <i class="bi bi-exclamation-triangle"></i>
            </button>
            <button class="btn-nav-icon" data-ruta="/admin/suscripciones" title="Suscripciones">
                <i class="bi bi-credit-card"></i>
            </button>
            <button class="btn-nav-icon" data-ruta="/admin/configuracion" title="Configuración">
                <i class="bi bi-gear"></i>
            </button>
        </div>
        
        <!-- Zona derecha -->
        <div class="topbar-der">
            <div class="campana-container">
                <i class="bi bi-bell icon-campana" id="iconCampana"></i>
                <span class="badge-campana" id="badgeCampana">9</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <div class="avatar-admin">A</div>
                <span class="admin-nombre">Admin</span>
                <i class="bi bi-chevron-down chevron-admin"></i>
            </div>
        </div>
    </div>
    
    <!-- CONTENIDO -->
    <div class="content-wrapper">
        @yield('content')
    </div>
    
    <!-- Bootstrap 5.3.8 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55RPKM/DDL/M2PgkxjQlro0Pnd8NF" crossorigin="anonymous"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @yield('scripts')
</body>
</html>
