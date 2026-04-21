<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SpotStay | Gestionar Propiedades</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <!-- Reutilizamos el CSS de miembro para el Header y Nav -->
    <link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}" />
    <!-- Estilos específicos para la gestión de inquilino -->
    <link rel="stylesheet" href="{{ asset('css/inquilino/gestionar_propiedades.css') }}" />
</head>

<body class="pagina-miembro">

    <!-- HEADER IDÉNTICO A MIEMBRO -->
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
                        <a href="#" class="item-submenu"><i class="bi bi-person"></i> Mi Perfil</a>
                        <a href="#" class="item-submenu"><i class="bi bi-gear"></i> Configuración</a>
                        <div class="separador-submenu"></div>
                        <a href="{{ route('logout') }}" class="item-submenu" style="color: red;"><i class="bi bi-box-arrow-right" style="color: red"></i> Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- NAVBAR HORIZONTAL IDÉNTICO A MIEMBRO -->
    <nav class="navegacion-horizontal">
        <div class="contenedor-nav">
            <ul class="lista-nav">
                <li><a href="/miembro/inicio" class="enlace-nav"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li><a href="#" class="enlace-nav"><i class="bi bi-plus-circle"></i> Registra tus Propiedades</a></li>
                <li><a href="#" class="enlace-nav"><i class="bi bi-journal-text"></i> Alquileres</a></li>
                <li><a href="#" class="enlace-nav"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
                @if ($esInquilino)
                <li><a href="{{ route('gestionar_propiedades') }}" class="enlace-nav activo"><i class="bi bi-building-gear"></i> Gestionar</a></li>
                @endif
            </ul>
        </div>
    </nav>

    <!-- CONTENIDO PRINCIPAL ADAPTADO AL ESTILO MIEMBRO -->
    <main class="contenido-miembro">
        <section class="seccion-gestion-inquilino">
            <div class="cabecera-seccion">
                <h1 class="titulo-principal">Gestión de tus Propiedades</h1>
                <p class="descripcion-principal">Desde aquí puedes consultar el estado de tus alquileres activos, pagos e incidencias.</p>
            </div>

            <!-- KPI GRID (Dinamizado) -->
            <div class="kpi-grid-inquilino">
                <div class="kpi-card-inquilino">
                    <div class="kpi-icon-inquilino primario">
                        <i class="bi bi-key"></i>
                    </div>
                    <div class="kpi-datos-inquilino">
                        <span class="kpi-numero">{{ $totalContratos }}</span>
                        <span class="kpi-etiqueta">Contratos Activos</span>
                    </div>
                </div>
                <div class="kpi-card-inquilino">
                    <div class="kpi-icon-inquilino advertencia">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div class="kpi-datos-inquilino">
                        <span class="kpi-numero">{{ $totalIncidencias }}</span>
                        <span class="kpi-etiqueta">Incidencias En Proceso</span>
                    </div>
                </div>
            </div>

            <!-- LISTADO DE PROPIEDADES (Dinamizado y Filtrable) -->
            <div class="listado-propiedades-gestion">
                <div class="cabecera-listado-gestion">
                    <h2 class="titulo-listado">Mis Alquileres Actuales</h2>
                </div>

                <div class="filtros-gestion-container">
                    <div class="grupo-filtro">
                        <i class="bi bi-search"></i>
                        <input type="text" id="busqueda-nombre" placeholder="Buscar por nombre..." class="input-filtro">
                    </div>
                    <div class="custom-select-wrapper" id="custom-select-ciudad">
                        <div class="grupo-filtro select-trigger">
                            <i class="bi bi-geo-alt"></i>
                            <span class="selected-value">Todas las ciudades</span>
                            <i class="bi bi-chevron-down arrow-icon"></i>
                        </div>
                        <ul class="select-options-list">
                            <li data-value="" class="option-item selected">Todas las ciudades</li>
                            @foreach($ciudades as $ciudad)
                            <li data-value="{{ $ciudad }}" class="option-item">{{ $ciudad }}</li>
                            @endforeach
                        </ul>
                        <input type="hidden" id="filtro-ciudad-valor" value="">
                    </div>
                </div>

                <div class="grid-propiedades-gestion" id="contenedor-grid-propiedades">
                    @include('inquilino.partials.grid_propiedades')
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('js/miembro/miembro.js') }}"></script>
    <script src="{{ asset('js/inquilino/filtros_gestion.js') }}"></script>
</body>

</html>