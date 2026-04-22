/**
 * Validación JavaScript para el formulario de reporte de incidencias.
 * Sigue la plantilla estándar del proyecto SpotStay.
 */

document.addEventListener("DOMContentLoaded", () => {
    iniciarValidacionIncidencia();
});

function iniciarValidacionIncidencia() {
    // Referencias a mensajes de error
    const eTitulo = document.getElementById("error-titulo");
    const eCategoria = document.getElementById("error-categoria");
    const ePrioridad = document.getElementById("error-prioridad");
    const eDescripcion = document.getElementById("error-descripcion");

    // Referencias a inputs
    const tituloInput = document.getElementById("titulo-incidencia");
    const categoriaInput = document.getElementById("categoria-incidencia");
    const prioridadInput = document.getElementById("prioridad-incidencia");
    const descripcionInput = document.getElementById("descripcion-incidencia");
    const botonEnviar = document.getElementById("boton-enviar");

    // Si no estamos en la página correcta, salimos
    if (!tituloInput || !botonEnviar) return;

    // Listeners para validación en tiempo real (oninput) y al perder el foco (onblur)
    tituloInput.oninput = comprobarTitulo;
    tituloInput.onblur = comprobarTitulo;

    categoriaInput.onchange = comprobarCategoria;
    categoriaInput.onblur = comprobarCategoria;

    prioridadInput.onchange = comprobarPrioridad;
    prioridadInput.onblur = comprobarPrioridad;

    descripcionInput.oninput = comprobarDescripcion;
    descripcionInput.onblur = comprobarDescripcion;

    /**
     * Comprueba el estado general del formulario para habilitar o deshabilitar el botón de envío.
     */
    function comprobarBoton() {
        const titulo = tituloInput.value.trim();
        const categoria = categoriaInput.value;
        const prioridad = prioridadInput.value;
        const descripcion = descripcionInput.value.trim();

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
        const valor = tituloInput.value.trim();
        if (valor === "") {
            eTitulo.innerText = "El título es obligatorio.";
            comprobarBoton();
            return;
        }
        if (valor.length < 5) {
            eTitulo.innerText = "Mínimo 5 caracteres.";
            comprobarBoton();
            return;
        }
        eTitulo.innerText = "";
        comprobarBoton();
    }

    /**
     * Valida el campo Categoría
     */
    function comprobarCategoria() {
        if (categoriaInput.value === "") {
            eCategoria.innerText = "Selecciona una categoría.";
            comprobarBoton();
            return;
        }
        eCategoria.innerText = "";
        comprobarBoton();
    }

    /**
     * Valida el campo Prioridad
     */
    function comprobarPrioridad() {
        if (prioridadInput.value === "") {
            ePrioridad.innerText = "Selecciona la prioridad.";
            comprobarBoton();
            return;
        }
        ePrioridad.innerText = "";
        comprobarBoton();
    }

    /**
     * Valida el campo Descripción
     */
    function comprobarDescripcion() {
        const valor = descripcionInput.value.trim();
        if (valor === "") {
            eDescripcion.innerText = "La descripción es obligatoria.";
            comprobarBoton();
            return;
        }
        if (valor.length < 15) {
            eDescripcion.innerText = "Danos más detalles (mínimo 15 carac.).";
            comprobarBoton();
            return;
        }
        eDescripcion.innerText = "";
        comprobarBoton();
    }

    // Ejecutar comprobación inicial
    comprobarBoton();

    // Limpieza al cerrar el modal (Bootstrap event)
    const modalElement = document.getElementById('modalReportar');
    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', () => {
            // Vaciamos los valores de los inputs
            tituloInput.value = "";
            categoriaInput.value = "";
            prioridadInput.value = "";
            descripcionInput.value = "";

            // Limpiamos los textos de error
            eTitulo.innerText = "";
            eCategoria.innerText = "";
            ePrioridad.innerText = "";
            eDescripcion.innerText = "";

            // Reseteamos el estado del botón (vuelve a estar deshabilitado)
            comprobarBoton();
        });
    }
}
