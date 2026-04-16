function obtenerTokenCsrf() {
  var meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function mostrarToast(texto) {
  var anterior = document.querySelector('.toast');
  if (anterior) {
    anterior.remove();
  }

  var toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = texto;
  document.body.appendChild(toast);

  setTimeout(function () { toast.classList.add('visible'); }, 10);
  setTimeout(function () {
    toast.classList.remove('visible');
    setTimeout(function () {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
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
    .then(function (response) {
      return response.json().then(function (payload) {
        return { ok: response.ok, payload: payload };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.payload.success) {
        throw new Error(resultado.payload.message || 'No se pudo cambiar el estado.');
      }

      actualizarFila(id, resultado.payload.estado);
      mostrarToast(resultado.payload.message || 'Cambio aplicado.');
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
