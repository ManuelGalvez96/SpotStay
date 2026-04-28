var mapa;
var capaMarcadores;
var capaPoligonos;
var rutaApiPropiedades = "/miembro/mapa/propiedades";

window.onload = function () {
	iniciarMapa();
	configurarFiltros();
};

function iniciarMapa() {
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

	cargarPropiedades();

	mapa.on("moveend", function () {
		cargarPropiedades();
	});
}

function configurarFiltros() {
	if (!window.FiltrosMiembro || typeof window.FiltrosMiembro.registrarBotonAplicar !== "function") {
		return;
	}

	window.FiltrosMiembro.registrarBotonAplicar("boton-aplicar-filtros", cargarPropiedades);
}

function obtenerFiltros() {
	if (!window.FiltrosMiembro || typeof window.FiltrosMiembro.obtenerFiltrosMapa !== "function") {
		return {};
	}

	return window.FiltrosMiembro.obtenerFiltrosMapa();
}

function cargarPropiedades() {
	if (!mapa) {
		return;
	}

	var filtros = obtenerFiltros();
	var limites = mapa.getBounds();

	var parametros = {
		lat_min: limites.getSouthWest().lat,
		lat_max: limites.getNorthEast().lat,
		lng_min: limites.getSouthWest().lng,
		lng_max: limites.getNorthEast().lng,
		precio_minimo: filtros.precio_minimo || "",
		precio_maximo: filtros.precio_maximo || "",
		tipo_inmueble: filtros.tipo_inmueble || "",
		habitaciones: filtros.habitaciones || "",
		metros_minimo: filtros.metros_minimo || "",
		metros_maximo: filtros.metros_maximo || "",
	};

	var url = rutaApiPropiedades + "?" + new URLSearchParams(parametros).toString();

	fetch(url, {
		headers: {
			Accept: "application/json",
		},
	})
		.then(function (respuesta) {
			if (!respuesta.ok) {
				throw new Error("Respuesta invalida del servidor");
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

function construirPopupPropiedad(propiedad) {
	var titulo = propiedad.titulo_propiedad || "Propiedad";
	var precio = formatearPrecio(propiedad.precio_propiedad);
	var ciudad = propiedad.ciudad_propiedad || "Ciudad no disponible";
	var direccion = propiedad.direccion_propiedad || "Direccion no disponible";
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

function crearIconoPrecio(textoPrecio) {
	return L.divIcon({
		className: "etiqueta-precio",
		html: "<span class='etiqueta-precio-texto'>" + textoPrecio + "</span>",
		iconSize: [1, 1],
		iconAnchor: [12, 24],
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

