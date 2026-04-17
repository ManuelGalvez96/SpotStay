<!doctype html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>SpotStay | Mapa</title>
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
		<link rel="stylesheet" href="{{ asset('css/miembro/miembro.css') }}" />
	</head>
	<body class="pagina-mapa">
		<header class="encabezado-mapa" id="encabezado-mapa">
			<div class="contenedor-encabezado">
				<div class="logo-spotstay">
					<img src="/img/logo.png"/>
				</div>
				<div class="texto-encabezado">Busqueda por mapa</div>
			</div>
		</header>

		<main class="contenido-mapa">
			<aside class="panel-filtros" id="panel-filtros">
				<div class="panel-filtros-encabezado">
					<h2 class="titulo-filtros">Filtros de busqueda</h2>
					<p class="descripcion-filtros">Ajusta los filtros y mueve el mapa para ver mas resultados.</p>
				</div>

				<div class="grupo-filtro">
					<label class="etiqueta-filtro" for="precio-minimo">Precio minimo</label>
					<div class="fila-campos">
						<input type="number" id="precio-minimo" class="campo-filtro" placeholder="0" min="0"/>
						<input type="number" id="precio-maximo" class="campo-filtro" placeholder="2000" min="0"/>
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
					<label class="etiqueta-filtro" for="numero-habitaciones">
						Numero de habitaciones
					</label>
					<select id="numero-habitaciones" class="campo-filtro">
						<option value="">Todas</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4+</option>
					</select>
				</div>

				<button class="boton-aplicar" id="boton-aplicar-filtros" type="button">
					Aplicar filtros
				</button>
			</aside>

			<section class="contenedor-mapa">
				<div id="mapa"></div>
			</section>
		</main>

		<script
			src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
			integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
			crossorigin=""
		></script>
		<script src="{{ asset('js/miembro/mapa.js') }}"></script>
	</body>
</html>
