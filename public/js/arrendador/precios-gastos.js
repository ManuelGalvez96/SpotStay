function obtenerTokenCsrf() {
  var meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function mostrarToast(mensaje, tipo) {
  var toast = document.getElementById('toastPrecios');
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

function formatearImporte(valor) {
  var numero = Number(valor || 0);
  return numero.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' EUR';
}

function parsearGastos(texto) {
  var limpio = (texto || '').trim();

  if (!limpio) {
    return { valido: true, total: 0, descripcion: 'Sin gastos extra.' };
  }

  var numeroDirecto = Number(limpio.replace(',', '.'));
  if (!isNaN(numeroDirecto)) {
    return { valido: true, total: numeroDirecto, descripcion: 'Gasto simple detectado.' };
  }

  try {
    var json = JSON.parse(limpio);
    var total = 0;

    if (Array.isArray(json)) {
      json.forEach(function (item) {
        var valor = Number(item);
        if (!isNaN(valor)) {
          total += valor;
        }
      });
      return { valido: true, total: total, descripcion: 'Gastos JSON en array.' };
    }

    if (json && typeof json === 'object') {
      Object.keys(json).forEach(function (clave) {
        var valor = Number(json[clave]);
        if (!isNaN(valor)) {
          total += valor;
        }
      });
      return { valido: true, total: total, descripcion: 'Gastos JSON por conceptos.' };
    }

    return { valido: false, total: 0, descripcion: 'Formato de gastos no compatible.' };
  } catch (error) {
    return { valido: false, total: 0, descripcion: 'Gastos no validos para calculo automatico.' };
  }
}

function actualizarResumenFormulario(formulario) {
  var precio = formulario.querySelector('input[name="precio_propiedad"]');
  var gastos = formulario.querySelector('textarea[name="gastos_propiedad"]');
  var total = formulario.querySelector('[data-total-mensual]');
  var estado = formulario.querySelector('[data-estado-gastos]');

  if (!precio || !gastos || !total || !estado) {
    return;
  }

  var precioValor = Number((precio.value || '0').replace(',', '.'));
  var precioSeguro = isNaN(precioValor) ? 0 : precioValor;
  var resultadoGastos = parsearGastos(gastos.value);

  if (!resultadoGastos.valido) {
    total.textContent = formatearImporte(precioSeguro);
    estado.textContent = resultadoGastos.descripcion;
    return;
  }

  total.textContent = formatearImporte(precioSeguro + resultadoGastos.total);
  estado.textContent = resultadoGastos.descripcion;
}

function enviarFormularioConFetch(formulario) {
  var precio = formulario.querySelector('input[name="precio_propiedad"]');
  var gastos = formulario.querySelector('textarea[name="gastos_propiedad"]');
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
      precio_propiedad: precio ? precio.value : '',
      gastos_propiedad: gastos ? gastos.value : ''
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

      mostrarToast(resultado.payload.message || 'Guardado correctamente.', 'ok');
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
  var gastos = formulario.querySelector('textarea[name="gastos_propiedad"]');

  if (precio) {
    precio.addEventListener('input', function () {
      actualizarResumenFormulario(formulario);
    });
  }

  if (gastos) {
    gastos.addEventListener('input', function () {
      actualizarResumenFormulario(formulario);
    });
  }

  actualizarResumenFormulario(formulario);

  formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    enviarFormularioConFetch(formulario);
  });
});
