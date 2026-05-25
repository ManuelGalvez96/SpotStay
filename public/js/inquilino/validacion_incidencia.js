/**
 * Validación JavaScript para el formulario de reporte de incidencias.
 * Sigue la plantilla estándar del proyecto SpotStay.
 */


function iniciarValidacionIncidencia() {
    // Referencias a mensajes de error e inputs con IDs en español
    const eTitulo = document.getElementById("error-titulo");
    const eCategoria = document.getElementById("error-categoria");
    const ePrioridad = document.getElementById("error-prioridad");
    const eDescripcion = document.getElementById("error-descripcion");

    const entradaTitulo = document.getElementById("titulo-incidencia");
    const entradaCategoria = document.getElementById("categoria-incidencia");
    const entradaPrioridad = document.getElementById("prioridad-incidencia");
    const entradaDescripcion = document.getElementById("descripcion-incidencia");
    const botonEnviar = document.getElementById("boton-enviar");

    if (!entradaTitulo || !botonEnviar) return;

    // Asignación directa de eventos
    entradaTitulo.oninput = comprobarBoton;
    entradaCategoria.onchange = comprobarBoton;
    entradaPrioridad.onchange = comprobarBoton;
    
    // Implementación de debounce para la descripción
    let tiempoEspera;
    entradaDescripcion.oninput = () => {
        clearTimeout(tiempoEspera);
        tiempoEspera = setTimeout(comprobarBoton, 300);
    };

    function comprobarBoton() {
        const titulo = entradaTitulo.value.trim();
        const categoria = entradaCategoria.value;
        const prioridad = entradaPrioridad.value;
        const descripcion = entradaDescripcion.value.trim();

        // Validaciones en tiempo real
        eTitulo.innerText = (titulo.length > 0 && titulo.length < 5) ? "Mínimo 5 caracteres." : "";
        eCategoria.innerText = (categoria === "" && titulo.length > 0) ? "Selecciona una categoría." : "";
        ePrioridad.innerText = (prioridad === "" && titulo.length > 0) ? "Selecciona la prioridad." : "";
        eDescripcion.innerText = (descripcion.length > 0 && descripcion.length < 15) ? "Danos más detalles (mínimo 15 carac.)." : "";

        const esValido = titulo.length >= 5 && categoria !== "" && prioridad !== "" && descripcion.length >= 15;

        if (esValido) {
            botonEnviar.disabled = false;
            botonEnviar.classList.remove("btn-login-desabilitado");
        } else {
            botonEnviar.disabled = true;
            botonEnviar.classList.add("btn-login-desabilitado");
        }
    }

    // Inicialización del estado del botón
    comprobarBoton();

    // Limpieza de campos al cerrar el modal (sin addEventListener)
    const botonesCierre = document.querySelectorAll('#modalReportar .btn-close, #modalReportar .btn-secondary');
    botonesCierre.forEach(boton => {
        const accionOriginal = boton.onclick;
        boton.onclick = () => {
            if (accionOriginal) accionOriginal();
            resetearFormularioIncidencia();
        };
    });

    function resetearFormularioIncidencia() {
        entradaTitulo.value = "";
        entradaCategoria.value = "";
        entradaPrioridad.value = "";
        entradaDescripcion.value = "";
        eTitulo.innerText = "";
        eCategoria.innerText = "";
        ePrioridad.innerText = "";
        eDescripcion.innerText = "";
        comprobarBoton();
    }
}

// Inicialización directa
iniciarValidacionIncidencia();
