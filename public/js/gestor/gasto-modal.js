function abrirModalNuevoGasto() {
  var modal = document.getElementById('modal-nuevo-gasto');
  if (!modal) return;

  var form = modal.querySelector('form');
  if (form) {
    form.reset();
    form.querySelector('select[name="categoria_gasto"]').value = '';
    form.querySelector('input[name="fecha_inicio_gasto"]').value = getTodayMonthStart();
    form.querySelector('input[name="fecha_fin_gasto"]').value = getTodayMonthEnd();
  }

  var errorDiv = modal.querySelector('.mensaje-error-js');
  if (errorDiv) {
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
  }

  modal.classList.add('is-open');
}

function cerrarModalNuevoGasto() {
  var modal = document.getElementById('modal-nuevo-gasto');
  if (modal) modal.classList.remove('is-open');
}

function getTodayMonthStart() {
  var d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-01';
}

function getTodayMonthEnd() {
  var d = new Date();
  var lastDay = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(lastDay).padStart(2, '0');
}

function obtenerTokenCsrf() {
  var etiquetaCsrf = document.querySelector('meta[name="csrf-token"]');
  return etiquetaCsrf ? etiquetaCsrf.getAttribute('content') : '';
}

document.querySelectorAll('form[data-ajax-nuevo-gasto="true"]').forEach(function (form) {
  form.addEventListener('submit', function (evento) {
    evento.preventDefault();

    var errorDiv = form.querySelector('.mensaje-error-js');
    if (errorDiv) {
      errorDiv.style.display = 'none';
      errorDiv.textContent = '';
    }

    var categoria = form.querySelector('select[name="categoria_gasto"]');
    var importe = form.querySelector('input[name="importe_estimado"]');
    var fechaInicio = form.querySelector('input[name="fecha_inicio_gasto"]');
    var fechaFin = form.querySelector('input[name="fecha_fin_gasto"]');

    var errors = [];
    if (!categoria || !categoria.value) errors.push('Selecciona una categoría.');

    if (importe) {
      var raw = (importe.value || '').toString().trim().replace(',', '.');
      var val = parseFloat(raw);
      if (Number.isNaN(val) || val < 0.01) errors.push('Introduce un importe válido (>= 0.01).');
    }

    if (!fechaInicio || !fechaInicio.value) errors.push('Selecciona la fecha inicio.');
    if (!fechaFin || !fechaFin.value) errors.push('Selecciona la fecha fin.');

    if (fechaInicio && fechaFin && fechaInicio.value && fechaFin.value) {
      if (fechaFin.value < fechaInicio.value) errors.push('La fecha fin no puede ser anterior a la fecha inicio.');
    }

    if (errors.length) {
      if (errorDiv) {
        errorDiv.textContent = errors.join(' ');
        errorDiv.style.display = '';
      }
      return;
    }

    var boton = form.querySelector('button[type="submit"]');
    if (boton) {
      boton.disabled = true;
      boton.textContent = 'Guardando...';
    }

    var formData = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': obtenerTokenCsrf(),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: formData,
      credentials: 'same-origin'
    })
      .then(function (respuesta) {
        return respuesta.json().catch(function () {
          return {};
        }).then(function (datos) {
          return { ok: respuesta.ok, datos: datos };
        });
      })
      .then(function (resultado) {
        if (!resultado.ok) {
          var msg = resultado.datos && resultado.datos.message ? resultado.datos.message : 'Error al añadir el recibo.';
          if (resultado.datos && resultado.datos.errors) {
            var campos = Object.keys(resultado.datos.errors);
            if (campos.length > 0 && resultado.datos.errors[campos[0]].length > 0) {
              msg = resultado.datos.errors[campos[0]][0];
            }
          }
          throw new Error(msg);
        }

        cerrarModalNuevoGasto();

        if (window.swalSuccess) {
          swalSuccess('Recibo añadido', resultado.datos.message || 'Recibo añadido correctamente.').then(function () {
            window.location.reload();
          });
        } else {
          window.location.reload();
        }
      })
      .catch(function (error) {
        if (errorDiv) {
          errorDiv.textContent = error.message || 'Error al procesar la solicitud.';
          errorDiv.style.display = '';
        } else if (window.swalError) {
          swalError('Error', error.message || 'No se pudo añadir el recibo.');
        }
      })
      .finally(function () {
        if (boton) {
          boton.disabled = false;
          boton.textContent = 'Añadir recibo';
        }
      });
  });
});

document.onkeydown = function (evento) {
  if (evento.key === 'Escape') {
    cerrarModalNuevoGasto();
  }
};
