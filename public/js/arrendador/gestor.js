function obtenerTokenCsrf() {
  var etiquetaCsrf = document.querySelector('meta[name="csrf-token"]');
  return etiquetaCsrf ? etiquetaCsrf.getAttribute('content') : '';
}

function mostrarToast(mensaje, tipo) {
  var aviso = document.getElementById('toastGestor');
  if (!aviso) {
    return;
  }

  aviso.textContent = mensaje;
  aviso.className = 'toast ' + (tipo || 'ok');
  aviso.hidden = false;

  window.setTimeout(function () {
    aviso.hidden = true;
  }, 2200);
}

function enviarFormularioConFetch(formulario) {
  var selectorGestor = formulario.querySelector('select[name="id_gestor_fk"]');
  var boton = formulario.querySelector('.btn-guardar');
  var textoBoton = formulario.querySelector('.texto-boton');
  var fila = formulario.closest('tr');
  var nombreActual = fila ? fila.querySelector('[data-nombre-gestor]') : null;
  var emailActual = fila ? fila.querySelector('[data-email-gestor]') : null;

  if (!selectorGestor) {
    return;
  }

  if (boton) {
    boton.disabled = true;
  }
  if (textoBoton) {
    textoBoton.textContent = 'Guardando...';
  }

  fetch(formulario.action, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': obtenerTokenCsrf(),
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    credentials: 'same-origin',
    body: JSON.stringify({
      id_gestor_fk: selectorGestor.value
    })
  })
    .then(function (respuesta) {
      return respuesta.json().then(function (datosRespuesta) {
        return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datosRespuesta.success) {
        throw new Error(resultado.datosRespuesta.message || 'No se pudo guardar.');
      }

      if (nombreActual) {
        if (selectorGestor.value === '') {
          nombreActual.textContent = 'Sin gestor';
        } else {
          nombreActual.textContent = selectorGestor.options[selectorGestor.selectedIndex].text.split(' - ')[0];
        }
      }

      if (emailActual) {
        if (selectorGestor.value === '') {
          emailActual.textContent = 'Sin email';
        } else {
          emailActual.textContent = selectorGestor.options[selectorGestor.selectedIndex].text.indexOf(' - ') !== -1 ? selectorGestor.options[selectorGestor.selectedIndex].text.split(' - ')[1] : '';
        }
      }

      mostrarToast(resultado.datosRespuesta.message || 'Gestor actualizado.', 'ok');
    })
    .catch(function (error) {
      mostrarToast(error.message || 'No se pudo guardar.', 'error');
    })
    .finally(function () {
      if (boton) {
        boton.disabled = false;
      }
      if (textoBoton) {
        textoBoton.textContent = 'Guardar';
      }
    });
}

function desasignarGestor(formulario) {
  var selectorGestor = formulario.querySelector('select[name="id_gestor_fk"]');

  if (!selectorGestor) {
    return;
  }

  selectorGestor.value = '';
  enviarFormularioConFetch(formulario);
}

document.querySelectorAll('[data-form-gestor="true"]').forEach(function (formulario) {
  formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    enviarFormularioConFetch(formulario);
  });

  var botonDesasignar = formulario.querySelector('[data-desasignar-gestor="true"]');
  if (botonDesasignar) {
    botonDesasignar.addEventListener('click', function () {
      desasignarGestor(formulario);
    });
  }
});
