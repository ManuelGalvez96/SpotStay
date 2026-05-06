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

	if (formulario) {
		// Evita recargar pagina y aplica filtros con fetch.
		formulario.onsubmit = function (evento) {
			evento.preventDefault();
			ejecutarBusqueda();
		};
	}

	if (boton) {
		// Aplica filtros con un click del usuario.
		boton.onclick = function (evento) {
			if (evento) {
				evento.preventDefault();
			}
			ejecutarBusqueda();
		};
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
		"<h3 class='popup-propiedad-titulo'>" + escaparHtml(titulo) + "</h3>" +
		"<p class='popup-propiedad-precio'>" + precio + " / mes</p>" +
		"<p class='popup-propiedad-linea'><strong>Ciudad:</strong> " + escaparHtml(ciudad) + "</p>" +
		"<p class='popup-propiedad-linea'><strong>Direccion:</strong> " + escaparHtml(direccion) + "</p>" +
		"<p class='popup-propiedad-linea'><strong>Estado:</strong> " + escaparHtml(estado) + "</p>" +
		"<a class='popup-propiedad-boton' href='" + urlDetalle + "'>Ver detalle</a>" +
		"</div>"
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

// function dibujarPoligonoEjemplo() {
// 	var coordenadas = [
// 		[41.3706, 2.0932],
// 		[41.3792, 2.1216],
// 		[41.3722, 2.1527],
// 		[41.3509, 2.1514],
// 		[41.3402, 2.1204],
// 		[41.3503, 2.0944],
// 	];

// 	var poligono = L.polygon(coordenadas, {
// 		color: "#2b62a8",
// 		weight: 2,
// 		fillColor: "#2b62a8",
// 		fillOpacity: 0.15,
// 	});

// 	poligono.addTo(capaPoligonos);
// }

