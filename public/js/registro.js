window.onload = () => {
    iniciarValidacionCrearUsuario();
};

function iniciarValidacionCrearUsuario() {
    // --- REFERENCIAS DOM ---
    const contenedor = document.getElementById("mainContainer");
    const cara = document.getElementById("face-group");
    const botonEnviar = document.getElementById("boton-enviar");

    // Inputs principales
    const entradaNombre = document.getElementById("nombre-usuario");
    const entradaEmail = document.getElementById("email-usuario");
    const entradaTelefono = document.getElementById("telefono-usuario");
    const entradaPassword = document.getElementById("password-usuario");
    const entradaPasswordConfirmacion = document.getElementById("password-confirmation-usuario");
    const entradaRol = document.getElementById("rol-usuario");

    // Mensajes de error/estado
    const errorNombre = document.getElementById("error-nombre");
    const errorEmail = document.getElementById("error-email");
    const errorTelefono = document.getElementById("error-telefono");
    const errorPassword = document.getElementById("error-password");
    const errorPasswordConfirmacion = document.getElementById("error-password-confirmation");
    const disponibilidadEmail = document.getElementById("disponibilidad-email");
    const disponibilidadTelefono = document.getElementById("disponibilidad-telefono");
    const disponibilidadDni = document.getElementById("disponibilidad-dni");

    // Secciones Dinámicas
    const seccionPlanes = document.getElementById("seccion-planes");
    const seccionArrendador = document.getElementById("seccion-arrendador");
    const selectorTipoArrendador = document.getElementById("tipo-arrendador");
    const selectorTipoDocumento = document.getElementById("tipo-documento");
    const contenedorNif = document.getElementById("contenedor-nif");
    
    // Inputs Identidad (Unificado)
    const entradaDni = document.getElementById("dni-usuario");
    const errorDni = document.getElementById("error-dni");

    // Inputs Arrendador
    const entradaFechaNac = document.getElementById("fecha-nacimiento-arrendador");
    const entradaNif = document.getElementById("nif-empresa");
    const errorFechaNac = document.getElementById("error-fecha-nacimiento");
    const errorNif = document.getElementById("error-nif");

    // Botones de visibilidad (Iconos de Ojo)
    const verPassword = document.getElementById("ver-password");
    const verPasswordConfirmacion = document.getElementById("ver-password-confirmacion");

    // --- VARIABLES DE ESTADO ---
    let emailDisponible = false;
    let telefonoDisponible = false;
    let dniDisponible = false;
    let temporizadorEmail = null;
    let temporizadorTelefono = null;
    let temporizadorDni = null;

    if (!entradaNombre || !cara) return;

    // --- FUNCIONES DE APOYO ---

    const gestionarMovimiento = (valor) => {
        const movimiento = Math.min(Math.max((valor.length - 12) * 0.6, -8), 8);
        cara.style.transform = `translateX(${movimiento}px)`;
    };

    const reiniciarCara = () => {
        cara.style.transform = `translateX(0px)`;
    };

    const comprobarEstadoVista = (input) => {
        if (input.type === "text") {
            contenedor.classList.remove("peek-active");
        } else {
            contenedor.classList.add("peek-active");
        }
    };

    // --- LÓGICA DE VALIDACIÓN ---

    function comprobarBoton() {
        const nombreValido = entradaNombre.value.trim().length >= 3;
        const emailFormato = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(entradaEmail.value.trim());
        const emailValido = emailFormato && emailDisponible;
        
        const telFormato = /^\+\d{1,4} \d{6,11}$/.test(entradaTelefono.value.trim());
        const telefonoValido = telFormato && telefonoDisponible;
        
        const passwordValido = entradaPassword.value.trim().length >= 6;
        const passwordConfirmacionValido = entradaPassword.value.trim() === entradaPasswordConfirmacion.value.trim() && entradaPasswordConfirmacion.value.trim() !== "";
        
        const rol = entradaRol.value;
        const planSeleccionado = document.querySelector('input[name="plan_id"]:checked') !== null;
        
        // Validación de DNI (Obligatorio para todos)
        const dniValido = validarDniGenerico(entradaDni, errorDni, false, selectorTipoDocumento.value, false) && dniDisponible;

        let arrendadorCamposValidos = true;
        if (rol === 'arrendador') {
            const fechaValida = validarFechaNac(false);
            let nifValido = true;
            if (selectorTipoArrendador.value === 'empresa') {
                nifValido = validarNif(false);
            }
            arrendadorCamposValidos = fechaValida && nifValido;
        }

        if (nombreValido && emailValido && telefonoValido && passwordValido && passwordConfirmacionValido && planSeleccionado && dniValido && arrendadorCamposValidos) {
            botonEnviar.disabled = false;
            botonEnviar.classList.remove("btn-login-desabilitado");
        } else {
            botonEnviar.disabled = true;
            botonEnviar.classList.add("btn-login-desabilitado");
        }
    }

    function validarNombre(esBlur = false) {
        const valor = entradaNombre.value.trim();
        if (valor === "") {
            errorNombre.innerText = esBlur ? "El nombre es obligatorio." : "";
        } else if (valor.length < 3) {
            errorNombre.innerText = "Mínimo 3 caracteres.";
        } else {
            errorNombre.innerText = "";
        }
        comprobarBoton();
    }

    function validarEmail(esBlur = false) {
        const valor = entradaEmail.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (valor === "") {
            errorEmail.innerText = esBlur ? "El correo es obligatorio." : "";
            disponibilidadEmail.innerText = "";
            emailDisponible = false;
            comprobarBoton();
            return;
        }

        if (!regex.test(valor)) {
            errorEmail.innerText = "Introduce un correo válido.";
            disponibilidadEmail.innerText = "";
            emailDisponible = false;
            comprobarBoton();
            return;
        }

        errorEmail.innerText = "";
        clearTimeout(temporizadorEmail);
        temporizadorEmail = setTimeout(() => {
            fetch(`/admin/usuarios/check-email?email=${encodeURIComponent(valor)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.disponible) {
                        disponibilidadEmail.innerText = "Disponible.";
                        emailDisponible = true;
                    } else {
                        errorEmail.innerText = "Ya está en uso.";
                        disponibilidadEmail.innerText = "";
                        emailDisponible = false;
                    }
                    comprobarBoton();
                });
        }, 100);
    }

    function validarTelefono(esBlur = false) {
        const valor = entradaTelefono.value.trim();
        const regexTel = /^\+\d{1,4} \d{6,11}$/;

        if (valor === "") {
            errorTelefono.innerText = esBlur ? "El teléfono es obligatorio." : "";
            disponibilidadTelefono.innerText = "";
            telefonoDisponible = false;
            comprobarBoton();
            return;
        }

        if (!regexTel.test(valor)) {
            errorTelefono.innerText = "Formato: +34 600123456";
            disponibilidadTelefono.innerText = "";
            telefonoDisponible = false;
            comprobarBoton();
            return;
        }

        errorTelefono.innerText = "";
        clearTimeout(temporizadorTelefono);
        temporizadorTelefono = setTimeout(() => {
            fetch(`/admin/usuarios/check-telefono?telefono=${encodeURIComponent(valor)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.disponible) {
                        disponibilidadTelefono.innerText = "Disponible.";
                        telefonoDisponible = true;
                    } else {
                        errorTelefono.innerText = "Ya está en uso.";
                        disponibilidadTelefono.innerText = "";
                        telefonoDisponible = false;
                    }
                    comprobarBoton();
                });
        }, 100);
    }

    function validarPassword(esBlur = false) {
        const valor = entradaPassword.value.trim();
        if (valor === "") {
            errorPassword.innerText = esBlur ? "La contraseña es obligatoria." : "";
        } else if (valor.length < 6) {
            errorPassword.innerText = "Mínimo 6 caracteres.";
        } else {
            errorPassword.innerText = "";
        }
        validarConfirmacion(esBlur);
    }

    function validarConfirmacion(esBlur = false) {
        const p1 = entradaPassword.value.trim();
        const p2 = entradaPasswordConfirmacion.value.trim();
        if (p2 === "") {
            errorPasswordConfirmacion.innerText = esBlur ? "Debes confirmar la contraseña." : "";
        } else if (p1 !== p2) {
            errorPasswordConfirmacion.innerText = "No coinciden.";
        } else {
            errorPasswordConfirmacion.innerText = "";
        }
        comprobarBoton();
    }

    function validarDni(esBlur = false) {
        return validarDniGenerico(entradaDni, errorDni, esBlur, selectorTipoDocumento.value);
    }

    function validarDniGenerico(input, spanError, esBlur, tipoForzado = 'dni', recalcularBoton = true) {
        if (!input) return true;
        const documento = input.value.trim().toUpperCase();
        const tipo = tipoForzado;

        if (documento === "") {
            spanError.innerText = esBlur ? "El documento es obligatorio." : "";
            disponibilidadDni.innerText = "";
            dniDisponible = false;
            if (recalcularBoton) comprobarBoton();
            return false;
        }

        if (tipo === 'dni') {
            const regexDni = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/;
            if (!regexDni.test(documento)) {
                spanError.innerText = "Formato DNI inválido (8 números + letra).";
                disponibilidadDni.innerText = "";
                dniDisponible = false;
                if (recalcularBoton) comprobarBoton();
                return false;
            }
            const numero = documento.substr(0, 8);
            const letra = documento.substr(8, 1);
            const letras = "TRWAGMYFPDXBNJZSQVHLCKE";
            if (letras[numero % 23] !== letra) {
                spanError.innerText = "La letra del DNI no coincide.";
                disponibilidadDni.innerText = "";
                dniDisponible = false;
                if (recalcularBoton) comprobarBoton();
                return false;
            }
        } else {
            // NIE: Letra inicial (X, Y, Z) + 7 números + letra final
            const regexNie = /^[XYZ][0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKE]$/;
            if (!regexNie.test(documento)) {
                spanError.innerText = "Formato NIE inválido (X/Y/Z + 7 números + letra).";
                disponibilidadDni.innerText = "";
                dniDisponible = false;
                if (recalcularBoton) comprobarBoton();
                return false;
            }
            let prefijo = documento.charAt(0);
            if (prefijo === 'X') prefijo = '0';
            else if (prefijo === 'Y') prefijo = '1';
            else if (prefijo === 'Z') prefijo = '2';

            const numeroNie = prefijo + documento.substr(1, 7);
            const letraNie = documento.substr(8, 1);
            const letrasNie = "TRWAGMYFPDXBNJZSQVHLCKE";
            if (letrasNie[numeroNie % 23] !== letraNie) {
                spanError.innerText = "La letra del NIE no coincide.";
                disponibilidadDni.innerText = "";
                dniDisponible = false;
                if (recalcularBoton) comprobarBoton();
                return false;
            }
        }

        spanError.innerText = "";
        
        // Validación de Disponibilidad vía Fetch
        clearTimeout(temporizadorDni);
        temporizadorDni = setTimeout(() => {
            fetch(`/admin/usuarios/check-dni?dni=${encodeURIComponent(documento)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.disponible) {
                        disponibilidadDni.innerText = "Disponible.";
                        dniDisponible = true;
                    } else {
                        spanError.innerText = "Este documento ya está registrado.";
                        disponibilidadDni.innerText = "";
                        dniDisponible = false;
                    }
                    if (recalcularBoton) comprobarBoton();
                });
        }, 150);

        return true;
    }

    function validarFechaNac(esBlur = false) {
        const fecha = entradaFechaNac.value;
        if (fecha === "") {
            errorFechaNac.innerText = esBlur ? "La fecha es obligatoria." : "";
            return false;
        }
        const hoy = new Date();
        const cumple = new Date(fecha);
        let edad = hoy.getFullYear() - cumple.getFullYear();
        const m = hoy.getMonth() - cumple.getMonth();
        if (m < 0 || (m === 0 && hoy.getDate() < cumple.getDate())) {
            edad--;
        }
        if (edad < 18) {
            errorFechaNac.innerText = "Debes ser mayor de edad (+18).";
            return false;
        }
        errorFechaNac.innerText = "";
        return true;
    }

    function validarNif(esBlur = false) {
        const nif = entradaNif.value.trim().toUpperCase();
        if (nif === "") {
            errorNif.innerText = esBlur ? "El NIF es obligatorio." : "";
            return false;
        }
        const regexNif = /^[ABCDEFGHJNPQRSUVW][0-9]{7}[0-9A-J]$/;
        if (!regexNif.test(nif)) {
            errorNif.innerText = "Formato de NIF inválido.";
            return false;
        }
        errorNif.innerText = "";
        return true;
    }

    function actualizarInterfazPorRol() {
        const rol = entradaRol.value;
        seccionPlanes.style.display = "block";
        seccionArrendador.style.display = (rol === "arrendador") ? "block" : "none";
        
        document.querySelectorAll(".card-plan-wrapper").forEach(card => {
            if (card.dataset.rol === rol) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
                const input = card.querySelector('input');
                if (input.checked) {
                    input.checked = false;
                    const label = card.querySelector('.card-plan');
                    label.style.borderColor = "#2a2a2a";
                    label.style.backgroundColor = "transparent";
                }
            }
        });
        comprobarBoton();
    }

    function gestionarTipoArrendador() {
        contenedorNif.style.display = (selectorTipoArrendador.value === 'empresa') ? 'block' : 'none';
        comprobarBoton();
    }

    function gestionarTipoDocumento() {
        const tipo = selectorTipoDocumento.value;
        entradaDni.placeholder = tipo.toUpperCase();
        document.getElementById("label-documento").innerText = "Número de " + tipo.toUpperCase();
        validarDni(false);
    }

    // --- ASIGNACIÓN DE EVENTOS ---

    entradaNombre.oninput = (e) => { gestionarMovimiento(e.target.value); validarNombre(false); };
    entradaNombre.onblur = () => { reiniciarCara(); validarNombre(true); };

    entradaEmail.oninput = (e) => { gestionarMovimiento(e.target.value); validarEmail(false); };
    entradaEmail.onblur = () => { reiniciarCara(); validarEmail(true); };

    entradaTelefono.oninput = (e) => { gestionarMovimiento(e.target.value); validarTelefono(false); };
    entradaTelefono.onblur = () => { reiniciarCara(); validarTelefono(true); };

    entradaPassword.onfocus = () => { comprobarEstadoVista(entradaPassword); contenedor.classList.add("hiding-pass"); };
    entradaPassword.oninput = () => { validarPassword(false); };
    entradaPassword.onblur = () => { contenedor.classList.remove("hiding-pass"); validarPassword(true); };

    entradaPasswordConfirmacion.onfocus = () => { comprobarEstadoVista(entradaPasswordConfirmacion); contenedor.classList.add("hiding-pass"); };
    entradaPasswordConfirmacion.oninput = () => { validarConfirmacion(false); };
    entradaPasswordConfirmacion.onblur = () => { contenedor.classList.remove("hiding-pass"); validarConfirmacion(true); };

    entradaRol.onchange = actualizarInterfazPorRol;
    selectorTipoArrendador.onchange = gestionarTipoArrendador;
    selectorTipoDocumento.onchange = gestionarTipoDocumento;
    
    if (entradaDni) {
        entradaDni.oninput = () => { validarDniGenerico(entradaDni, errorDni, false, selectorTipoDocumento.value); comprobarBoton(); };
        entradaDni.onblur = () => { validarDniGenerico(entradaDni, errorDni, true, selectorTipoDocumento.value); comprobarBoton(); };
    }
    if (entradaFechaNac) {
        entradaFechaNac.oninput = () => { validarFechaNac(false); comprobarBoton(); };
        entradaFechaNac.onblur = () => { validarFechaNac(true); comprobarBoton(); };
    }
    if (entradaNif) {
        entradaNif.oninput = () => { validarNif(false); comprobarBoton(); };
        entradaNif.onblur = () => { validarNif(true); comprobarBoton(); };
    }

    document.querySelectorAll(".card-plan").forEach(label => {
        label.onclick = () => {
            document.querySelectorAll(".card-plan").forEach(l => {
                l.style.borderColor = "#2a2a2a";
                l.style.backgroundColor = "transparent";
            });
            label.style.borderColor = "#035498";
            label.style.backgroundColor = "rgba(3, 84, 152, 0.1)";
            setTimeout(comprobarBoton, 10);
        };
    });

    const svgOjoAbierto = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" /><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" /></svg>`;
    const svgOjoCerrado = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/><path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/></svg>`;

    verPassword.onmousedown = (e) => {
        e.preventDefault();
        const esPassword = entradaPassword.type === "password";
        entradaPassword.type = esPassword ? "text" : "password";
        verPassword.innerHTML = esPassword ? svgOjoCerrado : svgOjoAbierto;
        comprobarEstadoVista(entradaPassword);
        entradaPassword.focus();
    };

    verPasswordConfirmacion.onmousedown = (e) => {
        e.preventDefault();
        const esPassword = entradaPasswordConfirmacion.type === "password";
        entradaPasswordConfirmacion.type = esPassword ? "text" : "password";
        verPasswordConfirmacion.innerHTML = esPassword ? svgOjoCerrado : svgOjoAbierto;
        comprobarEstadoVista(entradaPasswordConfirmacion);
        entradaPasswordConfirmacion.focus();
    };

    // Inicializar interfaz
    actualizarInterfazPorRol();
    gestionarTipoArrendador();
    validarEmail(false);
    validarTelefono(false);
    comprobarBoton();
}
