/* =========================================================
   SECCION 1: CONFIGURACION E INICIALIZACION
   Captura los campos del formulario y registra los
   validadores para cada uno.
   ========================================================= */

var formularioSolicitud = null;
var botonEnviarSolicitud = null;
var campos = {};
var tocados = {};
var solicitudEnviando = false;
var solicitudEnviada = false;

window.onload = function () {
	iniciarValidacionSolicitudArrendador();
};

function iniciarValidacionSolicitudArrendador() {
	formularioSolicitud = document.getElementById("formulario-solicitud-arrendador");
	botonEnviarSolicitud = document.getElementById("boton-enviar-solicitud");

	if (!formularioSolicitud || !botonEnviarSolicitud) {
		return;
	}

	campos.telefono = document.getElementById("telefono-solicitud");
	campos.fechaNacimiento = document.getElementById("fecha-nacimiento-solicitud");
	campos.tipoDocumento = document.getElementById("tipo-documento-solicitud");
	campos.numeroDocumento = document.getElementById("numero-documento-solicitud");
	campos.iban = document.getElementById("iban-solicitud");
	campos.titular = document.getElementById("titular-cuenta-solicitud");
	campos.nif = document.getElementById("nif-solicitud");
	campos.direccion = document.getElementById("direccion-fiscal-solicitud");
	campos.tipoArrendador = document.getElementById("tipo-arrendador-solicitud");
	campos.numPropiedades = document.getElementById("num-propiedades-previstas-solicitud");
	campos.descripcion = document.getElementById("descripcion-solicitud");
	campos.aceptaTerminos = document.getElementById("acepta-terminos-solicitud");
	campos.aceptaVeracidad = document.getElementById("acepta-veracidad-solicitud");

	registrarCampo("telefono", validarTelefono);
	registrarCampo("fechaNacimiento", validarFechaNacimiento);
	registrarCampo("tipoDocumento", validarSelectObligatorio);
	registrarCampo("numeroDocumento", validarTextoObligatorio);
	registrarCampo("iban", validarIban);
	registrarCampo("titular", validarTextoObligatorio);
	registrarCampo("nif", validarNif);
	registrarCampo("direccion", validarTextoObligatorio);
	registrarCampo("tipoArrendador", validarSelectObligatorio);
	registrarCampo("numPropiedades", validarNumeroPropiedades);
	registrarCampo("descripcion", validarDescripcion);
	registrarCampo("aceptaTerminos", validarCheckboxObligatorio);
	registrarCampo("aceptaVeracidad", validarCheckboxObligatorio);

	formularioSolicitud.onsubmit = function (evento) {
		evento.preventDefault();

		if (solicitudEnviando || solicitudEnviada) {
			return;
		}

		if (!validarFormulario(true)) {
			return;
		}

		solicitudEnviando = true;
		botonEnviarSolicitud.disabled = true;
		botonEnviarSolicitud.classList.add("btn-login-desabilitado");
		var textoOriginalBoton = botonEnviarSolicitud.innerText;

		var tokenMeta = document.getElementsByName("csrf-token");
		var csrf = "";
		if (tokenMeta && tokenMeta.length > 0) {
			csrf = tokenMeta[0].content || tokenMeta[0].getAttribute("content") || "";
		}
		if (!csrf && formularioSolicitud && formularioSolicitud.elements && formularioSolicitud.elements["_token"]) {
			csrf = formularioSolicitud.elements["_token"].value || "";
		}

		var fd = new FormData(formularioSolicitud);

		fetch(formularioSolicitud.action, {
			method: "POST",
			headers: {
				"X-CSRF-TOKEN": csrf,
				"Accept": "application/json"
			},
			body: fd
		})
			.then(function (response) {
				var ct = response.headers.get("content-type") || "";
				if (ct.indexOf("application/json") !== -1) {
					return response.json();
				}
				window.location.reload();
			})
			.then(function (data) {
				if (!data) {
					return;
				}

				if (data.success) {
					solicitudEnviada = true;
					if (typeof mostrarAlertaExito === "function") {
						mostrarAlertaExito("Solicitud enviada", data.message || "Solicitud enviada correctamente");
					} else {
						alert(data.message || "Solicitud enviada correctamente");
					}

					formularioSolicitud.reset();
					for (var k in tocados) {
						if (tocados.hasOwnProperty(k)) {
							tocados[k] = false;
						}
					}
					botonEnviarSolicitud.innerText = "Solicitud enviada";
					botonEnviarSolicitud.disabled = true;
					botonEnviarSolicitud.classList.add("btn-login-desabilitado");
					actualizarEstadoBoton(false);
				} else {
					if (typeof mostrarAlertaError === "function") {
						mostrarAlertaError("Error", data.message || "No se pudo enviar la solicitud");
					} else {
						alert(data.message || "No se pudo enviar la solicitud");
					}
					botonEnviarSolicitud.innerText = textoOriginalBoton;
					solicitudEnviando = false;
				}
			})
			.catch(function () {
				if (typeof mostrarAlertaError === "function") {
					mostrarAlertaError("Error", "Error de red al enviar la solicitud");
				} else {
					alert("Error de red al enviar la solicitud");
				}
				botonEnviarSolicitud.innerText = textoOriginalBoton;
				solicitudEnviando = false;
			})
			.finally(function () {
				if (!solicitudEnviada) {
					actualizarEstadoBoton(validarFormulario(false));
				}
			});
	};

	actualizarEstadoBoton(validarFormulario(false));
}

/* =========================================================
   SECCION 2: REGISTRO DE EVENTOS Y VALIDACION
   Asocia eventos (blur, input, change) a cada campo para
   validar en tiempo real y controlar el estado del boton.
   ========================================================= */

function registrarCampo(clave, validador) {
	var campo = campos[clave];
	if (!campo) {
		return;
	}

	campos[clave].onblur = function () {
		tocados[clave] = true;
		validarCampo(clave, validador, true);
		actualizarEstadoBoton(validarFormulario(false));
	};

	campos[clave].oninput = function () {
		if (tocados[clave]) {
			validarCampo(clave, validador, true);
		}
		actualizarEstadoBoton(validarFormulario(false));
	};

	campos[clave].onchange = function () {
		tocados[clave] = true;
		validarCampo(clave, validador, true);
		actualizarEstadoBoton(validarFormulario(false));
	};
}

function validarFormulario(mostrarErrores) {
	var errores = 0;
	errores += validarCampo("telefono", validarTelefono, mostrarErrores);
	errores += validarCampo("fechaNacimiento", validarFechaNacimiento, mostrarErrores);
	errores += validarCampo("tipoDocumento", validarSelectObligatorio, mostrarErrores);
	errores += validarCampo("numeroDocumento", validarTextoObligatorio, mostrarErrores);
	errores += validarCampo("iban", validarIban, mostrarErrores);
	errores += validarCampo("titular", validarTextoObligatorio, mostrarErrores);
	errores += validarCampo("nif", validarNif, mostrarErrores);
	errores += validarCampo("direccion", validarTextoObligatorio, mostrarErrores);
	errores += validarCampo("tipoArrendador", validarSelectObligatorio, mostrarErrores);
	errores += validarCampo("numPropiedades", validarNumeroPropiedades, mostrarErrores);
	errores += validarCampo("descripcion", validarDescripcion, mostrarErrores);
	errores += validarCampo("aceptaTerminos", validarCheckboxObligatorio, mostrarErrores);
	errores += validarCampo("aceptaVeracidad", validarCheckboxObligatorio, mostrarErrores);
	return errores === 0;
}

function validarCampo(clave, validador, mostrarErrores) {
	var campo = campos[clave];
	if (!campo) {
		return 1;
	}

	var mensajeError = validador(campo);
	var idError = obtenerIdErrorPorClave(clave);
	var esValido = mensajeError === "";

	if (mostrarErrores) {
		if (esValido) {
			limpiarError(idError);
		} else {
			mostrarError(idError, mensajeError);
		}
	}

	if (mostrarErrores || tocados[clave]) {
		marcarCampo(campo, esValido);
	} else {
		limpiarMarcaCampo(campo);
	}
	return esValido ? 0 : 1;
}

/* =========================================================
   SECCION 3: VALIDADORES ESPECIFICOS
   Funciones que contienen la logica (Regex/Date) para
   validar cada tipo de dato (IBAN, NIF, etc.).
   ========================================================= */

function validarTelefono(campo) {
	var valor = String(campo.value || "").trim();
	var regex = /^\+\d{1,4} \d{6,11}$/;

	if (valor === "") {
		return "El telefono es obligatorio.";
	}
	if (!regex.test(valor)) {
		return "Formato invalido. Ejemplo: +34 600123456";
	}
	return "";
}

function validarFechaNacimiento(campo) {
	var valor = String(campo.value || "").trim();
	if (valor === "") {
		return "La fecha de nacimiento es obligatoria.";
	}

	var fecha = new Date(valor + "T00:00:00");
	if (isNaN(fecha.getTime())) {
		return "La fecha de nacimiento no es valida.";
	}

	var hoy = new Date();
	var edad = hoy.getFullYear() - fecha.getFullYear();
	var diferenciaMes = hoy.getMonth() - fecha.getMonth();
	if (diferenciaMes < 0 || (diferenciaMes === 0 && hoy.getDate() < fecha.getDate())) {
		edad = edad - 1;
	}

	if (edad < 18) {
		return "Debes ser mayor de edad para enviar la solicitud.";
	}
	return "";
}

function validarSelectObligatorio(campo) {
	var valor = String(campo.value || "").trim();
	if (valor === "") {
		return "Este campo es obligatorio.";
	}
	return "";
}

function validarTextoObligatorio(campo) {
	var valor = String(campo.value || "").trim();
	if (valor === "") {
		return "Este campo es obligatorio.";
	}
	return "";
}

function validarIban(campo) {
	var valor = String(campo.value || "").toUpperCase().replace(/\s+/g, "").trim();
	if (valor === "") {
		return "El IBAN es obligatorio.";
	}
	if (!/^ES\d{22}$/.test(valor)) {
		return "El IBAN debe tener formato ES seguido de 22 digitos.";
	}
	return "";
}

function validarNif(campo) {
	var valor = String(campo.value || "").toUpperCase().trim();
	if (valor === "") {
		return "El NIF es obligatorio.";
	}
	if (!/^(\d{8}[A-Z]|[XYZ]\d{7}[A-Z])$/.test(valor)) {
		return "El NIF/NIE no tiene un formato valido.";
	}
	return "";
}

function validarNumeroPropiedades(campo) {
	var valor = String(campo.value || "").trim();
	if (valor === "") {
		return "El numero de propiedades es obligatorio.";
	}
	var numero = Number(valor);
	if (isNaN(numero) || numero <= 0 || numero % 1 !== 0) {
		return "Debes indicar un numero entero mayor que 0.";
	}
	return "";
}

function validarDescripcion(campo) {
	var valor = String(campo.value || "").trim();
	if (valor === "") {
		return "La descripcion es obligatoria.";
	}
	if (valor.length < 15) {
		return "La descripcion debe tener al menos 15 caracteres.";
	}
	return "";
}

function validarCheckboxObligatorio(campo) {
	if (!campo.checked) {
		return "Debes aceptar este campo para continuar.";
	}
	return "";
}

function obtenerIdErrorPorClave(clave) {
	var mapaErrores = {
		telefono: "error-telefono-solicitud",
		fechaNacimiento: "error-fecha-nacimiento-solicitud",
		tipoDocumento: "error-tipo-documento-solicitud",
		numeroDocumento: "error-numero-documento-solicitud",
		iban: "error-iban-solicitud",
		titular: "error-titular-cuenta-solicitud",
		nif: "error-nif-solicitud",
		direccion: "error-direccion-fiscal-solicitud",
		tipoArrendador: "error-tipo-arrendador-solicitud",
		numPropiedades: "error-num-propiedades-previstas-solicitud",
		descripcion: "error-descripcion-solicitud",
		aceptaTerminos: "error-acepta-terminos-solicitud",
		aceptaVeracidad: "error-acepta-veracidad-solicitud"
	};

	return mapaErrores[clave] || "";
}

/* =========================================================
   SECCION 4: HELPERS DE UI
   Funciones para mostrar errores visuales y habilitar
   el boton de envio cuando todo es valido.
   ========================================================= */

function mostrarError(idError, mensaje) {
	var error = document.getElementById(idError);
	if (!error) {
		return;
	}
	error.textContent = mensaje;
}

function limpiarError(idError) {
	var error = document.getElementById(idError);
	if (!error) {
		return;
	}
	error.textContent = "";
}

function marcarCampo(campo, esValido) {
	if (!campo || !campo.classList) {
		return;
	}
	if (esValido) {
		campo.classList.remove("solicitud-campo-error");
		return;
	}
	campo.classList.add("solicitud-campo-error");
}

function limpiarMarcaCampo(campo) {
	if (!campo || !campo.classList) {
		return;
	}

	campo.classList.remove("solicitud-campo-error");
}

function actualizarEstadoBoton(habilitar) {
	if (!botonEnviarSolicitud || solicitudEnviada) {
		return;
	}

	botonEnviarSolicitud.disabled = !habilitar;
	if (habilitar) {
		botonEnviarSolicitud.classList.remove("btn-login-desabilitado");
	} else {
		botonEnviarSolicitud.classList.add("btn-login-desabilitado");
	}
}
