/* ============================================
   INCIDENCIAS JAVASCRIPT - SpotStay Admin Dashboard
   Kanban Board View Interactions
   ============================================ */

var csrfToken;
var incidenciaIdActual;
var estadoActualModal;
var buscadorTimeout;

/* ============================================
   INITIALIZATION
   ============================================ */

window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    asignarEventosFiltros();
    asignarEventosTarjetas();
    asignarEventosModal();
    asignarEventosVista();
    asignarEventosNuevaIncidencia();
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
                filtrarIncidencias();
            }, 300);
        };
    }

    if (selectCategoria) {
        selectCategoria.onchange = function() {
            filtrarIncidencias();
        };
    }

    if (selectPrioridad) {
        selectPrioridad.onchange = function() {
            filtrarIncidencias();
        };
    }

    if (selectPropiedad) {
        selectPropiedad.onchange = function() {
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
};

var asignarEventosModal = function() {
    var btnCerrar = document.getElementById('btnCerrarModal');
    var btnAsignar = document.getElementById('btnAsignar');
    var btnGuardar = document.getElementById('btnGuardarCambios');
    var btnCerrarInc = document.getElementById('btnCerrarInc');
    var bgModal = document.getElementById('modalOverlay');
    var botonesEstado = document.querySelectorAll('.btn-estado');

    if (btnCerrar) {
        btnCerrar.onclick = function() {
            cerrarModal();
        };
    }

    if (bgModal) {
        bgModal.onclick = function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        };
    }

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
                alert('Selecciona un gestor para asignar');
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

    if (btnKanban) {
        btnKanban.onclick = function() {
            var kanban = document.getElementById('kanbanBoard');
            if (kanban) {
                kanban.style.display = 'grid';
            }
            if (btnKanban) { btnKanban.classList.add('activo'); }
            if (btnLista) { btnLista.classList.remove('activo'); }
        };
    }

    if (btnLista) {
        btnLista.onclick = function() {
            var kanban = document.getElementById('kanbanBoard');
            if (kanban) {
                kanban.style.display = 'none';
            }
            if (btnLista) { btnLista.classList.add('activo'); }
            if (btnKanban) { btnKanban.classList.remove('activo'); }
        };
    }
};

var asignarEventosNuevaIncidencia = function() {
    var btnNueva = document.getElementById('btnNuevaIncidencia');
    var btnCancelar = document.getElementById('btnCancelarNueva');
    var btnGuardar = document.getElementById('btnGuardarNueva');
    var btnCerrar = document.getElementById('btnCerrarModalNueva');
    var overlayNueva = document.getElementById('modalOverlayNueva');

    if (btnNueva) {
        btnNueva.onclick = function() {
            abrirModalNueva();
        };
    }

    if (btnCancelar) {
        btnCancelar.onclick = function() {
            cerrarModalNueva();
        };
    }

    if (btnCerrar) {
        btnCerrar.onclick = function() {
            cerrarModalNueva();
        };
    }

    if (overlayNueva) {
        overlayNueva.onclick = function(e) {
            if (e.target === this) {
                cerrarModalNueva();
            }
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
            + '&propiedad=' + encodeURIComponent(propiedad);

    fetch(url)
        .then(function(response) { return response.json(); })
        .then(function(data) {
            console.log('Incidencias filtradas: ' + data.total);
            location.reload();
        })
        .catch(function(error) {
            console.error('Error al filtrar:', error);
        });
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
            alert('Error al cargar la incidencia');
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
    var imagenTexto = document.getElementById('modalImagenTexto');

    if (titulo) { titulo.textContent = inc.titulo_incidencia || ''; }
    if (desc) { desc.textContent = inc.descripcion_incidencia || ''; }
    if (propiedad) { 
        propiedad.textContent = (inc.direccion_propiedad || '') + ', ' + (inc.ciudad_propiedad || '');
    }
    if (inquilino) { inquilino.textContent = inc.nombre_inquilino || ''; }
    if (categoria) { categoria.textContent = inc.categoria_incidencia || ''; }
    if (badgePrioridad) {
        badgePrioridad.textContent = inc.prioridad_incidencia || '';
        badgePrioridad.className = 'badge-prioridad badge-prioridad-' + (inc.prioridad_incidencia || 'media');
    }
    if (imagenTexto) { imagenTexto.textContent = inc.direccion_propiedad || ''; }
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
        botones[i].classList.remove('activo');
        if (botones[i].getAttribute('data-estado') === estado) {
            botones[i].classList.add('activo');
        }
    }
};

var mostrarModal = function() {
    var overlay = document.getElementById('modalOverlay');
    var modal = document.getElementById('modalIncidencia');
    if (overlay) { overlay.classList.add('active'); }
    if (modal) { modal.classList.add('active'); }
};

var cerrarModal = function() {
    var overlay = document.getElementById('modalOverlay');
    var modal = document.getElementById('modalIncidencia');
    if (overlay) { overlay.classList.remove('active'); }
    if (modal) { modal.classList.remove('active'); }
    incidenciaIdActual = null;
    estadoActualModal = null;
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
            cerrarModal();
            location.reload();
        } else {
            alert('Error al cambiar estado: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Error al cambiar el estado de la incidencia');
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
            alert('Gestor asignado correctamente');
            cerrarModal();
            location.reload();
        } else {
            alert('Error al asignar gestor: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Error al asignar el gestor');
    });
};

/* ============================================
   CREATE NEW INCIDENT
   ============================================ */

var abrirModalNueva = function() {
    var modal = document.getElementById('modalNuevaIncidencia');
    var overlay = document.getElementById('modalOverlayNueva');
    if (modal) {
        modal.classList.add('active');
    }
    if (overlay) {
        overlay.classList.add('active');
    }
};

var cerrarModalNueva = function() {
    var modal = document.getElementById('modalNuevaIncidencia');
    var overlay = document.getElementById('modalOverlayNueva');
    if (modal) {
        modal.classList.remove('active');
    }
    if (overlay) {
        overlay.classList.remove('active');
    }
};

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
        alert('Por favor completa todos los campos');
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
            alert('Incidencia creada correctamente');
            cerrarModalNueva();
            location.reload();
        } else {
            alert('Error al crear la incidencia: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        alert('Error al crear la incidencia');
    });
};
