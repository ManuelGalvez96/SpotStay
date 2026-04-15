document.addEventListener("DOMContentLoaded", function () {
    iniciarValidacionCrearUsuario();
});

function iniciarValidacionCrearUsuario() {
    // Referencias a mensajes de error/estado (siguiendo plantilla de norma global)
    const eNombre = document.getElementById("error-nombre");
    const eEmail = document.getElementById("error-email");
    const ePassword = document.getElementById("error-password");
    const ePasswordConfirmation = document.getElementById("error-password-confirmation");
    const sEmail = document.getElementById("disponibilidad-email");

    // Referencias a inputs
    const nombreInput = document.getElementById("nombre-usuario");
    const emailInput = document.getElementById("email-usuario");
    const passwordInput = document.getElementById("password-usuario");
    const passwordConfirmationInput = document.getElementById("password-confirmation-usuario");
    const botonEnviar = document.getElementById("boton-enviar");

    // Referencias a iconos de visibilidad (funcionalidad extra solicitada)
    const togglePassword = document.getElementById("toggle-password");
    const togglePasswordConfirmation = document.getElementById("toggle-password-confirmation");

    // Variables para debounce y disponibilidad
    let timeoutEmail = null;
    let emailDisponible = false;

    if (!nombreInput) return;

    // Listeners (siguiendo plantilla de norma global)
    nombreInput.oninput = comprobarNombre;
    nombreInput.onblur = comprobarNombre;

    emailInput.oninput = () => {
        clearTimeout(timeoutEmail);
        timeoutEmail = setTimeout(comprobarEmail, 100); // Debounce de 100ms según norma
    };
    emailInput.onblur = comprobarEmail;

    passwordInput.oninput = comprobarPassword;
    passwordInput.onblur = comprobarPassword;

    passwordConfirmationInput.oninput = comprobarPasswordConfirmation;
    passwordConfirmationInput.onblur = comprobarPasswordConfirmation;

    // Lógica independiente para mostrar/ocultar contraseñas
    if (togglePassword) {
        togglePassword.onclick = () => {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
        };
    }
    if (togglePasswordConfirmation) {
        togglePasswordConfirmation.onclick = () => {
            const type = passwordConfirmationInput.getAttribute("type") === "password" ? "text" : "password";
            passwordConfirmationInput.setAttribute("type", type);
        };
    }

    function comprobarBoton() {
        const nombre = nombreInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const passwordConfirmation = passwordConfirmationInput.value.trim();

        const emailFormato = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        let nombreValido = nombre !== "" && nombre.length >= 2; // La norma dice >= 2 en la descripción, 3 en la función
        let emailValido = email !== "" && emailFormato.test(email) && emailDisponible;
        let passwordValido = password !== "" && password.length >= 6;
        let passwordConfirmationValido = passwordConfirmation !== "" && password === passwordConfirmation;

        if (nombreValido && emailValido && passwordValido && passwordConfirmationValido) {
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
            comprobarBoton();
            return;
        }
        if (valor.length < 3) { // Usamos 3 como en el ejemplo de la norma
            eNombre.innerText = "El nombre tiene que tener minimo 3 caracteres.";
            comprobarBoton();
            return;
        }
        eNombre.innerText = "";
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

        // Petición al servidor (siguiendo lógica de plantilla)
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
                    // Bloqueo fulminante
                    botonEnviar.disabled = true;
                    botonEnviar.classList.add("btn-login-desabilitado");
                }
                comprobarBoton();
            })
            .catch(err => {
                console.error("Error comprobando email:", err);
                emailDisponible = false; // Por seguridad en caso de error de red
                comprobarBoton();
            });
    }


    function comprobarPassword() {
        const valor = passwordInput.value.trim();
        if (valor === "") {
            ePassword.innerText = "La contraseña es obligatoria.";
            if (passwordConfirmationInput.value.trim() !== "") comprobarPasswordConfirmation();
            comprobarBoton();
            return;
        }
        if (valor.length < 6) {
            ePassword.innerText = "Mínimo 6 caracteres.";
            if (passwordConfirmationInput.value.trim() !== "") comprobarPasswordConfirmation();
            comprobarBoton();
            return;
        }
        ePassword.innerText = "";
        if (passwordConfirmationInput.value.trim() !== "") comprobarPasswordConfirmation();
        comprobarBoton();
    }

    function comprobarPasswordConfirmation() {
        const p1 = passwordInput.value.trim();
        const p2 = passwordConfirmationInput.value.trim();
        if (p2 === "") {
            ePasswordConfirmation.innerText = "Debes confirmar la contraseña.";
            comprobarBoton();
            return;
        }
        if (p1 !== p2) {
            ePasswordConfirmation.innerText = "Las contraseñas no coinciden.";
            comprobarBoton();
            return;
        }
        ePasswordConfirmation.innerText = "";
        comprobarBoton();
    }

    // Inicialización al cargar (por si Laravel devuelve valores con errored)
    if (nombreInput.value !== "") comprobarNombre();
    if (emailInput.value !== "") comprobarEmail();
    if (passwordInput.value !== "") comprobarPassword();
    comprobarBoton();
}
