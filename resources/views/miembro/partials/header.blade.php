<header class="encabezado-miembro" id="encabezado-miembro">
    <div class="contenedor-encabezado-miembro">
        <div class="marca-miembro">
            <img src="/img/logo.png" alt="SpotStay Logo" />
        </div>

        <div class="acciones-miembro">
            <div class="campana-wrapper">
                <button class="campana-container" type="button" id="campanaContainer" aria-label="Ver notificaciones" aria-expanded="false">
                    <i class="bi bi-bell icon-campana" id="iconCampana" aria-hidden="true"></i>
                    @if(($notificacionesUsuarioSinLeer ?? 0) > 0)
                        <span class="badge-campana" id="badgeCampana">{{ $notificacionesUsuarioSinLeer }}</span>
                    @endif
                </button>

                <div class="campana-dropdown" id="campanaDropdown" aria-label="Notificaciones recientes">
                    <div class="campana-dropdown-header">
                        <div>
                            <span class="campana-dropdown-titulo">Notificaciones</span>
                            <p class="campana-dropdown-subtitulo">Últimos avisos del sistema</p>
                        </div>
                        <a href="{{ url('/notificaciones') }}" class="campana-dropdown-ver-todo">Ver todo</a>
                    </div>

                    <div class="campana-dropdown-lista">
                        @forelse($notificacionesUsuario as $notificacion)
                            @php
                                $icono = $notificacion->icono_notificacion ?? 'bell';
                                $color = $notificacion->color_notificacion ?? '#035498';
                                $url = !empty($notificacion->url_notificacion) ? $notificacion->url_notificacion : route('miembro.actividad');
                            @endphp
                            <div class="campana-item-wrap">
                                <a href="{{ $url }}" class="campana-item {{ $notificacion->leida_notificacion ? '' : 'no-leida' }}" data-notif-id="{{ $notificacion->id_notificacion }}">
                                    <span class="campana-item-icono" style="background: {{ $color }};">
                                        <i class="bi bi-{{ $icono }}"></i>
                                    </span>
                                    <span class="campana-item-cuerpo">
                                        <span class="campana-item-titulo">{{ $notificacion->titulo_notificacion ?? 'Actualización' }}</span>
                                        <span class="campana-item-mensaje">{{ \Illuminate\Support\Str::limit($notificacion->mensaje_notificacion ?? '', 80) }}</span>
                                        <span class="campana-item-tiempo">{{ \Carbon\Carbon::parse($notificacion->creado_notificacion)->diffForHumans() }}</span>
                                    </span>
                                </a>
                                <button type="button" class="campana-item-borrar" data-notif-id="{{ $notificacion->id_notificacion }}" title="Borrar" aria-label="Borrar notificación">
                                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                                </button>
                            </div>
                        @empty
                            <div class="campana-vacia">No hay notificaciones recientes.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="perfil-miembro" id="boton-perfil">
                <span class="nombre-miembro">{{ $nombreUsuario }}</span>
                @if ($tieneFoto)
                <img class="foto-perfil" src="{{ $fotoUsuario }}" alt="Foto de perfil" />
                @else
                <div class="inicial-perfil" aria-hidden="true">{{ $inicialUsuario }}</div>
                @endif
                <div class="submenu-perfil" id="submenu-perfil">
                    <a href="{{ route('miembro.perfil.show', ['id' => auth()->user()->id_usuario]) }}" class="item-submenu"><i class="bi bi-person"></i> Mi Perfil</a>
                    

                    <div class="separador-submenu"></div>
                    <a href="{{ route('logout') }}" class="item-submenu" style="color: red;"><i class="bi bi-box-arrow-right" style="color: red"></i> Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>
</header>