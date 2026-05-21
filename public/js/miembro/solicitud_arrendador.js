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

window.onload = iniciarAplicacionSolicitudArrendador;

function iniciarAplicacionSolicitudArrendador() {
	// Inicia la validacion cuando carga la pagina
	iniciarValidacionSolicitudArrendador();
}

function iniciarValidacionSolicitudArrendador() {
	// Pillamos el formulario y sus piezas para usarlas en validacion y envio
    // --- REFERENCIAS DOM ---
    const formulario = document.getElementById("formulario-solicitud-arrendador");
    const botonEnviar = document.getElementById("boton-enviar-solicitud");

    // Elementos de error
    const errorTipoArrendador = document.getElementById("error-tipo-arrendador-solicitud");
    const errorNumPropiedades = document.getElementById("error-num-propiedades-previstas-solicitud");
    const errorDescripcion = document.getElementById("error-descripcion-solicitud");
    const errorAceptaTerminos = document.getElementById("error-acepta-terminos-solicitud");
    const errorAceptaVeracidad = document.getElementById("error-acepta-veracidad-solicitud");

    // Inputs
    const entradaTipoArrendador = document.getElementById("tipo-arrendador-solicitud");
    const entradaNumPropiedades = document.getElementById("num-propiedades-previstas-solicitud");
    const entradaDescripcion = document.getElementById("descripcion-solicitud");
    const entradaAceptaTerminos = document.getElementById("acepta-terminos-solicitud");
    const entradaAceptaVeracidad = document.getElementById("acepta-veracidad-solicitud");

    if (!formulario || !botonEnviar) return;

	// Guardamos el formulario y el boton en variables globales
	formularioSolicitud = formulario;
	botonEnviarSolicitud = botonEnviar;
	// Guardamos cada campo para validar y controlar el boton
	campos = {
		tipoArrendador: entradaTipoArrendador,
		numPropiedades: entradaNumPropiedades,
		descripcion: entradaDescripcion,
		aceptaTerminos: entradaAceptaTerminos,
		aceptaVeracidad: entradaAceptaVeracidad
	};
	tocados = {
		tipoArrendador: false,
		numPropiedades: false,
		descripcion: false,
		aceptaTerminos: false,
		aceptaVeracidad: false
	};

	// Enganchamos validadores por campo
	registrarCampo("tipoArrendador", validarSelectObligatorio);
	registrarCampo("numPropiedades", validarNumeroPropiedades);
	registrarCampo("descripcion", validarDescripcion);
	registrarCampo("aceptaTerminos", validarCheckboxObligatorio);
	registrarCampo("aceptaVeracidad", validarCheckboxObligatorio);

	// Controla el envio con validacion previa
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
		// Monta los datos del formulario como texto tipo query
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

		// Envia el formulario y actualiza el estado segun la respuesta
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
					// Resetea el formulario y bloquea el boton si fue bien
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
					// Devuelve el boton a su estado si hay error
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
				// Mensaje de fallo de red
				if (typeof mostrarAlertaError === "function") {
					mostrarAlertaError("Error", "Error de red al enviar la solicitud");
				} else {
					alert("Error de red al enviar la solicitud");
				}
				botonEnviarSolicitud.innerText = textoOriginalBoton;
				solicitudEnviando = false;
			})
			.finally(function () {
				// Reactiva el boton si no se envio bien
				if (!solicitudEnviada) {
					actualizarEstadoBoton(validarFormulario(false));
				}
			});
	};

	// Estado inicial del boton al cargar
	actualizarEstadoBoton(validarFormulario(false));
}

/* =========================================================
   SECCION 2: REGISTRO DE EVENTOS Y VALIDACION
   Asocia eventos (blur, input, change) a cada campo para
   validar en tiempo real y controlar el estado del boton.
   ========================================================= */

function registrarCampo(clave, validador) {
	// Conecta los eventos del campo con su validador
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
	// Lanza todas las validaciones y devuelve si esta todo ok
	var errores = 0;
	errores += validarCampo("tipoArrendador", validarSelectObligatorio, mostrarErrores);
	errores += validarCampo("numPropiedades", validarNumeroPropiedades, mostrarErrores);
	errores += validarCampo("descripcion", validarDescripcion, mostrarErrores);
	errores += validarCampo("aceptaTerminos", validarCheckboxObligatorio, mostrarErrores);
	errores += validarCampo("aceptaVeracidad", validarCheckboxObligatorio, mostrarErrores);
	return errores === 0;
}

function validarCampo(clave, validador, mostrarErrores) {
	// Valida un campo y pinta su estado segun el resultado
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



function validarNumeroPropiedades(campo) {
	// Valida que sea un numero entero y positivo
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
	// Pide una descripcion con un minimo de texto
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
	// Activa o bloquea el boton segun el estado del formulario
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
