/* =========================================================
   SECCIÓN 1: CONFIGURACIÓN Y CARGA INICIAL DEL CHAT
   Define variables globales y arranca la carga de mensajes
   y el intervalo de actualización automática (polling).
   ========================================================= */

var urlMensajesActual = "";
var urlEnviarActual = "";
var tokenCsrfActual = "";
var intervaloMensajes = null;
var enviandoMensaje = false;

window.onload = function () {
	inicializarChatFetch();
	inicializarToggleConversaciones();
};

function inicializarToggleConversaciones() {
	var botonAbrir = document.getElementById("boton-abrir-conversaciones");
	var botonCerrar = document.getElementById("boton-cerrar-conversaciones");

	if (!botonAbrir && !botonCerrar) {
		return;
	}

	function abrirConversaciones() {
		document.body.classList.add("conversaciones-abiertas");
	}

	function cerrarConversaciones() {
		document.body.classList.remove("conversaciones-abiertas");
	}

	if (botonAbrir) {
		botonAbrir.onclick = function () {
			abrirConversaciones();
		};
	}

	if (botonCerrar) {
		botonCerrar.onclick = function () {
			cerrarConversaciones();
		};
	}
}

function inicializarChatFetch() {
	var datosMensajes = document.getElementById("mensajes-datos");
	var formulario = document.getElementById("mensajes-formulario");
	var listaMensajes = document.getElementById("mensajes-mensajes");
	var campoMensaje = document.getElementById("mensaje");

	if (!datosMensajes || !formulario || !listaMensajes || !campoMensaje) {
		return;
	}

	urlMensajesActual = datosMensajes.getAttribute("data-url-mensajes") || "";
	urlEnviarActual = datosMensajes.getAttribute("data-url-enviar") || "";
	tokenCsrfActual = obtenerTokenFormulario(formulario);

	formulario.onsubmit = function (evento) {
		evento.preventDefault();
		enviarMensaje(campoMensaje, listaMensajes);
	};

	campoMensaje.onkeydown = function (evento) {
		if (evento.key === "Enter" && !evento.shiftKey) {
			evento.preventDefault();
			enviarMensaje(campoMensaje, listaMensajes);
		}
	};

	cargarMensajes(listaMensajes);

	intervaloMensajes = window.setInterval(function () {
		if (!enviandoMensaje) {
			cargarMensajes(listaMensajes);
		}
	}, 3000);
}

/* =========================================================
   SECCIÓN 2: LÓGICA DE COMUNICACIÓN (FETCH)
   Se encarga de obtener y enviar mensajes al servidor.
   ========================================================= */

function obtenerTokenFormulario(formulario) {
	var tokenInput = formulario.elements["_token"];
	if (!tokenInput) {
		return "";
	}

	return tokenInput.value;
}

function cargarMensajes(listaMensajes) {
	if (!urlMensajesActual) {
		return;
	}

	fetch(urlMensajesActual, {
		method: "GET",
		headers: {
			"X-Requested-With": "XMLHttpRequest",
			"Accept": "application/json"
		}
	})
		.then(function (respuesta) {
			if (!respuesta.ok) {
				throw new Error("No se pudieron cargar mensajes");
			}

			return respuesta.json();
		})
		.then(function (data) {
			if (!data || !data.ok || !data.mensajes) {
				return;
			}

			pintarMensajes(listaMensajes, data.mensajes);
		})
		.catch(function () {
		});
}

function enviarMensaje(campoMensaje, listaMensajes) {
	if (!urlEnviarActual || enviandoMensaje) {
		return;
	}

	var texto = campoMensaje.value.trim();

	if (texto === "") {
		return;
	}

	enviandoMensaje = true;

	var formData = new FormData();
	formData.append("mensaje", texto);

	fetch(urlEnviarActual, {
		method: "POST",
		headers: {
			"X-CSRF-TOKEN": tokenCsrfActual,
			"X-Requested-With": "XMLHttpRequest",
			"Accept": "application/json"
		},
		body: formData
	})
		.then(function (respuesta) {
			if (!respuesta.ok) {
				throw new Error("No se pudo enviar el mensaje");
			}

			return respuesta.json();
		})
		.then(function () {
			campoMensaje.value = "";
			campoMensaje.focus();
			cargarMensajes(listaMensajes);
		})
		.catch(function () {
		})
		.finally(function () {
			enviandoMensaje = false;
		});
}

/* =========================================================
   SECCIÓN 3: RENDERIZADO DE UI (BURBUJAS)
   Genera el HTML de los mensajes y actualiza el DOM.
   ========================================================= */

function pintarMensajes(listaMensajes, mensajes) {
	var html = "";
	var i;

	if (!mensajes.length) {
		listaMensajes.innerHTML = '<div class="estado-vacio"><p>Aun no hay mensajes en esta conversación.</p></div>';
		return;
	}

	for (i = 0; i < mensajes.length; i++) {
		html += construirBurbuja(mensajes[i]);
	}

	listaMensajes.innerHTML = html;
	listaMensajes.scrollTop = listaMensajes.scrollHeight;
}

function construirBurbuja(mensaje) {
	var clase = mensaje.es_mio ? "mio" : "otro";
	var texto = escaparHtml(mensaje.cuerpo_mensaje || "");
	var fecha = escaparHtml(mensaje.fecha || "");

	return '<div class="mensajes-burbuja ' + clase + '">' +
		'<p class="mensajes-burbuja-texto">' + texto + '</p>' +
		'<span class="mensajes-burbuja-fecha">' + fecha + '</span>' +
		'</div>';
}

/* =========================================================
   SECCIÓN 4: UTILIDADES DE SEGURIDAD
   Escapa caracteres para prevenir inyección de código (XSS).
   ========================================================= */

function escaparHtml(valor) {
	var texto = String(valor);

	return texto
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/\"/g, "&quot;")
		.replace(/'/g, "&#039;");
}
