/**
 * Filtros dinámicos para incidencias en ver_propiedad
 * Maneja cambios en los select de filtro y actualiza la lista vía fetch
 */

document.addEventListener('DOMContentLoaded', function () {
    // Referencias a elementos del DOM
    const filtroAutor = document.getElementById('filtro-autor');
    const filtroEstado = document.getElementById('filtro-estado');
    const contenedorListaIncidencias = document.getElementById('contenedor-lista-incidencias');

    if (!filtroAutor || !filtroEstado || !contenedorListaIncidencias) {
        console.warn('Elementos de filtro de incidencias no encontrados');
        return;
    }

    // Obtener ID de propiedad desde el contenedor de datos
    const propiedadId = contenedorListaIncidencias.getAttribute('data-propiedad-id');

    if (!propiedadId) {
        console.warn('ID de propiedad no encontrado');
        return;
    }

    /**
     * Mostrar overlay de carga
     */
    function mostrarOverlayCarga() {
        let overlay = document.getElementById('overlay-carga-incidencias');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'overlay-carga-incidencias';
            overlay.className = 'overlay-carga-incidencias';
            overlay.innerHTML = `
                <div class="spinner-carga">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p>Cargando incidencias...</p>
                </div>
            `;
            contenedorListaIncidencias.parentElement.style.position = 'relative';
            contenedorListaIncidencias.parentElement.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    }

    /**
     * Ocultar overlay de carga
     */
    function ocultarOverlayCarga() {
        const overlay = document.getElementById('overlay-carga-incidencias');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    /**
     * Cargar incidencias filtradas vía fetch
     */
    async function cargarIncidenciasFiltradasFetch() {
        const autor = filtroAutor.value;
        const estado = filtroEstado.value;

        mostrarOverlayCarga();

        // URL base de la ruta (reemplazar con la ruta correcta según tu configuración)
        const url = `/inquilino/propiedad/${propiedadId}/incidencias?autor=${encodeURIComponent(autor)}&estado=${encodeURIComponent(estado)}`;

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Error al cargar incidencias: ${response.status}`);
            }

            const incidencias = await response.json();

            // Esperar al menos 1 segundo antes de mostrar resultados
            setTimeout(() => {
                actualizarListaIncidencias(incidencias);
                ocultarOverlayCarga();
            }, 1000);
        } catch (error) {
            console.error('Error al cargar incidencias:', error);
            // Mostrar error amigable en la UI si lo deseas
            setTimeout(() => {
                if (contenedorListaIncidencias) {
                    contenedorListaIncidencias.innerHTML = `
                        <div class="aviso-vacio">
                            <p style="color: red;">Error al cargar incidencias. Intenta nuevamente.</p>
                        </div>
                    `;
                }
                ocultarOverlayCarga();
            }, 1000);
        }
    }

    /**
     * Actualizar el HTML de la lista de incidencias
     */
    function actualizarListaIncidencias(incidencias) {
        if (!incidencias || incidencias.length === 0) {
            contenedorListaIncidencias.innerHTML = `
                <div class="aviso-vacio">
                    <p>No hay incidencias registradas con los filtros aplicados.</p>
                </div>
            `;
            return;
        }

        // Generar HTML de cada incidencia
        const htmlIncidencias = incidencias.map((inc) => `
            <div class="item-incidencia">
                <div class="incidencia-info">
                    <span class="titulo btn-detalle-incidencia"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-detalle-incidencia"
                        data-id="${inc.id}">
                        ${inc.titulo}
                    </span>
                    <span class="fecha">${inc.fecha}</span>
                </div>
                <div class="incidencia-acciones">
                    <span class="estado-tag ${inc.estado}">${inc.estado_texto}</span>

                    ${inc.id_reporta === inc.auth_id && inc.estado !== 'resuelta' ? `
                        <button type="button" class="btn-resolver" title="Marcar como resuelta" onclick="cerrarIncidencia(${inc.id})">
                            <i class="bi bi-check-circle"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `).join('');

        contenedorListaIncidencias.innerHTML = htmlIncidencias;

        // Re-adjuntar event listeners a los botones de detalle (modal)
        adjuntarEventListenersDetalleIncidencia();
    }

    /**
     * Adjuntar event listeners a los botones de detalle de incidencias
     */
    function adjuntarEventListenersDetalleIncidencia() {
        const botonesDetalle = document.querySelectorAll('.btn-detalle-incidencia');
        botonesDetalle.forEach((btn) => {
            btn.addEventListener('click', function () {
                const incidenciaId = this.getAttribute('data-id');
                cargarDetalleIncidencia(incidenciaId);
            });
        });
    }

    /**
     * Cargar detalle de una incidencia vía fetch
     */
    async function cargarDetalleIncidencia(incidenciaId) {
        const url = `/inquilino/incidencia/${incidenciaId}/detalle`;

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Error al cargar detalle: ${response.status}`);
            }

            const data = await response.json();

            // Actualizar modal con los datos
            actualizarModalDetalleIncidencia(data);
        } catch (error) {
            console.error('Error al cargar detalle de incidencia:', error);
        }
    }

    /**
     * Actualizar el contenido del modal de detalle
     */
    function actualizarModalDetalleIncidencia(data) {
        // Buscar el modal y actualizar su contenido
        const modalDetalleBody = document.querySelector('#modal-detalle-incidencia .modal-body');
        
        if (!modalDetalleBody) {
            console.warn('Modal de detalle no encontrado');
            return;
        }

        const html = `
            <div class="mb-3">
                <strong>Título:</strong>
                ${data.titulo || '-'}
            </div>

            <div class="mb-3">
                <strong>Descripción:</strong>
                <p>${data.descripcion || '-'}</p>
            </div>

            <div class="row mb-3">
                <div class="col-md-6"><strong>Categoría:</strong> ${data.categoria || '-'}</div>
                <div class="col-md-6"><strong>Prioridad:</strong> ${data.prioridad || '-'}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6"><strong>Fecha:</strong> ${data.fecha || '-'}</div>
                <div class="col-md-6"><strong>Estado:</strong> ${data.estado || '-'}</div>
            </div>
        `;

        modalDetalleBody.innerHTML = html;
    }

    // Event listeners para los select de filtro
    filtroAutor.addEventListener('change', cargarIncidenciasFiltradasFetch);
    filtroEstado.addEventListener('change', cargarIncidenciasFiltradasFetch);

    // Cargar incidencias al iniciar
    cargarIncidenciasFiltradasFetch();
});
