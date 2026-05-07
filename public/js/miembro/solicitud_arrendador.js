function iniciarAplicacionSolicitudArrendador() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciarValidacionSolicitudArrendador, { once: true });
        return;
    }

    iniciarValidacionSolicitudArrendador();
}

window.addEventListener('pageshow', function () {
    iniciarAplicacionSolicitudArrendador();
});

iniciarAplicacionSolicitudArrendador();

function iniciarValidacionSolicitudArrendador() {
    // --- REFERENCIAS DOM ---
    const formulario = document.getElementById("formulario-solicitud-arrendador");
    const botonEnviar = document.getElementById("boton-enviar-solicitud");

    // Elementos de error
    const errorTelefono = document.getElementById("error-telefono-solicitud");
    const errorFechaNacimiento = document.getElementById("error-fecha-nacimiento-solicitud");
    const errorTipoDocumento = document.getElementById("error-tipo-documento-solicitud");
    const errorNumeroDocumento = document.getElementById("error-numero-documento-solicitud");
    const errorIban = document.getElementById("error-iban-solicitud");
    const errorTitular = document.getElementById("error-titular-cuenta-solicitud");
    const errorNif = document.getElementById("error-nif-solicitud");
    const errorDireccion = document.getElementById("error-direccion-fiscal-solicitud");
    const errorTipoArrendador = document.getElementById("error-tipo-arrendador-solicitud");
    const errorNumPropiedades = document.getElementById("error-num-propiedades-previstas-solicitud");
    const errorDescripcion = document.getElementById("error-descripcion-solicitud");
    const errorAceptaTerminos = document.getElementById("error-acepta-terminos-solicitud");
    const errorAceptaVeracidad = document.getElementById("error-acepta-veracidad-solicitud");

    // Inputs
    const entradaTelefono = document.getElementById("telefono-solicitud");
    const entradaFechaNacimiento = document.getElementById("fecha-nacimiento-solicitud");
    const entradaTipoDocumento = document.getElementById("tipo-documento-solicitud");
    const entradaNumeroDocumento = document.getElementById("numero-documento-solicitud");
    const entradaIban = document.getElementById("iban-solicitud");
    const entradaTitular = document.getElementById("titular-cuenta-solicitud");
    const entradaNif = document.getElementById("nif-solicitud");
    const entradaDireccion = document.getElementById("direccion-fiscal-solicitud");
    const entradaTipoArrendador = document.getElementById("tipo-arrendador-solicitud");
    const entradaNumPropiedades = document.getElementById("num-propiedades-previstas-solicitud");
    const entradaDescripcion = document.getElementById("descripcion-solicitud");
    const entradaAceptaTerminos = document.getElementById("acepta-terminos-solicitud");
    const entradaAceptaVeracidad = document.getElementById("acepta-veracidad-solicitud");

    if (!formulario || !botonEnviar) return;

    const camposObligatorios = [
        errorTelefono,
        errorFechaNacimiento,
        errorTipoDocumento,
        errorNumeroDocumento,
        errorIban,
        errorTitular,
        errorNif,
        errorDireccion,
        errorTipoArrendador,
        errorNumPropiedades,
        errorDescripcion,
        errorAceptaTerminos,
        errorAceptaVeracidad,
        entradaTelefono,
        entradaFechaNacimiento,
        entradaTipoDocumento,
        entradaNumeroDocumento,
        entradaIban,
        entradaTitular,
        entradaNif,
        entradaDireccion,
        entradaTipoArrendador,
        entradaNumPropiedades,
        entradaDescripcion,
        entradaAceptaTerminos,
        entradaAceptaVeracidad
    ];

    if (camposObligatorios.some(function (campo) { return !campo; })) {
        console.error('No se pudo inicializar la validación de solicitud arrendador: faltan elementos del formulario.');
        return;
    }

    // --- FUNCIONES DE VALIDACIÓN ---

    function comprobarBoton() {
        const telefono = entradaTelefono.value.trim();
        const fechaNacimiento = entradaFechaNacimiento.value;
        const tipoDocumento = entradaTipoDocumento.value;
        const numeroDocumento = entradaNumeroDocumento.value.trim();
        const iban = entradaIban.value.trim();
        const titular = entradaTitular.value.trim();
        const nif = entradaNif.value.trim();
        const direccion = entradaDireccion.value.trim();
        const tipoArrendador = entradaTipoArrendador.value;
        const numPropiedades = entradaNumPropiedades.value.trim();
        const descripcion = entradaDescripcion.value.trim();
        const aceptaTerminos = entradaAceptaTerminos.checked;
        const aceptaVeracidad = entradaAceptaVeracidad.checked;

        const regexTel = /^\+\d{1,4} \d{6,11}$/;
        const regexIban = /^[A-Z]{2}\d{2}\s?[\d\s]{10,30}$/i;
        const regexNif = /^[A-Z0-9]\d{7}[A-Z0-9]$/i;

        let telefonoValido = regexTel.test(telefono);
        let fechaValida = fechaNacimiento !== "" && new Date(fechaNacimiento) < new Date();
        let tipoDocValido = tipoDocumento !== "";
        let numDocValido = numeroDocumento !== "";
        let ibanValido = regexIban.test(iban.replace(/\s/g, ""));
        let titularValido = titular !== "";
        let nifValido = regexNif.test(nif.replace(/\s/g, ""));
        let direccionValida = direccion !== "";
        let tipoArrendadorValido = tipoArrendador !== "";
        let numPropValido = numPropiedades !== "" && parseInt(numPropiedades) >= 1;
        let descValida = descripcion !== "";
        let terminosValidos = aceptaTerminos;
        let veracidadValida = aceptaVeracidad;

        if (
            telefonoValido && fechaValida && tipoDocValido && numDocValido &&
            ibanValido && titularValido && nifValido && direccionValida &&
            tipoArrendadorValido && numPropValido && descValida &&
            terminosValidos && veracidadValida
        ) {
            botonEnviar.disabled = false;
            botonEnviar.classList.remove("btn-login-desabilitado");
        } else {
            botonEnviar.disabled = true;
            botonEnviar.classList.add("btn-login-desabilitado");
        }
    }

    function comprobarTelefono() {
        const valor = entradaTelefono.value.trim();
        const regex = /^\+\d{1,4} \d{6,11}$/;

        if (valor === "") {
            errorTelefono.innerText = "El teléfono es obligatorio.";
        } else if (!regex.test(valor)) {
            errorTelefono.innerText = "Formato: +34 600123456 (Prefijo + Espacio + 6 a 11 dígitos)";
        } else {
            errorTelefono.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarFechaNacimiento() {
        const valor = entradaFechaNacimiento.value;

        if (valor === "") {
            errorFechaNacimiento.innerText = "La fecha de nacimiento es obligatoria.";
        } else {
            const hoy = new Date();
            const nacimiento = new Date(valor);
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mesDiff = hoy.getMonth() - nacimiento.getMonth();
            if (mesDiff < 0 || (mesDiff === 0 && hoy.getDate() < nacimiento.getDate())) {
                edad--;
            }

            if (edad < 18) {
                errorFechaNacimiento.innerText = "Debes tener al menos 18 años.";
            } else if (edad > 120) {
                errorFechaNacimiento.innerText = "Introduce una fecha válida.";
            } else {
                errorFechaNacimiento.innerText = "";
            }
        }
        comprobarBoton();
    }

    function comprobarTipoDocumento() {
        const valor = entradaTipoDocumento.value;

        if (valor === "") {
            errorTipoDocumento.innerText = "Selecciona un tipo de documento.";
        } else {
            errorTipoDocumento.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarNumeroDocumento() {
        const valor = entradaNumeroDocumento.value.trim();

        if (valor === "") {
            errorNumeroDocumento.innerText = "El número de documento es obligatorio.";
        } else {
            errorNumeroDocumento.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarIban() {
        const valor = entradaIban.value.trim().replace(/\s/g, "");
        const regex = /^[A-Z]{2}\d{2}\d{10,30}$/i;

        if (valor === "") {
            errorIban.innerText = "El IBAN es obligatorio.";
        } else if (!regex.test(valor)) {
            errorIban.innerText = "Introduce un IBAN válido (ej: ES12 1234 5678 9012 3456 7890)";
        } else {
            errorIban.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarTitular() {
        const valor = entradaTitular.value.trim();

        if (valor === "") {
            errorTitular.innerText = "El titular de la cuenta es obligatorio.";
        } else {
            errorTitular.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarNif() {
        const valor = entradaNif.value.trim().replace(/\s/g, "");
        const regex = /^[A-Z0-9]\d{7}[A-Z0-9]$/i;

        if (valor === "") {
            errorNif.innerText = "El NIF es obligatorio.";
        } else if (!regex.test(valor)) {
            errorNif.innerText = "Introduce un NIF válido (ej: 12345678A o B12345678)";
        } else {
            errorNif.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarDireccion() {
        const valor = entradaDireccion.value.trim();

        if (valor === "") {
            errorDireccion.innerText = "La dirección fiscal es obligatoria.";
        } else {
            errorDireccion.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarTipoArrendador() {
        const valor = entradaTipoArrendador.value;

        if (valor === "") {
            errorTipoArrendador.innerText = "Selecciona un tipo de arrendador.";
        } else {
            errorTipoArrendador.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarNumPropiedades() {
        const valor = entradaNumPropiedades.value.trim();

        if (valor === "") {
            errorNumPropiedades.innerText = "El número de propiedades es obligatorio.";
        } else if (parseInt(valor) < 1) {
            errorNumPropiedades.innerText = "Debe ser al menos 1.";
        } else {
            errorNumPropiedades.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarDescripcion() {
        const valor = entradaDescripcion.value.trim();

        if (valor === "") {
            errorDescripcion.innerText = "La descripción es obligatoria.";
        } else if (valor.length < 20) {
            errorDescripcion.innerText = "Mínimo 20 caracteres.";
        } else {
            errorDescripcion.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarAceptaTerminos() {
        if (!entradaAceptaTerminos.checked) {
            errorAceptaTerminos.innerText = "Debes aceptar los términos y condiciones.";
        } else {
            errorAceptaTerminos.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarAceptaVeracidad() {
        if (!entradaAceptaVeracidad.checked) {
            errorAceptaVeracidad.innerText = "Debes declarar que los datos son veraces.";
        } else {
            errorAceptaVeracidad.innerText = "";
        }
        comprobarBoton();
    }

    // --- ASIGNACIÓN DE EVENTOS ---

    entradaTelefono.oninput = comprobarTelefono;

    entradaFechaNacimiento.oninput = comprobarFechaNacimiento;

    entradaTipoDocumento.onchange = comprobarTipoDocumento;

    entradaNumeroDocumento.oninput = comprobarNumeroDocumento;

    entradaIban.oninput = comprobarIban;

    entradaTitular.oninput = comprobarTitular;

    entradaNif.oninput = comprobarNif;

    entradaDireccion.oninput = comprobarDireccion;

    entradaTipoArrendador.onchange = comprobarTipoArrendador;

    entradaNumPropiedades.oninput = comprobarNumPropiedades;

    entradaDescripcion.oninput = comprobarDescripcion;

    entradaAceptaTerminos.onchange = comprobarAceptaTerminos;

    entradaAceptaVeracidad.onchange = comprobarAceptaVeracidad;

    // --- ENVÍO DEL FORMULARIO ---

    formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();

        comprobarTelefono();
        comprobarFechaNacimiento();
        comprobarTipoDocumento();
        comprobarNumeroDocumento();
        comprobarIban();
        comprobarTitular();
        comprobarNif();
        comprobarDireccion();
        comprobarTipoArrendador();
        comprobarNumPropiedades();
        comprobarDescripcion();
        comprobarAceptaTerminos();
        comprobarAceptaVeracidad();

        if (botonEnviar.disabled) return;

        botonEnviar.disabled = true;
        botonEnviar.classList.add("btn-login-desabilitado");

        var csrf = "";
        var tokenInput = formulario.elements["_token"];
        if (tokenInput) {
            csrf = tokenInput.value;
        }

        var fd = new FormData(formulario);

        fetch(formulario.action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrf,
                "Accept": "application/json"
            },
            body: fd
        })
        .then(function (response) {
            var ct = response.headers.get("content-type") || "";

            if (!response.ok) {
                if (ct.indexOf("application/json") !== -1) {
                    return response.json().then(function (errData) {
                        return Promise.reject({ status: response.status, datos: errData });
                    });
                }

                return response.text().then(function (texto) {
                    return Promise.reject({ status: response.status, datos: { message: texto || "Error del servidor" } });
                });
            }

            if (ct.indexOf("application/json") !== -1) {
                return response.json();
            }

            window.location.reload();
        })
        .then(function (data) {
            if (!data) return;
            if (data.success) {
                if (typeof mostrarAlertaExito === "function") {
                    mostrarAlertaExito("Solicitud enviada", data.message || "Solicitud enviada correctamente");
                } else {
                    alert(data.message || "Solicitud enviada correctamente");
                }
                formulario.reset();
                actualizarErroresVacios();
                comprobarBoton();
            } else {
                if (typeof mostrarAlertaError === "function") {
                    mostrarAlertaError("Error", data.message || "No se pudo enviar la solicitud");
                } else {
                    alert(data.message || "No se pudo enviar la solicitud");
                }
            }
        })
        .catch(function (error) {
            var mensaje = "No se pudo enviar la solicitud. Revisa los campos.";

            if (error && error.datos) {
                if (error.datos.message) {
                    mensaje = error.datos.message;
                }

                if (error.datos.errors) {
                    actualizarErroresVacios();

                    var mapaErrores = {
                        telefono_solicitud: errorTelefono,
                        fecha_nacimiento_solicitud: errorFechaNacimiento,
                        tipo_documento_solicitud: errorTipoDocumento,
                        numero_documento_solicitud: errorNumeroDocumento,
                        iban_solicitud: errorIban,
                        titular_cuenta_solicitud: errorTitular,
                        nif_solicitud: errorNif,
                        direccion_fiscal_solicitud: errorDireccion,
                        tipo_arrendador_solicitud: errorTipoArrendador,
                        num_propiedades_previstas_solicitud: errorNumPropiedades,
                        descripcion_solicitud: errorDescripcion,
                        acepta_terminos_solicitud: errorAceptaTerminos,
                        acepta_veracidad_solicitud: errorAceptaVeracidad,
                    };

                    for (var campo in error.datos.errors) {
                        if (error.datos.errors.hasOwnProperty(campo) && mapaErrores[campo]) {
                            mapaErrores[campo].innerText = error.datos.errors[campo][0];
                        }
                    }
                }
            }

            if (typeof mostrarAlertaError === "function") {
                mostrarAlertaError("Error", mensaje);
            } else {
                alert(mensaje);
            }
        })
        .finally(function () {
            botonEnviar.disabled = false;
            botonEnviar.classList.remove("btn-login-desabilitado");
        });
    });

    function actualizarErroresVacios() {
        errorTelefono.innerText = "";
        errorFechaNacimiento.innerText = "";
        errorTipoDocumento.innerText = "";
        errorNumeroDocumento.innerText = "";
        errorIban.innerText = "";
        errorTitular.innerText = "";
        errorNif.innerText = "";
        errorDireccion.innerText = "";
        errorTipoArrendador.innerText = "";
        errorNumPropiedades.innerText = "";
        errorDescripcion.innerText = "";
        errorAceptaTerminos.innerText = "";
        errorAceptaVeracidad.innerText = "";
    }

    // Inicialización si hay valores previos (old input del servidor)
    if (entradaTelefono.value !== "") comprobarTelefono();
    if (entradaFechaNacimiento.value !== "") comprobarFechaNacimiento();
    if (entradaTipoDocumento.value !== "") comprobarTipoDocumento();
    if (entradaNumeroDocumento.value !== "") comprobarNumeroDocumento();
    if (entradaIban.value !== "") comprobarIban();
    if (entradaTitular.value !== "") comprobarTitular();
    if (entradaNif.value !== "") comprobarNif();
    if (entradaDireccion.value !== "") comprobarDireccion();
    if (entradaTipoArrendador.value !== "") comprobarTipoArrendador();
    if (entradaNumPropiedades.value !== "") comprobarNumPropiedades();
    if (entradaDescripcion.value !== "") comprobarDescripcion();
    comprobarBoton();
}
