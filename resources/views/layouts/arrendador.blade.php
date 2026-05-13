<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-pi9qg5Dvprt5r+gZsxslCbWUUcc2/djiCCwYinnBJlcgkYR5LAWaxkulGLmQ40SP" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <title>@yield('titulo', 'Arrendador - SpotStay')</title>

    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    @yield('css')
</head>
<body>
@php
    $arrendadorIdNav = $arrendadorId ?? request('arrendador_id');
@endphp

    <div class="topbar">
        <div class="topbar-izq">
            <img src="{{ asset('img/logo.png') }}" alt="SpotStay Logo" class="topbar-logo-img">
        </div>

        <div class="topbar-central">
            <button class="btn-nav-icon {{ request()->is('arrendador/dashboard') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorIdNav]) }}" title="Panel arrendador">
                <i class="bi bi-grid"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('arrendador/propiedades*') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.propiedades', ['arrendador_id' => $arrendadorIdNav]) }}" title="Propiedades">
                <i class="bi bi-house"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('arrendador/solicitudes*') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.solicitudes', ['arrendador_id' => $arrendadorIdNav]) }}" title="Solicitudes">
                <i class="bi bi-inbox"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('arrendador/inquilinos*') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.inquilinos', ['arrendador_id' => $arrendadorIdNav]) }}" title="Inquilinos">
                <i class="bi bi-people"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('arrendador/contratos*') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.contratos', ['arrendador_id' => $arrendadorIdNav]) }}" title="Contratos">
                <i class="bi bi-file-text"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('arrendador/incidencias*') ? 'activo' : '' }}" data-ruta="{{ route('arrendador.incidencias', ['arrendador_id' => $arrendadorIdNav]) }}" title="Incidencias">
                <i class="bi bi-exclamation-triangle"></i>
            </button>
        </div>

        <div class="topbar-der">
            <div class="admin-container" id="adminContainer">
                <div class="avatar-admin">{{ strtoupper(substr(trim($__env->yieldContent('avatar', 'A')), 0, 1)) }}</div>
                <span class="admin-nombre">Arrendador</span>
                <i class="bi bi-chevron-down chevron-admin"></i>

                <div class="admin-dropdown" id="adminDropdown">
                    <a class="dropdown-item" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorIdNav]) }}">Panel</a>
                    <button class="dropdown-item dropdown-item-logout" id="btnLogout">Cerrar sesion</button>
                </div>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55RPKM/DDL/M2PgkxjQlro0Pnd8NF" crossorigin="anonymous"></script>
    <script src="{{ asset('js/admin/layout.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/shared/swal-oso.js') }}"></script>

    @yield('scripts')
</body>
</html>
