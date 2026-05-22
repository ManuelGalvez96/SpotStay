<header class="encabezado-miembro" id="encabezado-miembro">
    <div class="contenedor-encabezado-miembro">
        <div class="marca-miembro">
            <img src="/img/logo.png" alt="SpotStay Logo" />
        </div>

        <div class="acciones-miembro">
            <button class="boton-icono" type="button" aria-label="Notificaciones">
                <i class="bi bi-bell" aria-hidden="true"></i>
            </button>
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