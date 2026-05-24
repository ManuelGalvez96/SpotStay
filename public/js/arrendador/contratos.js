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

// Eliminada la funcionalidad de firma del arrendador (botón y petición).
// Las acciones ahora se limitan a subir PDF y mostrar/enlazar el contrato si existe.

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
        if (!enlaceExistente) {
          var enlace = document.createElement('a');
          enlace.className = 'btn-ver';
          // Construimos la ruta de descarga a partir del action del formulario
          // Ej: /arrendador/contratos/{id}/subir-pdf -> /arrendador/contratos/{id}/descargar-pdf
          var downloadHref = formulario.action.replace('/subir-pdf', '/descargar-pdf');
          enlace.href = downloadHref;
          enlace.textContent = 'Ver Contrato';
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
