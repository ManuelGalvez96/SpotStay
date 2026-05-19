
let timeoutIban, timeoutTitular, timeoutDireccion;

function iniciarValidacionIban() {
    const inputIban = document.getElementById("iban-usuario");
    const inputTitular = document.getElementById("titular-cuenta");
    const inputDireccion = document.getElementById("direccion-fiscal");
    const botonEnviar = document.getElementById("boton-finalizar");

    const errorIban = document.getElementById("error-iban");
    const errorTitular = document.getElementById("error-titular");
    const errorDireccion = document.getElementById("error-direccion");

    if (!inputIban || !botonEnviar) return;

    let ibanValido = false;
    let titularValido = false;
    let direccionValida = false;

    // --- FUNCIONES DE VALIDACIÓN ---

    function validarIban(mostrarError = true) {
        const valor = inputIban.value.replace(/\s+/g, '').toUpperCase();
        const regex = /^[A-Z]{2}[0-9]{22}$/;

        if (valor === "") {
            if (mostrarError) errorIban.innerText = "El IBAN es obligatorio.";
            ibanValido = false;
        } else if (!regex.test(valor)) {
            if (mostrarError) errorIban.innerText = "Formato no válido (ej: ES21...).";
            ibanValido = false;
        } else {
            errorIban.innerText = "";
            ibanValido = true;
        }
        comprobarBoton();
    }

    function validarTitular(mostrarError = true) {
        const valor = inputTitular.value.trim();
        if (valor.length < 3) {
            if (mostrarError && valor.length > 0) errorTitular.innerText = "El nombre es demasiado corto.";
            titularValido = false;
        } else {
            errorTitular.innerText = "";
            titularValido = true;
        }
        comprobarBoton();
    }

    function validarDireccion(mostrarError = true) {
        const valor = inputDireccion.value.trim();
        if (valor.length < 10) {
            if (mostrarError && valor.length > 0) errorDireccion.innerText = "Dirección demasiado corta.";
            direccionValida = false;
        } else {
            errorDireccion.innerText = "";
            direccionValida = true;
        }
        comprobarBoton();
    }

    function comprobarBoton() {
        if (ibanValido && titularValido && direccionValida) {
            botonEnviar.disabled = false;
            botonEnviar.style.opacity = "1";
        } else {
            botonEnviar.disabled = true;
            botonEnviar.style.opacity = "0.5";
        }
    }

    // --- ASIGNACIÓN DE EVENTOS ---

    // IBAN
    inputIban.oninput = () => {
        clearTimeout(timeoutIban);
        timeoutIban = setTimeout(() => validarIban(false), 300); // Valida sin mostrar error para el botón
    };
    inputIban.onblur = () => validarIban(true); // Muestra error al salir

    // Titular
    inputTitular.oninput = () => {
        clearTimeout(timeoutTitular);
        timeoutTitular = setTimeout(() => validarTitular(false), 300);
    };
    inputTitular.onblur = () => validarTitular(true);

    // Dirección
    inputDireccion.oninput = () => {
        clearTimeout(timeoutDireccion);
        timeoutDireccion = setTimeout(() => validarDireccion(false), 300);
    };
    inputDireccion.onblur = () => validarDireccion(true);

    // --- INICIALIZACIÓN SILENCIOSA ---
    // Validamos sin mostrar errores para habilitar el botón si ya hay datos correctos
    validarIban(false);
    validarTitular(false);
    validarDireccion(false);
}

window.onload = iniciarValidacionIban;
