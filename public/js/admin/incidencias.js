/* Incidencias JavaScript */

var csrfToken;
var incidenciaIdActual;
var estadoActualModal;

window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    asignarEventosFiltros();
    asignarEventosTarjetas();
    asignarEventosModal();
};

var asignarEventosFiltros = function() {
    document.getElementById('buscadorInc').onblur = function() {
        filtrarIncidencias();
    };
    document.getElementById('buscadorInc').onkeyup = function() {
        if (this.value.length === 0) { filtrarIncidencias(); }
    };
    document.getElementById('selectCategoria').onchange = function() {
        filtrarIncidencias();
    };
    document.getElementById('selectPrioridad').onchange = function() {
        filtrarIncidencias();
    };
};

var filtrarIncidencias = function() {
    var q = document.getElementById('buscadorInc').value;
    var categoria = document.getElementById('selectCategoria').value;
    var prioridad = document.getElementById('selectPrioridad').value;
    fetch('/admin/incidencias/filtrar?q=' + q
          + '&categoria=' + categoria
          + '&prioridad=' + prioridad)
      .then(function(r) { return r.json(); })
      .then(function(data) {
          console.log('Filtrado: ' + data.total + ' incidencias');
      });
};

var asignarEventosTarjetas = function() {
    var tarjetas = document.querySelectorAll('.tarjeta-inc');
    var i;
    for (i = 0; i < tarjetas.length; i++) {
        tarjetas[i].onclick = function() {
            var id = this.dataset.id;
            abrirModal(id);
        };
    }
};

var abrirModal = function(id) {
    incidenciaIdActual = id;
    fetch('/admin/incidencias/' + id)
      .then(function(r) { return r.json(); })
      .then(function(data) {
          rellenarModal(data.incidencia);
          rellenarHistorial(data.historial);
          marcarEstadoActivo(data.incidencia.estado_incidencia);
          document.getElementById('modalOverlay').classList.add('visible');
          document.getElementById('modalIncidencia').classList.add('visible');
      });
};

var rellenarModal = function(inc) {
    estadoActualModal = inc.estado_incidencia;
    document.getElementById('modalTituloInc').textContent = inc.titulo_incidencia;
    document.getElementById('modalDescInc').textContent = inc.descripcion_incidencia;
    document.getElementById('modalPropiedadInc').textContent =
      inc.direccion_propiedad + ', ' + inc.ciudad_propiedad;
    document.getElementById('modalInquilinoInc').textContent =
      inc.nombre_inquilino;
    document.getElementById('modalCategoriaInc').textContent =
      inc.categoria_incidencia;
    document.getElementById('modalBadgePrioridad').textContent =
      inc.prioridad_incidencia;
    document.getElementById('modalBadgePrioridad').className =
      'badge-prioridad badge-prioridad-' + inc.prioridad_incidencia;
    document.getElementById('modalImagenTexto').textContent =
      inc.direccion_propiedad;
};

var rellenarHistorial = function(historial) {
    var contenedor = document.getElementById('timelineHistorial');
    contenedor.innerHTML = '';
    var div = document.createElement('div');
    div.className = 'timeline-linea-v';
    contenedor.appendChild(div);
    var i;
    for (i = 0; i < historial.length; i++) {
        var item = historial[i];
        var evento = document.createElement('div');
        evento.className = 'timeline-evento';
        var colorPunto = item.cambio_estado_historial === 'resuelta'
            ? '#1AA068'
            : item.cambio_estado_historial === 'en_proceso'
            ? '#D97706'
            : item.cambio_estado_historial === 'cerrada'
            ? '#6B7280'
            : '#035498';
        evento.innerHTML = '<div class="timeline-punto-modal" '
          + 'style="background:' + colorPunto + '"></div>'
          + '<div class="timeline-evento-info">'
          + '<p class="timeline-evento-texto">'
          + (item.comentario_historial || '') + '</p>'
          + '<span class="timeline-evento-hora">'
          + item.nombre_usuario + '</span>'
          + '</div>';
        contenedor.appendChild(evento);
    }
};

var marcarEstadoActivo = function(estado) {
    var botones = document.querySelectorAll('.btn-estado');
    var i;
    for (i = 0; i < botones.length; i++) {
        botones[i].classList.remove('activo');
        if (botones[i].dataset.estado === estado) {
            botones[i].classList.add('activo');
        }
    }
};

var cerrarModal = function() {
    document.getElementById('modalOverlay').classList.remove('visible');
    document.getElementById('modalIncidencia').classList.remove('visible');
    incidenciaIdActual = null;
};

var asignarEventosModal = function() {
    document.getElementById('btnCerrarModal').onclick = function() {
        cerrarModal();
    };
    document.getElementById('modalOverlay').onclick = function() {
        cerrarModal();
    };

    var botonesEstado = document.querySelectorAll('.btn-estado');
    var i;
    for (i = 0; i < botonesEstado.length; i++) {
        botonesEstado[i].onclick = function() {
            marcarEstadoActivo(this.dataset.estado);
            estadoActualModal = this.dataset.estado;
        };
    }

    document.getElementById('btnAsignar').onclick = function() {
        var idGestor = document.getElementById('selectGestorModal').value;
        if (!idGestor) {
            alert('Selecciona un gestor');
            return;
        }
        asignarGestor(incidenciaIdActual, idGestor);
    };

    document.getElementById('btnGuardarCambios').onclick = function() {
        var comentario = document.getElementById('modalNotasInc').value;
        cambiarEstado(incidenciaIdActual, estadoActualModal, comentario);
    };

    document.getElementById('btnCerrarInc').onclick = function() {
        cambiarEstado(incidenciaIdActual, 'cerrada', 'Incidencia cerrada');
    };

    document.getElementById('btnContactarInquilino').onclick = function() {
        console.log('Abrir chat con inquilino');
    };
};

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
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            cerrarModal();
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
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
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            cerrarModal();
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
};
