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

function renderizarDetalle(datosRespuesta) {
  var titulo = document.getElementById('tituloInquilino');
  var datos = document.getElementById('datosInquilino');
  var lista = document.getElementById('listaPropiedades');

  if (!titulo || !datos || !lista) {
    return;
  }

  titulo.textContent = datosRespuesta.inquilino.nombre_usuario;
  datos.textContent = datosRespuesta.inquilino.email_usuario + ' · ' + (datosRespuesta.inquilino.telefono_usuario || 'Sin teléfono');

  if (!datosRespuesta.propiedades || datosRespuesta.propiedades.length === 0) {
    lista.innerHTML = '<p class="muted">No tiene propiedades activas con este arrendador.</p>';
    return;
  }

  var contenidoHtml = '';
  datosRespuesta.propiedades.forEach(function (propiedad) {
    contenidoHtml += '<div class="prop-item">';
    contenidoHtml += '<strong>' + propiedad.titulo_propiedad + '</strong><br>';
    contenidoHtml += '<span class="muted">' + propiedad.direccion_propiedad + '</span><br>';
    contenidoHtml += '<span class="muted">Inicio: ' + propiedad.fecha_inicio_alquiler + '</span>';
    contenidoHtml += '</div>';
  });

  lista.innerHTML = contenidoHtml;
}

function obtenerDetalleInquilino(id, arrendadorId) {
  var ruta = '/arrendador/inquilinos/' + id + '?arrendador_id=' + encodeURIComponent(arrendadorId);

  fetch(ruta, {
    method: 'GET',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    credentials: 'same-origin'
  })
    .then(function (respuesta) {
      return respuesta.json().then(function (datosRespuesta) {
        return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datosRespuesta.success) {
        throw new Error(resultado.datosRespuesta.message || 'No se pudo cargar el detalle.');
      }
      renderizarDetalle(resultado.datosRespuesta);
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
