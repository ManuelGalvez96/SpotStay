document.addEventListener("DOMContentLoaded", function () {
    iniciarValidacionCrearUsuario();
});

function iniciarValidacionCrearUsuario() {
    // --- REFERENCIAS DOM ---
    const container = document.getElementById("mainContainer");
    const face = document.getElementById("face-group");

    // Mensajes de error/estado
    const eNombre = document.getElementById("error-nombre");
    const eEmail = document.getElementById("error-email");
    const eTelefono = document.getElementById("error-telefono");
    const ePassword = document.getElementById("error-password");
    const ePasswordConfirmation = document.getElementById("error-password-confirmation");
    const sEmail = document.getElementById("disponibilidad-email");
    const sTelefono = document.getElementById("disponibilidad-telefono");

    // Inputs
    const nombreInput = document.getElementById("nombre-usuario");
    const emailInput = document.getElementById("email-usuario");
    const telefonoInput = document.getElementById("telefono-usuario");
    const passwordInput = document.getElementById("password-usuario");
    const passwordConfirmationInput = document.getElementById("password-confirmation-usuario");
    const botonEnviar = document.getElementById("boton-enviar");

    // Botones de visibilidad (Iconos de Ojo)
    const verPassword = document.getElementById("ver-password");
    const verPasswordConfirmation = document.getElementById("ver-password-confirmacion");

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
    let telefonoDisponible = false; // Ahora es obligatorio, empieza en false
    let timeoutEmail = null;
    let timeoutTelefono = null;

    if (!nombreInput || !face) return;

    // --- FUNCIONES DEL YETI ---

    const handleMove = (val) => {
        const move = Math.min(Math.max((val.length - 12) * 0.6, -8), 8);
        face.style.transform = `translateX(${move}px)`;
    };

    const resetFace = () => {
        face.style.transform = `translateX(0px)`;
    };

    const checkState = (input) => {
        if (input.type === "text") {
            container.classList.remove("peek-active");
        } else {
            container.classList.add("peek-active");
        }
    };

    // --- FUNCIONES DE VALIDACIÓN ---

    function comprobarBoton() {
        const nombre = nombreInput.value.trim();
        const email = emailInput.value.trim();
        const telefono = telefonoInput ? telefonoInput.value.trim() : "";
        const password = passwordInput.value.trim();
        const passwordConfirmation = passwordConfirmationInput.value.trim();
        const emailFormato = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const regexTel = /^\+\d{1,4} \d{6,11}$/;

        let nombreValido = nombre !== "" && nombre.length >= 3;
        let emailValido = email !== "" && emailFormato.test(email) && emailDisponible;
        let passwordValido = password !== "" && password.length >= 6;
        let passwordConfirmationValido = passwordConfirmation !== "" && password === passwordConfirmation;

        // Teléfono OBLIGATORIO: debe cumplir el formato Y estar disponible
        let telefonoValido = telefono !== "" && regexTel.test(telefono) && telefonoDisponible;

        if (nombreValido && emailValido && passwordValido && passwordConfirmationValido && telefonoValido) {
            botonEnviar.disabled = false;
            botonEnviar.classList.remove("btn-login-desabilitado");
        } else {
            botonEnviar.disabled = true;
            botonEnviar.classList.add("btn-login-desabilitado");
        }
    }

    function comprobarNombre() {
        const valor = nombreInput.value.trim();
        if (valor === "") {
            eNombre.innerText = "El nombre no puede estar vacío.";
        } else if (valor.length < 3) {
            eNombre.innerText = "El nombre tiene que tener minimo 3 caracteres.";
        } else {
            eNombre.innerText = "";
        }
        comprobarBoton();
    }

    function comprobarEmail() {
        const valor = emailInput.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (valor === "") {
            eEmail.innerText = "El correo electrónico es obligatorio.";
            sEmail.innerText = "";
            emailDisponible = false;
            comprobarBoton();
            return;
        }

        if (!regex.test(valor)) {
            eEmail.innerText = "Introduce un correo válido.";
            sEmail.innerText = "";
            emailDisponible = false;
            comprobarBoton();
            return;
        }

        eEmail.innerText = "";
        clearTimeout(timeoutEmail);
        timeoutEmail = setTimeout(() => {
            fetch(`/admin/usuarios/check-email?email=${encodeURIComponent(valor)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.disponible) {
                        eEmail.innerText = "";
                        sEmail.innerText = "Disponible.";
                        emailDisponible = true;
                    } else {
                        sEmail.innerText = "";
                        eEmail.innerText = "Ya está en uso.";
                        emailDisponible = false;
                    }
                    comprobarBoton();
                })
                .catch(err => console.error("Error comprobando email:", err));
        }, 300);
    }

    function comprobarTelefono() {
        if (!telefonoInput || !eTelefono || !sTelefono) return;
        const valor = telefonoInput.value.trim();
        const regexTel = /^\+\d{1,4} \d{6,11}$/;

        if (valor === "") {
            eTelefono.innerText = "El teléfono es obligatorio.";
            sTelefono.innerText = "";
            telefonoDisponible = false;
            comprobarBoton();
            return;
        }

        if (!regexTel.test(valor)) {
            eTelefono.innerText = "Formato: +34 600123456 (Prefijo + Espacio + 6 a 11 dígitos)";
            sTelefono.innerText = "";
            telefonoDisponible = false;
            comprobarBoton();
            return;
        }

        eTelefono.innerText = "";
        clearTimeout(timeoutTelefono);
        timeoutTelefono = setTimeout(() => {
            fetch(`/admin/usuarios/check-telefono?telefono=${encodeURIComponent(valor)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.disponible) {
                        eTelefono.innerText = "";
                        sTelefono.innerText = "Disponible.";
                        telefonoDisponible = true;
                    } else {
                        sTelefono.innerText = "";
                        eTelefono.innerText = "Ya está en uso.";
                        telefonoDisponible = false;
                    }
                    comprobarBoton();
                })
                .catch(err => console.error("Error comprobando teléfono:", err));
        }, 300);
    }

    function comprobarPassword() {
        const valor = passwordInput.value.trim();
        if (valor === "") {
            ePassword.innerText = "La contraseña es obligatoria.";
        } else if (valor.length < 6) {
            ePassword.innerText = "Mínimo 6 caracteres.";
        } else {
            ePassword.innerText = "";
        }
        if (passwordConfirmationInput.value.trim() !== "") comprobarPasswordConfirmation();
        comprobarBoton();
    }

    function comprobarPasswordConfirmation() {
        const p1 = passwordInput.value.trim();
        const p2 = passwordConfirmationInput.value.trim();
        if (p2 === "") {
            ePasswordConfirmation.innerText = "Debes confirmar la contraseña.";
        } else if (p1 !== p2) {
            ePasswordConfirmation.innerText = "Las contraseñas no coinciden.";
        } else {
            ePasswordConfirmation.innerText = "";
        }
        comprobarBoton();
    }

    // --- ASIGNACIÓN DE LISTENERS (UNIFICADO) ---

    // Nombre
    nombreInput.oninput = (e) => {
        handleMove(e.target.value);
        comprobarNombre();
    };
    nombreInput.onblur = resetFace;

    // Email
    emailInput.oninput = (e) => {
        handleMove(e.target.value);
        comprobarEmail();
    };
    emailInput.onblur = resetFace;

    // Teléfono
    if (telefonoInput) {
        telefonoInput.oninput = (e) => {
            handleMove(e.target.value);
            comprobarTelefono();
        };
        telefonoInput.onblur = resetFace;
    }

    // Password
    passwordInput.onfocus = () => {
        checkState(passwordInput);
        container.classList.add("hiding-pass");
    };
    passwordInput.onblur = () => {
        container.classList.remove("hiding-pass");
    };
    passwordInput.oninput = comprobarPassword;

    // Password Confirmation
    passwordConfirmationInput.onfocus = () => {
        checkState(passwordConfirmationInput);
        container.classList.add("hiding-pass");
    };
    passwordConfirmationInput.onblur = () => {
        container.classList.remove("hiding-pass");
    };
    passwordConfirmationInput.oninput = comprobarPasswordConfirmation;

    // Toggles de Visibilidad
    if (verPassword) {
        verPassword.onmousedown = (e) => {
            e.preventDefault();
            const esPassword = passwordInput.type === "password";
            passwordInput.type = esPassword ? "text" : "password";
            verPassword.innerHTML = esPassword ? svgOjoCerrado : svgOjoAbierto;
            verPassword.style.color = esPassword ? "#2d79f3" : "inherit";
            checkState(passwordInput);
            passwordInput.focus();
        };
    }

    if (verPasswordConfirmation) {
        verPasswordConfirmation.onmousedown = (e) => {
            e.preventDefault();
            const esPassword = passwordConfirmationInput.type === "password";
            passwordConfirmationInput.type = esPassword ? "text" : "password";
            verPasswordConfirmation.innerHTML = esPassword ? svgOjoCerrado : svgOjoAbierto;
            verPasswordConfirmation.style.color = esPassword ? "#2d79f3" : "inherit";
            checkState(passwordConfirmationInput);
            passwordConfirmationInput.focus();
        };
    }

    // Inicialización al cargar
    if (nombreInput.value !== "") comprobarNombre();
    if (emailInput.value !== "") comprobarEmail();
    if (telefonoInput && telefonoInput.value !== "") comprobarTelefono();
    if (passwordInput.value !== "") comprobarPassword();
    comprobarBoton();
}
