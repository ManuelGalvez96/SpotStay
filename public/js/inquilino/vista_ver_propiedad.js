/**
 * Lógica exclusiva para la vista ver_propiedad (Incidencias, Contrato, Historial)
 */

// ==========================================
// SECCIÓN INCIDENCIAS
// ==========================================

function iniciarIncidencias() {
    const contenedorLista = document.getElementById("contenedor-lista-incidencias");
    const filtroAutor = document.getElementById("filtro-autor");
    const filtroEstado = document.getElementById("filtro-estado");
    const formularioReporte = document.getElementById("form-reportar-incidencia");

    if (!contenedorLista) return;

    const idPropiedad = contenedorLista.getAttribute("data-propiedad-id");

    // 1. Cargar estados dinámicamente
    cargarEstados();

    // 2. Cargar incidencias al iniciar
    cargarIncidencias(idPropiedad);

    // 3. Listeners en filtros
    if (filtroAutor) filtroAutor.onchange = () => cargarIncidencias(idPropiedad);
    if (filtroEstado) filtroEstado.onchange = () => cargarIncidencias(idPropiedad);

    // 4. Interceptar submit del formulario
    if (formularioReporte) {
        interceptarSubmitFormulario(formularioReporte, idPropiedad);
    }
}

function cargarEstados() {
    const filtroEstado = document.getElementById("filtro-estado");
    if (!filtroEstado) return;

    fetch("/inquilino/incidencias/estados")
        .then(response => {
            if (!response.ok) throw new Error("Error al cargar estados");
            return response.json();
        })
        .then(data => {
            filtroEstado.innerHTML = "";
            const optionTodas = document.createElement("option");
            optionTodas.value = "todas";
            optionTodas.textContent = "Todos los estados";
            filtroEstado.appendChild(optionTodas);

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

function cargarIncidencias(idPropiedad) {
    const contenedor = document.getElementById("contenedor-lista-incidencias");
    const filtroAutor = document.getElementById("filtro-autor");
    const filtroEstado = document.getElementById("filtro-estado");

    if (!contenedor) return;

    const autor = filtroAutor ? filtroAutor.value : "todas";
    const estado = filtroEstado ? filtroEstado.value : "todas";
    const url = `/inquilino/propiedad/${idPropiedad}/incidencias?autor=${autor}&estado=${estado}`;

    contenedor.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando incidencias...</p></div>';

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
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

function pintarIncidencias(incidencias) {
    const contenedor = document.getElementById("contenedor-lista-incidencias");
    if (!contenedor) return;

    if (!incidencias || incidencias.length === 0) {
        contenedor.innerHTML = '<div class="aviso-vacio"><p>No hay incidencias registradas.</p></div>';
        return;
    }

    let html = "";
    incidencias.forEach(incidencia => {
        const esAutor = incidencia.id_reporta == incidencia.auth_id;
        const botónResolver = (esAutor && (incidencia.estado === "abierta" || incidencia.estado === "solucionada"))
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
    reasignarEventosDetalle();
}

function reasignarEventosDetalle() {
    const botonesDetalle = document.querySelectorAll(".btn-detalle-incidencia");
    botonesDetalle.forEach(boton => {
        boton.onclick = function() {
            const idIncidencia = this.getAttribute("data-id");
            cargarDetalleIncidencia(idIncidencia);
        };
    });
}

function cargarDetalleIncidencia(idIncidencia) {
    const modalDetalle = document.getElementById('modal-detalle-incidencia');
    if (!modalDetalle) return;

    const cuerpoModal = modalDetalle.querySelector('.modal-body');
    cuerpoModal.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Cargando...</p></div>';

    fetch(`../incidencia/${idIncidencia}/detalle`)
        .then(respuesta => respuesta.json())
        .then(datos => {
            if (datos.error) throw new Error(datos.error);
            cuerpoModal.innerHTML = `
                <div class="mb-3"><strong>Título:</strong> ${datos.titulo || '-'}</div>
                <div class="mb-3"><strong>Descripción:</strong> <p>${datos.descripcion || '-'}</p></div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Categoría:</strong> ${datos.categoria || '-'}</div>
                    <div class="col-md-6"><strong>Prioridad:</strong> ${datos.prioridad || '-'}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Fecha:</strong> ${datos.fecha || '-'}</div>
                    <div class="col-md-6"><strong>Estado:</strong> ${datos.estado || '-'}</div>
                </div>
                ${datos.presupuesto ? `
                    <div class="tarjeta-presupuesto mt-3 p-3 bg-light rounded border">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="bi bi-wallet2"></i> Presupuesto de Reparación</h6>
                                <p class="mb-0 text-muted small">Importe total a abonar</p>
                            </div>
                            <div class="text-end">
                                <span class="fs-4 fw-bold text-primary">${parseFloat(datos.presupuesto).toLocaleString('es-ES', { minimumFractionDigits: 2 })} €</span>
                            </div>
                        </div>
                        ${datos.estado_workflow === 'esperando_pago' ? `
                            <div class="alert alert-warning w-100 mb-0 mt-3 py-2 text-center" style="font-size: 0.95rem;">
                                <i class="bi bi-info-circle"></i> Para abonar esta incidencia, por favor dirígete a la sección de <strong>Gestionar mis Gastos</strong>.
                            </div>
                        ` : (datos.estado_workflow === 'pagado' ? `
                            <div class="alert alert-success mb-0 mt-3 py-2 text-center">
                                <i class="bi bi-check-circle"></i> Pago completado
                            </div>
                        ` : '')}
                    </div>
                ` : ''}`;
        })
        .catch(error => {
            console.error('Error:', error);
            cuerpoModal.innerHTML = '<div class="alert alert-danger">Error al cargar los detalles.</div>';
        });
}

function cerrarIncidencia(idIncidencia) {
    if (typeof Swal !== "undefined" && typeof crearOsoPregunta === "function") {
        Swal.fire({
            title: '¿Confirmas la solución?',
            text: '¿Seguro que quieres marcar esta incidencia como resuelta?',
            iconHtml: crearOsoPregunta(),
            customClass: { icon: 'oso-icon' },
            showCancelButton: true,
            confirmButtonText: 'Sí, resuelta',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#1AA068',
            cancelButtonColor: '#6B7280'
        }).then((resultado) => {
            if (resultado.isConfirmed) procesarCierreIncidencia(idIncidencia);
        });
    } else {
        if (confirm("¿Marcar esta incidencia como resuelta?")) {
            procesarCierreIncidencia(idIncidencia);
        }
    }
}

function procesarCierreIncidencia(idIncidencia) {
    const token = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    fetch(`../incidencia/${idIncidencia}/cerrar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(respuesta => respuesta.json())
    .then(datos => {
        if (datos.success || respuesta.ok) {
            if (typeof Swal !== "undefined" && typeof crearOsoExito === "function") {
                Swal.fire({
                    title: '¡Genial!',
                    text: 'La incidencia se ha cerrado con éxito.',
                    iconHtml: crearOsoExito(),
                    customClass: { icon: 'oso-icon' },
                    confirmButtonColor: '#035498'
                });
            } else {
                alert("Incidencia cerrada con éxito.");
            }
            const idPropiedad = document.getElementById("contenedor-lista-incidencias").getAttribute("data-propiedad-id");
            cargarIncidencias(idPropiedad);
        } else {
            alert(datos.message || "Error al resolver la incidencia.");
        }
    })
    .catch(error => console.error('Error:', error));
}

function interceptarSubmitFormulario(formulario, idPropiedad) {
    formulario.onsubmit = async (e) => {
        e.preventDefault();

        const botonEnviar = document.getElementById('boton-enviar');
        const textoOriginal = botonEnviar.innerText;
        botonEnviar.disabled = true;
        botonEnviar.innerText = 'Enviando...';

        try {
            const response = await fetch(formulario.action, {
                method: "POST",
                body: new FormData(formulario),
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            const data = await response.json();

            if (!response.ok) throw new Error(data.message || "Error al reportar incidencia");

            // Éxito: cerrar modal, resetear formulario, recargar lista
            const modal = bootstrap.Modal.getInstance(document.getElementById("modalReportar"));
            if (modal) modal.hide();

            formulario.reset();
            const elemsErrores = ["error-titulo", "error-categoria", "error-prioridad", "error-descripcion"];
            elemsErrores.forEach(id => { if (document.getElementById(id)) document.getElementById(id).innerText = ""; });

            cargarIncidencias(idPropiedad);

            if (typeof Swal !== "undefined" && typeof crearOsoExito === "function") {
                Swal.fire({
                    iconHtml: crearOsoExito(),
                    title: "¡Éxito!",
                    text: data.message || "Incidencia reportada correctamente.",
                    customClass: { icon: 'oso-icon' },
                    confirmButtonColor: '#035498'
                });
            }
        } catch (error) {
            console.error("Error al reportar:", error);
            if (typeof Swal !== "undefined" && typeof crearOsoError === "function") {
                Swal.fire({
                    iconHtml: crearOsoError(),
                    title: "Error",
                    text: error.message || "Error al reportar la incidencia.",
                    customClass: { icon: 'oso-icon' },
                    confirmButtonColor: '#d9534f'
                });
            } else {
                alert(error.message || "Error al reportar la incidencia.");
            }
        } finally {
            botonEnviar.disabled = false;
            botonEnviar.innerText = textoOriginal;
        }
    };
}


// ==========================================
// SECCIÓN CONTRATO Y ALERTAS
// ==========================================

function verificarEstadoAlquilerFetch() {
    const tarjetaFin = document.querySelector('.card-gestion.fin-contrato');
    const contenedorAlerta = document.getElementById('contenedor-alerta-contrato');
    
    if (!tarjetaFin || !contenedorAlerta) return;

    const idAlquiler = tarjetaFin.getAttribute('data-id-alquiler');
    if (!idAlquiler) return;

    fetch(`/inquilino/alquiler/${idAlquiler}/estado-contrato`)
        .then(respuesta => respuesta.json())
        .then(datos => {
            if (datos.semana_excedida) {
                contenedorAlerta.innerText = datos.mensaje;
                contenedorAlerta.style.display = 'block';
                
                tarjetaFin.style.border = '2px solid #ff4d4d';
                tarjetaFin.style.boxShadow = '0 0 15px rgba(255, 77, 77, 0.2)';
            }
        })
        .catch(error => console.error('Error al verificar estado del contrato:', error));
}


// ==========================================
// SECCIÓN HISTORIAL DE PAGOS
// ==========================================

function cargarHistorialPagosFetch(tipo) {
    const tabContent = document.getElementById('tabHistorialContent');
    if (!tabContent) return;
    
    const idAlquiler = tabContent.getAttribute('data-id-alquiler');
    if (!idAlquiler) return;

    const idTab = tipo === 'alquiler' ? 'alquiler-history' : 'gastos-history';
    const tabPane = document.getElementById(idTab);
    if (!tabPane) return;

    const endpoint = tipo === 'alquiler' ? 'historial-alquiler' : 'historial-suministros';
    
    const colorTexto = tipo === 'alquiler' ? '' : 'texto-suministro';
    const colorBadge = tipo === 'alquiler' ? 'bg-success' : 'badge-suministro-abonado';
    const textoBadge = tipo === 'alquiler' ? 'Pagado' : 'Abonado';
    const spinnerColor = tipo === 'alquiler' ? 'text-primary' : 'text-info';

    tabPane.innerHTML = `
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Fecha</th>
                        <th>${tipo === 'alquiler' ? 'Concepto' : 'Categoría(Concepto)'}</th>
                        <th class="text-end">Importe</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center pe-3">Factura</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center p-5">
                            <div class="spinner-border ${spinnerColor}" role="status"></div>
                            <p class="mt-2 small text-muted">Cargando ${tipo}...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>`;

    const cuerpoTabla = tabPane.querySelector('tbody');

    fetch(`/inquilino/alquiler/${idAlquiler}/${endpoint}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(respuesta => respuesta.json())
    .then(datos => {
        if (!datos || datos.length === 0) {
            cuerpoTabla.innerHTML = `<tr><td colspan="5" class="p-5 text-center text-muted">No hay registros de ${tipo} en este alquiler todavía.</td></tr>`;
            return;
        }

        cuerpoTabla.innerHTML = datos.map(pago => {
            const fecha = new Date(pago.creado_pago);
            const fechaFormateada = fecha.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const importe = parseFloat(pago.importe_pago).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
            const btnFactura = pago.factura_url 
                ? `<a href="${pago.factura_url}" target="_blank" class="btn btn-sm btn-outline-danger" title="Descargar Factura">
                    <i class="bi bi-file-pdf"></i> PDF
                   </a>`
                : `<span class="text-muted small">N/A</span>`;

            return `
            <tr>
                <td class="ps-3 align-middle">${fechaFormateada}</td>
                <td class="align-middle">${pago.concepto_pago}</td>
                <td class="text-end fw-bold align-middle ${colorTexto}">${importe} €</td>
                <td class="text-center align-middle">
                    <span class="badge ${colorBadge}">${textoBadge}</span>
                </td>
                <td class="text-center pe-3 align-middle">
                    ${btnFactura}
                </td>
            </tr>`;
        }).join('');
    })
    .catch(error => {
        console.error('Error al cargar el historial:', error);
        cuerpoTabla.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-danger">Error de conexión con el servidor.</td></tr>`;
    });
}

function inicializarHistorialPagosEvents() {
    const botonTabAlquiler = document.getElementById('alquiler-tab');
    const botonTabGastos = document.getElementById('gastos-tab');
    const botonesVerHistorial = document.querySelectorAll('.btn-ver-historial');

    if (botonTabAlquiler) botonTabAlquiler.onclick = () => cargarHistorialPagosFetch('alquiler');
    if (botonTabGastos) botonTabGastos.onclick = () => cargarHistorialPagosFetch('suministros');

    botonesVerHistorial.forEach(boton => {
        boton.onclick = () => {
            setTimeout(() => {
                const pestañaActiva = document.querySelector('#tabHistorial .nav-link.active');
                if (pestañaActiva && pestañaActiva.id === 'alquiler-tab') {
                    cargarHistorialPagosFetch('alquiler');
                } else if (pestañaActiva) {
                    cargarHistorialPagosFetch('suministros');
                }
            }, 150);
        };
    });
}


// ==========================================
// INICIALIZACIÓN GLOBAL VISTA
// ==========================================

// Inicialización directa
iniciarIncidencias();
verificarEstadoAlquilerFetch();
setInterval(verificarEstadoAlquilerFetch, 300000); // 5 min
inicializarHistorialPagosEvents();
