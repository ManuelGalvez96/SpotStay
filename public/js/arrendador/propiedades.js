function obtenerTokenCsrf() {
  var meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function mostrarMensaje(texto, esError) {
  var existing = document.querySelector('.fetch-toast');
  if (existing) {
    existing.remove();
  }

  var toast = document.createElement('div');
  toast.className = 'fetch-toast ' + (esError ? 'fetch-toast-error' : 'fetch-toast-success');
  toast.textContent = texto;
  document.body.appendChild(toast);

  setTimeout(function () {
    toast.classList.add('is-visible');
  }, 10);

  setTimeout(function () {
    toast.classList.remove('is-visible');
    setTimeout(function () {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 220);
  }, 2200);
}

function enviarFormularioConFetch(formulario) {
  formulario.onsubmit = function (event) {
    event.preventDefault();

    var submitButton = formulario.querySelector('button[type="submit"]');
    var originalText = submitButton ? submitButton.textContent : '';

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Guardando...';
    }

    var formData = new FormData(formulario);

    fetch(formulario.action, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': obtenerTokenCsrf(),
        'Accept': 'application/json'
      },
      body: formData,
      credentials: 'same-origin'
    })
      .then(function (response) {
        return response.json().then(function (payload) {
          return { ok: response.ok, payload: payload };
        });
      })
      .then(function (result) {
        if (!result.ok || !result.payload.success) {
          throw new Error(result.payload.message || 'No se pudo guardar la propiedad.');
        }
 
        mostrarMensaje(result.payload.message || 'Cambio aplicado correctamente.', false);
        window.location.reload();
      })
      .catch(function (error) {
        mostrarMensaje(error.message || 'Error al procesar la solicitud.', true);
      })
      .finally(function () {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.textContent = originalText;
        }
      });
  };
}

document.querySelectorAll('form[data-ajax-form="true"]').forEach(enviarFormularioConFetch);
document.querySelectorAll('form[data-ajax-state-form="true"]').forEach(enviarFormularioConFetch);
