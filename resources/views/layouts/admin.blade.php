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
<body class="admin">
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
            <button class="btn-nav-icon {{ request()->is('admin/planes*') ? 'activo' : '' }}" data-ruta="/admin/planes" title="Planes">
                <i class="bi bi-card-list"></i>
            </button>
            {{-- <button class="btn-nav-icon {{ request()->is('admin/suscripciones*') ? 'activo' : '' }}" data-ruta="/admin/suscripciones" title="Suscripciones">
                <i class="bi bi-credit-card"></i>
            </button> --}}
            <button class="btn-nav-icon {{ request()->is('admin/configuracion*') ? 'activo' : '' }}" data-ruta="/admin/configuracion" title="Notificaciones">
                <i class="bi bi-bell"></i>
            </button>
        </div>
        
        <!-- Zona derecha -->
        <div class="topbar-der">
                <div class="perfil-miembro admin-container" id="adminContainer">
                    <div class="avatar-admin">A</div>
                    <span class="admin-nombre">Admin</span>
                    <i class="bi bi-chevron-down chevron-admin"></i>

                    <!-- DROPDOWN MENU (estilo miembro) -->
                    <div class="submenu-perfil admin-dropdown" id="adminDropdown">
                        <a href="#" class="item-submenu cerrar-sesion dropdown-item-logout" id="btnLogout">
                            <i class="bi bi-box-arrow-right" style="color: #EF4444"></i>
                            Cerrar sesión
                        </a>
                    </div>
                </div>
        </div>
    </div>
    
    <!-- CONTENIDO -->
    <div class="content-wrapper">
        @yield('content')
    </div>

    <!-- MODAL ALERTA ADMIN -->
    <div class="modal fade" id="modalAlertaAdmin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="modalAlertaTituloAdmin">Aviso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center pt-2">
                    <div id="modalAlertaIconoAdmin" class="oso-icon d-flex justify-content-center mb-3"></div>
                    <p id="modalAlertaMensajeAdmin" class="mb-0 text-muted"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0">
                    <button type="button" class="btn btn-primary" id="modalAlertaBotonAdmin" data-bs-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- MODAL CONFIRMACIÓN ADMIN -->
    <div class="modal fade" id="modalConfirmAdmin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="modalConfirmTituloAdmin">Confirmar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center pt-2">
                    <p id="modalConfirmMensajeAdmin" class="mb-0 text-muted"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="modalConfirmCancelarAdmin">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="modalConfirmBotonConfirmarAdmin">Confirmar</button>
                </div>
            </div>
        </div>
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
