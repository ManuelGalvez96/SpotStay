<!doctype html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>SpotStay | Mapa de Propiedades</title>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
		<link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}" />
	</head>
	<body class="pagina-mapa">

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

		<nav class="navegacion-horizontal">
			<div class="contenedor-nav">
				<ul class="lista-nav">
					<li><a href="/miembro/inicio" class="enlace-nav"><i class="bi bi-house-door"></i> Inicio</a></li>
					<li><a href="#" class="enlace-nav"><i class="bi bi-plus-circle"></i> Registra tus Propiedades</a></li>
					<li><a href="#" class="enlace-nav"><i class="bi bi-journal-text"></i> Alquileres</a></li>
					<li><a href="#" class="enlace-nav"><i class="bi bi-chat-dots"></i> Mensajes</a></li>
					<li><a href="{{ route('miembro.mapa') }}" class="enlace-nav activo"><i class="bi bi-map"></i> Mapa</a></li>
					@if ($esInquilino)
					<li><a href="{{ route('gestionar_propiedades') }}" class="enlace-nav"><i class="bi bi-building-gear"></i> Gestionar</a></li>
					@endif
				</ul>
			</div>
		</nav>

		<main class="contenido-mapa">
			<aside class="panel-filtros" id="panel-filtros">
				<div class="panel-filtros-encabezado">
					<h2 class="titulo-filtros">Filtros de busqueda</h2>
					<p class="descripcion-filtros">Ajusta los filtros y mueve el mapa para ver mas resultados.</p>
				</div>

				<div class="grupo-filtro">
					<label class="etiqueta-filtro" for="precio-minimo">Rango de precio</label>
					<div class="fila-campos">
						<input type="number" id="precio-minimo" class="campo-filtro" placeholder="Min" min="0"/>
						<input type="number" id="precio-maximo" class="campo-filtro" placeholder="Max" min="0"/>
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
					<label class="etiqueta-filtro" for="numero-habitaciones">Numero de habitaciones</label>
					<input type="text" id="numero-habitaciones" class="campo-filtro" placeholder="Ej: 1, 2, 3, 4" />
				</div>

				<div class="grupo-filtro">
					<label class="etiqueta-filtro" for="metros-minimo">Metros cuadrados</label>
					<div class="fila-campos">
						<input type="number" id="metros-minimo" class="campo-filtro" placeholder="Min" min="0" />
						<input type="number" id="metros-maximo" class="campo-filtro" placeholder="Max" min="0" />
					</div>
				</div>
			</aside>

			<section class="contenedor-mapa">
				<div id="mapa"></div>
			</section>
		</main>

		<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
		<script src="{{ asset('js/miembro/miembro.js') }}"></script>
		<script src="{{ asset('js/miembro/mapa.js') }}"></script>
	</body>
</html>
