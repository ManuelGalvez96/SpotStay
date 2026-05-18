function inicializarEdicionInline() {
  var card = document.getElementById('gastos-propiedad');
  if (!card) return;

  var categoriaMap = {
    'Luz': 'luz', 'Agua': 'agua', 'Gas': 'gas',
    'Internet': 'internet', 'Comunidad': 'comunidad',
    'Otros': 'otros', 'Base propiedad': 'base_propiedad'
  };

  card.addEventListener('click', function (e) {
    var target = e.target;

    if (target.classList.contains('btn-cuota-edit')) {
      var row = target.closest('.cuota-row');
      if (!row) return;

      var gastoId = row.dataset.gastoId;
      var propiedadId = row.dataset.propiedadId;
      var importe = row.dataset.importe || '';
      var mes = row.dataset.mes || '';
      var fechaInicio = row.dataset.fechaInicio || mes;
      var fechaFin = row.dataset.fechaFin || mes;

      var conceptoSpan = row.querySelector('.display-concepto');
      var categoriaSpan = row.querySelector('.display-categoria');

      var concepto = conceptoSpan ? conceptoSpan.textContent.trim() : '';
      var categoriaTexto = categoriaSpan ? categoriaSpan.textContent.trim() : '';
      var categoriaValue = categoriaMap[categoriaTexto] || '';

      abrirModalEditarGasto(gastoId, propiedadId, {
        categoria: categoriaValue,
        concepto: concepto === 'Sin concepto' ? '' : concepto,
        importe: importe,
        fechaInicio: fechaInicio,
        fechaFin: fechaFin
      });
    }

    if (target.classList.contains('btn-cuota-delete')) {
      var row = target.closest('.cuota-row');
      if (!row) return;

      var gastoId = row.dataset.gastoId;
      var propiedadId = row.dataset.propiedadId;

      if (window.Swal) {
        window.Swal.fire({
          title: '¿Eliminar recibo?',
          text: 'Esta acción no se puede deshacer.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#DC2626',
          cancelButtonColor: '#6B7280',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then(function (result) {
          if (!result.isConfirmed) return;

          var formData = new FormData();
          formData.append('_token', obtenerTokenCsrf());

          fetch('/gestor/propiedades/' + propiedadId + '/gastos/' + gastoId + '/eliminar', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
          }).then(function (res) {
            if (res.ok) {
              if (window.swalSuccess) {
                swalSuccess('Eliminado', 'Recibo eliminado correctamente.').then(function () { window.location.reload(); });
              } else {
                window.location.reload();
              }
            } else {
              return res.json().then(function (data) {
                throw new Error(data.message || 'Error al eliminar el recibo.');
              });
            }
          }).catch(function (err) {
            if (window.swalError) {
              swalError('Error', err.message || 'No se pudo eliminar el recibo.');
            }
          });
        });
      } else {
        if (confirm('¿Eliminar este recibo?')) {
          var formData = new FormData();
          formData.append('_token', obtenerTokenCsrf());

          fetch('/gestor/propiedades/' + propiedadId + '/gastos/' + gastoId + '/eliminar', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
          }).then(function (res) {
            if (res.ok) window.location.reload();
          });
        }
      }
    }
  });
}

function abrirModalEditarGasto(gastoId, propiedadId, datos) {
  var modal = document.getElementById('modal-editar-gasto');
  if (!modal) return;

  var form = modal.querySelector('form');
  if (!form) return;

  form.action = '/gestor/propiedades/' + propiedadId + '/gastos/' + gastoId + '/editar';

  var selectCategoria = form.querySelector('select[name="categoria_gasto"]');
  var inputConcepto = form.querySelector('input[name="concepto_gasto"]');
  var inputImporte = form.querySelector('input[name="importe_estimado"]');
  var inputFechaInicio = form.querySelector('input[name="fecha_inicio_gasto"]');
  var inputFechaFin = form.querySelector('input[name="fecha_fin_gasto"]');

  if (selectCategoria) selectCategoria.value = datos.categoria || '';
  if (inputConcepto) inputConcepto.value = datos.concepto || '';
  if (inputImporte) inputImporte.value = datos.importe || '';
  if (inputFechaInicio) inputFechaInicio.value = datos.fechaInicio || '';
  if (inputFechaFin) inputFechaFin.value = datos.fechaFin || '';

  var errorDiv = modal.querySelector('.mensaje-error-js');
  if (errorDiv) {
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
  }

  modal.style.display = 'flex';
}

function cerrarModalEditarGasto() {
  var modal = document.getElementById('modal-editar-gasto');
  if (modal) modal.style.display = 'none';
}

document.querySelectorAll('form[data-ajax-editar-gasto="true"]').forEach(function (form) {
  form.addEventListener('submit', function (evento) {
    evento.preventDefault();

    var modal = document.getElementById('modal-editar-gasto');

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
          var msg = resultado.datos && resultado.datos.message ? resultado.datos.message : 'Error al guardar el recibo.';
          if (resultado.datos && resultado.datos.errors) {
            var campos = Object.keys(resultado.datos.errors);
            if (campos.length > 0 && resultado.datos.errors[campos[0]].length > 0) {
              msg = resultado.datos.errors[campos[0]][0];
            }
          }
          throw new Error(msg);
        }

        cerrarModalEditarGasto();

        if (window.swalSuccess) {
          swalSuccess('Recibo actualizado', resultado.datos.message || 'Recibo actualizado correctamente.').then(function () {
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
          swalError('Error', error.message || 'No se pudo guardar el recibo.');
        }
      })
      .finally(function () {
        if (boton) {
          boton.disabled = false;
          boton.textContent = 'Guardar cambios';
        }
      });
  });
});

document.addEventListener('DOMContentLoaded', inicializarEdicionInline);
