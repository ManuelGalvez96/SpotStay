/* ========================================
   DASHBOARD ADMIN — SPOTYSTAY
   JavaScript Vanilla — Sin frameworks, sin async/await
   ======================================== */

/* ── Variables globales ── */
var csrfToken = null;
var modalSolicitud = null;
var solicitudActualId = null;

/* ── window.onload ── */
window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    
    // Inicializar modal de solicitudes si existe
    var modalElement = document.getElementById('modalSolicitudDash');
    if (modalElement && typeof bootstrap !== 'undefined') {
        modalSolicitud = new bootstrap.Modal(modalElement);
    }
    
    iniciarDonut();
    asignarEventosBotones();
    asignarEventoBuscadorAlquileres();
    asignarEventoBuscadorSolicitudes();
    asignarEventosBotonesSolicitudes();
    asignarEventosNavIconos();
    asignarEventosModalSolicitudes();
    asignarEventosContactarIncidencias();
};

/* ================================================
   FUNCIÓN: iniciarDonut
   Inicializa el gráfico Chart.js tipo doughnut
   ================================================ */
function iniciarDonut() {
    var canvasElement = document.getElementById('donutChart');
    
    if (!canvasElement) {
        return;
    }
    
    // Fetch stats from API
    fetch('/admin/dashboard/stats')
        .then(function(response) {
            if (!response.ok) throw new Error('Error al cargar estadísticas');
            return response.json();
        })
        .then(function(result) {
            var chartData = result.data || [0, 0, 0, 0];
            iniciarDonutConDatos(chartData);
        })
        .catch(function(error) {
            console.error('Error al cargar estadísticas del dashboard:', error);
            // Usar datos por defecto si falla la carga
            iniciarDonutConDatos([0, 0, 0, 0]);
        });
}

function iniciarDonutConDatos(chartData) {
    var canvasElement = document.getElementById('donutChart');
    var ctx = canvasElement.getContext('2d');
    
    var donutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Inquilinos', 'Arrendadores', 'Miembros', 'Gestores'],
            datasets: [{
                data: chartData,
                backgroundColor: ['#1AA068', '#035498', '#94A3B8', '#CBD5E1'],
                borderColor: '#FFFFFF',
                borderWidth: 2
            }]
        },
        options: {
            cutout: '72%',
            responsive: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 10,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    borderColor: '#FFFFFF',
                    borderWidth: 1
                }
            },
            animation: false
        }
    });
}

/* ================================================
   FUNCIÓN: asignarEventosBotones
   Asigna .onclick a botones Aprobar y Rechazar
   ================================================ */
function asignarEventosBotones() {
    var botonesAprobar = document.querySelectorAll('.btn-aprobar');
    var botonesRechazar = document.querySelectorAll('.btn-rechazar');
    
    // Asignar onclick a botones Aprobar
    for (var i = 0; i < botonesAprobar.length; i++) {
        var btnAprobar = botonesAprobar[i];
        btnAprobar.onclick = function(event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            aprobarAlquiler(id);
        };
    }
    
    // Asignar onclick a botones Rechazar
    for (var i = 0; i < botonesRechazar.length; i++) {
        var btnRechazar = botonesRechazar[i];
        btnRechazar.onclick = function(event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            rechazarAlquiler(id);
        };
    }
}

/* ================================================
   FUNCIÓN: aprobarAlquiler
   Hace fetch POST para aprobar un alquiler
   ================================================ */
function aprobarAlquiler(id) {
    var url = '/admin/alquiler/' + id + '/aprobar';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            // Actualizar badge a "Activo"
            var tr = document.querySelector('tr[data-id="' + id + '"]');
            if (tr) {
                var badgeElement = tr.querySelector('.badge-estado');
                if (badgeElement) {
                    badgeElement.textContent = 'Activo';
                    badgeElement.className = 'badge-estado badge-activo';
                }
                
                // Ocultar botones de acción
                var accionesDiv = tr.querySelector('.acciones-tabla');
                if (accionesDiv) {
                    accionesDiv.innerHTML = '<span class="sin-accion">—</span>';
                }
            }
        } else {
            console.error('Error al aprobar alquiler:', data.message);
        }
    })
    .catch(function(error) {
        console.error('Error en fetch:', error);
    });
}

/* ================================================
   FUNCIÓN: rechazarAlquiler
   Hace fetch POST para rechazar un alquiler
   ================================================ */
function rechazarAlquiler(id) {
    var url = '/admin/alquiler/' + id + '/rechazar';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            // Actualizar badge a "Rechazado"
            var tr = document.querySelector('tr[data-id="' + id + '"]');
            if (tr) {
                var badgeElement = tr.querySelector('.badge-estado');
                if (badgeElement) {
                    badgeElement.textContent = 'Rechazado';
                    badgeElement.className = 'badge-estado badge-rechazado';
                }
                
                // Ocultar botones de acción
                var accionesDiv = tr.querySelector('.acciones-tabla');
                if (accionesDiv) {
                    accionesDiv.innerHTML = '<span class="sin-accion">—</span>';
                }
            }
        } else {
            console.error('Error al rechazar alquiler:', data.message);
        }
    })
    .catch(function(error) {
        console.error('Error en fetch:', error);
    });
}

/* ================================================
   FUNCIÓN: asignarEventoBuscadorAlquileres
   Asigna evento onkeyup para filtro AJAX
   ================================================ */
function asignarEventoBuscadorAlquileres() {
    var buscador = document.getElementById('buscadorAlquileres');
    
    if (!buscador) {
        return;
    }
    
    // Evento onkeyup para búsqueda en vivo
    buscador.onkeyup = function() {
        filtrarAlquileres();
    };
}

/* ================================================
   FUNCIÓN: asignarEventoBuscadorSolicitudes
   Asigna evento onkeyup para filtro AJAX de solicitudes
   ================================================ */
function asignarEventoBuscadorSolicitudes() {
    var buscador = document.getElementById('buscadorSolicitudes');
    
    if (!buscador) {
        return;
    }
    
    // Evento onkeyup para búsqueda en vivo
    buscador.onkeyup = function() {
        filtrarSolicitudesDash();
    };
}

/* ================================================
   FUNCIÓN: filtrarAlquileres
   Hace fetch AJAX para filtrar alquileres
   ================================================ */
function filtrarAlquileres() {
    var buscador = document.getElementById('buscadorAlquileres');
    var busqueda = buscador ? buscador.value : '';
    
    var url = '/admin/alquileres/filtrar?q=' + encodeURIComponent(busqueda);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        actualizarTablaAlquileres(data);
        asignarEventosBotones();
    })
    .catch(function(error) {
        console.error('Error en filtro AJAX:', error);
    });
}

/* ================================================
   FUNCIÓN: actualizarTablaAlquileres
   Actualiza las filas de la tabla de alquileres
   Máximo 5 resultados
   ================================================ */
function actualizarTablaAlquileres(data) {
    var tbody = document.getElementById('tbodyAlquileres');
    
    if (!tbody) {
        return;
    }
    
    tbody.innerHTML = '';
    
    if (data.alquileres && data.alquileres.length > 0) {
        // Limitar a máximo 5 resultados
        var limite = Math.min(data.alquileres.length, 5);
        
        for (var i = 0; i < limite; i++) {
            var alquiler = data.alquileres[i];
            var badgeClass = 'badge-' + alquiler.estado_alquiler.replace('_', '-');
            var estadoLabel = alquiler.estado_alquiler.charAt(0).toUpperCase() + alquiler.estado_alquiler.slice(1);
            
            var fila = document.createElement('tr');
            fila.setAttribute('data-id', alquiler.id_alquiler);
            fila.setAttribute('data-nombre', alquiler.titulo_propiedad + ', ' + alquiler.ciudad_propiedad);
            fila.setAttribute('data-inquilino', alquiler.nombre_inquilino);
            fila.setAttribute('data-estado', alquiler.estado_alquiler);
            
            var accionesHTML = '';
            if (alquiler.estado_alquiler === 'pendiente') {
                accionesHTML = '<div class="acciones-tabla">' +
                    '<button class="btn-aprobar" data-id="' + alquiler.id_alquiler + '">✓ Aprobar</button>' +
                    '<button class="btn-rechazar" data-id="' + alquiler.id_alquiler + '">✕ Rechazar</button>' +
                    '</div>';
            } else {
                accionesHTML = '<span class="sin-accion">—</span>';
            }
            
            fila.innerHTML = '<td>' + alquiler.titulo_propiedad + ', ' + alquiler.ciudad_propiedad + '</td>' +
                '<td>' + alquiler.nombre_inquilino + '</td>' +
                '<td><span class="badge-estado ' + badgeClass + '">' + estadoLabel + '</span></td>' +
                '<td>' + accionesHTML + '</td>';
            
            tbody.appendChild(fila);
        }
    } else {
        var fila = document.createElement('tr');
        fila.innerHTML = '<td colspan="4" class="tabla-vacia-cell">No hay alquileres pendientes</td>';
        tbody.appendChild(fila);
    }
}

/* ================================================
   FUNCIÓN: asignarEventosNavIconos
   Asigna .onclick a iconos de navegación
   ================================================ */
function asignarEventosNavIconos() {
    var botonesNav = document.querySelectorAll('.btn-nav-icon');
    
    for (var i = 0; i < botonesNav.length; i++) {
        var btnNav = botonesNav[i];
        btnNav.onclick = function(event) {
            event.preventDefault();
            var ruta = this.getAttribute('data-ruta');
            if (ruta) {
                window.location.href = ruta;
            }
        };
    }
}

/* ================================================
   FUNCIÓN: asignarEventosBotonesSolicitudes
   Asigna onclick a botones "Revisar" solicitudes
   ================================================ */
function asignarEventosBotonesSolicitudes() {
    var botonesRevisar = document.querySelectorAll('.btn-revisar');
    
    for (var i = 0; i < botonesRevisar.length; i++) {
        var btnRevisar = botonesRevisar[i];
        btnRevisar.onclick = function(event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            abrirModalSolicitud(id);
        };
    }
}

/* ================================================
   FUNCIÓN: abrirModalSolicitud
   Abre modal con datos de la solicitud
   ================================================ */
function abrirModalSolicitud(id) {
    solicitudActualId = id;

    fetch('/admin/solicitudes/' + id)
        .then(function(response) {
            return response.json();
        })
        .then(function(datos) {
            rellenarModalSolicitud(datos);
            
            if (modalSolicitud) {
                modalSolicitud.show();
            }
        })
        .catch(function(error) {
            console.error('Error al abrir modal:', error);
        });
}

/* ================================================
   FUNCIÓN: rellenarModalSolicitud
   Rellena el modal con datos de solicitud
   ================================================ */
function rellenarModalSolicitud(datos) {
    var partes = (datos.nombre_usuario || '').split(' ');
    var iniciales = (partes[0] ? partes[0].charAt(0) : '') + (partes[1] ? partes[1].charAt(0) : '');
    var colores = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#FFD5CC', '#D7EAF9', '#EDE7F6', '#D5F5E3', '#FAD7D7'];
    var color = colores[datos.id_solicitud_arrendador % 8];
    
    function establecerTexto(id, valor) {
        var elemento = document.getElementById(id);
        if (elemento) {
            elemento.textContent = valor || '—';
        }
    }

    function formatearBooleano(valor) {
        return valor ? 'Sí' : 'No';
    }

    function formatearFecha(valor) {
        if (!valor) {
            return '—';
        }

        var fecha = new Date(valor);
        if (isNaN(fecha.getTime())) {
            return valor;
        }

        return fecha.toLocaleDateString('es-ES');
    }
    
    // Rellenar datos básicos
    var avatarEl = document.getElementById('modalAvatarSolicitudDash');
    if (avatarEl) {
        avatarEl.style.background = color;
        avatarEl.textContent = iniciales.toUpperCase();
    }
    
    var nombreEl = document.getElementById('modalNombreSolicitudDash');
    if (nombreEl) nombreEl.textContent = datos.nombre_usuario || '—';
    
    var emailEl = document.getElementById('modalEmailSolicitudDash');
    if (emailEl) emailEl.textContent = datos.email_usuario || '—';
    
    var ciudadEl = document.getElementById('modalCiudadSolicitudDash');
    if (ciudadEl) {
        ciudadEl.innerHTML = '<i class="bi bi-geo-alt"></i> ' + (datos.direccion_fiscal_solicitud || 'No disponible');
    }
    
    establecerTexto('modalTelefonoSolicitudDash', datos.telefono_solicitud);
    establecerTexto('modalFechaNacimientoSolicitudDash', formatearFecha(datos.fecha_nacimiento_solicitud));
    establecerTexto('modalTipoDocumentoSolicitudDash', datos.tipo_documento_solicitud);
    establecerTexto('modalNumeroDocumentoSolicitudDash', datos.numero_documento_solicitud);
    establecerTexto('modalNifSolicitudDash', datos.nif_solicitud);
    establecerTexto('modalIbanSolicitudDash', datos.iban_solicitud);
    establecerTexto('modalTitularCuentaSolicitudDash', datos.titular_cuenta_solicitud);
    establecerTexto('modalDireccionFiscalSolicitudDash', datos.direccion_fiscal_solicitud);
    establecerTexto('modalTipoArrendadorSolicitudDash', datos.tipo_arrendador_solicitud);
    establecerTexto('modalNumPropiedadesSolicitudDash', datos.num_propiedades_previstas_solicitud !== null && datos.num_propiedades_previstas_solicitud !== undefined ? String(datos.num_propiedades_previstas_solicitud) : '—');
    establecerTexto('modalEsPropietarioSolicitudDash', formatearBooleano(datos.es_propietario_solicitud));
    establecerTexto('modalAceptaTerminosSolicitudDash', formatearBooleano(datos.acepta_terminos_solicitud));
    establecerTexto('modalAceptaVeracidadSolicitudDash', formatearBooleano(datos.acepta_veracidad_solicitud));
    establecerTexto('modalDescripcionSolicitudDash', datos.descripcion_solicitud || '—');
    establecerTexto('modalFechaAceptacionSolicitudDash', formatearFecha(datos.fecha_aceptacion_solicitud));
    
    // Actualizar estado
    var badgeEl = document.getElementById('modalBadgeEstadoSolicitudDash');
    if (badgeEl) {
        var estado = datos.estado_solicitud_arrendador || 'pendiente';
        var estadoLabel = estado.charAt(0).toUpperCase() + estado.slice(1);
        badgeEl.textContent = estadoLabel;
        badgeEl.className = 'badge';
        if (estado === 'aprobada') {
            badgeEl.classList.add('bg-success');
        } else if (estado === 'rechazada') {
            badgeEl.classList.add('bg-danger');
        } else {
            badgeEl.classList.add('bg-warning');
        }
    }
    
    establecerTexto('modalEstadoSolicitudDash', (datos.estado_solicitud_arrendador || 'pendiente').charAt(0).toUpperCase() + (datos.estado_solicitud_arrendador || 'pendiente').slice(1));
    
    // Limpiar notas
    var notasEl = document.getElementById('modalNotasSolicitudDash');
    if (notasEl) notasEl.value = '';
}

/* ================================================
   FUNCIÓN: asignarEventosModalSolicitudes
   Asigna eventos a los botones del modal de solicitudes
   ================================================ */
function asignarEventosModalSolicitudes() {
    var botonAprobar = document.getElementById('btnAprobarSolicitudDash');
    var botonRechazar = document.getElementById('btnRechazarSolicitudDash');

    if (botonAprobar) {
        botonAprobar.onclick = function (event) {
            event.preventDefault();
            aprobarSolicitudModal();
        };
    }

    if (botonRechazar) {
        botonRechazar.onclick = function (event) {
            event.preventDefault();
            rechazarSolicitudModal();
        };
    }
}

/* ================================================
   FUNCIÓN: aprobarSolicitudModal
   Aprueba la solicitud seleccionada en el modal
   ================================================ */
function aprobarSolicitudModal() {
    if (!solicitudActualId) {
        console.error('No hay una solicitud seleccionada.');
        return;
    }

    fetch('/admin/solicitudes/' + solicitudActualId + '/aprobar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {
        if (data.success) {
            if (modalSolicitud) {
                modalSolicitud.hide();
            }

            window.location.reload();
        } else {
            console.error('Error al aprobar solicitud:', data.error || data.message || 'Respuesta inválida');
        }
    })
    .catch(function (error) {
        console.error('Error en fetch al aprobar solicitud:', error);
    });
}

/* ================================================
   FUNCIÓN: rechazarSolicitudModal
   Rechaza la solicitud seleccionada en el modal
   ================================================ */
function rechazarSolicitudModal() {
    if (!solicitudActualId) {
        console.error('No hay una solicitud seleccionada.');
        return;
    }

    var notasEl = document.getElementById('modalNotasSolicitudDash');
    var notas = notasEl ? notasEl.value.trim() : '';

    fetch('/admin/solicitudes/' + solicitudActualId + '/rechazar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ notas: notas })
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (data) {
        if (data.success) {
            if (modalSolicitud) {
                modalSolicitud.hide();
            }

            window.location.reload();
        } else {
            console.error('Error al rechazar solicitud:', data.error || data.message || 'Respuesta inválida');
        }
    })
    .catch(function (error) {
        console.error('Error en fetch al rechazar solicitud:', error);
    });
}

/* ================================================
   FUNCIÓN: asignarEventosContactarIncidencias
   Asigna eventos a los botones contactar incidencias
   ================================================ */
function asignarEventosContactarIncidencias() {
    var botonesContactar = document.querySelectorAll('.btn-contactar-inc');
    var btnEnviar = document.getElementById('btnEnviarContactoIncidencia');

    // Asignar onclick a botones "Contactar"
    for (var i = 0; i < botonesContactar.length; i++) {
        var btn = botonesContactar[i];
        btn.onclick = function(event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            rellenarModalIncidencia(id);
        };
    }

    // Asignar onclick al botón enviar
    if (btnEnviar) {
        btnEnviar.onclick = function(event) {
            event.preventDefault();
            enviarContactoIncidencia();
        };
    }
}

/* ================================================
   FUNCIÓN: rellenarModalIncidencia
   Rellena el modal con datos de la incidencia
   ================================================ */
var incidenciaActualId = null;

function rellenarModalIncidencia(id) {
    incidenciaActualId = id;

    // Obtener fila de la tabla
    var fila = document.querySelector('tr[data-id="' + id + '"]');
    if (!fila) return;

    var titulo = fila.getAttribute('data-titulo') || '—';
    var propiedad = fila.getAttribute('data-propiedad') || '—';
    var estado = fila.getAttribute('data-estado') || '';
    var inquilino = fila.getAttribute('data-inquilino') || '—';
    var arrendador = fila.getAttribute('data-arrendador') || '—';
    var gestor = fila.getAttribute('data-gestor') || '—';
    var encargadoPago = fila.getAttribute('data-encargado-pago') || '—';

    // Rellenar campos del modal
    var modalTitulo = document.getElementById('modalTituloIncidencia');
    var modalPropiedad = document.getElementById('modalPropiedadIncidencia');
    var modalInquilino = document.getElementById('modalInquilinoIncidencia');
    var modalArrendador = document.getElementById('modalArrendadorIncidencia');
    var modalGestor = document.getElementById('modalGestorIncidencia');
    var modalEncargadoPagoSeccion = document.getElementById('modalEncargadoPagoSeccion');
    var modalEncargadoPago = document.getElementById('modalEncargadoPagoIncidencia');
    var modalDestino = document.getElementById('modalDestinoContacto');
    var modalAsunto = document.getElementById('modalAsuntoContacto');
    var modalMensaje = document.getElementById('modalMensajeContacto');

    if (modalTitulo) modalTitulo.textContent = titulo;
    if (modalPropiedad) modalPropiedad.textContent = propiedad;
    if (modalInquilino) modalInquilino.textContent = inquilino;
    if (modalArrendador) modalArrendador.textContent = arrendador;
    if (modalGestor) modalGestor.textContent = gestor;
    
    // Mostrar/ocultar sección de encargado de pago
    if (estado === 'esperando_pago' && modalEncargadoPagoSeccion) {
        modalEncargadoPagoSeccion.style.display = 'block';
        if (modalEncargadoPago) modalEncargadoPago.textContent = encargadoPago;
    } else if (modalEncargadoPagoSeccion) {
        modalEncargadoPagoSeccion.style.display = 'none';
    }
    
    if (modalDestino) modalDestino.value = '';
    if (modalAsunto) modalAsunto.value = 'Incidencia inactiva — Requiere atención';
    if (modalMensaje) modalMensaje.value = '';

    // Mostrar modal
    var modal = document.getElementById('modalContactarIncidencia');
    if (modal && typeof bootstrap !== 'undefined') {
        var bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

/* ================================================
   FUNCIÓN: enviarContactoIncidencia
   Envía el correo de contacto sobre la incidencia
   ================================================ */
function enviarContactoIncidencia() {
    if (!incidenciaActualId) {
        alert('Error: No hay incidencia seleccionada');
        return;
    }

    var destino = document.getElementById('modalDestinoContacto').value;
    var asunto = document.getElementById('modalAsuntoContacto').value;
    var mensaje = document.getElementById('modalMensajeContacto').value;

    if (!destino) {
        alert('Por favor, selecciona un destinatario');
        return;
    }

    if (!asunto.trim()) {
        alert('Por favor, escribe un asunto');
        return;
    }

    if (!mensaje.trim()) {
        alert('Por favor, escribe un mensaje');
        return;
    }

    var url = '/admin/incidencias/' + incidenciaActualId + '/contactar';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            destino: destino,
            asunto: asunto,
            mensaje: mensaje
        })
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            alert('Correo enviado correctamente');
            var modal = document.getElementById('modalContactarIncidencia');
            if (modal && typeof bootstrap !== 'undefined') {
                var bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            }
        } else {
            alert('Error al enviar correo: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Error al enviar correo: ' + error.message);
    });
}
