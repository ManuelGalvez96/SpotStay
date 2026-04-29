/**
 * Funciones generadoras del Oso (Mascota SpotStay)
 */
const crearOsoExito = () => `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <circle cx="62" cy="52" r="14" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <circle cx="138" cy="52" r="14" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <path d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" fill="#004A99" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" fill="#FFFFFF" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 128 Q100 133 108 128" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle cx="48" cy="180" r="19" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <circle cx="152" cy="180" r="19" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#90EE90" stroke="#006400" stroke-width="2.5"/>
        <text x="100" y="165" font-size="32" font-weight="bold" text-anchor="middle" fill="#006400">✓</text>
    </svg>`;

const crearOsoError = () => `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <circle cx="62" cy="52" r="14" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <circle cx="138" cy="52" r="14" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <path d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" fill="#004A99" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" fill="#FFFFFF" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 135 Q100 128 108 135" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle cx="48" cy="180" r="19" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <circle cx="152" cy="180" r="19" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FFB6C1" stroke="#DC143C" stroke-width="2.5"/>
        <text x="100" y="165" font-size="32" font-weight="bold" text-anchor="middle" fill="#DC143C">✗</text>
    </svg>`;

const crearOsoPregunta = () => `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <circle cx="62" cy="52" r="14" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <circle cx="138" cy="52" r="14" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <path d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" fill="#004A99" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" fill="#FFFFFF" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M85 105 L115 105" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle cx="48" cy="180" r="19" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <circle cx="152" cy="180" r="19" fill="#FFFFFF" stroke="#000" stroke-width="2" />
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FFD700" stroke="#B8860B" stroke-width="2.5"/>
        <text x="100" y="165" font-size="32" font-weight="bold" text-anchor="middle" fill="#B8860B">?</text>
    </svg>`;


/**
 * Función global para asignar eventos de pago a los formularios.
 */
function inicializarPagosInquilino() {
    const formularios = document.querySelectorAll('.form-pago-cuota, .form-pago-grid');
    
    formularios.forEach(formulario => {
        formulario.onsubmit = (evento) => {
            evento.preventDefault();

            const importe = formulario.getAttribute('data-monto') || 'la cuota';
            
            Swal.fire({
                title: '¿Realizar pago?',
                text: `Vas a proceder al pago de ${importe} correspondiente al alquiler.`,
                iconHtml: crearOsoPregunta(),
                customClass: { icon: 'oso-icon' },
                showCancelButton: true,
                confirmButtonText: 'Sí, pagar ahora',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#1AA068',
                cancelButtonColor: '#6B7280'
            }).then((resultado) => {
                if (resultado.isConfirmed) {
                    const boton = formulario.querySelector('button');
                    const textoOriginal = boton.innerText;
                    boton.disabled = true;
                    boton.innerText = 'Procesando...';

                    const ruta = formulario.getAttribute('action');
                    const fichaToken = formulario.querySelector('input[name="_token"]').value;

                    fetch(ruta, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': fichaToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(respuesta => respuesta.json())
                    .then(datos => {
                        if (datos.success) {
                            Swal.fire({
                                title: '¡Pago realizado!',
                                text: 'Tu cuota ha sido abonada correctamente. ¡Gracias!',
                                iconHtml: crearOsoExito(),
                                customClass: { icon: 'oso-icon' },
                                confirmButtonColor: '#035498'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error en el pago',
                                text: datos.message || 'No se pudo procesar el pago.',
                                iconHtml: crearOsoError(),
                                customClass: { icon: 'oso-icon' },
                                confirmButtonColor: '#d9534f'
                            });
                            boton.disabled = false;
                            boton.innerText = textoOriginal;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor de pagos.',
                            iconHtml: crearOsoError(),
                            customClass: { icon: 'oso-icon' },
                            confirmButtonColor: '#d9534f'
                        });
                        boton.disabled = false;
                        boton.innerText = textoOriginal;
                    });
                }
            });
        };
    });
}

// Exponemos la función a window
window.inicializarPagosInquilino = inicializarPagosInquilino;

window.onload = () => {
    iniciarTemporizadorAlquileres();
    cargarIncidencias();
    inicializarPagosInquilino();

    setInterval(iniciarTemporizadorAlquileres, 60000);
    setInterval(cargarIncidencias, 30000);

    const formularioReportar = document.getElementById('form-reportar-incidencia');
    if (formularioReportar) {
        formularioReportar.onsubmit = (evento) => {
            evento.preventDefault();
            const botonEnviar = formularioReportar.querySelector('#boton-enviar');
            const textoOriginal = botonEnviar.innerText;

            botonEnviar.disabled = true;
            botonEnviar.innerText = 'Enviando...';

            const datosFormulario = new FormData(formularioReportar);
            const ruta = formularioReportar.getAttribute('action');

            fetch(ruta, {
                method: 'POST',
                body: datosFormulario,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(respuesta => respuesta.json())
            .then(datos => {
                if (datos.success) {
                    formularioReportar.reset();
                    const elementoModal = document.getElementById('modalReportar');
                    const objetoModal = bootstrap.Modal.getInstance(elementoModal);
                    if (objetoModal) objetoModal.hide();

                    Swal.fire({
                        title: '¡Reportada!',
                        text: 'La incidencia se ha registrado correctamente.',
                        iconHtml: crearOsoExito(),
                        customClass: { icon: 'oso-icon' },
                        confirmButtonColor: '#035498'
                    });

                    cargarIncidencias();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error: ' + datos.message,
                        iconHtml: crearOsoError(),
                        customClass: { icon: 'oso-icon' },
                        confirmButtonColor: '#d9534f'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al enviar el reporte.',
                    iconHtml: crearOsoError(),
                    customClass: { icon: 'oso-icon' },
                    confirmButtonColor: '#d9534f'
                });
            })
            .finally(() => {
                botonEnviar.disabled = false;
                botonEnviar.innerText = textoOriginal;
            });
        };
    }

    const modalDetalle = document.getElementById('modalDetalleIncidencia');
    if (modalDetalle) {
        // Usamos la propiedad directa para eventos de Bootstrap si es posible, 
        // o lo manejamos mediante una función asignada que Bootstrap reconozca.
        // En Bootstrap 5, los eventos se disparan sobre el elemento, usaremos un truco de compatibilidad:
        modalDetalle.addEventListener('show.bs.modal', function (evento) {
            const disparador = evento.relatedTarget;
            const idIncidencia = disparador.getAttribute('data-id');

            const capaCarga = modalDetalle.querySelector('#loading-detalle');
            const contenedorContenido = modalDetalle.querySelector('#contenido-detalle');

            if (capaCarga) capaCarga.style.display = 'block';
            if (contenedorContenido) contenedorContenido.style.display = 'none';

            fetch(`../incidencia/${idIncidencia}`)
                .then(respuesta => respuesta.json())
                .then(datos => {
                    if (datos.error) {
                        alert(datos.error);
                        return;
                    }

                    modalDetalle.querySelector('#detalle-titulo').textContent = datos.titulo;
                    modalDetalle.querySelector('#detalle-descripcion').textContent = datos.descripcion;
                    modalDetalle.querySelector('#detalle-categoria').textContent = datos.categoria;
                    modalDetalle.querySelector('#detalle-prioridad').textContent = datos.prioridad;
                    modalDetalle.querySelector('#detalle-fecha').textContent = datos.fecha;

                    if (capaCarga) capaCarga.style.display = 'none';
                    if (contenedorContenido) contenedorContenido.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los detalles de la incidencia.');
                });
        });
    }

    const filtroAutor = document.getElementById('filtro-autor');
    const filtroEstado = document.getElementById('filtro-estado');
    if (filtroAutor) filtroAutor.onchange = cargarIncidencias;
    if (filtroEstado) filtroEstado.onchange = cargarIncidencias;
};

function iniciarTemporizadorAlquileres() {
    const nodosTemporizador = document.querySelectorAll('.js-tiempo-restante');

    nodosTemporizador.forEach(nodo => {
        const fechaFinBruta = nodo.getAttribute('data-fecha-fin');
        if (!fechaFinBruta) return;

        const cadenaFecha = fechaFinBruta.split(' ')[0];
        const partesFecha = cadenaFecha.split('-');

        if (partesFecha.length !== 3) return;

        const anio = parseInt(partesFecha[0], 10);
        const mes = parseInt(partesFecha[1], 10) - 1;
        const dia = parseInt(partesFecha[2], 10);

        const finDelDia = new Date(anio, mes, dia, 23, 59, 59);
        const ahora = new Date();

        let diferenciaMilis = finDelDia - ahora;
        let diferenciaAbsoluta = Math.abs(diferenciaMilis);

        if (diferenciaMilis <= 0) {
            let diasPasados = Math.floor(diferenciaAbsoluta / 86400000);
            const alertaCuadricula = nodo.closest('.contenedor-alerta-js');
            
            if (alertaCuadricula) {
                const cajaExpirada = alertaCuadricula.closest('.alerta-fin-contrato');
                if (cajaExpirada) cajaExpirada.classList.add('estado-expirado');

                let textoTiempo = diasPasados >= 1 
                    ? `hace <strong>${diasPasados} día${diasPasados > 1 ? 's' : ''}</strong>`
                    : `hace <strong>${Math.floor(diferenciaAbsoluta / 3600000)}h ${Math.floor((diferenciaAbsoluta % 3600000) / 60000)}m</strong>`;

                alertaCuadricula.innerHTML = `El contrato finalizó ${textoTiempo}. <br>Para mas informacion entra en ver detalles.`;
            } else {
                let textoTiempo = diasPasados >= 1
                    ? `hace ${diasPasados} día${diasPasados > 1 ? 's' : ''}`
                    : `hace ${Math.floor(diferenciaAbsoluta / 3600000)}h ${Math.floor((diferenciaAbsoluta % 3600000) / 60000)}m`;

                const tarjetaGestion = nodo.closest('.card-gestion');
                if (tarjetaGestion) {
                    tarjetaGestion.classList.add('estado-expirado');
                    const etiqueta = tarjetaGestion.querySelector('.label');
                    if (etiqueta) etiqueta.innerText = 'CONTRATO FINALIZADO';
                    const valorKpi = tarjetaGestion.querySelector('.valor-kpi');
                    if (valorKpi) valorKpi.style.display = 'none';
                }

                nodo.innerText = `¡El contrato finalizó ${textoTiempo}!`;
                nodo.classList.add('texto-expirado');
            }
        } else {
            let minutosTotales = Math.floor(diferenciaMilis / 60000);
            let horas = Math.floor(minutosTotales / 60);
            let minutos = minutosTotales % 60;
            nodo.innerText = `${horas}h ${minutos}m`;
        }
    });
}

function cargarIncidencias() {
    const contenedor = document.getElementById('contenedor-lista-incidencias');
    if (!contenedor) return;

    const idPropiedad = contenedor.getAttribute('data-propiedad-id');
    const autorSeleccionado = document.getElementById('filtro-autor')?.value || 'todas';
    const estadoSeleccionado = document.getElementById('filtro-estado')?.value || 'todas';

    contenedor.classList.add('lista-cargando');

    fetch(`../propiedad/${idPropiedad}/incidencias?autor=${autorSeleccionado}&estado=${estadoSeleccionado}`)
    .then(respuesta => respuesta.json())
    .then(incidencias => {
        setTimeout(() => {
            contenedor.classList.remove('lista-cargando');
            if (incidencias.length === 0) {
                contenedor.innerHTML = '<div class="aviso-vacio"><p>No se encontraron incidencias con los filtros aplicados.</p></div>';
                return;
            }

            let html = '';
            incidencias.forEach(inc => {
                const botonResolver = (inc.id_reporta == inc.auth_id && inc.estado != 'resuelta')
                    ? `<button type="button" class="btn-resolver" title="Marcar como resuelta" onclick="cerrarIncidencia(${inc.id})"><i class="bi bi-check-circle"></i></button>`
                    : '';

                html += `
                    <div class="item-incidencia">
                        <div class="incidencia-info">
                            <span class="titulo btn-detalle-incidencia" data-bs-toggle="modal" data-bs-target="#modalDetalleIncidencia" data-id="${inc.id}">${inc.titulo}</span>
                            <span class="fecha">${inc.fecha}</span>
                        </div>
                        <div class="incidencia-acciones">
                            <span class="estado-tag ${inc.estado}">${inc.estado_texto}</span>
                            ${botonResolver}
                        </div>
                    </div>`;
            });
            contenedor.innerHTML = html;
        }, 600);
    })
    .catch(error => {
        contenedor.classList.remove('lista-cargando');
        console.error('Error al cargar incidencias:', error);
    });
}

function cerrarIncidencia(idIncidencia) {
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
        if (resultado.isConfirmed) {
            fetch(`../incidencia/${idIncidencia}/cerrar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(respuesta => {
                if (respuesta.ok) {
                    Swal.fire({
                        title: '¡Genial!',
                        text: 'La incidencia se ha cerrado con éxito.',
                        iconHtml: crearOsoExito(),
                        customClass: { icon: 'oso-icon' },
                        confirmButtonColor: '#035498'
                    });
                    cargarIncidencias();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo cerrar la incidencia.',
                        iconHtml: crearOsoError(),
                        customClass: { icon: 'oso-icon' },
                        confirmButtonColor: '#d9534f'
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });
}
