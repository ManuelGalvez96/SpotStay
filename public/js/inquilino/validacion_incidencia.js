/**
 * Validación JavaScript para el formulario de reporte de incidencias.
 * Sigue la plantilla estándar del proyecto SpotStay.
 */

window.onload = () => {
    iniciarValidacionIncidencia();
};

function iniciarValidacionIncidencia() {
    // Referencias a mensajes de error
    const errorTitulo = document.getElementById("error-titulo");
    const errorCategoria = document.getElementById("error-categoria");
    const errorPrioridad = document.getElementById("error-prioridad");
    const errorDescripcion = document.getElementById("error-descripcion");

    // Referencias a inputs
    const entradaTitulo = document.getElementById("titulo-incidencia");
    const entradaCategoria = document.getElementById("categoria-incidencia");
    const entradaPrioridad = document.getElementById("prioridad-incidencia");
    const entradaDescripcion = document.getElementById("descripcion-incidencia");
    const botonEnviar = document.getElementById("boton-enviar");

    // Si no estamos en la página correcta, salimos
    if (!entradaTitulo || !botonEnviar) return;

    // Asignación directa de eventos (oninput, onchange, onblur)
    entradaTitulo.oninput = comprobarTitulo;
    entradaTitulo.onblur = comprobarTitulo;

    entradaCategoria.onchange = comprobarCategoria;
    entradaCategoria.onblur = comprobarCategoria;

    entradaPrioridad.onchange = comprobarPrioridad;
    entradaPrioridad.onblur = comprobarPrioridad;

    entradaDescripcion.oninput = comprobarDescripcion;
    entradaDescripcion.onblur = comprobarDescripcion;

    /**
     * Comprueba el estado general del formulario para habilitar o deshabilitar el botón de envío.
     */
    function comprobarBoton() {
        const titulo = entradaTitulo.value.trim();
        const categoria = entradaCategoria.value;
        const prioridad = entradaPrioridad.value;
        const descripcion = entradaDescripcion.value.trim();

        let tituloValido = titulo !== "" && titulo.length >= 5;
        let categoriaValida = categoria !== "";
        let prioridadValida = prioridad !== "";
        let descripcionValida = descripcion !== "" && descripcion.length >= 15;

        if (tituloValido && categoriaValida && prioridadValida && descripcionValida) {
            botonEnviar.disabled = false;
            botonEnviar.classList.remove("btn-login-desabilitado");
        } else {
            botonEnviar.disabled = true;
            botonEnviar.classList.add("btn-login-desabilitado");
        }
    }

    /**
     * Valida el campo Título
     */
    function comprobarTitulo() {
        const valor = entradaTitulo.value.trim();
        if (valor === "") {
            errorTitulo.innerText = "El título es obligatorio.";
            comprobarBoton();
            return;
        }
        if (valor.length < 5) {
            errorTitulo.innerText = "Mínimo 5 caracteres.";
            comprobarBoton();
            return;
        }
        errorTitulo.innerText = "";
        comprobarBoton();
    }

    /**
     * Valida el campo Categoría
     */
    function comprobarCategoria() {
        if (entradaCategoria.value === "") {
            errorCategoria.innerText = "Selecciona una categoría.";
            comprobarBoton();
            return;
        }
        errorCategoria.innerText = "";
        comprobarBoton();
    }

    /**
     * Valida el campo Prioridad
     */
    function comprobarPrioridad() {
        if (entradaPrioridad.value === "") {
            errorPrioridad.innerText = "Selecciona la prioridad.";
            comprobarBoton();
            return;
        }
        errorPrioridad.innerText = "";
        comprobarBoton();
    }

    /**
     * Valida el campo Descripción
     */
    function comprobarDescripcion() {
        const valor = entradaDescripcion.value.trim();
        if (valor === "") {
            errorDescripcion.innerText = "La descripción es obligatoria.";
            comprobarBoton();
            return;
        }
        if (valor.length < 15) {
            errorDescripcion.innerText = "Danos más detalles (mínimo 15 carac.).";
            comprobarBoton();
            return;
        }
        errorDescripcion.innerText = "";
        comprobarBoton();
    }

    // Ejecutar comprobación inicial
    comprobarBoton();

    // Limpieza al cerrar el modal
    const elementoModal = document.getElementById('modalReportar');
    if (elementoModal) {
        // En Bootstrap 5, 'hidden.bs.modal' es un evento personalizado.
        // Lo asignamos mediante addEventListener solo porque Bootstrap lo requiere para sus eventos internos,
        // pero procuramos usar lógica limpia.
        elementoModal.addEventListener('hidden.bs.modal', () => {
            entradaTitulo.value = "";
            entradaCategoria.value = "";
            entradaPrioridad.value = "";
            entradaDescripcion.value = "";

            errorTitulo.innerText = "";
            errorCategoria.innerText = "";
            errorPrioridad.innerText = "";
            errorDescripcion.innerText = "";

            comprobarBoton();
        });
    }
}
