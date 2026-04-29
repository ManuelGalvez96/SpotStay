/**
 * Filtros dinámicos para incidencias en ver_propiedad
 * Maneja cambios en los select de filtro y actualiza la lista vía fetch
 */

window.onload = () => {
    // Referencias a elementos del DOM
    const filtroAutor = document.getElementById('filtro-autor');
    const filtroEstado = document.getElementById('filtro-estado');
    const contenedorListaIncidencias = document.getElementById('contenedor-lista-incidencias');

    if (!filtroAutor || !filtroEstado || !contenedorListaIncidencias) {
        console.warn('Elementos de filtro de incidencias no encontrados');
        return;
    }

    // Obtener ID de propiedad desde el contenedor de datos
    const idPropiedad = contenedorListaIncidencias.getAttribute('data-propiedad-id');

    if (!idPropiedad) {
        console.warn('ID de propiedad no encontrado');
        return;
    }

    /**
     * Mostrar capa de carga
     */
    function mostrarCapaCarga() {
        let capaCarga = document.getElementById('overlay-carga-incidencias');
        if (!capaCarga) {
            capaCarga = document.createElement('div');
            capaCarga.id = 'overlay-carga-incidencias';
            capaCarga.className = 'overlay-carga-incidencias';
            capaCarga.innerHTML = `
                <div class="spinner-carga">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p>Cargando incidencias...</p>
                </div>
            `;
            contenedorListaIncidencias.parentElement.style.position = 'relative';
            contenedorListaIncidencias.parentElement.appendChild(capaCarga);
        }
        capaCarga.style.display = 'flex';
    }

    /**
     * Ocultar capa de carga
     */
    function ocultarCapaCarga() {
        const capaCarga = document.getElementById('overlay-carga-incidencias');
        if (capaCarga) {
            capaCarga.style.display = 'none';
        }
    }

    /**
     * Cargar incidencias filtradas vía fetch
     */
    async function cargarIncidenciasFiltradasFetch() {
        const autor = filtroAutor.value;
        const estado = filtroEstado.value;

        mostrarCapaCarga();

        const ruta = `/inquilino/propiedad/${idPropiedad}/incidencias?autor=${encodeURIComponent(autor)}&estado=${encodeURIComponent(estado)}`;

        try {
            const respuesta = await fetch(ruta, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!respuesta.ok) {
                throw new Error(`Error al cargar incidencias: ${respuesta.status}`);
            }

            const incidencias = await respuesta.json();

            // Pequeño retardo para suavizar la transición visual
            setTimeout(() => {
                actualizarListaIncidencias(incidencias);
                ocultarCapaCarga();
            }, 800);
        } catch (error) {
            console.error('Error al cargar incidencias:', error);
            setTimeout(() => {
                if (contenedorListaIncidencias) {
                    contenedorListaIncidencias.innerHTML = `
                        <div class="aviso-vacio">
                            <p style="color: red;">Error al cargar incidencias. Intenta nuevamente.</p>
                        </div>
                    `;
                }
                ocultarCapaCarga();
            }, 800);
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
        
        // Re-asignamos los eventos a los nuevos elementos insertados
        adjuntarEventosDetalle();
    }

    /**
     * Adjuntar eventos a los botones de detalle (reemplazando addEventListener)
     */
    function adjuntarEventosDetalle() {
        const botonesVerDetalle = document.querySelectorAll('.btn-detalle-incidencia');
        botonesVerDetalle.forEach((boton) => {
            boton.onclick = function () {
                const idIncidencia = this.getAttribute('data-id');
                cargarDetalleIncidencia(idIncidencia);
            };
        });
    }

    /**
     * Cargar detalle de una incidencia vía fetch
     */
    async function cargarDetalleIncidencia(idIncidencia) {
        const ruta = `/inquilino/incidencia/${idIncidencia}/detalle`;

        try {
            const respuesta = await fetch(ruta, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!respuesta.ok) {
                throw new Error(`Error al cargar detalle: ${respuesta.status}`);
            }

            const datos = await respuesta.json();
            actualizarModalDetalle(datos);
        } catch (error) {
            console.error('Error al cargar detalle de incidencia:', error);
        }
    }

    /**
     * Actualizar el contenido del modal de detalle
     */
    function actualizarModalDetalle(datos) {
        const cuerpoModalDetalle = document.querySelector('#modal-detalle-incidencia .modal-body');
        
        if (!cuerpoModalDetalle) {
            console.warn('Modal de detalle no encontrado');
            return;
        }

        const html = `
            <div class="mb-3">
                <strong>Título:</strong>
                ${datos.titulo || '-'}
            </div>

            <div class="mb-3">
                <strong>Descripción:</strong>
                <p>${datos.descripcion || '-'}</p>
            </div>

            <div class="row mb-3">
                <div class="col-md-6"><strong>Categoría:</strong> ${datos.categoria || '-'}</div>
                <div class="col-md-6"><strong>Prioridad:</strong> ${datos.prioridad || '-'}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6"><strong>Fecha:</strong> ${datos.fecha || '-'}</div>
                <div class="col-md-6"><strong>Estado:</strong> ${datos.estado || '-'}</div>
            </div>
        `;

        cuerpoModalDetalle.innerHTML = html;
    }

    // Eventos de cambio para los select
    filtroAutor.onchange = cargarIncidenciasFiltradasFetch;
    filtroEstado.onchange = cargarIncidenciasFiltradasFetch;

    // Carga inicial
    cargarIncidenciasFiltradasFetch();
};
