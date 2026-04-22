function obtenerTokenCsrf() {
  var etiquetaCsrf = document.querySelector('meta[name="csrf-token"]');
  return etiquetaCsrf ? etiquetaCsrf.getAttribute('content') : '';
}

function mostrarMensaje(texto, esError) {
  var mensajeActual = document.querySelector('.fetch-toast');
  if (mensajeActual) {
    mensajeActual.remove();
  }

  var aviso = document.createElement('div');
  aviso.className = 'fetch-toast ' + (esError ? 'fetch-toast-error' : 'fetch-toast-success');
  aviso.textContent = texto;
  document.body.appendChild(aviso);

  setTimeout(function () {
    aviso.classList.add('is-visible');
  }, 10);

  setTimeout(function () {
    aviso.classList.remove('is-visible');
    setTimeout(function () {
      if (aviso.parentNode) {
        aviso.parentNode.removeChild(aviso);
      }
    }, 220);
  }, 2200);
}

function extraerMensajeError(datosRespuesta) {
  if (!datosRespuesta) {
    return 'Error al procesar la solicitud.';
  }

  if (datosRespuesta.message) {
    return datosRespuesta.message;
  }

  if (datosRespuesta.errors) {
    var campos = Object.keys(datosRespuesta.errors);
    if (campos.length > 0 && datosRespuesta.errors[campos[0]] && datosRespuesta.errors[campos[0]].length > 0) {
      return datosRespuesta.errors[campos[0]][0];
    }
  }

  return 'Error al procesar la solicitud.';
}

function actualizarEstadoEnTarjeta(formulario, nuevoEstado) {
  var tarjeta = formulario.closest('.property-card');
  if (!tarjeta) {
    return;
  }

  var insignia = tarjeta.querySelector('.badge');
  if (insignia) {
    insignia.className = 'badge badge-' + nuevoEstado;
    insignia.textContent = nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1);
  }

  var boton = formulario.querySelector('[data-state-button="true"]');
  if (boton) {
    boton.textContent = nuevoEstado === 'publicada' ? 'Inactivar' : 'Publicar';
  }
}

function enviarFormularioConFetch(formulario) {
  formulario.onsubmit = function (evento) {
    evento.preventDefault();

    var botonEnviar = formulario.querySelector('button[type="submit"]');
    var textoOriginal = botonEnviar ? botonEnviar.textContent : '';

    if (botonEnviar) {
      botonEnviar.disabled = true;
      botonEnviar.textContent = 'Guardando...';
    }

    var datosFormulario = new FormData(formulario);

    fetch(formulario.action, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': obtenerTokenCsrf(),
        'Accept': 'application/json'
      },
      body: datosFormulario,
      credentials: 'same-origin'
    })
      .then(function (respuesta) {
        return respuesta.json().catch(function () {
          return {};
        }).then(function (datosRespuesta) {
          return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
        });
      })
      .then(function (resultado) {
        if (!resultado.ok || !resultado.datosRespuesta.success) {
          throw new Error(extraerMensajeError(resultado.datosRespuesta));
        }
 
        mostrarMensaje(resultado.datosRespuesta.message || 'Cambio aplicado correctamente.', false);

        if (formulario.dataset.ajaxStateForm === 'true' && resultado.datosRespuesta.estado) {
          actualizarEstadoEnTarjeta(formulario, resultado.datosRespuesta.estado);
        }

        window.location.reload();
      })
      .catch(function (error) {
        mostrarMensaje(error.message || 'Error al procesar la solicitud.', true);
      })
      .finally(function () {
        if (botonEnviar) {
          botonEnviar.disabled = false;
          botonEnviar.textContent = textoOriginal;
        }
      });
  };
}

document.querySelectorAll('form[data-ajax-form="true"]').forEach(enviarFormularioConFetch);
document.querySelectorAll('form[data-ajax-state-form="true"]').forEach(enviarFormularioConFetch);
