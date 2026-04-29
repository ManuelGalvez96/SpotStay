window.onload = () => {
    // --- REFERENCIAS DOM ---
    const contenedor = document.getElementById("mainContainer");
    const cara = document.getElementById("face-group");
    const entradaEmail = document.getElementById("email-login");
    const entradaPassword = document.getElementById("pass");
    const botonEnviar = document.getElementById("boton-enviar");
    const verPassword = document.getElementById("ver-password");

    // Mensajes de error
    const errorEmail = document.getElementById("error-email");
    const errorPassword = document.getElementById("error-password");

    // Tiempos para debounce
    let temporizadorEmail = null;

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

    if (!entradaEmail || !cara) return;

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
        const email = entradaEmail.value.trim();
        const password = entradaPassword.value.trim();
        const emailFormato = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        let emailValido = email !== "" && emailFormato.test(email);
        let passwordValido = password !== "" && password.length >= 6;

        if (emailValido && passwordValido) {
            botonEnviar.disabled = false;
            botonEnviar.classList.remove("btn-login-desabilitado");
        } else {
            botonEnviar.disabled = true;
            botonEnviar.classList.add("btn-login-desabilitado");
        }
    }

    function comprobarEmail() {
        const valor = entradaEmail.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (valor === "") {
            errorEmail.innerText = "El correo electrónico es obligatorio.";
        } else if (!regex.test(valor)) {
            errorEmail.innerText = "Introduce un correo válido.";
        } else {
            errorEmail.innerText = "";
        }
        comprobarBoton();
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
        comprobarBoton();
    }

    // --- ASIGNACIÓN DE EVENTOS (ESTÁNDAR) ---

    entradaEmail.oninput = (e) => {
        gestionarMovimiento(e.target.value);
        clearTimeout(temporizadorEmail);
        temporizadorEmail = setTimeout(comprobarEmail, 100);
    };

    entradaEmail.onblur = reiniciarCara;

    entradaPassword.onfocus = () => {
        comprobarEstadoVista(entradaPassword);
        contenedor.classList.add("hiding-pass");
    };

    entradaPassword.onblur = () => {
        contenedor.classList.remove("hiding-pass");
    };

    entradaPassword.oninput = comprobarPassword;

    // Toggle de Visibilidad
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

    // Inicialización si hay valores previos
    if (entradaEmail.value !== "") comprobarEmail();
    if (entradaPassword.value !== "") comprobarPassword();
};
