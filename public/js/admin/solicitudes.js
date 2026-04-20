/* ========================================
   GESTIÓN DE SOLICITUDES — SPOTYSTAY
   JavaScript Vanilla — Sin frameworks, sin async/await
   ======================================== */

var csrfToken;
var solicitudIdActual;
var modalSolicitud;
var paginaActualSol = 1;

/* ──────────────────────────────────────────
   FUNCIÓN: Crear OSO de ÉXITO
   Retorna SVG HTML del oso con expresión feliz
──────────────────────────────────────────── */
var crearOsoExito = function() {
    return `
    <svg viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg">
        <!-- Cabeza -->
        <circle class="yeti-part" cx="100" cy="80" r="45" />
        <!-- Orejas -->
        <circle class="yeti-part" cx="70" cy="45" r="18" />
        <circle class="yeti-part" cx="130" cy="45" r="18" />
        <!-- Traje -->
        <rect class="suit-jacket" x="55" y="120" width="90" height="100" rx="10" />
        <!-- Camisa -->
        <rect class="suit-shirt" x="65" y="130" width="70" height="50" rx="5" />
        <!-- Corbata -->
        <polygon class="suit-tie" points="100,130 95,160 105,160" />
        <!-- Cara con expresión feliz -->
        <g id="face-group">
            <!-- Ojos felices -->
            <circle cx="82" cy="75" r="5" fill="#000" />
            <circle cx="118" cy="75" r="5" fill="#000" />
            <!-- Boca sonriente -->
            <path d="M85 95 Q100 110 115 95" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <!-- Manos -->
        <circle class="hand" cx="48" cy="180" r="19" />
        <circle class="hand" cx="152" cy="180" r="19" />
        <!-- Checkmark de validación -->
        <g transform="translate(100, 215)">
            <circle cx="0" cy="0" r="15" fill="#1AA068" />
            <path d="M -8 0 L -2 8 L 10 -5" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round" />
        </g>
    </svg>
    `;
};

/* ──────────────────────────────────────────
   FUNCIÓN: Crear OSO de ERROR
   Retorna SVG HTML del oso con expresión triste
──────────────────────────────────────────── */
var crearOsoError = function() {
    return `
    <svg viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg">
        <!-- Cabeza -->
        <circle class="yeti-part" cx="100" cy="80" r="45" />
        <!-- Orejas -->
        <circle class="yeti-part" cx="70" cy="45" r="18" />
        <circle class="yeti-part" cx="130" cy="45" r="18" />
        <!-- Traje -->
        <rect class="suit-jacket" x="55" y="120" width="90" height="100" rx="10" />
        <!-- Camisa -->
        <rect class="suit-shirt" x="65" y="130" width="70" height="50" rx="5" />
        <!-- Corbata -->
        <polygon class="suit-tie" points="100,130 95,160 105,160" />
        <!-- Cara con expresión triste -->
        <g id="face-group">
            <!-- Ojos tristes -->
            <circle cx="82" cy="75" r="5" fill="#000" />
            <circle cx="118" cy="75" r="5" fill="#000" />
            <!-- Boca triste -->
            <path d="M85 100 Q100 90 115 100" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <!-- Manos -->
        <circle class="hand" cx="48" cy="180" r="19" />
        <circle class="hand" cx="152" cy="180" r="19" />
        <!-- X de error -->
        <g transform="translate(100, 215)">
            <circle cx="0" cy="0" r="15" fill="#EF4444" />
            <path d="M -8 -8 L 8 8" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" />
            <path d="M 8 -8 L -8 8" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" />
        </g>
    </svg>
    `;
};

/* ──────────────────────────────────────────
   FUNCIÓN: Mostrar alerta de éxito con oso
──────────────────────────────────────────── */
var mostrarAlertaExito = function(titulo, mensaje) {
    Swal.fire({
        title: titulo,
        html: mensaje,
        iconHtml: crearOsoExito(),
        customClass: {
            icon: 'oso-icon'
        },
        confirmButtonText: 'Ok',
        confirmButtonColor: '#035498'
    });
};

/* ──────────────────────────────────────────
   FUNCIÓN: Mostrar alerta de error con oso
──────────────────────────────────────────── */
var mostrarAlertaError = function(titulo, mensaje) {
    Swal.fire({
        title: titulo,
        html: mensaje,
        iconHtml: crearOsoError(),
        customClass: {
            icon: 'oso-icon'
        },
        confirmButtonText: 'Ok',
        confirmButtonColor: '#d9534f'
    });
};

/* ──────────────────────────────────────────
   FUNCIÓN: Inicializar al cargar página
──────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function() {
    try {
        csrfToken = document.querySelector('meta[name=csrf-token]').content;
        
        /* Inicializar instancia de Bootstrap Modal */
        var modalElement = document.getElementById('modalSolicitud');
        if (modalElement && typeof bootstrap !== 'undefined') {
            modalSolicitud = new bootstrap.Modal(modalElement);
            console.log('✓ Modal inicializado correctamente');
        } else {
            console.error('✗ Error: Modal element o bootstrap no disponible');
        }
        
        asignarEventosFiltros();
        asignarEventosModal();
        
        /* Cargar solicitudes iniciales */
        filtrarSolicitudes();
        
        console.log('✓ Sistema de solicitudes cargado');
    } catch (error) {
        console.error('Error en DOMContentLoaded:', error);
    }
});

/* ──────────────────────────────────────────
   FUNCIÓN: Asignar eventos a filtros
──────────────────────────────────────────── */
var asignarEventosFiltros = function() {
    var buscador = document.getElementById('buscadorSolicitudes');
    var selectEstado = document.getElementById('selectEstadoSol');
    var selectCiudad = document.getElementById('selectCiudadSol');

    if (buscador) {
        buscador.onkeyup = function() {
            paginaActualSol = 1;
            filtrarSolicitudes();
        };
    }

    if (selectEstado) {
        selectEstado.onchange = function() {
            paginaActualSol = 1;
            filtrarSolicitudes();
        };
    }

    if (selectCiudad) {
        selectCiudad.onchange = function() {
            paginaActualSol = 1;
            filtrarSolicitudes();
        };
    }
};

/* ──────────────────────────────────────────
   FUNCIÓN: Asignar eventos a la tabla
──────────────────────────────────────────── */
var asignarEventosTabla = function() {
    var botonesAprobar = document.querySelectorAll('.btn-aprobar-sol');
    var i;
    for (i = 0; i < botonesAprobar.length; i++) {
        botonesAprobar[i].onclick = function(evento) {
            evento.preventDefault();
            var id = this.getAttribute('data-id');
            abrirModal(id);
        };
    }

    var botonesRechazar = document.querySelectorAll('.btn-rechazar-sol');
    for (i = 0; i < botonesRechazar.length; i++) {
        botonesRechazar[i].onclick = function(evento) {
            evento.preventDefault();
            var id = this.getAttribute('data-id');
            abrirModal(id);
        };
    }

    var botonesVer = document.querySelectorAll('.btn-ver-sol');
    for (i = 0; i < botonesVer.length; i++) {
        botonesVer[i].onclick = function(evento) {
            evento.preventDefault();
            var id = this.getAttribute('data-id');
            abrirModal(id);
        };
    }
};

/* ──────────────────────────────────────────
   FUNCIÓN: Asignar eventos a paginación
──────────────────────────────────────────── */
var asignarEventosPaginacion = function() {
    var botonesPage = document.querySelectorAll('#paginacionSolicitudes .btn-paginacion');
    var i;
    for (i = 0; i < botonesPage.length; i++) {
        var btn = botonesPage[i];
        btn.onclick = function(evento) {
            evento.preventDefault();
            var page = parseInt(this.getAttribute('data-page'));
            if (page && page > 0) {
                cambiarPaginaSol(page);
            }
        };
    }
};

/* ──────────────────────────────────────────
   FUNCIÓN: Filtrar solicitudes
──────────────────────────────────────────── */
var filtrarSolicitudes = function() {
    var selectEstado = document.getElementById('selectEstadoSol');
    var selectCiudad = document.getElementById('selectCiudadSol');
    var buscador = document.getElementById('buscadorSolicitudes');
    
    var estado = selectEstado ? selectEstado.value : '';
    var ciudad = selectCiudad ? selectCiudad.value : '';
    var q = buscador ? buscador.value : '';
    
    console.log('Filtrando con - Estado:', estado, 'Ciudad:', ciudad, 'Búsqueda:', q, 'Página:', paginaActualSol);
    
    var url = '/admin/solicitudes/filtrar?estado=' + encodeURIComponent(estado) +
              '&ciudad=' + encodeURIComponent(ciudad) +
              '&q=' + encodeURIComponent(q) +
              '&page=' + paginaActualSol;
    
    console.log('URL:', url);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(respuesta) {
        return respuesta.json();
    })
    .then(function(datos) {
        console.log('Datos recibidos:', datos);
        console.log('Total encontrado:', datos.total);
        actualizarTabla(datos);
        actualizarPaginacionUI(datos);
        asignarEventosPaginacion();
    })
    .catch(function(error) {
        console.error('Error al filtrar: ', error);
    });
};

/* ──────────────────────────────────────────
   FUNCIÓN: Actualizar tabla con datos
──────────────────────────────────────────── */
var actualizarTabla = function(datos) {
    var tablaBody = document.getElementById('tablaSolicitudes');
    if (!tablaBody) return;

    tablaBody.innerHTML = '';

    if (datos.data && datos.data.length > 0) {
        var i;
        for (i = 0; i < datos.data.length; i++) {
            var solicitud = datos.data[i];
            console.log('Datos solicitud:', solicitud);
            console.log('JSON datos_solicitud_arrendador:', solicitud.datos_solicitud_arrendador);
            var partes = solicitud.nombre_usuario.split(' ');
            var iniciales = (partes[0] ? partes[0].charAt(0) : '') + (partes[1] ? partes[1].charAt(0) : '');
            var colores = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#FFD5CC', '#D7EAF9', '#EDE7F6', '#D5F5E3', '#FAD7D7'];
            var color = colores[solicitud.id_solicitud_arrendador % 8];
            /* datos_solicitud_arrendador ya es un objeto (no necesita JSON.parse) */
            var datos_prop = solicitud.datos_solicitud_arrendador || {};
            var fecha = new Date(solicitud.creado_solicitud_arrendador).toLocaleDateString('es-ES');
            
            /* Determinar badge de estado */
            var estado = solicitud.estado_solicitud_arrendador || 'pendiente';
            var badgeCss = '';
            var estadoLabel = '';
            
            if (estado === 'aprobada') {
                badgeCss = 'bg-success';
                estadoLabel = 'Aprobada';
            } else if (estado === 'rechazada') {
                badgeCss = 'bg-danger';
                estadoLabel = 'Rechazada';
            } else {
                badgeCss = 'bg-warning';
                estadoLabel = 'Pendiente';
            }

            var fila = document.createElement('tr');
            fila.className = 'fila-solicitud';
            fila.setAttribute('data-id', solicitud.id_solicitud_arrendador);

            var htmlFila = '<td><div class="usuario-celda">' +
                '<div class="avatar-tabla" style="background:' + color + '">' + iniciales.toUpperCase() + '</div>' +
                '<div class="usuario-info-tabla">' +
                '<span class="usuario-nombre-tabla">' + solicitud.nombre_usuario + '</span>' +
                '<span class="usuario-email-tabla">' + solicitud.email_usuario + '</span>' +
                '</div></div></td>' +
                '<td>' + (datos_prop.ciudad || '—') + '</td>' +
                '<td>' + (datos_prop.direccion || '—') + '</td>' +
                '<td>' + fecha + '</td>' +
                '<td><span class="badge ' + badgeCss + '">' + estadoLabel + '</span></td>' +
                '<td><div class="acciones-tabla">' +
                '<button class="btn-icono btn-ver-sol" data-id="' + solicitud.id_solicitud_arrendador + '" title="Ver detalles"><i class="bi bi-eye"></i></button>' +
                '<button class="btn-icono btn-aprobar-sol" data-id="' + solicitud.id_solicitud_arrendador + '" title="Aprobar"><i class="bi bi-check-circle"></i></button>' +
                '<button class="btn-icono btn-rechazar-sol" data-id="' + solicitud.id_solicitud_arrendador + '" title="Rechazar"><i class="bi bi-x-circle"></i></button>' +
                '</div></td>';

            fila.innerHTML = htmlFila;
            tablaBody.appendChild(fila);
        }
    } else {
        var fila = document.createElement('tr');
        fila.innerHTML = '<td colspan="6" class="sin-resultados">No hay solicitudes que coincidan con los filtros</td>';
        tablaBody.appendChild(fila);
    }

    /* Actualizar información de paginación */
    var infoPaginacion = document.querySelector('.info-paginacion');
    if (infoPaginacion && datos.from && datos.to) {
        var footerText = 'Mostrando ' + datos.from + '-' + datos.to + ' de ' + datos.total + ' solicitudes';
        infoPaginacion.textContent = footerText;
    }

    asignarEventosTabla();
};

/* ──────────────────────────────────────────
   FUNCIÓN: Actualizar controles de paginación
──────────────────────────────────────────── */
/* ──────────────────────────────────────────
   FUNCIÓN: Actualizar paginación en UI
──────────────────────────────────────────── */
var actualizarPaginacionUI = function(datos) {
    var paginacion = document.getElementById('paginacionSolicitudes');
    if (!paginacion) return;

    paginacion.innerHTML = '';

    /* Botón anterior */
    var botIzq = document.createElement('button');
    botIzq.className = 'btn-paginacion ' + (datos.current_page === 1 ? 'deshabilitado' : '');
    botIzq.innerHTML = '<i class="bi bi-chevron-left"></i>';
    botIzq.setAttribute('data-page', datos.current_page - 1);
    if (datos.current_page > 1) {
        botIzq.disabled = false;
    } else {
        botIzq.disabled = true;
    }
    paginacion.appendChild(botIzq);

    /* Botones de página */
    var j;
    for (j = 1; j <= datos.last_page; j++) {
        var bot = document.createElement('button');
        bot.className = 'btn-paginacion' + (j === datos.current_page ? ' activo' : '');
        bot.textContent = j;
        bot.setAttribute('data-page', j);
        paginacion.appendChild(bot);
    }

    /* Botón siguiente */
    var botDer = document.createElement('button');
    botDer.className = 'btn-paginacion ' + (datos.current_page === datos.last_page ? 'deshabilitado' : '');
    botDer.innerHTML = '<i class="bi bi-chevron-right"></i>';
    botDer.setAttribute('data-page', datos.current_page + 1);
    if (datos.current_page < datos.last_page) {
        botDer.disabled = false;
    } else {
        botDer.disabled = true;
    }
    paginacion.appendChild(botDer);
};
/* ──────────────────────────────────────────
   FUNCIÓN: Cambiar página de solicitudes
──────────────────────────────────────────── */
var cambiarPaginaSol = function(pagina) {
    paginaActualSol = pagina;
    filtrarSolicitudes();
};

/* ──────────────────────────────────────────
   FUNCIÓN: Abrir modal para ver solicitud
──────────────────────────────────────────── */
var abrirModal = function(id) {
    solicitudIdActual = id;
    console.log('Abriendo modal para solicitud:', id);
    
    fetch('/admin/solicitudes/' + id)
        .then(function(respuesta) {
            return respuesta.json();
        })
        .then(function(datos) {
            console.log('Datos recibidos:', datos);
            rellenarModal(datos);
            
            /* Mostrar/ocultar botones según estado */
            var estado = datos.estado_solicitud_arrendador || 'pendiente';
            var btnAprobar = document.getElementById('btnAprobarModal');
            var btnRechazar = document.getElementById('btnRechazarModal');
            
            if (estado === 'aprobada') {
                /* Si está aprobada, solo se puede rechazar */
                if (btnAprobar) btnAprobar.style.display = 'none';
                if (btnRechazar) btnRechazar.style.display = 'block';
            } else if (estado === 'rechazada') {
                /* Si está rechazada, solo se puede aprobar */
                if (btnAprobar) btnAprobar.style.display = 'block';
                if (btnRechazar) btnRechazar.style.display = 'none';
            } else {
                /* Si está pendiente, se pueden hacer ambas acciones */
                if (btnAprobar) btnAprobar.style.display = 'block';
                if (btnRechazar) btnRechazar.style.display = 'block';
            }
            
            document.getElementById('modalNotas').value = '';
            
            if (modalSolicitud) {
                modalSolicitud.show();
                console.log('✓ Modal mostrado');
            } else {
                console.error('✗ modalSolicitud no está inicializado');
            }
        })
        .catch(function(error) {
            console.error('Error al abrir modal:', error);
            mostrarAlertaError('Error', 'No se pudo cargar la solicitud');
        });
};

/* ──────────────────────────────────────────
   FUNCIÓN: Rellenar modal con datos
──────────────────────────────────────────── */
var rellenarModal = function(datos) {
    var partes = datos.nombre_usuario.split(' ');
    var iniciales = (partes[0] ? partes[0].charAt(0) : '') + (partes[1] ? partes[1].charAt(0) : '');
    var colores = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#FFD5CC', '#D7EAF9', '#EDE7F6', '#D5F5E3', '#FAD7D7'];
    var color = colores[datos.id_solicitud_arrendador % 8];

    document.getElementById('modalAvatar').style.background = color;
    document.getElementById('modalAvatar').textContent = iniciales.toUpperCase();
    document.getElementById('modalNombre').textContent = datos.nombre_usuario;
    document.getElementById('modalEmail').textContent = datos.email_usuario;
    document.getElementById('modalCiudad').innerHTML = '<i class="bi bi-geo-alt"></i> ' + (JSON.parse(datos.datos_solicitud_arrendador || '{}').ciudad || 'No disponible');
    
    /* Actualizar badge de estado */
    var estado = datos.estado_solicitud_arrendador || 'pendiente';
    var badgeElement = document.getElementById('modalBadgeEstado');
    if (badgeElement) {
        var estadoLabel = estado.charAt(0).toUpperCase() + estado.slice(1);
        badgeElement.textContent = estadoLabel;
        badgeElement.className = 'badge';
        if (estado === 'aprobada') {
            badgeElement.classList.add('bg-success');
        } else if (estado === 'rechazada') {
            badgeElement.classList.add('bg-danger');
        } else {
            badgeElement.classList.add('bg-warning');
        }
    }

    var datosPropiedad = datos.datos_solicitud_arrendador || {};
    var gridElement = document.getElementById('modalDatosPropiedad');
    if (gridElement) {
        gridElement.innerHTML = '';

        var propiedades = [
            { label: 'Dirección', valor: datosPropiedad.direccion || '—' },
            { label: 'Tipo', valor: datosPropiedad.tipo || '—' },
            { label: 'Precio', valor: '$' + (datosPropiedad.precio_estimado || '0') + '/mes' },
            { label: 'Habitaciones', valor: datosPropiedad.habitaciones || '—' },
            { label: 'Baños', valor: datosPropiedad.banos || '—' },
            { label: 'Tamaño', valor: (datosPropiedad.tamano || '0') + ' m²' }
        ];

        var k;
        for (k = 0; k < propiedades.length; k++) {
            var div = document.createElement('div');
            div.className = 'col-md-6';
            div.innerHTML = '<small class="text-muted d-block">' + propiedades[k].label + '</small><p class="fw-500">' + propiedades[k].valor + '</p>';
            gridElement.appendChild(div);
        }
    }
};

/* ──────────────────────────────────────────
   FUNCIÓN: Asignar eventos a modal
──────────────────────────────────────────── */
var asignarEventosModal = function() {
    var btnAprobar = document.getElementById('btnAprobarModal');
    var btnRechazar = document.getElementById('btnRechazarModal');

    if (btnAprobar) {
        btnAprobar.onclick = function() {
            aprobarSolicitud(solicitudIdActual);
        };
    }

    if (btnRechazar) {
        btnRechazar.onclick = function() {
            var notas = document.getElementById('modalNotas').value;
            rechazarSolicitud(solicitudIdActual, notas);
        };
    }
};

/* ──────────────────────────────────────────
   FUNCIÓN: Aprobar solicitud
──────────────────────────────────────────── */
var aprobarSolicitud = function(id) {
    if (!id) {
        mostrarAlertaError('Error', 'ID de solicitud no disponible');
        return;
    }

    fetch('/admin/solicitudes/' + id + '/aprobar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
        .then(function(respuesta) {
            return respuesta.json();
        })
        .then(function(datos) {
            if (datos.success) {
                modalSolicitud.hide();
                mostrarAlertaExito('¡Éxito!', 'Solicitud aprobada correctamente');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                mostrarAlertaError('Error', datos.error || 'Error desconocido al aprobar');
            }
        })
        .catch(function(error) {
            console.log('Error en aprobar: ', error);
            mostrarAlertaError('Error', 'Error al procesar la solicitud');
        });
};

/* ──────────────────────────────────────────
   FUNCIÓN: Rechazar solicitud
──────────────────────────────────────────── */
var rechazarSolicitud = function(id, notas) {
    if (!id) {
        mostrarAlertaError('Error', 'ID de solicitud no disponible');
        return;
    }

    fetch('/admin/solicitudes/' + id + '/rechazar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ notas: notas })
    })
        .then(function(respuesta) {
            return respuesta.json();
        })
        .then(function(datos) {
            if (datos.success) {
                modalSolicitud.hide();
                mostrarAlertaExito('¡Rechazada!', 'Solicitud rechazada correctamente');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                mostrarAlertaError('Error', datos.error || 'Error desconocido al rechazar');
            }
        })
        .catch(function(error) {
            console.log('Error en rechazar: ', error);
            mostrarAlertaError('Error', 'Error al procesar la solicitud');
        });
};
