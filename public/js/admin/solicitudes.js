/* Solicitudes JavaScript */

var csrfToken;
var solicitudIdActual;

window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    asignarEventosFiltros();
    asignarEventosTarjetas();
    asignarEventosModal();
};

var asignarEventosFiltros = function() {
    document.getElementById('selectEstadoSol').onchange = function() {
        filtrarSolicitudes();
    };
    document.getElementById('selectCiudadSol').onchange = function() {
        filtrarSolicitudes();
    };
    document.getElementById('buscadorSolicitudes').onblur = function() {
        filtrarSolicitudes();
    };
    document.getElementById('buscadorSolicitudes').onkeyup = function() {
        if (this.value.length === 0) {
            filtrarSolicitudes();
        }
    };
};

var filtrarSolicitudes = function() {
    var estado = document.getElementById('selectEstadoSol').value;
    var ciudad = document.getElementById('selectCiudadSol').value;
    var q = document.getElementById('buscadorSolicitudes').value;
    fetch('/admin/solicitudes/filtrar?estado=' + estado
          + '&ciudad=' + ciudad + '&q=' + q)
      .then(function(r) { return r.json(); })
      .then(function(data) {
          actualizarContador(data.total);
      });
};

var actualizarContador = function(total) {
    var el = document.querySelector('.texto-pendientes');
    if (el) { el.textContent = total + ' pendientes de revisión'; }
};

var asignarEventosTarjetas = function() {
    var botonesAprobar = document.querySelectorAll('.btn-aprobar-sol');
    var i;
    for (i = 0; i < botonesAprobar.length; i++) {
        botonesAprobar[i].onclick = function() {
            var id = this.dataset.id;
            aprobarSolicitud(id);
        };
    }
    var botonesRechazar = document.querySelectorAll('.btn-rechazar-sol');
    for (i = 0; i < botonesRechazar.length; i++) {
        botonesRechazar[i].onclick = function() {
            var id = this.dataset.id;
            abrirModalRechazar(id);
        };
    }
    var botonesVer = document.querySelectorAll('.btn-ver-sol');
    for (i = 0; i < botonesVer.length; i++) {
        botonesVer[i].onclick = function() {
            var id = this.dataset.id;
            abrirModal(id);
        };
    }
};

var aprobarSolicitud = function(id) {
    fetch('/admin/solicitudes/' + id + '/aprobar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al aprobar: ' + data.error);
        }
    });
};

var abrirModalRechazar = function(id) {
    solicitudIdActual = id;
    document.getElementById('modalNotas').value = '';
    document.getElementById('modalBadgeEstado').textContent = 'Pendiente';
    document.getElementById('modalOverlay').classList.add('visible');
    document.getElementById('modalSolicitud').classList.add('visible');
    document.getElementById('btnAprobarModal').style.display = 'none';
    document.getElementById('btnRechazarModal').style.display = 'block';
};

var abrirModal = function(id) {
    solicitudIdActual = id;
    fetch('/admin/solicitudes/' + id)
      .then(function(r) { return r.json(); })
      .then(function(data) {
          rellenarModal(data);
          document.getElementById('modalOverlay').classList.add('visible');
          document.getElementById('modalSolicitud').classList.add('visible');
          document.getElementById('btnAprobarModal').style.display = 'block';
          document.getElementById('btnRechazarModal').style.display = 'none';
      });
};

var rellenarModal = function(data) {
    var partes = data.nombre_usuario.split(' ');
    var iniciales = (partes[0] ? partes[0][0] : '') + (partes[1] ? partes[1][0] : '');
    document.getElementById('modalAvatar').textContent = iniciales.toUpperCase();
    document.getElementById('modalNombre').textContent = data.nombre_usuario;
    document.getElementById('modalEmail').textContent = data.email_usuario;
    document.getElementById('modalTelefono').textContent = data.telefono_usuario || 'No disponible';
    
    var datosPropiedad = JSON.parse(data.datos_solicitud_arrendador || '{}');
    var grid = document.getElementById('modalDatosPropiedad');
    grid.innerHTML = '';
    
    var campos = [
        ['Dirección', datosPropiedad.direccion],
        ['Tipo', datosPropiedad.tipo],
        ['Precio estimado', '$' + datosPropiedad.precio_estimado + '/mes'],
        ['Habitaciones', datosPropiedad.habitaciones],
        ['Baños', datosPropiedad.banos],
        ['Tamaño', datosPropiedad.tamano + ' m²']
    ];
    
    var j;
    for (j = 0; j < campos.length; j++) {
        var div = document.createElement('div');
        div.className = 'dato-item';
        div.innerHTML = '<span class="dato-label">' + campos[j][0]
          + '</span><span class="dato-valor">'
          + (campos[j][1] || '—') + '</span>';
        grid.appendChild(div);
    }
};

var cerrarModal = function() {
    document.getElementById('modalOverlay').classList.remove('visible');
    document.getElementById('modalSolicitud').classList.remove('visible');
    solicitudIdActual = null;
};

var asignarEventosModal = function() {
    document.getElementById('btnCerrarModal').onclick = function() {
        cerrarModal();
    };
    document.getElementById('modalOverlay').onclick = function() {
        cerrarModal();
    };
    document.getElementById('btnAprobarModal').onclick = function() {
        aprobarSolicitud(solicitudIdActual);
    };
    document.getElementById('btnRechazarModal').onclick = function() {
        var notas = document.getElementById('modalNotas').value;
        rechazarSolicitud(solicitudIdActual, notas);
    };
};

var rechazarSolicitud = function(id, notas) {
    fetch('/admin/solicitudes/' + id + '/rechazar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ notas: notas })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            cerrarModal();
            location.reload();
        } else {
            alert('Error al rechazar: ' + data.error);
        }
    });
};
