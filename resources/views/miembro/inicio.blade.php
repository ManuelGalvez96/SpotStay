<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SpotStay | Inicio miembro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}" />
</head>

<body class="pagina-miembro">


    <header class="encabezado-miembro" id="encabezado-miembro">
        <div class="contenedor-encabezado-miembro">
            <div class="marca-miembro">
                <img src="/img/logo.png" />
            </div>

            @if(session('error'))
            <div class="header-error-msg">
                <div class="alert alert-error">
                    {!! session('error') !!}
                </div>
            </div>
            @endif

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
    <nav class="navegacion-horizontal">
        <div class="contenedor-nav">
            <ul class="lista-nav">
                <li><a href="#" class="enlace-nav activo"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li><a href="#" class="enlace-nav"><i class="bi bi-plus-circle"></i> Registra tus Propiedades</a></li>
                <li><a href="#" class="enlace-nav"><i class="bi bi-journal-text"></i> Alquileres</a></li>
                <li><a href="#" class="enlace-nav"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
                @if ($esInquilino)
                <li><a href="{{ route('gestionar_propiedades') }}" class="enlace-nav"><i class="bi bi-building-gear"></i> Gestionar</a></li>
                @endif
            </ul>
        </div>
    </nav>
    <main class="contenido-miembro">
        <section class="seccion-mapa-previsualizacion">
            <div class="cabecera-seccion">
                <h1 class="titulo-principal">Explora propiedades destacadas</h1>
                <p class="descripcion-principal">Visualiza el mapa y ajusta los filtros para encontrar tu proximo hogar.</p>
            </div>
            <div class="mapa-previsualizacion">Mapa</div>
        </section>

        <section class="seccion-listado">
            <div class="contenedor-listado">
                <aside class="panel-filtros-miembro" id="panel-filtros-miembro">
                    <div class="buscador-filtros">
                        <label class="etiqueta-filtro" for="buscador-propiedades">Buscador</label>
                        <input type="text" id="buscador-propiedades" class="campo-filtro" placeholder="Ciudad o barrio" />
                    </div>

                    <div class="filtros-miembro">
                        <h2 class="titulo-filtros">Filtros</h2>
                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="precio-minimo">Precio mínimo</label>
                            <div class="fila-campos">
                                <input type="number" id="precio-minimo" class="campo-filtro" placeholder="0" min="0" />
                                <input type="number" id="precio-maximo" class="campo-filtro" placeholder="2000" min="0" />
                            </div>
                        </div>
                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="tipo-inmueble">Tipo de inmueble</label>
                            <select id="tipo-inmueble" class="campo-filtro">
                                <option value="">Todos</option>
                                <option value="piso">Piso</option>
                                <option value="casa">Casa</option>
                                <option value="estudio">Estudio</option>
                                <option value="atico">Atico</option>
                            </select>
                        </div>
                        <div class="grupo-filtro">
                            <label class="etiqueta-filtro" for="numero-habitaciones">Número de habitaciones</label>
                            <select id="numero-habitaciones" class="campo-filtro">
                                <option value="">Todas</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4+</option>
                            </select>
                        </div>
                        <button class="boton-aplicar" type="button">Aplicar filtros</button>
                    </div>
                </aside>

                <div class="listado-propiedades">
                    <div class="cabecera-listado">
                        <h2 class="titulo-listado">Propiedades para ti</h2>
                        <span class="contador-propiedades">
                            {{ $totalPropiedades }} resultados
                        </span>
                    </div>

                    <div class="grid-propiedades">
                        @forelse ($propiedades as $propiedad)
                        <a class="link-propiedad" href="{{ route('miembro.detalle_propiedad', ['id' => $propiedad->id_propiedad]) }}">
                            <article class="tarjeta-propiedad">
                                <div class="imagen-propiedad">
                                    <span class="etiqueta-precio-tarjeta">
                                        {{ number_format($propiedad->precio_propiedad, 0, ',', '.') }} €
                                    </span>
                                </div>
                                <div class="contenido-propiedad">
                                    <h3 class="titulo-propiedad">{{ $propiedad->titulo_propiedad }}</h3>
                                    <p class="ubicacion-propiedad">{{ $propiedad->ciudad_propiedad }} · {{ $propiedad->direccion_propiedad }}</p>
                                    <p class="precio-propiedad">{{ number_format($propiedad->precio_propiedad, 0, ',', '.') }} € / mes</p>
                                </div>
                            </article>
                        </a>
                        @empty
                        <div class="estado-vacio">
                            <p>No hay propiedades disponibles en este momento.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

    </main>
    <script src="{{ asset('js/miembro/miembro.js') }}"></script>
</body>

</html>