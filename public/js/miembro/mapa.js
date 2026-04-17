var mapa;
var capaMarcadores;
var capaPoligonos;
var rutaApiPropiedades = "/api/propiedades";

window.onload = function () {
	iniciarMapa();
	configurarFiltros();
};

function iniciarMapa() {
	var centroInicial = [41.3663, 2.1166];
	mapa = L.map("mapa", {
		zoomControl: false,
	}).setView(centroInicial, 13);

	L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
		maxZoom: 19,
		attribution: "&copy; OpenStreetMap",
	}).addTo(mapa);

	L.control.zoom({ position: "bottomright" }).addTo(mapa);

	capaMarcadores = L.layerGroup().addTo(mapa);
	capaPoligonos = L.layerGroup().addTo(mapa);

	dibujarPoligonoEjemplo();
	cargarPropiedades();

	mapa.on("moveend", function () {
		cargarPropiedades();
	});
}

function configurarFiltros() {
	var boton = document.getElementById("boton-aplicar-filtros");
	if (!boton) {
		return;
	}

	boton.onclick = function () {
		cargarPropiedades();
	};
}

function obtenerFiltros() {
	return {
		precio_minimo: obtenerValor("precio-minimo"),
		precio_maximo: obtenerValor("precio-maximo"),
		tipo_inmueble: obtenerValor("tipo-inmueble"),
		habitaciones: obtenerValor("numero-habitaciones"),
	};
}

function obtenerValor(idCampo) {
	var campo = document.getElementById(idCampo);
	if (!campo || campo.value === "") {
		return "";
	}

	return campo.value;
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
		precio_minimo: filtros.precio_minimo,
		precio_maximo: filtros.precio_maximo,
		tipo_inmueble: filtros.tipo_inmueble,
		habitaciones: filtros.habitaciones,
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
		marcador.bindPopup(
			"<strong>" + escaparHtml(titulo) + "</strong><br>" + precio
		);
		marcador.addTo(capaMarcadores);
	}
}

function crearIconoPrecio(textoPrecio) {
	return L.divIcon({
		className: "etiqueta-precio",
		html: "<span class=\"etiqueta-precio-texto\">" + textoPrecio + "</span>",
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

