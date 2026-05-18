/* =========================================================
   SECCIÓN 1: INICIALIZACIÓN DEL MAPA
   Crea la instancia de Leaflet, añade las capas base y
   configura los controles.
   ========================================================= */

var mapa;
var capaMarcadores;
var capaPoligonos;
var rutaApiPropiedades = "/miembro/mapa/propiedades";
// Si hay otro window.onload, lo ejecuta antes de este.
var anteriorOnload = window.onload;

window.onload = function () {
	if (typeof anteriorOnload === 'function') {
		anteriorOnload();
	}

	iniciarMapa();
};

function iniciarMapa() {
	// Configura Leaflet y deja el mapa listo para pintar marcadores.
	var centroInicial = [41.38684, 2.16959];
	mapa = L.map("mapa", {
		zoomControl: false,
	}).setView(centroInicial, 7);

	L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
		maxZoom: 19,
		attribution: "&copy; OpenStreetMap",
	}).addTo(mapa);

	L.control.zoom({ position: "bottomright" }).addTo(mapa);

	capaMarcadores = L.layerGroup().addTo(mapa);
	capaPoligonos = L.layerGroup().addTo(mapa);

	/* =========================================================
	   SECCIÓN 2: FILTROS Y PETICIÓN A LA API (BUSCADOR)
	   Recoge los valores del formulario, añade los límites
	   visibles del mapa (Bounds) y llama al backend.
	   ========================================================= */

	var ids = [
		'ciudad-propiedad',
		'precio-minimo',
		'precio-maximo',
		'tipo-inmueble',
		'numero-habitaciones',
		'banos-propiedad',
		'metros-minimo',
		'metros-maximo',
		'amueblado-propiedad',
		'terraza-propiedad',
		'piscina-propiedad',
		'garaje-propiedad',
		'ascensor-propiedad',
		'aire-acondicionado-propiedad',
		'calefaccion-propiedad',
		'trastero-propiedad'
	];

	var ejecutarBusqueda = function () {
		// Construye la query con filtros + limites del mapa.
		var parametros = new URLSearchParams();
		var i;

		// Recorre cada filtro y lo anade si tiene valor.
		for (i = 0; i < ids.length; i++) {
			var campo = document.getElementById(ids[i]);
			if (campo && campo.value !== '') {
				parametros.append(campo.name || ids[i], campo.value);
			}
		}

		// Limites visibles del mapa para acotar resultados.
		var limites = mapa.getBounds();
		// Latitud y longitud min/max en la vista actual.
		parametros.append('lat_min', limites.getSouthWest().lat);
		parametros.append('lat_max', limites.getNorthEast().lat);
		parametros.append('lng_min', limites.getSouthWest().lng);
		parametros.append('lng_max', limites.getNorthEast().lng);

		// Convierte los parametros a query string.
		var query = parametros.toString();
		var url = rutaApiPropiedades + (query !== '' ? '?' + query : '');

		// Pide propiedades filtradas en formato JSON.
		fetch(url, {
			headers: {
				Accept: 'application/json'
			}
		})
			.then(function (respuesta) {
				if (!respuesta.ok) {
					throw new Error('No se pudo cargar el mapa');
				}

				return respuesta.json();
			})
			.then(function (datos) {
				var propiedades = datos.data ? datos.data : datos;
				renderizarMarcadores(propiedades || []);
			})
			.catch(function () {
				renderizarMarcadores([]);
			});
	};

	var formulario = document.getElementById('form-filtros-mapa');
	var boton = document.getElementById('boton-aplicar-filtros');
	var botonBorrar = document.getElementById('boton-borrar-filtros');
	var botonToggleFiltros = document.getElementById('boton-toggle-filtros');
	var textoToggleFiltros = document.getElementById('texto-boton-filtros');

	if (formulario) {
		// Evita recargar pagina y aplica filtros con fetch.
		formulario.onsubmit = function (evento) {
			evento.preventDefault();
			ejecutarBusqueda();
		};
	}

	var timeoutFiltros;
	for (var j = 0; j < ids.length; j++) {
		var campoFiltro = document.getElementById(ids[j]);
		if (campoFiltro) {
			campoFiltro.oninput = function () {
				clearTimeout(timeoutFiltros);
				timeoutFiltros = setTimeout(ejecutarBusqueda, 300);
			};
			campoFiltro.onchange = function () {
				clearTimeout(timeoutFiltros);
				timeoutFiltros = setTimeout(ejecutarBusqueda, 300);
			};
		}
	}
	
	if (botonBorrar) {
		// Limpia los campos y vuelve a cargar el mapa.
		botonBorrar.onclick = function (evento) {
			var i;
			
			if (evento) {
				evento.preventDefault();
			}
			
			for (i = 0; i < ids.length; i++) {
				var campo = document.getElementById(ids[i]);
				if (campo) {
					campo.value = '';
				}
			}
			
			ejecutarBusqueda();
		};
	}

	if (botonToggleFiltros) {
		botonToggleFiltros.onclick = function () {
			var ocultos = document.body.classList.toggle('filtros-ocultos');
			var texto = ocultos ? 'Mostrar filtros' : 'Ocultar filtros';
			if (textoToggleFiltros) {
				textoToggleFiltros.textContent = texto;
			}
			botonToggleFiltros.setAttribute('aria-label', texto);
			if (mapa) {
				setTimeout(function () {
					mapa.invalidateSize();
				}, 120);
			}
		};
	}
	
	// Carga los puntos al cargar la pagina por primera vez
	ejecutarBusqueda();
	
	// Carga los puntos al cambiar el zoom o mover el mapa
	mapa.on('moveend', ejecutarBusqueda);

	/* =========================================================
	   SECCIÓN 3: RENDERIZADO DE MARCADORES
	   Limpia los marcadores antiguos y pinta los nuevos
	   recibidos del servidor.
	   ========================================================= */
}

function renderizarMarcadores(propiedades) {
	capaMarcadores.clearLayers();

	for (var i = 0; i < propiedades.length; i++) {
		var propiedad = propiedades[i];
		if (!propiedad.latitud_propiedad || !propiedad.longitud_propiedad) {
			continue;
		}

		var precio = formatearPrecio(propiedad.precio_propiedad);
		var marcador = L.marker([
			propiedad.latitud_propiedad,
			propiedad.longitud_propiedad,
		], {
			icon: crearIconoPrecio(precio),
		});

		var titulo = propiedad.titulo_propiedad || "Propiedad";
		marcador.bindPopup(construirPopupPropiedad(propiedad), {
			maxWidth: 340,
			minWidth: 280,
			className: "popup-propiedad-contenedor",
		});
		marcador.addTo(capaMarcadores);
	}
}

/* =========================================================
   SECCIÓN 4: CONSTRUCCIÓN DE POPUPS Y DIRECCIONES
   Genera el HTML interno de las burbujas de información
   y compone la dirección legible a partir de las columnas.
   ========================================================= */

function construirPopupPropiedad(propiedad) {
	var titulo = propiedad.titulo_propiedad || "Propiedad";
	var precio = formatearPrecio(propiedad.precio_propiedad);
	var ciudad = propiedad.ciudad_propiedad || "Ciudad no disponible";
	var direccion = propiedad.direccion_propiedad || construirDireccion(propiedad);
	var estado = propiedad.estado_propiedad || "N/D";
	var urlDetalle = "/miembro/propiedad/" + propiedad.id_propiedad;

	return (
		"<div class='popup-propiedad'>" +
		"<div class='popup-propiedad-oso'>" + obtenerSvgOsoPopup() + "</div>" +
		"<h3 class='popup-propiedad-titulo'>" + escaparHtml(titulo) + "</h3>" +
		"<p class='popup-propiedad-precio'>" + precio + " / mes</p>" +
		"<p class='popup-propiedad-linea'><strong>Ciudad:</strong> " + escaparHtml(ciudad) + "</p>" +
		"<p class='popup-propiedad-linea'><strong>Direccion:</strong> " + escaparHtml(direccion) + "</p>" +
		"<p class='popup-propiedad-linea'><strong>Estado:</strong> " + escaparHtml(estado) + "</p>" +
		"<a class='popup-propiedad-boton' href='" + urlDetalle + "'>Ver detalle</a>" +
		"</div>"
	);
}

function obtenerSvgOsoPopup() {
	return (
		"<svg class='oso-popup-svg' viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg' aria-hidden='true' focusable='false'>" +
		"<circle cx='62' cy='52' r='14' fill='#ffffff' stroke='#000' stroke-width='2' />" +
		"<circle cx='138' cy='52' r='14' fill='#ffffff' stroke='#000' stroke-width='2' />" +
		"<path d='M40,200 Q40,55 100,55 Q160,55 160,200 Z' fill='#ffffff' stroke='#000' stroke-width='2' />" +
		"<path d='M30,200 L170,200 L160,152 Q100,132 40,152 Z' fill='#035498' stroke='#000' stroke-width='2' />" +
		"<path d='M100,140 L120,168 L100,200 L80,168 Z' fill='#ffffff' stroke='#000' stroke-width='2' />" +
		"<path d='M100,146 L113,164 L100,190 L87,164 Z' fill='#1AA068' stroke='#000' stroke-width='2' />" +
		"<g>" +
		"<circle cx='82' cy='105' r='5' fill='#000' />" +
		"<circle cx='118' cy='105' r='5' fill='#000' />" +
		"<path d='M92 128 Q100 133 108 128' stroke='#000' stroke-width='2.5' fill='none' stroke-linecap='round' />" +
		"</g>" +
		"<circle cx='48' cy='180' r='19' fill='#ffffff' stroke='#000' stroke-width='2' />" +
		"<circle cx='152' cy='180' r='19' fill='#ffffff' stroke='#000' stroke-width='2' />" +
		"</svg>"
	);
}

function construirDireccion(propiedad) {
	var calleNumero = [propiedad.calle_propiedad, propiedad.numero_propiedad]
		.filter(Boolean)
		.join(" ");
	var piso = propiedad.piso_propiedad ? "Piso " + propiedad.piso_propiedad : "";
	var puerta = propiedad.puerta_propiedad ? "Puerta " + propiedad.puerta_propiedad : "";
	var partes = [calleNumero, piso, puerta].filter(Boolean);

	if (partes.length === 0) {
		return "Direccion no disponible";
	}

	return partes.join(", ");
}

/* =========================================================
   SECCIÓN 5: ICONOS DE PRECIO Y UTILIDADES
   Crea los marcadores visuales con el precio y funciones
   auxiliares de formato y seguridad (XSS).
   ========================================================= */

function crearIconoPrecio(textoPrecio) {
	return L.divIcon({
		className: "etiqueta-precio-icono",
		html: "<span class='etiqueta-precio'>" + textoPrecio + "</span>",
		iconSize: [92, 36],
		iconAnchor: [46, 36],
	});
}

function formatearPrecio(valor) {
	if (valor === null || valor === undefined || valor === "") {
		return "Sin precio";
	}

	var numero = Number(valor);
	if (isNaN(numero)) {
		return "Sin precio";
	}

	var texto = numero.toLocaleString("es-ES", {
		maximumFractionDigits: 0,
	});

	return texto + " &euro;";
}

function escaparHtml(texto) {
	var mapaCaracteres = {
		"&": "&amp;",
		"<": "&lt;",
		">": "&gt;",
		'"': "&quot;",
		"'": "&#039;",
	};

	return String(texto).replace(/[&<>"']/g, function (caracter) {
		return mapaCaracteres[caracter];
	});
}
