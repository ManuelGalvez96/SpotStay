/**
 * Funciones generadoras del Oso (Mascota SpotStay)
 */
const crearOsoExito = () => `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <circle class="yeti-part" cx="62" cy="52" r="14" fill="#4B5563" />
        <circle class="yeti-part" cx="138" cy="52" r="14" fill="#4B5563" />
        <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" fill="#6B7280" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" fill="#2C3E50" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" fill="#34495E" />
        <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" fill="#E74C3C" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 128 Q100 133 108 128" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle class="hand hand-l" cx="48" cy="180" r="19" fill="#5D6D7B" />
        <circle class="hand hand-r" cx="152" cy="180" r="19" fill="#5D6D7B" />
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#90EE90" stroke="#228B22" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#228B22">✓</text>
    </svg>`;

const crearOsoError = () => `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <circle class="yeti-part" cx="62" cy="52" r="14" fill="#4B5563" />
        <circle class="yeti-part" cx="138" cy="52" r="14" fill="#4B5563" />
        <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" fill="#6B7280" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" fill="#2C3E50" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" fill="#34495E" />
        <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" fill="#E74C3C" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 135 Q100 128 108 135" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle class="hand hand-l" cx="48" cy="180" r="19" fill="#5D6D7B" />
        <circle class="hand hand-r" cx="152" cy="180" r="19" fill="#5D6D7B" />
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FFB6C1" stroke="#DC143C" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#DC143C">✗</text>
    </svg>`;

const crearOsoPregunta = () => `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <circle class="yeti-part" cx="62" cy="52" r="14" fill="#4B5563" />
        <circle class="yeti-part" cx="138" cy="52" r="14" fill="#4B5563" />
        <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" fill="#6B7280" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" fill="#2C3E50" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" fill="#34495E" />
        <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" fill="#E74C3C" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M85 105 L115 105" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle class="hand hand-l" cx="48" cy="180" r="19" fill="#5D6D7B" />
        <circle class="hand hand-r" cx="152" cy="180" r="19" fill="#5D6D7B" />
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FFE4B5" stroke="#FF8C00" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#FF8C00">?</text>
    </svg>`;


/**
 * Función global para asignar eventos de pago a los formularios.
 * Se define como declaración de función para que el hoisting la haga disponible siempre.
 */
function inicializarPagosInquilino() {
    const formularios = document.querySelectorAll('.form-pago-cuota, .form-pago-grid');
    
    formularios.forEach(form => {
        form.onsubmit = (e) => {
            e.preventDefault();

            const monto = form.getAttribute('data-monto') || 'la cuota';
            
            Swal.fire({
                title: '¿Realizar pago?',
                text: `Vas a proceder al pago de ${monto} correspondiente al alquiler.`,
                iconHtml: crearOsoPregunta(),
                customClass: { icon: 'oso-icon' },
                showCancelButton: true,
                confirmButtonText: 'Sí, pagar ahora',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#1AA068',
                cancelButtonColor: '#6B7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    const btn = form.querySelector('button');
                    const originalText = btn.innerText;
                    btn.disabled = true;
                    btn.innerText = 'Procesando...';

                    const url = form.getAttribute('action');
                    const token = form.querySelector('input[name="_token"]').value;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
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
                                text: data.message || 'No se pudo procesar el pago.',
                                iconHtml: crearOsoError(),
                                customClass: { icon: 'oso-icon' },
                                confirmButtonColor: '#d9534f'
                            });
                            btn.disabled = false;
                            btn.innerText = originalText;
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
                        btn.disabled = false;
                        btn.innerText = originalText;
                    });
                }
            });
        };
    });
}

// Exponemos la función a window para que otros scripts (filtros) puedan llamarla
window.inicializarPagosInquilino = inicializarPagosInquilino;

let _oldOnloadInquilino = window.onload;
window.onload = () => {
    if (_oldOnloadInquilino) _oldOnloadInquilino();
    iniciarTemporizadorAlquileres();
    cargarIncidencias();
    inicializarPagosInquilino();

    setInterval(iniciarTemporizadorAlquileres, 60000);
    setInterval(cargarIncidencias, 30000);

    const formReportar = document.getElementById('form-reportar-incidencia');
    if (formReportar) {
        formReportar.onsubmit = (e) => {
            e.preventDefault();
            const btnEnviar = formReportar.querySelector('#boton-enviar');
            const originalText = btnEnviar.innerText;

            btnEnviar.disabled = true;
            btnEnviar.innerText = 'Enviando...';

            const formData = new FormData(formReportar);
            const url = formReportar.getAttribute('action');

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    formReportar.reset();
                    const modalEl = document.getElementById('modalReportar');
                    const modalObj = bootstrap.Modal.getInstance(modalEl);
                    if (modalObj) modalObj.hide();

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
                        text: 'Error: ' + data.message,
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
                btnEnviar.disabled = false;
                btnEnviar.innerText = originalText;
            });
        };
    }

    const modalDetalle = document.getElementById('modalDetalleIncidencia');
    if (modalDetalle) {
        modalDetalle.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const id = trigger.getAttribute('data-id');

            const loading = modalDetalle.querySelector('#loading-detalle');
            const contenido = modalDetalle.querySelector('#contenido-detalle');

            loading.style.display = 'block';
            contenido.style.display = 'none';

            fetch(`../incidencia/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    modalDetalle.querySelector('#detalle-titulo').textContent = data.titulo;
                    modalDetalle.querySelector('#detalle-descripcion').textContent = data.descripcion;
                    modalDetalle.querySelector('#detalle-categoria').textContent = data.categoria;
                    modalDetalle.querySelector('#detalle-prioridad').textContent = data.prioridad;
                    modalDetalle.querySelector('#detalle-fecha').textContent = data.fecha;

                    loading.style.display = 'none';
                    contenido.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los detalles de la incidencia.');
                });
        });
    }

    const fAutor = document.getElementById('filtro-autor');
    const fEstado = document.getElementById('filtro-estado');
    if (fAutor) fAutor.onchange = cargarIncidencias;
    if (fEstado) fEstado.onchange = cargarIncidencias;
};

function iniciarTemporizadorAlquileres() {
    const nodosTemporizador = document.querySelectorAll('.js-tiempo-restante');

    nodosTemporizador.forEach(nodo => {
        const fechaFinRaw = nodo.getAttribute('data-fecha-fin');
        if (!fechaFinRaw) return;

        const stringFecha = fechaFinRaw.split(' ')[0];
        const partesFecha = stringFecha.split('-');

        if (partesFecha.length !== 3) return;

        const year = parseInt(partesFecha[0], 10);
        const month = parseInt(partesFecha[1], 10) - 1;
        const day = parseInt(partesFecha[2], 10);

        const finDelDia = new Date(year, month, day, 23, 59, 59);
        const ahora = new Date();

        let diffMillis = finDelDia - ahora;
        let diffAbsoluta = Math.abs(diffMillis);

        if (diffMillis <= 0) {
            let diasPasados = Math.floor(diffAbsoluta / 86400000);
            const alertaGrid = nodo.closest('.contenedor-alerta-js');
            
            if (alertaGrid) {
                const cajaExpirada = alertaGrid.closest('.alerta-fin-contrato');
                if (cajaExpirada) cajaExpirada.classList.add('estado-expirado');

                let textoTiempo = diasPasados >= 1 
                    ? `hace <strong>${diasPasados} día${diasPasados > 1 ? 's' : ''}</strong>`
                    : `hace <strong>${Math.floor(diffAbsoluta / 3600000)}h ${Math.floor((diffAbsoluta % 3600000) / 60000)}m</strong>`;

                alertaGrid.innerHTML = `El contrato finalizó ${textoTiempo}. <br>Para mas informacion entra en ver detalles.`;
            } else {
                let textoTiempo = diasPasados >= 1
                    ? `hace ${diasPasados} día${diasPasados > 1 ? 's' : ''}`
                    : `hace ${Math.floor(diffAbsoluta / 3600000)}h ${Math.floor((diffAbsoluta % 3600000) / 60000)}m`;

                const cardGestion = nodo.closest('.card-gestion');
                if (cardGestion) {
                    cardGestion.classList.add('estado-expirado');
                    const label = cardGestion.querySelector('.label');
                    if (label) label.innerText = 'CONTRATO FINALIZADO';
                    const valorKpi = cardGestion.querySelector('.valor-kpi');
                    if (valorKpi) valorKpi.style.display = 'none';
                }

                nodo.innerText = `¡El contrato finalizó ${textoTiempo}!`;
                nodo.classList.add('texto-expirado');
            }
        } else {
            let minutosTotales = Math.floor(diffMillis / 60000);
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
    const filtroAutor = document.getElementById('filtro-autor')?.value || 'todas';
    const filtroEstado = document.getElementById('filtro-estado')?.value || 'todas';

    contenedor.classList.add('lista-cargando');

    fetch(`../propiedad/${idPropiedad}/incidencias?autor=${filtroAutor}&estado=${filtroEstado}`)
    .then(response => response.json())
    .then(incidencias => {
        setTimeout(() => {
            contenedor.classList.remove('lista-cargando');
            if (incidencias.length === 0) {
                contenedor.innerHTML = '<div class="aviso-vacio"><p>No se encontraron incidencias con los filtros aplicados.</p></div>';
                return;
            }

            let html = '';
            incidencias.forEach(inc => {
                const btnResolver = (inc.id_reporta == inc.auth_id && inc.estado != 'resuelta')
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
                            ${btnResolver}
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

function cerrarIncidencia(id) {
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
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`../incidencia/${id}/cerrar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
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
