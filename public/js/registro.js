window.onload = () => {
    iniciarValidacionCrearUsuario();
};

function iniciarValidacionCrearUsuario() {
    // --- REFERENCIAS DOM ---
    const contenedor = document.getElementById("mainContainer");
    const cara = document.getElementById("face-group");

    // Mensajes de error/estado
    const errorNombre = document.getElementById("error-nombre");
    const errorEmail = document.getElementById("error-email");
    const errorTelefono = document.getElementById("error-telefono");
    const errorPassword = document.getElementById("error-password");
    const errorPasswordConfirmacion = document.getElementById("error-password-confirmation");
    const disponibilidadEmail = document.getElementById("disponibilidad-email");
    const disponibilidadTelefono = document.getElementById("disponibilidad-telefono");

    // Inputs
    const entradaNombre = document.getElementById("nombre-usuario");
    const entradaEmail = document.getElementById("email-usuario");
    const entradaTelefono = document.getElementById("telefono-usuario");
    const entradaPassword = document.getElementById("password-usuario");
    const entradaPasswordConfirmacion = document.getElementById("password-confirmation-usuario");
    const botonEnviar = document.getElementById("boton-enviar");

    // Botones de visibilidad (Iconos de Ojo)
    const verPassword = document.getElementById("ver-password");
    const verPasswordConfirmacion = document.getElementById("ver-password-confirmacion");

    // SVG Icons
    const svgOjoAbierto = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
    </svg>`;
    const svgOjoCerrado = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash" viewBox="0 0 16 16">
        <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
        <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
        <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
    </svg>`;

    // --- VARIABLES DE ESTADO ---
    let emailDisponible = false;
    let telefonoDisponible = false;
    let temporizadorEmail = null;
    let temporizadorTelefono = null;

    if (!entradaNombre || !cara) return;

    // --- FUNCIONES DE LA MASCOTA (YETI) ---

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

    // --- FUNCIONES DE VALIDACIÓN ---

    function comprobarBoton() {
        const nombre = entradaNombre.value.trim();
        const email = entradaEmail.value.trim();
        const telefono = entradaTelefono ? entradaTelefono.value.trim() : "";
        const password = entradaPassword.value.trim();
        const passwordConfirmacion = entradaPasswordConfirmacion.value.trim();
        const emailFormato = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const regexTel = /^\+\d{1,4} \d{6,11}$/;

        let nombreValido = nombre !== "" && nombre.length >= 3;
        let emailValido = email !== "" && emailFormato.test(email) && emailDisponible;
        let passwordValido = password !== "" && password.length >= 6;
        let passwordConfirmacionValido = passwordConfirmacion !== "" && password === passwordConfirmacion;
        let telefonoValido = telefono !== "" && regexTel.test(telefono) && telefonoDisponible;

        if (nombreValido && emailValido && passwordValido && passwordConfirmacionValido && telefonoValido) {
            botonEnviar.disabled = false;
            botonEnviar.classList.remove("btn-login-desabilitado");
        } else {
            botonEnviar.disabled = true;
            botonEnviar.classList.add("btn-login-desabilitado");
        }
    }

    function comprobarNombre() {
        const valor = entradaNombre.value.trim();
        if (valor === "") {
            errorNombre.innerText = "El nombre no puede estar vacío.";
        } else if (valor.length < 3) {
            errorNombre.innerText = "El nombre tiene que tener minimo 3 caracteres.";
        } else {
            errorNombre.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarEmail() {
        const valor = entradaEmail.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (valor === "") {
            errorEmail.innerText = "El correo electrónico es obligatorio.";
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
                        errorEmail.innerText = "";
                        disponibilidadEmail.innerText = "Disponible.";
                        emailDisponible = true;
                    } else {
                        disponibilidadEmail.innerText = "";
                        errorEmail.innerText = "Ya está en uso.";
                        emailDisponible = false;
                    }
                    comprobarBoton();
                })
                .catch(err => console.error("Error comprobando email:", err));
        }, 300);
    }

    function comprobarTelefono() {
        if (!entradaTelefono || !errorTelefono || !disponibilidadTelefono) return;
        const valor = entradaTelefono.value.trim();
        const regexTel = /^\+\d{1,4} \d{6,11}$/;

        if (valor === "") {
            errorTelefono.innerText = "El teléfono es obligatorio.";
            disponibilidadTelefono.innerText = "";
            telefonoDisponible = false;
            comprobarBoton();
            return;
        }

        if (!regexTel.test(valor)) {
            errorTelefono.innerText = "Formato: +34 600123456 (Prefijo + Espacio + 6 a 11 dígitos)";
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
                        errorTelefono.innerText = "";
                        disponibilidadTelefono.innerText = "Disponible.";
                        telefonoDisponible = true;
                    } else {
                        disponibilidadTelefono.innerText = "";
                        errorTelefono.innerText = "Ya está en uso.";
                        telefonoDisponible = false;
                    }
                    comprobarBoton();
                })
                .catch(err => console.error("Error comprobando teléfono:", err));
        }, 300);
    }

    function comprobarPassword() {
        const valor = entradaPassword.value.trim();
        if (valor === "") {
            errorPassword.innerText = "La contraseña es obligatoria.";
        } else if (valor.length < 6) {
            errorPassword.innerText = "Mínimo 6 caracteres.";
        } else {
            errorPassword.innerText = "";
        }
        if (entradaPasswordConfirmacion.value.trim() !== "") comprobarPasswordConfirmacion();
        comprobarBoton();
    }

    function comprobarPasswordConfirmacion() {
        const p1 = entradaPassword.value.trim();
        const p2 = entradaPasswordConfirmacion.value.trim();
        if (p2 === "") {
            errorPasswordConfirmacion.innerText = "Debes confirmar la contraseña.";
        } else if (p1 !== p2) {
            errorPasswordConfirmacion.innerText = "Las contraseñas no coinciden.";
        } else {
            errorPasswordConfirmacion.innerText = "";
        }
        comprobarBoton();
    }

    // --- ASIGNACIÓN DE EVENTOS ---

    entradaNombre.oninput = (e) => {
        gestionarMovimiento(e.target.value);
        comprobarNombre();
    };
    entradaNombre.onblur = reiniciarCara;

    entradaEmail.oninput = (e) => {
        gestionarMovimiento(e.target.value);
        comprobarEmail();
    };
    entradaEmail.onblur = reiniciarCara;

    if (entradaTelefono) {
        entradaTelefono.oninput = (e) => {
            gestionarMovimiento(e.target.value);
            comprobarTelefono();
        };
        entradaTelefono.onblur = reiniciarCara;
    }

    entradaPassword.onfocus = () => {
        comprobarEstadoVista(entradaPassword);
        contenedor.classList.add("hiding-pass");
    };
    entradaPassword.onblur = () => {
        contenedor.classList.remove("hiding-pass");
    };
    entradaPassword.oninput = comprobarPassword;

    entradaPasswordConfirmacion.onfocus = () => {
        comprobarEstadoVista(entradaPasswordConfirmacion);
        contenedor.classList.add("hiding-pass");
    };
    entradaPasswordConfirmacion.onblur = () => {
        contenedor.classList.remove("hiding-pass");
    };
    entradaPasswordConfirmacion.oninput = comprobarPasswordConfirmacion;

    if (verPassword) {
        verPassword.onmousedown = (e) => {
            e.preventDefault();
            const esPassword = entradaPassword.type === "password";
            entradaPassword.type = esPassword ? "text" : "password";
            verPassword.innerHTML = esPassword ? svgOjoCerrado : svgOjoAbierto;
            verPassword.style.color = esPassword ? "#2d79f3" : "inherit";
            comprobarEstadoVista(entradaPassword);
            entradaPassword.focus();
        };
    }

    if (verPasswordConfirmacion) {
        verPasswordConfirmacion.onmousedown = (e) => {
            e.preventDefault();
            const esPassword = entradaPasswordConfirmacion.type === "password";
            entradaPasswordConfirmacion.type = esPassword ? "text" : "password";
            verPasswordConfirmacion.innerHTML = esPassword ? svgOjoCerrado : svgOjoAbierto;
            verPasswordConfirmacion.style.color = esPassword ? "#2d79f3" : "inherit";
            comprobarEstadoVista(entradaPasswordConfirmacion);
            entradaPasswordConfirmacion.focus();
        };
    }

    // Inicialización al cargar
    if (entradaNombre.value !== "") comprobarNombre();
    if (entradaEmail.value !== "") comprobarEmail();
    if (entradaTelefono && entradaTelefono.value !== "") comprobarTelefono();
    if (entradaPassword.value !== "") comprobarPassword();
    comprobarBoton();
}
