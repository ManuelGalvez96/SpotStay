/**
 * Gestión de incidencias inquilino
 * Carga dinámica de incidencias, estados y formulario AJAX
 */

const anteriorOnload = window.onload;
window.onload = () => {
    if (anteriorOnload) anteriorOnload();
    iniciarIncidencias();
};

function iniciarIncidencias() {
    const contenedorLista = document.getElementById("contenedor-lista-incidencias");
    const filtroAutor = document.getElementById("filtro-autor");
    const filtroEstado = document.getElementById("filtro-estado");
    const formularioReporte = document.getElementById("form-reportar-incidencia");

    if (!contenedorLista || !filtroAutor || !filtroEstado) return;

    // 1. Cargar estados dinámicamente
    cargarEstados();

    // 2. Cargar incidencias al iniciar
    const idPropiedad = contenedorLista.getAttribute("data-propiedad-id");
    cargarIncidencias(idPropiedad);

    // 3. Listeners en filtros
    filtroAutor.onchange = () => cargarIncidencias(idPropiedad);
    filtroEstado.onchange = () => cargarIncidencias(idPropiedad);

    // 4. Interceptar submit del formulario
    if (formularioReporte) {
        interceptarSubmitFormulario(formularioReporte, idPropiedad);
    }
}

// --- FUNCIONES PRINCIPALES ---

/**
 * Cargar los estados disponibles y llenar el select dinámicamente
 */
function cargarEstados() {
    const filtroEstado = document.getElementById("filtro-estado");
    if (!filtroEstado) return;

    fetch("/inquilino/incidencias/estados")
        .then(response => {
            if (!response.ok) throw new Error("Error al cargar estados");
            return response.json();
        })
        .then(data => {
            // Limpiar opciones previas (mantener la primera si es "Todas")
            const primeraOpcion = filtroEstado.querySelector('option:first-child');
            filtroEstado.innerHTML = "";

            // Agregar opción "Todas"
            const optionTodas = document.createElement("option");
            optionTodas.value = "todas";
            optionTodas.textContent = "Todos los estados";
            filtroEstado.appendChild(optionTodas);

            // Agregar estados dinámicamente
            if (data.estados) {
                Object.entries(data.estados).forEach(([valor, texto]) => {
                    const option = document.createElement("option");
                    option.value = valor;
                    option.textContent = texto;
                    filtroEstado.appendChild(option);
                });
            }
        })
        .catch(error => console.error("Error cargando estados:", error));
}

/**
 * Cargar incidencias con filtros aplicados
 */
function cargarIncidencias(idPropiedad) {
    const contenedor = document.getElementById("contenedor-lista-incidencias");
    const filtroAutor = document.getElementById("filtro-autor");
    const filtroEstado = document.getElementById("filtro-estado");

    if (!contenedor || !filtroAutor || !filtroEstado) return;

    const autor = filtroAutor.value;
    const estado = filtroEstado.value;
    const url = `/inquilino/propiedad/${idPropiedad}/incidencias?autor=${autor}&estado=${estado}`;

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error("Error al cargar incidencias");
            return response.json();
        })
        .then(incidencias => {
            pintarIncidencias(incidencias);
        })
        .catch(error => {
            console.error("Error cargando incidencias:", error);
            contenedor.innerHTML = '<div class="aviso-vacio"><p>Error al cargar incidencias.</p></div>';
        });
}

/**
 * Pintar las incidencias en el DOM
 */
function pintarIncidencias(incidencias) {
    const contenedor = document.getElementById("contenedor-lista-incidencias");
    if (!contenedor) return;

    if (!incidencias || incidencias.length === 0) {
        contenedor.innerHTML = '<div class="aviso-vacio"><p>No hay incidencias registradas.</p></div>';
        return;
    }

    let html = "";
    incidencias.forEach(incidencia => {
        const esAutor = incidencia.id_reporta === incidencia.auth_id;
        const botónResolver = (esAutor && incidencia.estado !== "resuelta")
            ? `<button type="button" class="btn-resolver" title="Marcar como resuelta" onclick="cerrarIncidencia(${incidencia.id})">
                <i class="bi bi-check-circle"></i>
            </button>`
            : "";

        html += `
            <div class="item-incidencia">
                <div class="incidencia-info">
                    <span class="titulo btn-detalle-incidencia"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-detalle-incidencia"
                        data-id="${incidencia.id}">
                        ${incidencia.titulo}
                    </span>
                    <span class="fecha">${incidencia.fecha}</span>
                </div>
                <div class="incidencia-acciones">
                    <span class="estado-tag ${incidencia.estado}">${incidencia.estado_texto}</span>
                    ${botónResolver}
                </div>
            </div>
        `;
    });

    contenedor.innerHTML = html;

    // Re-asignar eventos a los botones de detalle (modal)
    reasignarEventosDetalle();
}

/**
 * Re-asignar eventos a los botones de detalle de incidencia (para modal)
 */
function reasignarEventosDetalle() {
    const botonesDetalle = document.querySelectorAll(".btn-detalle-incidencia");
    botonesDetalle.forEach(boton => {
        boton.onclick = function() {
            const idIncidencia = this.getAttribute("data-id");
            cargarDetalleIncidencia(idIncidencia);
        };
    });
}

/**
 * Cargar el detalle de una incidencia (para el modal)
 */
function cargarDetalleIncidencia(idIncidencia) {
    fetch(`/inquilino/incidencia/${idIncidencia}/detalle`)
        .then(response => {
            if (!response.ok) throw new Error("Error al cargar detalle");
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            console.log("Detalle de incidencia:", data);
        })
        .catch(error => console.error("Error cargando detalle:", error));
}

/**
 * Interceptar el submit del formulario de reporte
 * Lo convierte en AJAX para que se cree y aparezca sin recargar
 */
function interceptarSubmitFormulario(formulario, idPropiedad) {
    formulario.onsubmit = async (e) => {
        e.preventDefault();

        // Validar que el formulario sea válido
        const titulo = document.getElementById("titulo-incidencia");
        const categoria = document.getElementById("categoria-incidencia");
        const prioridad = document.getElementById("prioridad-incidencia");
        const descripcion = document.getElementById("descripcion-incidencia");

        if (!titulo.value.trim() || !categoria.value || !prioridad.value || !descripcion.value.trim()) {
            console.error("Formulario incompleto");
            return;
        }

        // Preparar datos
        const formData = new FormData(formulario);

        // Enviar por AJAX
        try {
            const response = await fetch(formulario.action, {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Error al reportar incidencia");
            }

            // Éxito: cerrar modal, resetear formulario, recargar lista
            const modal = bootstrap.Modal.getInstance(document.getElementById("modalReportar"));
            if (modal) modal.hide();

            // Resetear formulario
            formulario.reset();
            document.getElementById("error-titulo").innerText = "";
            document.getElementById("error-categoria").innerText = "";
            document.getElementById("error-prioridad").innerText = "";
            document.getElementById("error-descripcion").innerText = "";
            document.getElementById("boton-enviar").disabled = true;
            document.getElementById("boton-enviar").classList.add("btn-login-desabilitado");

            // Recargar lista (automáticamente aparecerá la nueva incidencia)
            cargarIncidencias(idPropiedad);

            // Mostrar mensaje de éxito (opcional, usando SweetAlert si está disponible)
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: data.message || "Incidencia reportada correctamente.",
                    timer: 2000
                });
            } else {
                console.log(data.message || "Incidencia reportada correctamente.");
            }

        } catch (error) {
            console.error("Error al reportar:", error);
            if (typeof Swal !== "undefined") {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error.message || "Error al reportar la incidencia."
                });
            } else {
                alert(error.message || "Error al reportar la incidencia.");
            }
        }
    };
}

/**
 * Cerrar una incidencia (función existente que se llama desde el HTML)
 * Esta función debería estar en el JS anterior o adaptarse
 */
function cerrarIncidencia(idIncidencia) {
    if (typeof Swal !== "undefined") {
        Swal.fire({
            title: "¿Marcar como resuelta?",
            text: "No podrás cambiar el estado después.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, resolver",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/inquilino/incidencia/${idIncidencia}/cerrar`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const idPropiedad = document.getElementById("contenedor-lista-incidencias").getAttribute("data-propiedad-id");
                        cargarIncidencias(idPropiedad);
                        Swal.fire("¡Listo!", "Incidencia marcada como resuelta.", "success");
                    } else {
                        Swal.fire("Error", data.message || "No se pudo resolver la incidencia.", "error");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire("Error", "Error al resolver la incidencia.", "error");
                });
            }
        });
    } else {
        // Sin SweetAlert, usar confirm simple
        if (confirm("¿Marcar esta incidencia como resuelta?")) {
            fetch(`/inquilino/incidencia/${idIncidencia}/cerrar`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const idPropiedad = document.getElementById("contenedor-lista-incidencias").getAttribute("data-propiedad-id");
                    cargarIncidencias(idPropiedad);
                    alert("Incidencia resuelta correctamente.");
                } else {
                    alert(data.message || "Error al resolver la incidencia.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error al resolver la incidencia.");
            });
        }
    }
}
