document.addEventListener("DOMContentLoaded", () => {
    iniciarValidacionLogin();
});

function iniciarValidacionLogin() {
    // Referencias a mensajes de error
    const eEmail = document.getElementById("error-email");
    const ePassword = document.getElementById("error-password");

    // Referencias a inputs
    const emailInput = document.getElementById("email-usuario");
    const passwordInput = document.getElementById("password-usuario");
    const botonEnviar = document.getElementById("boton-enviar");
    const togglePassword = document.getElementById("toggle-password");

    if (!emailInput || !passwordInput) return;

    // Listeners
    emailInput.oninput = comprobarEmail;
    emailInput.onblur = comprobarEmail;

    passwordInput.oninput = comprobarPassword;
    passwordInput.onblur = comprobarPassword;

    // Funcionalidad del ojo (ver/ocultar contraseña)
    if (togglePassword) {
        togglePassword.onclick = () => {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            
            // Opcional: Podríamos cambiar el icono aquí si tuviéramos dos SVGs, 
            // pero mantendremos la lógica simple de cambio de tipo solicitada.
            togglePassword.style.color = type === "text" ? "#2d79f3" : "inherit";
        };
    }

    function comprobarBoton() {
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const emailFormato = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        let emailValido = email !== "" && emailFormato.test(email);
        let passwordValido = password !== "" && password.length >= 6;

        if (emailValido && passwordValido) {
            botonEnviar.disabled = false;
            botonEnviar.classList.remove("btn-login-deshabilitado");
        } else {
            botonEnviar.disabled = true;
            botonEnviar.classList.add("btn-login-deshabilitado");
        }
    }

    function comprobarEmail() {
        const valor = emailInput.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (valor === "") {
            eEmail.innerText = "El correo electrónico es obligatorio.";
            comprobarBoton();
            return;
        }

        if (!regex.test(valor)) {
            eEmail.innerText = "Introduce un correo válido.";
            comprobarBoton();
            return;
        }

        eEmail.innerText = "";
        comprobarBoton();
    }

    function comprobarPassword() {
        const valor = passwordInput.value.trim();

        if (valor === "") {
            ePassword.innerText = "La contraseña es obligatoria.";
            comprobarBoton();
            return;
        }

        if (valor.length < 6) {
            ePassword.innerText = "Mínimo 6 caracteres.";
            comprobarBoton();
            return;
        }

        ePassword.innerText = "";
        comprobarBoton();
    }

    // Validar inicialmente (por si hay valores previos del navegador o old('email'))
    if (emailInput.value !== "") comprobarEmail();
    if (passwordInput.value !== "") comprobarPassword();
    
    comprobarBoton();
}
