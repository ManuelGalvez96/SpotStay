<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-pi9qg5Dvprt5r+gZsxslCbWUUcc2/djiCCwYinnBJlcgkYR5LAWaxkulGLmQ40SP" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <title>@yield('titulo', 'Gestor - SpotStay')</title>

    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    @yield('css')
</head>
<body>
    <div class="topbar">
        <div class="topbar-izq">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 4C2.44772 4 2 4.44772 2 5V16C2 17.1046 2.89543 18 4 18H16C17.1046 18 18 17.1046 18 16V5C18 4.44772 17.5523 4 17 4H3Z" fill="#035498"/>
                <path d="M6 2V6M14 2V6" stroke="#035498" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="topbar-logo">SpotStay</span>
        </div>

        <div class="topbar-central">
            <button class="btn-nav-icon {{ request()->is('gestor/dashboard') ? 'activo' : '' }}" data-ruta="/gestor/dashboard" title="Panel gestor">
                <i class="bi bi-grid"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('gestor/incidencias*') ? 'activo' : '' }}" data-ruta="/gestor/incidencias" title="Incidencias">
                <i class="bi bi-exclamation-triangle"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('gestor/propiedades*') ? 'activo' : '' }}" data-ruta="/gestor/propiedades" title="Propiedades asignadas">
                <i class="bi bi-house"></i>
            </button>
        </div>

        <div class="topbar-der">
            <div class="campana-container">
                <i class="bi bi-bell icon-campana" id="iconCampana"></i>
                <span class="badge-campana" id="badgeCampana">3</span>
            </div>
            <div class="admin-container" id="adminContainer">
                <div class="avatar-admin">G</div>
                <span class="admin-nombre">Gestor</span>
                <i class="bi bi-chevron-down chevron-admin"></i>

                <div class="admin-dropdown" id="adminDropdown">
                    <button class="dropdown-item">Perfil</button>
                    <button class="dropdown-item dropdown-item-logout" id="btnLogout">Cerrar sesión</button>
                </div>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55RPKM/DDL/M2PgkxjQlro0Pnd8NF" crossorigin="anonymous"></script>
    <script src="{{ asset('js/admin/layout.js') }}"></script>
    @yield('scripts')
</body>
</html>
