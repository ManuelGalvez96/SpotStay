/* ============================================
   INCIDENCIAS JAVASCRIPT - SpotStay Admin Dashboard
   Kanban Board View Interactions
   ============================================ */

var csrfToken;
var incidenciaIdActual;
var estadoActualModal;
var buscadorTimeout;
var paginaActualInc = 1;
var modalIncidencia = null;
var modalNuevaIncidencia = null;

/* ============================================
   SWEET ALERT FUNCTIONS WITH OSO
   ============================================ */

var crearOsoExito = function() {
    return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <!-- Oso igual que login -->
        <circle class="yeti-part" cx="62" cy="52" r="14" />
        <circle class="yeti-part" cx="138" cy="52" r="14" />
        <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" />
        <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 128 Q100 133 108 128" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle class="hand hand-l" cx="48" cy="180" r="19" />
        <circle class="hand hand-r" cx="152" cy="180" r="19" />
        
        <!-- Cartel éxito sostenido por las manos -->
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#90EE90" stroke="#228B22" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#228B22">✓</text>
    </svg>
    `;
};

var crearOsoError = function() {
    return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <!-- Oso igual que login -->
        <circle class="yeti-part" cx="62" cy="52" r="14" />
        <circle class="yeti-part" cx="138" cy="52" r="14" />
        <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" />
        <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 135 Q100 128 108 135" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle class="hand hand-l" cx="48" cy="180" r="19" />
        <circle class="hand hand-r" cx="152" cy="180" r="19" />
        
        <!-- Cartel error sostenido por las manos -->
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FFB6C1" stroke="#DC143C" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#DC143C">✗</text>
    </svg>
    `;
};

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

/* ============================================
   INITIALIZATION
   ============================================ */

window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    modalIncidencia = new bootstrap.Modal(document.getElementById('modalIncidencia'));
    modalNuevaIncidencia = new bootstrap.Modal(document.getElementById('modalNuevaIncidencia'));
    asignarEventosFiltros();
    asignarEventosTarjetas();
    asignarEventosModal();
    asignarEventosVista();
    asignarEventosNuevaIncidencia();
    asignarEventosPaginacion();
    filtrarIncidencias();
};

/* ============================================
   EVENT HANDLERS ASSIGNMENT
   ============================================ */

var asignarEventosFiltros = function() {
    var buscador = document.getElementById('buscadorInc');
    var selectCategoria = document.getElementById('selectCategoria');
    var selectPrioridad = document.getElementById('selectPrioridad');
    var selectPropiedad = document.getElementById('selectPropiedad');

    if (buscador) {
        buscador.onkeyup = function() {
            clearTimeout(buscadorTimeout);
            buscadorTimeout = setTimeout(function() {
                paginaActualInc = 1;
                filtrarIncidencias();
            }, 300);
        };
    }

    if (selectCategoria) {
        selectCategoria.onchange = function() {
            paginaActualInc = 1;
            filtrarIncidencias();
        };
    }

    if (selectPrioridad) {
        selectPrioridad.onchange = function() {
            paginaActualInc = 1;
            filtrarIncidencias();
        };
    }

    if (selectPropiedad) {
        selectPropiedad.onchange = function() {
            paginaActualInc = 1;
            filtrarIncidencias();
        };
    }
};

var asignarEventosTarjetas = function() {
    var tarjetas = document.querySelectorAll('.tarjeta-inc');
    var i;
    for (i = 0; i < tarjetas.length; i++) {
        tarjetas[i].onclick = function() {
            var id = this.getAttribute('data-id');
            abrirModal(id);
        };
    }
    
    // Asignar evento a botones de ver en la tabla
    var botonesVer = document.querySelectorAll('.tabla-admin .btn-ver');
    for (i = 0; i < botonesVer.length; i++) {
        botonesVer[i].onclick = function() {
            var id = this.getAttribute('data-id');
            abrirModal(id);
        };
    }
};

var asignarEventosModal = function() {
    var btnAsignar = document.getElementById('btnAsignar');
    var btnGuardar = document.getElementById('btnGuardarCambios');
    var btnCerrarInc = document.getElementById('btnCerrarInc');
    var botonesEstado = document.querySelectorAll('.btn-estado');

    var i;
    for (i = 0; i < botonesEstado.length; i++) {
        botonesEstado[i].onclick = function() {
            marcarEstadoActivo(this.getAttribute('data-estado'));
            estadoActualModal = this.getAttribute('data-estado');
        };
    }

    if (btnAsignar) {
        btnAsignar.onclick = function() {
            var selectGestor = document.getElementById('selectGestorModal');
            var idGestor = selectGestor ? selectGestor.value : '';
            if (!idGestor) {
                mostrarAlertaError('Error', 'Selecciona un gestor para asignar');
                return;
            }
            asignarGestor(incidenciaIdActual, idGestor);
        };
    }

    if (btnGuardar) {
        btnGuardar.onclick = function() {
            var notasTextarea = document.getElementById('modalNotasInc');
            var comentario = notasTextarea ? notasTextarea.value : '';
            cambiarEstado(incidenciaIdActual, estadoActualModal, comentario);
        };
    }

    if (btnCerrarInc) {
        btnCerrarInc.onclick = function() {
            cambiarEstado(incidenciaIdActual, 'cerrada', 'Incidencia cerrada por administrador');
        };
    }
};

var asignarEventosVista = function() {
    var btnKanban = document.getElementById('btnVistaKanban');
    var btnLista = document.getElementById('btnVistaLista');
    var kanban = document.getElementById('kanbanBoard');
    var tabla = document.getElementById('vistaLista');

    if (btnKanban) {
        btnKanban.onclick = function() {
            if (kanban) { kanban.style.display = 'grid'; }
            if (tabla) { tabla.style.display = 'none'; }
            if (btnKanban) { btnKanban.classList.add('activo'); }
            if (btnLista) { btnLista.classList.remove('activo'); }
        };
    }

    if (btnLista) {
        btnLista.onclick = function() {
            if (kanban) { kanban.style.display = 'none'; }
            if (tabla) { tabla.style.display = 'block'; }
            if (btnLista) { btnLista.classList.add('activo'); }
            if (btnKanban) { btnKanban.classList.remove('activo'); }
        };
    }
};

var asignarEventosNuevaIncidencia = function() {
    var btnNueva = document.getElementById('btnNuevaIncidencia');
    var btnGuardar = document.getElementById('btnGuardarNueva');

    if (btnNueva) {
        btnNueva.onclick = function() {
            document.getElementById('formNuevaIncidencia').reset();
            modalNuevaIncidencia.show();
        };
    }

    if (btnGuardar) {
        btnGuardar.onclick = function() {
            crearIncidencia();
        };
    }
};

/* ============================================
   FILTERING AND SEARCH
   ============================================ */

var filtrarIncidencias = function() {
    var q = '';
    var categoria = '';
    var prioridad = '';
    var propiedad = '';

    var buscador = document.getElementById('buscadorInc');
    var selectCategoria = document.getElementById('selectCategoria');
    var selectPrioridad = document.getElementById('selectPrioridad');
    var selectPropiedad = document.getElementById('selectPropiedad');

    if (buscador) { q = buscador.value; }
    if (selectCategoria) { categoria = selectCategoria.value; }
    if (selectPrioridad) { prioridad = selectPrioridad.value; }
    if (selectPropiedad) { propiedad = selectPropiedad.value; }

    var url = '/admin/incidencias/filtrar?q=' + encodeURIComponent(q)
            + '&categoria=' + encodeURIComponent(categoria)
            + '&prioridad=' + encodeURIComponent(prioridad)
            + '&propiedad=' + encodeURIComponent(propiedad)
            + '&page=' + paginaActualInc;

    fetch(url)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            renderizarKanban(data);
            asignarEventosTarjetas();
            if (data.currentPage && data.totalPages) {
                actualizarPaginacion(data.currentPage, data.totalPages);
            }
            asignarEventosPaginacion();
        })
        .catch(function(error) {
            console.error('Error al filtrar:', error);
        });
};

var renderizarKanban = function(data) {
    // Actualizar badges de totales en headers
    actualizarBadgesKanban(data);
    
    // Renderizar columna Abierta
    renderizarColumnaKanban('kanban-col-abierta', data.abiertas);
    
    // Renderizar columna En proceso
    renderizarColumnaKanban('kanban-col-proceso', data.enProceso);
    
    // Renderizar columna Resuelta
    renderizarColumnaKanban('kanban-col-resuelta', data.resueltas);
    
    // Renderizar columna Cerrada
    renderizarColumnaKanban('kanban-col-cerrada', data.cerradas);
    
    // Renderizar tabla de lista
    renderizarTablaIncidencias(data);
};

var actualizarBadgesKanban = function(data) {
    var badgeAbierta = document.querySelector('.kanban-col-abierta .badge-kanban');
    var badgeProceso = document.querySelector('.kanban-col-proceso .badge-kanban');
    var badgeResuelta = document.querySelector('.kanban-col-resuelta .badge-kanban');
    var badgeCerrada = document.querySelector('.kanban-col-cerrada .badge-kanban');
    
    if (badgeAbierta) { badgeAbierta.textContent = data.totalAbiertas; }
    if (badgeProceso) { badgeProceso.textContent = data.totalEnProceso; }
    if (badgeResuelta) { badgeResuelta.textContent = data.totalResueltas; }
    if (badgeCerrada) { badgeCerrada.textContent = data.totalCerradas; }
};

var renderizarColumnaKanban = function(className, incidencias) {
    var columna = document.querySelector('.' + className + ' .kanban-col-body');
    if (!columna) { return; }
    
    columna.innerHTML = '';
    
    if (!incidencias || incidencias.length === 0) {
        var mensaje = document.createElement('p');
        mensaje.className = 'kanban-vacio';
        mensaje.textContent = 'Sin incidencias';
        columna.appendChild(mensaje);
        return;
    }
    
    var i;
    for (i = 0; i < incidencias.length; i++) {
        var inc = incidencias[i];
        var tarjeta = crearTarjetaIncidencia(inc);
        columna.appendChild(tarjeta);
    }
};

var crearTarjetaIncidencia = function(inc) {
    var bordeColor = '#6B7280';
    switch(inc.prioridad_incidencia) {
        case 'urgente': bordeColor = '#EF4444'; break;
        case 'alta': bordeColor = '#D97706'; break;
        case 'media': bordeColor = '#6B7280'; break;
        case 'baja': bordeColor = '#1AA068'; break;
    }
    
    var iconoCat = 'bi-wrench';
    switch(inc.categoria_incidencia) {
        case 'fontaneria': iconoCat = 'bi-droplet'; break;
        case 'electricidad': iconoCat = 'bi-lightning'; break;
        case 'calefaccion': iconoCat = 'bi-thermometer'; break;
        case 'climatizacion': iconoCat = 'bi-fan'; break;
        case 'humedades': iconoCat = 'bi-cloud-rain'; break;
        case 'cerrajeria': iconoCat = 'bi-key'; break;
    }
    
    var partes = (inc.nombre_inquilino || '').split(' ');
    var inicial1 = partes[0] ? partes[0].substring(0, 1).toUpperCase() : '';
    var inicial2 = partes[1] ? partes[1].substring(0, 1).toUpperCase() : '';
    var iniciales = inicial1 + inicial2;
    
    var nombreInquilino = (partes[0] || '') + ' ' + (partes[1] ? partes[1].substring(0, 1).toUpperCase() + '.' : '');
    
    var tarjeta = document.createElement('div');
    tarjeta.className = 'tarjeta-inc';
    tarjeta.setAttribute('data-id', inc.id_incidencia);
    tarjeta.style.borderLeft = '3px solid ' + bordeColor;
    
    var tiempoCreacion = calcularTiempoTranscurrido(inc.creado_incidencia);
    
    var html = '<div class="tarjeta-inc-top">' +
               '<span class="badge-prioridad badge-prioridad-' + inc.prioridad_incidencia + '">' + inc.prioridad_incidencia.charAt(0).toUpperCase() + inc.prioridad_incidencia.slice(1) + '</span>' +
               '<span class="tarjeta-tiempo">' + tiempoCreacion + '</span>' +
               '</div>' +
               '<p class="tarjeta-titulo">' + (inc.titulo_incidencia || '') + '</p>' +
               '<p class="tarjeta-desc">' + truncarTexto(inc.descripcion_incidencia || '', 60) + '</p>' +
               '<div class="tarjeta-inc-bottom">' +
               '<span class="tarjeta-propiedad">' + truncarTexto(inc.direccion_propiedad || '', 15) + '</span>' +
               '<div class="tarjeta-inquilino">' +
               '<div class="avatar-mini">' + iniciales + '</div>' +
               '<span>' + nombreInquilino + '</span>' +
               '</div>' +
               '</div>' +
               '<div class="tarjeta-categoria">' +
               '<i class="bi ' + iconoCat + '"></i>' +
               '<span>' + (inc.categoria_incidencia || '').charAt(0).toUpperCase() + (inc.categoria_incidencia || '').slice(1) + '</span>' +
               '</div>';
    
    if (inc.nombre_gestor && (inc.estado_incidencia === 'en_proceso' || inc.estado_incidencia === 'resuelta')) {
        html += '<div class="tarjeta-gestor">' +
                '<span class="gestor-label">Asignado a:</span>' +
                '<div class="avatar-mini avatar-mini-gestor">MG</div>' +
                '<span class="gestor-nombre">' + (inc.nombre_gestor || '') + ' (gestor)</span>' +
                '</div>';
    }
    
    tarjeta.innerHTML = html;
    return tarjeta;
};

var truncarTexto = function(texto, longitud) {
    if (texto.length > longitud) {
        return texto.substring(0, longitud) + '...';
    }
    return texto;
};

var calcularTiempoTranscurrido = function(fecha) {
    // Formato esperado: "2024-04-20 10:30:45"
    var ahora = new Date();
    var fechaObj = new Date(fecha);
    var diferencia = Math.floor((ahora - fechaObj) / 1000); // segundos
    
    if (diferencia < 60) {
        return 'hace segundos';
    } else if (diferencia < 3600) {
        var minutos = Math.floor(diferencia / 60);
        return 'hace ' + minutos + ' min';
    } else if (diferencia < 86400) {
        var horas = Math.floor(diferencia / 3600);
        return 'hace ' + horas + ' h';
    } else if (diferencia < 604800) {
        var dias = Math.floor(diferencia / 86400);
        return 'hace ' + dias + ' d';
    } else {
        var semanas = Math.floor(diferencia / 604800);
        return 'hace ' + semanas + ' sem';
    }
};

/* ============================================
   TABLE VIEW RENDERING
   ============================================ */

var renderizarTablaIncidencias = function(data) {
    console.log('→ Renderizando tabla incidencias...');
    var tbody = document.getElementById('tbodyIncidencias');
    var contador = document.getElementById('contadorResultados');
    
    if (!tbody) { 
        console.error('✗ ERROR: no se encontró elemento tbodyIncidencias');
        return; 
    }
    console.log('✓ tbody encontrado');
    
    tbody.innerHTML = '';
    
    // Usar datos paginados si están disponibles, sino usar todos
    var todasIncidencias = data.tabla || [];
    
    if (todasIncidencias.length === 0) {
        todasIncidencias = [];
        if (data.abiertas) {
            var i;
            for (i = 0; i < data.abiertas.length; i++) {
                todasIncidencias.push(data.abiertas[i]);
            }
        }
        if (data.enProceso) {
            var i;
            for (i = 0; i < data.enProceso.length; i++) {
                todasIncidencias.push(data.enProceso[i]);
            }
        }
        if (data.resueltas) {
            var i;
            for (i = 0; i < data.resueltas.length; i++) {
                todasIncidencias.push(data.resueltas[i]);
            }
        }
        if (data.cerradas) {
            var i;
            for (i = 0; i < data.cerradas.length; i++) {
                todasIncidencias.push(data.cerradas[i]);
            }
        }
    }
    
    var totalIncidencias = data.total || todasIncidencias.length;
    
    console.log('Total incidencias encontradas: ' + totalIncidencias);
    
    if (contador) {
        contador.textContent = totalIncidencias + ' incidencias encontradas';
    }
    
    if (todasIncidencias.length === 0) {
        console.log('⚠ Sin incidencias para mostrar');
        var fila = document.createElement('tr');
        fila.innerHTML = '<td colspan="7" style="text-align: center; color: #9CA3AF; padding: 20px;">Sin incidencias</td>';
        tbody.appendChild(fila);
        return;
    }
    
    console.log('✓ Iniciando renderizado de ' + todasIncidencias.length + ' filas...');
    var i;
    for (i = 0; i < todasIncidencias.length; i++) {
        var fila = crearFilaIncidencia(todasIncidencias[i]);
        tbody.appendChild(fila);
    }
    
    console.log('✓ Tabla renderizada con ' + todasIncidencias.length + ' filas');
};

var crearFilaIncidencia = function(inc) {
    var fila = document.createElement('tr');
    
    var estadoBadgeClass = 'badge-' + inc.estado_incidencia;
    var estadoLabel = inc.estado_incidencia.charAt(0).toUpperCase() + inc.estado_incidencia.slice(1);
    if (inc.estado_incidencia === 'en_proceso') {
        estadoLabel = 'En proceso';
    }
    
    var prioridadLabel = inc.prioridad_incidencia.charAt(0).toUpperCase() + inc.prioridad_incidencia.slice(1);
    var categoriaLabel = inc.categoria_incidencia.charAt(0).toUpperCase() + inc.categoria_incidencia.slice(1);
    
    fila.className = (inc.estado_incidencia === 'cerrada') ? 'fila-inactiva' : '';
    fila.innerHTML = '<td><strong>' + (inc.titulo_incidencia || '') + '</strong></td>' +
                     '<td>' + truncarTexto(inc.direccion_propiedad || '', 30) + '</td>' +
                     '<td>' + categoriaLabel + '</td>' +
                     '<td><span class="badge-prioridad badge-prioridad-' + inc.prioridad_incidencia + '">' + prioridadLabel + '</span></td>' +
                     '<td><span id="estadoBadge-' + inc.id_incidencia + '" class="badge-estado ' + estadoBadgeClass + '">' + estadoLabel + '</span></td>' +
                     '<td>' + (inc.nombre_inquilino || '') + '</td>' +
                     '<td><button class="btn-accion btn-ver" data-id="' + inc.id_incidencia + '" title="Ver detalles"><i class="bi bi-eye"></i></button></td>';
    
    return fila;
};

/* ============================================
   MODAL MANAGEMENT
   ============================================ */

var abrirModal = function(id) {
    incidenciaIdActual = id;

    fetch('/admin/incidencias/' + id)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            rellenarModal(data.incidencia);
            rellenarHistorial(data.historial);
            marcarEstadoActivo(data.incidencia.estado_incidencia);
            mostrarModal();
        })
        .catch(function(error) {
            console.error('Error al abrir modal:', error);
            mostrarAlertaError('Error', 'No se pudo cargar la incidencia');
        });
};

var rellenarModal = function(inc) {
    estadoActualModal = inc.estado_incidencia;

    var titulo = document.getElementById('modalTituloInc');
    var desc = document.getElementById('modalDescInc');
    var propiedad = document.getElementById('modalPropiedadInc');
    var inquilino = document.getElementById('modalInquilinoInc');
    var categoria = document.getElementById('modalCategoriaInc');
    var badgePrioridad = document.getElementById('modalBadgePrioridad');
    var badgeCategoria = document.getElementById('modalBadgeCategoria');

    if (titulo) { titulo.textContent = inc.titulo_incidencia || ''; }
    if (desc) { desc.textContent = inc.descripcion_incidencia || ''; }
    if (propiedad) { 
        propiedad.textContent = (inc.direccion_propiedad || '') + ', ' + (inc.ciudad_propiedad || '');
    }
    if (inquilino) { inquilino.textContent = inc.nombre_inquilino || ''; }
    if (categoria) { categoria.textContent = inc.categoria_incidencia || ''; }
    if (badgePrioridad) {
        badgePrioridad.textContent = inc.prioridad_incidencia || '';
        badgePrioridad.className = 'badge';
        switch(inc.prioridad_incidencia) {
            case 'urgente': badgePrioridad.classList.add('bg-danger'); break;
            case 'alta': badgePrioridad.classList.add('bg-warning', 'text-dark'); break;
            case 'media': badgePrioridad.classList.add('bg-secondary'); break;
            case 'baja': badgePrioridad.classList.add('bg-success'); break;
        }
    }
    if (badgeCategoria) {
        badgeCategoria.textContent = inc.categoria_incidencia || '';
    }
};

var rellenarHistorial = function(historial) {
    var contenedor = document.getElementById('timelineHistorial');
    if (!contenedor) { return; }

    contenedor.innerHTML = '';

    if (!historial || historial.length === 0) {
        var empty = document.createElement('p');
        empty.style.textAlign = 'center';
        empty.style.color = '#9CA3AF';
        empty.textContent = 'Sin historial';
        contenedor.appendChild(empty);
        return;
    }

    var timeline = document.createElement('div');
    timeline.className = 'timeline';

    var i;
    for (i = 0; i < historial.length; i++) {
        var item = historial[i];
        var evento = document.createElement('div');
        evento.className = 'timeline-evento';

        var tipo = item.cambio_estado_historial || 'creacion';
        evento.setAttribute('data-tipo', tipo);

        var textoEvento = item.comentario_historial || '';
        if (tipo === 'abierta') {
            textoEvento = textoEvento || 'Incidencia abierta';
        } else if (tipo === 'en_proceso') {
            textoEvento = textoEvento || 'Incidencia en proceso';
        } else if (tipo === 'resuelta') {
            textoEvento = textoEvento || 'Incidencia resuelta';
        } else if (tipo === 'cerrada') {
            textoEvento = textoEvento || 'Incidencia cerrada';
        }

        evento.innerHTML = '<div class="timeline-punto-modal"></div>'
                         + '<div class="timeline-evento-info">'
                         + '<p class="timeline-evento-texto">' + textoEvento + '</p>'
                         + '<span class="timeline-evento-hora">'
                         + (item.nombre_usuario || 'Sistema') + ' - ' + (item.creado_historial || '')
                         + '</span>'
                         + '</div>';
        timeline.appendChild(evento);
    }

    contenedor.appendChild(timeline);
};

var marcarEstadoActivo = function(estado) {
    var botones = document.querySelectorAll('.btn-estado');
    var i;
    for (i = 0; i < botones.length; i++) {
        botones[i].classList.remove('active');
        if (botones[i].getAttribute('data-estado') === estado) {
            botones[i].classList.add('active');
        }
    }
};

var mostrarModal = function() {
    if (modalIncidencia) {
        modalIncidencia.show();
    }
};

var cerrarModal = function() {
    incidenciaIdActual = null;
    estadoActualModal = null;
    if (modalIncidencia) {
        modalIncidencia.hide();
    }
};

/* ============================================
   STATE AND ASSIGNMENT CHANGES
   ============================================ */

var cambiarEstado = function(id, estado, comentario) {
    fetch('/admin/incidencias/' + id + '/estado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            estado: estado,
            comentario: comentario
        })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            mostrarAlertaExito('¡Éxito!', 'Estado actualizado correctamente');
            cerrarModal();
            filtrarIncidencias();
        } else {
            mostrarAlertaError('Error', data.error || 'Error desconocido');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        mostrarAlertaError('Error', 'No se pudo cambiar el estado de la incidencia');
    });
};

var asignarGestor = function(id, idGestor) {
    fetch('/admin/incidencias/' + id + '/asignar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ id_gestor: idGestor })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            mostrarAlertaExito('¡Éxito!', 'Gestor asignado correctamente');
            cerrarModal();
            filtrarIncidencias();
        } else {
            mostrarAlertaError('Error', data.error || 'Error desconocido');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        mostrarAlertaError('Error', 'No se pudo asignar el gestor');
    });
};

/* ============================================
   CREATE NEW INCIDENT
   ============================================ */

var crearIncidencia = function() {
    var propiedad = document.getElementById('nuevaPropiedadId');
    var inquilino = document.getElementById('nuevaInquilinoId');
    var titulo = document.getElementById('nuevaTitulo');
    var descripcion = document.getElementById('nuevaDescripcion');
    var categoria = document.getElementById('nuevaCategoria');
    var prioridad = document.getElementById('nuevaPrioridad');

    var idPropiedad = propiedad ? propiedad.value : '';
    var idInquilino = inquilino ? inquilino.value : '';
    var tituloVal = titulo ? titulo.value : '';
    var descripcionVal = descripcion ? descripcion.value : '';
    var categoriaVal = categoria ? categoria.value : '';
    var prioridadVal = prioridad ? prioridad.value : '';

    if (!idPropiedad || !idInquilino || !tituloVal || !descripcionVal || !categoriaVal || !prioridadVal) {
        mostrarAlertaError('Error', 'Por favor completa todos los campos');
        return;
    }

    fetch('/admin/incidencias/crear', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            id_propiedad: idPropiedad,
            id_inquilino: idInquilino,
            titulo: tituloVal,
            descripcion: descripcionVal,
            categoria: categoriaVal,
            prioridad: prioridadVal
        })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            mostrarAlertaExito('¡Éxito!', 'Incidencia creada correctamente');
            modalNuevaIncidencia.hide();
            filtrarIncidencias();
        } else {
            mostrarAlertaError('Error', data.error || 'Error desconocido');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        mostrarAlertaError('Error', 'No se pudo crear la incidencia');
    });
};

/* ============================================
   PAGINATION FUNCTIONS
   ============================================ */

var asignarEventosPaginacion = function() {
    var btnAnterior = document.getElementById('btnAnteriorInc');
    var btnSiguiente = document.getElementById('btnSiguienteInc');
    var contenedor = document.getElementById('paginasInc');
    var botonesNumero = contenedor ? contenedor.querySelectorAll('.pag-numero') : [];
    
    // Botón anterior
    if (btnAnterior) {
        btnAnterior.onclick = function(event) {
            event.preventDefault();
            if (paginaActualInc > 1) {
                cambiarPaginaInc(paginaActualInc - 1);
            }
        };
    }
    
    // Botón siguiente
    if (btnSiguiente) {
        btnSiguiente.onclick = function(event) {
            event.preventDefault();
            cambiarPaginaInc(paginaActualInc + 1);
        };
    }
    
    // Botones número de página
    for (var i = 0; i < botonesNumero.length; i++) {
        var btnNum = botonesNumero[i];
        btnNum.onclick = function(event) {
            event.preventDefault();
            var pagina = parseInt(this.getAttribute('data-pagina'));
            cambiarPaginaInc(pagina);
        };
    }
};

var cambiarPaginaInc = function(numeroPagina) {
    paginaActualInc = numeroPagina;
    filtrarIncidencias();
};

var actualizarPaginacion = function(paginaActual, totalPaginas) {
    var contenedor = document.getElementById('paginasInc');
    if (!contenedor) return;
    
    // Limpiar los botones anteriores
    contenedor.innerHTML = '';
    
    // Generar los botones de página
    for (var i = 1; i <= totalPaginas; i++) {
        var btn = document.createElement('button');
        btn.className = 'pag-numero' + (i === paginaActual ? ' activo' : '');
        btn.setAttribute('data-pagina', i);
        btn.textContent = i;
        contenedor.appendChild(btn);
    }
};
