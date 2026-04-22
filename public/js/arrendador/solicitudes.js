function obtenerTokenCsrf() {
  var etiquetaCsrf = document.querySelector('meta[name="csrf-token"]');
  return etiquetaCsrf ? etiquetaCsrf.getAttribute('content') : '';
}

function mostrarToast(texto) {
  var anterior = document.querySelector('.toast');
  if (anterior) {
    anterior.remove();
  }

  var aviso = document.createElement('div');
  aviso.className = 'toast';
  aviso.textContent = texto;
  document.body.appendChild(aviso);

  setTimeout(function () { aviso.classList.add('visible'); }, 10);
  setTimeout(function () {
    aviso.classList.remove('visible');
    setTimeout(function () {
      if (aviso.parentNode) {
        aviso.parentNode.removeChild(aviso);
      }
    }, 200);
  }, 1800);
}

function actualizarFila(id, estado) {
  var estadoNodo = document.getElementById('estado-' + id);
  var accionesNodo = document.querySelector('[data-acciones="' + id + '"]');

  if (estadoNodo) {
    estadoNodo.textContent = estado.charAt(0).toUpperCase() + estado.slice(1);
    estadoNodo.className = 'estado estado-' + estado;
  }

  if (accionesNodo) {
    accionesNodo.innerHTML = '<span class="muted">Sin acciones</span>';
  }
}

function enviarCambio(id, arrendadorId, accion) {
  var ruta = '/arrendador/solicitudes/' + id + '/' + accion;

  fetch(ruta, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': obtenerTokenCsrf(),
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    body: new URLSearchParams({ arrendador_id: arrendadorId }),
    credentials: 'same-origin'
  })
    .then(function (respuesta) {
      return respuesta.json().then(function (datosRespuesta) {
        return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datosRespuesta.success) {
        throw new Error(resultado.datosRespuesta.message || 'No se pudo cambiar el estado.');
      }

      actualizarFila(id, resultado.datosRespuesta.estado);
      mostrarToast(resultado.datosRespuesta.message || 'Cambio aplicado.');
    })
    .catch(function (error) {
      mostrarToast(error.message || 'Error al procesar la solicitud.');
    });
}

document.querySelectorAll('[data-aprobar]').forEach(function (boton) {
  boton.addEventListener('click', function () {
    enviarCambio(boton.getAttribute('data-aprobar'), boton.getAttribute('data-arrendador'), 'aprobar');
  });
});

document.querySelectorAll('[data-rechazar]').forEach(function (boton) {
  boton.addEventListener('click', function () {
    enviarCambio(boton.getAttribute('data-rechazar'), boton.getAttribute('data-arrendador'), 'rechazar');
  });
});
