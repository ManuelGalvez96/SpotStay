/* =========================================================
   SECCIÓN 1: UTILIDADES (TOKEN, TOAST)
   Funciones básicas para CSRF y notificaciones visuales.
   ========================================================= */

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

/* =========================================================
   SECCIÓN 2: ACTUALIZACIÓN DE UI (DOM)
   Modifica la fila de la tabla tras firmar un contrato.
   ========================================================= */

function actualizarFilaContrato(idContrato, estado) {
  var nodoEstado = document.getElementById('estado-' + idContrato);
  var nodoFirmaArrendador = document.getElementById('firma-arrendador-' + idContrato);
  var nodoAcciones = document.querySelector('[data-acciones="' + idContrato + '"]');

  if (nodoEstado) {
    nodoEstado.textContent = estado.charAt(0).toUpperCase() + estado.slice(1);
    nodoEstado.className = 'estado estado-' + estado;
  }

  if (nodoFirmaArrendador) {
    nodoFirmaArrendador.innerHTML = 'Firmado';
  }

  if (nodoAcciones) {
    var enlacePdf = nodoAcciones.querySelector('.btn-ver');
    if (enlacePdf) {
      nodoAcciones.innerHTML = '<span class="muted">Sin acciones</span>';
      nodoAcciones.appendChild(enlacePdf);
      return;
    }

    nodoAcciones.innerHTML = '<span class="muted">Sin acciones</span>';
  }
}

/* =========================================================
   SECCIÓN 3: FIRMA DE CONTRATO (API)
   Envía la petición para firmar y actualiza el resultado.
   ========================================================= */

function firmarContratoArrendador(idContrato, arrendadorId) {
  var ruta = '/arrendador/contratos/' + idContrato + '/firmar-arrendador';

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
        throw new Error(resultado.datosRespuesta.message || 'No se pudo firmar el contrato.');
      }

      actualizarFilaContrato(idContrato, resultado.datosRespuesta.estado || 'pendiente');
      mostrarToast(resultado.datosRespuesta.message || 'Contrato firmado.');
    })
    .catch(function (error) {
      mostrarToast(error.message || 'Error al firmar el contrato.');
    });
}

document.querySelectorAll('[data-firmar-arrendador]').forEach(function (boton) {
  boton.addEventListener('click', function () {
    firmarContratoArrendador(
      boton.getAttribute('data-firmar-arrendador'),
      boton.getAttribute('data-arrendador')
    );
  });
});

/* =========================================================
   SECCIÓN 4: SUBIR PDF (FILE PICKER)
   Al pulsar "Subir PDF", abre el selector de archivos.
   ========================================================= */

document.querySelectorAll('.btn-subir-pdf').forEach(function (boton) {
  boton.addEventListener('click', function () {
    var idContrato = boton.getAttribute('data-contrato');
    var input = document.getElementById('pdf-input-' + idContrato);
    if (input) {
      input.click();
    }
  });
});

/* =========================================================
   SECCIÓN 5: SUBIR PDF (AUTO-SUBMIT)
   Cuando se selecciona un archivo, se envía el formulario
   por fetch y se muestra el resultado con toast.
   ========================================================= */

document.querySelectorAll('input[name="pdf_contrato"]').forEach(function (input) {
  input.addEventListener('change', function () {
    var formulario = input.closest('.form-subir-pdf');
    if (!formulario || !input.files || input.files.length === 0) return;

    var datos = new FormData(formulario);

    fetch(formulario.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': obtenerTokenCsrf(),
        'Accept': 'application/json',
      },
      body: datos,
    })
    .then(function (respuesta) {
      return respuesta.json().then(function (datosRespuesta) {
        return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datosRespuesta.success) {
        throw new Error(resultado.datosRespuesta.message || 'No se pudo subir el PDF.');
      }

      // Mostrar el botón "Ver PDF" en la fila
      var contenedor = formulario.closest('.acciones');
      if (contenedor) {
        var enlaceExistente = contenedor.querySelector('.btn-ver');
        if (!enlaceExistente && resultado.datosRespuesta.url_pdf) {
          var enlace = document.createElement('a');
          enlace.className = 'btn-ver';
          enlace.href = resultado.datosRespuesta.url_pdf;
          enlace.target = '_blank';
          enlace.textContent = 'Ver PDF';
          contenedor.appendChild(enlace);
        }
      }

      mostrarToast(resultado.datosRespuesta.message || 'PDF subido correctamente.');
    })
    .catch(function (error) {
      mostrarToast(error.message || 'Error al subir el PDF.');
    });
  });
});
