/* =========================================================
   SECCIÓN 1: UTILIDADES (TOKEN, TOAST, FORMATO)
   Funciones auxiliares para obtener el CSRF y mostrar feedback.
   ========================================================= */

function obtenerTokenCsrf() {
  var etiquetaCsrf = document.querySelector('meta[name="csrf-token"]');
  return etiquetaCsrf ? etiquetaCsrf.getAttribute('content') : '';
}

function mostrarToast(mensaje, tipo) {
  var aviso = document.getElementById('toastPrecios');
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

function formatearImporte(valor) {
  var numero = Number(valor || 0);
  return numero.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' EUR';
}

/* =========================================================
   SECCIÓN 2: ACTUALIZACIÓN EN TIEMPO REAL
   Recalcula el total mensual al escribir el precio.
   ========================================================= */

function actualizarResumenFormulario(formulario) {
  var precio = formulario.querySelector('input[name="precio_propiedad"]');
  var total = formulario.querySelector('[data-total-mensual]');
  var estado = formulario.querySelector('[data-estado-gastos]');

  if (!precio || !total || !estado) {
    return;
  }

  var precioValor = Number((precio.value || '0').replace(',', '.'));
  var precioSeguro = isNaN(precioValor) ? 0 : precioValor;
  total.textContent = formatearImporte(precioSeguro);
  estado.textContent = precioSeguro > 0 ? 'Importe calculado con el precio mensual.' : 'Introduce un precio para calcular.';
}

/* =========================================================
   SECCIÓN 3: ENVÍO ASÍNCRONO (FETCH)
   Guarda los cambios sin recargar la página.
   ========================================================= */

function enviarFormularioConFetch(formulario) {
  var precio = formulario.querySelector('input[name="precio_propiedad"]');
  var boton = formulario.querySelector('.btn-guardar');
  var textoBoton = formulario.querySelector('.texto-boton');

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
      precio_propiedad: precio ? precio.value : ''
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

      mostrarToast(resultado.datosRespuesta.message || 'Guardado correctamente.', 'ok');
    })
    .catch(function (error) {
      mostrarToast(error.message || 'No se pudo guardar.', 'error');
    })
    .finally(function () {
      if (boton) {
        boton.disabled = false;
      }
      if (textoBoton) {
        textoBoton.textContent = 'Guardar cambios';
      }
    });
}

document.querySelectorAll('[data-form-precios="true"]').forEach(function (formulario) {
  var precio = formulario.querySelector('input[name="precio_propiedad"]');

  if (precio) {
    precio.addEventListener('input', function () {
      actualizarResumenFormulario(formulario);
    });
  }

  actualizarResumenFormulario(formulario);

  formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    enviarFormularioConFetch(formulario);
  });
});
