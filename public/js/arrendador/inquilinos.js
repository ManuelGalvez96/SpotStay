function abrirModal() {
  var modal = document.getElementById('modalInquilino');
  if (modal) {
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
  }
}

function cerrarModal() {
  var modal = document.getElementById('modalInquilino');
  if (modal) {
    modal.hidden = true;
    document.body.style.overflow = '';
  }
}

function renderizarDetalle(payload) {
  var titulo = document.getElementById('tituloInquilino');
  var datos = document.getElementById('datosInquilino');
  var lista = document.getElementById('listaPropiedades');

  if (!titulo || !datos || !lista) {
    return;
  }

  titulo.textContent = payload.inquilino.nombre_usuario;
  datos.textContent = payload.inquilino.email_usuario + ' · ' + (payload.inquilino.telefono_usuario || 'Sin teléfono');

  if (!payload.propiedades || payload.propiedades.length === 0) {
    lista.innerHTML = '<p class="muted">No tiene propiedades activas con este arrendador.</p>';
    return;
  }

  var html = '';
  payload.propiedades.forEach(function (prop) {
    html += '<div class="prop-item">';
    html += '<strong>' + prop.titulo_propiedad + '</strong><br>';
    html += '<span class="muted">' + prop.direccion_propiedad + '</span><br>';
    html += '<span class="muted">Inicio: ' + prop.fecha_inicio_alquiler + '</span>';
    html += '</div>';
  });

  lista.innerHTML = html;
}

function obtenerDetalleInquilino(id, arrendadorId) {
  var ruta = '/arrendador/inquilinos/' + id + '?arrendador_id=' + encodeURIComponent(arrendadorId);

  fetch(ruta, {
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
    .then(function (response) {
      return response.json().then(function (payload) {
        return { ok: response.ok, payload: payload };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.payload.success) {
        throw new Error(resultado.payload.message || 'No se pudo cargar el detalle.');
      }
      renderizarDetalle(resultado.payload);
      abrirModal();
    })
    .catch(function () {
      alert('No se pudo cargar el detalle del inquilino.');
    });
}

document.querySelectorAll('[data-ver-inquilino]').forEach(function (boton) {
  boton.addEventListener('click', function () {
    obtenerDetalleInquilino(boton.getAttribute('data-ver-inquilino'), boton.getAttribute('data-arrendador'));
  });
});

var botonCerrar = document.getElementById('cerrarModalInquilino');
if (botonCerrar) {
  botonCerrar.addEventListener('click', cerrarModal);
}

var modalInquilino = document.getElementById('modalInquilino');
if (modalInquilino) {
  modalInquilino.addEventListener('click', function (evento) {
    if (evento.target === modalInquilino) {
      cerrarModal();
    }
  });
}

document.addEventListener('keydown', function (evento) {
  if (evento.key === 'Escape') {
    cerrarModal();
  }
});
