function obtenerTokenCsrf() {
  var meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function mostrarToast(mensaje, tipo) {
  var toast = document.getElementById('toastGestor');
  if (!toast) {
    return;
  }

  toast.textContent = mensaje;
  toast.className = 'toast ' + (tipo || 'ok');
  toast.hidden = false;

  window.setTimeout(function () {
    toast.hidden = true;
  }, 2200);
}

function enviarFormularioConFetch(formulario) {
  var selector = formulario.querySelector('select[name="id_gestor_fk"]');
  var boton = formulario.querySelector('.btn-guardar');
  var textoBoton = formulario.querySelector('.texto-boton');

  if (!selector) {
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
      id_gestor_fk: selector.value
    })
  })
    .then(function (response) {
      return response.json().then(function (payload) {
        return { ok: response.ok, payload: payload };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.payload.success) {
        throw new Error(resultado.payload.message || 'No se pudo guardar.');
      }

      var fila = formulario.closest('tr');
      var nombreActual = fila ? fila.querySelector('[data-nombre-gestor]') : null;
      if (nombreActual) {
        nombreActual.textContent = selector.options[selector.selectedIndex].text.split(' - ')[0];
      }

      mostrarToast(resultado.payload.message || 'Gestor actualizado.', 'ok');
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

document.querySelectorAll('[data-form-gestor="true"]').forEach(function (formulario) {
  formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    enviarFormularioConFetch(formulario);
  });
});
