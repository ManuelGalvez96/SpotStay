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

		// preparar envio por fetch
		solicitudEnviando = true;
		botonEnviarSolicitud.disabled = true;
		botonEnviarSolicitud.classList.add('btn-login-desabilitado');
		var textoOriginalBoton = botonEnviarSolicitud.innerText;

		var tokenMeta = document.getElementsByName('csrf-token');
		var csrf = '';
		if (tokenMeta && tokenMeta.length > 0) {
			csrf = tokenMeta[0].content || tokenMeta[0].getAttribute('content') || '';
		}
		// fallback: obtener token del campo oculto _token del formulario
		if (!csrf && formularioSolicitud && formularioSolicitud.elements && formularioSolicitud.elements['_token']) {
			csrf = formularioSolicitud.elements['_token'].value || '';
		}

		var fd = new FormData(formularioSolicitud);

		fetch(formularioSolicitud.action, {
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': csrf,
				'Accept': 'application/json'
			},
			body: fd
		})
		.then(function (response) {
			var ct = response.headers.get('content-type') || '';
			if (ct.indexOf('application/json') !== -1) {
				return response.json();
			}
			// si no es JSON recargar página
			window.location.reload();
		})
		.then(function (data) {
			if (!data) return;
			if (data.success) {
				solicitudEnviada = true;
				if (typeof mostrarAlertaExito === 'function') {
					mostrarAlertaExito('Solicitud enviada', data.message || 'Solicitud enviada correctamente');
				} else {
					alert(data.message || 'Solicitud enviada correctamente');
				}
				formularioSolicitud.reset();
				// resetear estados locales
				for (var k in tocados) { if (tocados.hasOwnProperty(k)) tocados[k] = false; }
				botonEnviarSolicitud.innerText = 'Solicitud enviada';
				botonEnviarSolicitud.disabled = true;
				botonEnviarSolicitud.classList.add('btn-login-desabilitado');
				actualizarEstadoBoton(false);
			} else {
				if (typeof mostrarAlertaError === 'function') {
					mostrarAlertaError('Error', data.message || 'No se pudo enviar la solicitud');
				} else {
					alert(data.message || 'No se pudo enviar la solicitud');
				}
				botonEnviarSolicitud.innerText = textoOriginalBoton;
				solicitudEnviando = false;
			}
		})
		.catch(function (err) {
			if (typeof mostrarAlertaError === 'function') {
				mostrarAlertaError('Error', 'Error de red al enviar la solicitud');
			} else {
				alert('Error de red al enviar la solicitud');
			}
			botonEnviarSolicitud.innerText = textoOriginalBoton;
			solicitudEnviando = false;
		})
		.finally(function () {
			if (!solicitudEnviada) {
				botonEnviarSolicitud.disabled = false;
				botonEnviarSolicitud.classList.remove('btn-login-desabilitado');
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

	var error = validador(campo);
	var idError = "error-" + obtenerIdCampo(clave);

	if (error === 0) {
		limpiarError(idError);
		marcarCampo(campo, true);
		return 0;
	}

	if (mostrarErrores || tocados[clave]) {
		mostrarError(idError, obtenerMensajeError(clave));
		marcarCampo(campo, false);
	} else {
		limpiarError(idError);
		marcarCampo(campo, true);
	}

	return 1;
}

function obtenerIdCampo(clave) {
	if (clave === "fechaNacimiento") return "fecha-nacimiento-solicitud";
	if (clave === "tipoDocumento") return "tipo-documento-solicitud";
	if (clave === "numeroDocumento") return "numero-documento-solicitud";
	if (clave === "titular") return "titular-cuenta-solicitud";
	if (clave === "tipoArrendador") return "tipo-arrendador-solicitud";
	if (clave === "numPropiedades") return "num-propiedades-previstas-solicitud";
	if (clave === "descripcion") return "descripcion-solicitud";
	if (clave === "aceptaTerminos") return "acepta-terminos-solicitud";
	if (clave === "aceptaVeracidad") return "acepta-veracidad-solicitud";
	if (clave === "direccion") return "direccion-fiscal-solicitud";
	if (clave === "telefono") return "telefono-solicitud";
	if (clave === "iban") return "iban-solicitud";
	if (clave === "nif") return "nif-solicitud";
	return clave;
}

function obtenerMensajeError(clave) {
	if (clave === "telefono") return "El telefono debe tener formato +34 600123456 y solo numeros despues del prefijo.";
	if (clave === "fechaNacimiento") return "La fecha de nacimiento debe ser anterior a hoy.";
	if (clave === "tipoDocumento") return "Selecciona un tipo de documento.";
	if (clave === "numeroDocumento") return "El numero de documento es obligatorio.";
	if (clave === "iban") return "El IBAN es obligatorio y debe tener un formato valido.";
	if (clave === "titular") return "El titular de la cuenta es obligatorio.";
	if (clave === "nif") return "El NIF es obligatorio y debe tener un formato valido.";
	if (clave === "direccion") return "La direccion fiscal es obligatoria.";
	if (clave === "tipoArrendador") return "Selecciona el tipo de arrendador.";
	if (clave === "numPropiedades") return "El valor debe estar entre 1 y 255.";
	if (clave === "descripcion") return "La descripcion debe tener al menos 15 caracteres.";
	if (clave === "aceptaTerminos") return "Debes aceptar los terminos.";
	if (clave === "aceptaVeracidad") return "Debes aceptar la veracidad de los datos.";
	return "Campo invalido.";
}

function validarTelefono(campo) {
	var valor = String(campo.value || "").trim();
	var regex = /^\+\d{1,4} \d{6,11}$/;

	if (valor === "") {
		return 1;
	}

	if (!regex.test(valor)) {
		return 1;
	}

	return 0;
}

function validarFechaNacimiento(campo) {
	var valor = String(campo.value || "").trim();
	if (valor === "") {
		return 1;
	}

	var hoy = new Date();
	hoy.setHours(0, 0, 0, 0);
	var fecha = new Date(valor + "T00:00:00");

	if (isNaN(fecha.getTime()) || fecha >= hoy) {
		return 1;
	}

	return 0;
}

function validarSelectObligatorio(campo) {
	return String(campo.value || "").trim() === "" ? 1 : 0;
}

function validarTextoObligatorio(campo) {
	var valor = String(campo.value || "").trim();
	return valor === "" ? 1 : 0;
}

function validarIban(campo) {
	var valor = String(campo.value || "").replace(/\s+/g, "").toUpperCase();
	var regex = /^[A-Z]{2}[0-9]{2}[0-9]{10,30}$/;

	if (valor === "") {
		return 1;
	}

	if (!regex.test(valor)) {
		return 1;
	}

	return 0;
}

function validarNif(campo) {
	var valor = String(campo.value || "").trim().toUpperCase();
	var regex = /^[A-Z0-9\-]{5,20}$/;

	if (valor === "") {
		return 1;
	}

	if (!regex.test(valor)) {
		return 1;
	}

	return 0;
}

function validarNumeroPropiedades(campo) {
	var valor = String(campo.value || "").trim();
	var numero = parseInt(valor, 10);

	if (valor === "" || isNaN(numero) || numero < 1 || numero > 255) {
		return 1;
	}

	return 0;
}

function validarDescripcion(campo) {
	var valor = String(campo.value || "").trim();
	return valor.length >= 15 ? 0 : 1;
}

function validarCheckboxObligatorio(campo) {
	return campo.checked ? 0 : 1;
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

function actualizarEstadoBoton(habilitar) {
	if (!botonEnviarSolicitud) {
		return;
	}

	botonEnviarSolicitud.disabled = !habilitar;
	if (habilitar) {
		botonEnviarSolicitud.classList.remove("btn-login-desabilitado");
	} else {
		botonEnviarSolicitud.classList.add("btn-login-desabilitado");
	}
}
