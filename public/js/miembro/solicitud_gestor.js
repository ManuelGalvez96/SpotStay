var formularioSolicitud = null;
var botonEnviarSolicitud = null;
var campos = {};
var tocados = {};
var solicitudEnviando = false;
var solicitudEnviada = false;

window.onload = iniciarAplicacionSolicitudGestor;

function iniciarAplicacionSolicitudGestor() {
	iniciarValidacionSolicitudGestor();
}

function iniciarValidacionSolicitudGestor() {
    const formulario = document.getElementById("formulario-solicitud-gestor");
    const botonEnviar = document.getElementById("boton-enviar-solicitud");

    const errorDescripcion = document.getElementById("error-descripcion-solicitud");
    const errorExperiencia = document.getElementById("error-experiencia-solicitud");
    const errorAceptaTerminos = document.getElementById("error-acepta-terminos-solicitud");
    const errorAceptaVeracidad = document.getElementById("error-acepta-veracidad-solicitud");

    const entradaDescripcion = document.getElementById("descripcion-solicitud");
    const entradaExperiencia = document.getElementById("experiencia-solicitud");
    const entradaAceptaTerminos = document.getElementById("acepta-terminos-solicitud");
    const entradaAceptaVeracidad = document.getElementById("acepta-veracidad-solicitud");

    if (!formulario || !botonEnviar) return;

	formularioSolicitud = formulario;
	botonEnviarSolicitud = botonEnviar;
	campos = {
		descripcion: entradaDescripcion,
		experiencia: entradaExperiencia,
		aceptaTerminos: entradaAceptaTerminos,
		aceptaVeracidad: entradaAceptaVeracidad
	};
	tocados = {
		descripcion: false,
		experiencia: false,
		aceptaTerminos: false,
		aceptaVeracidad: false
	};

	registrarCampo("descripcion", validarDescripcion);
	registrarCampo("experiencia", validarExperiencia);
	registrarCampo("aceptaTerminos", validarCheckboxObligatorio);
	registrarCampo("aceptaVeracidad", validarCheckboxObligatorio);

	formularioSolicitud.onsubmit = function (evento) {
		evento.preventDefault();

		if (solicitudEnviando || solicitudEnviada) {
			return;
		}

		if (!validarFormulario(true)) {
			actualizarEstadoBoton(false);
			return;
		}

		solicitudEnviando = true;
		botonEnviarSolicitud.disabled = true;
		botonEnviarSolicitud.classList.add("btn-login-desabilitado");
		var textoOriginalBoton = botonEnviarSolicitud.innerText;
		var formularioDatos = "";
		for (var i = 0; i < formularioSolicitud.elements.length; i++) {
			var elementoFormulario = formularioSolicitud.elements[i];
			if (!elementoFormulario.name || elementoFormulario.disabled) {
				continue;
			}

			if ((elementoFormulario.type === "checkbox" || elementoFormulario.type === "radio") && !elementoFormulario.checked) {
				continue;
			}

			if (formularioDatos !== "") {
				formularioDatos += "&";
			}
			formularioDatos += encodeURIComponent(elementoFormulario.name) + "=" + encodeURIComponent(elementoFormulario.value);
		}

		var tokenMeta = document.getElementsByName("csrf-token");
		var csrf = "";
		if (tokenMeta && tokenMeta.length > 0) {
			csrf = tokenMeta[0].content || tokenMeta[0].getAttribute("content") || "";
		}
		if (!csrf && formularioSolicitud.elements && formularioSolicitud.elements["_token"]) {
			csrf = formularioSolicitud.elements["_token"].value || "";
		}

		fetch(formularioSolicitud.action, {
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
				"X-CSRF-TOKEN": csrf,
				"Accept": "application/json"
			},
			body: formularioDatos
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
						tocados[k] = false;
					}
					botonEnviarSolicitud.innerText = "Solicitud enviada";
					botonEnviarSolicitud.disabled = true;
					botonEnviarSolicitud.classList.add("btn-login-desabilitado");
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
	errores += validarCampo("descripcion", validarDescripcion, mostrarErrores);
	errores += validarCampo("experiencia", validarExperiencia, mostrarErrores);
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

function validarDescripcion(campo) {
	var valor = String(campo.value || "").trim();
	if (valor === "") {
		return "";
	}
	if (valor.length < 15) {
		return "La descripcion debe tener al menos 15 caracteres.";
	}
	return "";
}

function validarExperiencia(campo) {
	var valor = String(campo.value || "").trim();
	if (valor === "") {
		return "";
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
		descripcion: "error-descripcion-solicitud",
		experiencia: "error-experiencia-solicitud",
		aceptaTerminos: "error-acepta-terminos-solicitud",
		aceptaVeracidad: "error-acepta-veracidad-solicitud"
	};

	return mapaErrores[clave] || "";
}

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
