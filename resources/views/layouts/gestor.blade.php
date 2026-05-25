<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <title>@yield('titulo', 'Gestor - SpotStay')</title>

    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    @yield('css')
</head>

<body>
    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-izq">
            <img src="{{ asset('img/logo.png') }}" alt="SpotStay Logo" class="topbar-logo-img">
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
            <button class="btn-nav-icon {{ request()->is('gestor/mensajes*') ? 'activo' : '' }}" data-ruta="/gestor/mensajes" title="Mensajes">
                <i class="bi bi-chat-dots"></i>
            </button>
            <button class="btn-nav-icon {{ request()->is('gestor/asesoria*') ? 'activo' : '' }}" data-ruta="/gestor/asesoria" title="Asesoría Legal">
                <i class="bi bi-bank2"></i>
            </button>
        </div>

        <div class="topbar-der">
            <div class="campana-wrapper">
                <button class="campana-container" type="button" id="campanaContainer" aria-label="Ver notificaciones" aria-expanded="false">
                    <i class="bi bi-bell icon-campana" id="iconCampana"></i>
                    @if(($notificacionesGestorSinLeer ?? 0) > 0)
                        <span class="badge-campana" id="badgeCampana">{{ $notificacionesGestorSinLeer }}</span>
                    @endif
                </button>
                <div class="campana-dropdown" id="campanaDropdown" aria-label="Notificaciones recientes">
                    <div class="campana-dropdown-header">
                        <div>
                            <span class="campana-dropdown-titulo">Notificaciones</span>
                            <p class="campana-dropdown-subtitulo">Últimos avisos del sistema</p>
                        </div>
                    </div>

                    <div class="campana-dropdown-lista">
                        @forelse($notificacionesGestor as $notificacion)
                            @php
                                $icono = $notificacion->icono_notificacion ?? 'bell';
                                $color = $notificacion->color_notificacion ?? '#035498';
                                $url = !empty($notificacion->url_notificacion) ? $notificacion->url_notificacion : '#';

                                if (is_string($url) && preg_match('#^/miembro/chat/(\d+)$#', $url, $coincidencias)) {
                                    $url = route('gestor.mensajes.index', ['activa' => (int) $coincidencias[1]]);
                                }
                            @endphp
                            <div class="campana-item-wrap">
                                <div class="campana-item {{ $notificacion->leida_notificacion ? '' : 'no-leida' }}" data-notif-id="{{ $notificacion->id_notificacion }}">
                                    <span class="campana-item-icono" style="background: {{ $color }};">
                                        <i class="bi bi-{{ $icono }}"></i>
                                    </span>
                                    <span class="campana-item-cuerpo">
                                        <span class="campana-item-titulo">{{ $notificacion->titulo_notificacion ?? 'Actualización' }}</span>
                                        <span class="campana-item-mensaje">{{ \Illuminate\Support\Str::limit($notificacion->mensaje_notificacion ?? '', 80) }}</span>
                                        <span class="campana-item-tiempo">{{ \Carbon\Carbon::parse($notificacion->creado_notificacion)->diffForHumans() }}</span>
                                    </span>
                                </div>
                                <button type="button" class="campana-item-borrar" data-notif-id="{{ $notificacion->id_notificacion }}" title="Borrar" aria-label="Borrar notificación">
                                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        @empty
                            <div class="campana-vacia">
                                No hay notificaciones recientes.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="admin-container" id="adminContainer">
                @if ($tieneFoto)
                    <img class="avatar-admin" src="{{ $fotoUsuario }}" alt="Foto de perfil">
                @else
                    <div class="avatar-admin">{{ $inicialUsuario }}</div>
                @endif
                <span class="admin-nombre">{{ $nombreUsuario ?? 'Gestor' }}</span>
                
                <i class="bi bi-chevron-down chevron-admin"></i>

                <div class="admin-dropdown" id="adminDropdown">
                    <a class="dropdown-item" href="{{ route('gestor.perfil') }}">Perfil</a>
                    <button class="dropdown-item dropdown-item-logout" id="btnLogout">Cerrar sesión</button>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="content-wrapper">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/admin/layout.js') }}"></script>
    <!-- SweetAlert2 + shared Oso helper -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/shared/swal-oso.js') }}"></script>
    @yield('scripts')
</body>

</html>