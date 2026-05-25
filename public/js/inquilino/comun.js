/**
 * Funciones generadoras del Oso (Mascota SpotStay) y Lógica Común
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
    const botonesPagar = document.querySelectorAll('.btn-pago');
    
    botonesPagar.forEach(botonPagar => {
        let pagoEnCurso = false;

        botonPagar.onclick = () => {
            if (pagoEnCurso) return;

            const formulario = botonPagar.closest('form');
            if (!formulario) return;

            const montoTotal = formulario.getAttribute('data-monto') || 'la cuota';
            const conceptos = formulario.getAttribute('data-concepto');
            
            let mensajeAlerta = `Vas a proceder al pago de un total de ${montoTotal}.`;
            if (conceptos && conceptos.trim() !== "") {
                mensajeAlerta += `<br><small>Correspondiente a: <b>${conceptos}</b></small>`;
            }
            mensajeAlerta += `<br><br>¿Deseas continuar?`;
            
            Swal.fire({
                title: '¿Confirmas el pago?',
                html: mensajeAlerta,
                iconHtml: crearOsoPregunta(),
                customClass: { icon: 'oso-icon' },
                showCancelButton: true,
                confirmButtonText: 'Sí, pagar ahora',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#1AA068',
                cancelButtonColor: '#6B7280'
            }).then((resultado) => {
                if (resultado.isConfirmed) {
                    pagoEnCurso = true;
                    const textoOriginal = botonPagar.innerText;
                    botonPagar.disabled = true;
                    botonPagar.innerText = 'Procesando...';

                    const rutaEnvio = formulario.getAttribute('action');
                    const fichaToken = document.querySelector('input[name="_token"]')?.value;
                    const datosFormulario = new FormData(formulario);

                    fetch(rutaEnvio, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': fichaToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: datosFormulario
                    })
                    .then(respuesta => respuesta.json())
                    .then(datos => {
                        if (datos.success && datos.url) {
                            // REDIRECCIÓN A STRIPE
                            window.location.href = datos.url;
                        } else {
                            Swal.fire({
                                title: 'Error en el pago',
                                text: datos.message || 'No se pudo procesar el pago en este momento.',
                                iconHtml: crearOsoError(),
                                customClass: { icon: 'oso-icon' },
                                confirmButtonColor: '#d9534f'
                            });
                            pagoEnCurso = false;
                            botonPagar.disabled = false;
                            botonPagar.innerText = textoOriginal;
                        }
                    })
                    .catch(error => {
                        console.error('Error en la petición de pago:', error);
                        Swal.fire({
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor para procesar el pago.',
                            iconHtml: crearOsoError(),
                            customClass: { icon: 'oso-icon' },
                            confirmButtonColor: '#d9534f'
                        });
                        pagoEnCurso = false;
                        botonPagar.disabled = false;
                        botonPagar.innerText = textoOriginal;
                    });
                }
            });
        };
    });
}

/**
 * Comprueba si hay mensajes de éxito o error inyectados desde Laravel (Session)
 * y muestra el SweetAlert correspondiente con la mascota.
 */
function comprobarAlertasSesion() {
    const contenedor = document.querySelector('.contenedor-ver-propiedad') || document.querySelector('.seccion-gestion-inquilino');
    if (!contenedor) return;

    const exito = contenedor.getAttribute('data-mensaje-exito');
    const error = contenedor.getAttribute('data-mensaje-error');

    if (exito && exito.trim() !== "") {
        Swal.fire({
            title: '¡Operación con éxito!',
            text: exito,
            iconHtml: crearOsoExito(),
            customClass: { icon: 'oso-icon' },
            confirmButtonColor: '#035498'
        });
    }

    if (error && error.trim() !== "") {
        Swal.fire({
            title: 'Ups...',
            text: error,
            iconHtml: crearOsoError(),
            customClass: { icon: 'oso-icon' },
            confirmButtonColor: '#d33'
        });
    }
}

/**
 * Gestiona los temporizadores de fin de contrato en las tarjetas
 */
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

// Inicialización directa al cargar el script común
inicializarPagosInquilino();
comprobarAlertasSesion();

if (document.querySelectorAll('.js-tiempo-restante').length > 0) {
    iniciarTemporizadorAlquileres();
    setInterval(iniciarTemporizadorAlquileres, 60000);
}
