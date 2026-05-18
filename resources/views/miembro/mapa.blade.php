<!doctype html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>SpotStay | Mapa</title>
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
		<link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}?v=3" />
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
	</head>
	<body class="pagina-mapa">
		<header class="encabezado-mapa" id="encabezado-mapa">
			<div class="contenedor-encabezado">
				<div class="logo-spotstay">
					<img src="/img/logo.png"/>
				</div>
				<div class="acciones-miembro">
					<button class="boton-icono" type="button" aria-label="Notificaciones">
						<i class="bi bi-bell" aria-hidden="true"></i>
					</button>
					<button class="boton-filtros-header" id="boton-toggle-filtros" type="button" aria-label="Ocultar filtros">
						<i class="bi bi-funnel" aria-hidden="true"></i>
						<span id="texto-boton-filtros">Ocultar filtros</span>
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

		<main class="contenido-mapa">
			<aside class="panel-filtros" id="panel-filtros">
				<form id="form-filtros-mapa">
				<div class="panel-filtros-encabezado">
					<h2 class="titulo-filtros">Filtros de busqueda</h2>
					<a class="detalle-volver" href="/miembro/inicio" aria-label="Volver">
						<i class="bi bi-arrow-left" aria-hidden="true"></i>
					</a>
					<p class="descripcion-filtros">Ajusta los filtros y mueve el mapa para ver mas resultados.</p>
				</div>

				<div class="grupo-filtro">
					<label class="etiqueta-filtro" for="ciudad-propiedad">Ciudad</label>
					<select id="ciudad-propiedad" name="ciudad" class="campo-filtro">
						<option value="">Todas</option>
						@foreach ($ciudades as $ciudad)
							<option value="{{ $ciudad }}">{{ $ciudad }}</option>
						@endforeach
					</select>
				</div>

				<div class="grupo-filtro">
					<label class="etiqueta-filtro" for="precio-minimo">Rango de precio</label>
					<div class="fila-campos">
						<input type="number" id="precio-minimo" class="campo-filtro" name="precio_minimo" placeholder="Min" min="0"/>
						<input type="number" id="precio-maximo" class="campo-filtro" name="precio_maximo" placeholder="Max" min="0"/>
					</div>
				</div>

				<div class="grupo-filtro">
					<label class="etiqueta-filtro" for="tipo-inmueble">Tipo de inmueble</label>
					<select id="tipo-inmueble" class="campo-filtro" name="tipo_inmueble">
						<option value="">Todos</option>
						<option value="piso">Piso</option>
						<option value="casa">Casa</option>
						<option value="estudio">Estudio</option>
						<option value="atico">Atico</option>
					</select>
				</div>

				<div class="grupo-filtro">
					<label class="etiqueta-filtro" for="numero-habitaciones">Numero de habitaciones</label>
					<select id="numero-habitaciones" class="campo-filtro" name="habitaciones">
						<option value="">Todas</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4+">4+</option>
					</select>
				</div>

				<div class="grupo-filtro">
					<label class="etiqueta-filtro" for="banos-propiedad">Baños</label>
					<select id="banos-propiedad" class="campo-filtro" name="banos">
						<option value="">Todos</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4+">4 o más</option>
					</select>
				</div>

				<div class="grupo-filtro">
					<label class="etiqueta-filtro" for="metros-minimo">Metros cuadrados</label>
					<div class="fila-campos">
						<input type="number" id="metros-minimo" class="campo-filtro" name="metros_minimo" placeholder="Min" min="0" />
						<input type="number" id="metros-maximo" class="campo-filtro" name="metros_maximo" placeholder="Max" min="0" />
					</div>
				</div>

				<div class="grupo-filtro filtros-propiedad-grid">
					<select id="amueblado-propiedad" name="amueblado" class="campo-filtro">
						<option value="">Amueblado</option>
						<option value="1">Sí</option>
						<option value="0">No</option>
					</select>
					<select id="terraza-propiedad" name="terraza" class="campo-filtro">
						<option value="">Terraza</option>
						<option value="1">Sí</option>
						<option value="0">No</option>
					</select>
					<select id="piscina-propiedad" name="piscina" class="campo-filtro">
						<option value="">Piscina</option>
						<option value="1">Sí</option>
						<option value="0">No</option>
					</select>
					<select id="garaje-propiedad" name="garaje" class="campo-filtro">
						<option value="">Garaje</option>
						<option value="1">Sí</option>
						<option value="0">No</option>
					</select>
					<select id="ascensor-propiedad" name="ascensor" class="campo-filtro">
						<option value="">Ascensor</option>
						<option value="1">Sí</option>
						<option value="0">No</option>
					</select>
					<select id="aire-acondicionado-propiedad" name="aire_acondicionado" class="campo-filtro">
						<option value="">Aire acondicionado</option>
						<option value="1">Sí</option>
						<option value="0">No</option>
					</select>
					<select id="calefaccion-propiedad" name="calefaccion" class="campo-filtro">
						<option value="">Calefacción</option>
						<option value="1">Sí</option>
						<option value="0">No</option>
					</select>
					<select id="trastero-propiedad" name="trastero" class="campo-filtro">
						<option value="">Trastero</option>
						<option value="1">Sí</option>
						<option value="0">No</option>
					</select>
				</div>

				<button class="boton-aplicar" id="boton-aplicar-filtros" type="submit">Aplicar filtros</button>
				<button class="boton-aplicar" id="boton-borrar-filtros" type="reset">Borrar filtros</button>
				</form>
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
