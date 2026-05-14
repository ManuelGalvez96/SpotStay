function obtenerTokenCsrf() {
  var etiquetaCsrf = document.querySelector('meta[name="csrf-token"]');
  return etiquetaCsrf ? etiquetaCsrf.getAttribute('content') : '';
}

function abrirModalEditar(propiedadId) {
  var modal = document.getElementById('modal-editar-propiedad');
  if (!modal) return;

  var form = modal.querySelector('form');
  if (form) form.reset();

  document.getElementById('edit-id-propiedad').value = propiedadId;
  document.getElementById('modal-editar-titulo').textContent = 'Cargando...';

  modal.classList.add('is-open');

  var ruta = '/gestor/propiedades/' + propiedadId + '/editar-datos';

  fetch(ruta, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    credentials: 'same-origin'
  })
    .then(function (respuesta) {
      return respuesta.json().then(function (datos) {
        return { ok: respuesta.ok, datos: datos };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datos.success) {
        throw new Error(resultado.datos.message || 'Error al cargar la propiedad.');
      }

      var propiedad = resultado.datos.propiedad;
      document.getElementById('modal-editar-titulo').textContent = 'Editar: ' + propiedad.titulo_propiedad;

      document.getElementById('edit-form-titulo').value = propiedad.titulo_propiedad || '';
      document.getElementById('edit-form-tipo').value = propiedad.tipo_propiedad || '';
      document.getElementById('edit-form-calle').value = propiedad.calle_propiedad || '';
      document.getElementById('edit-form-numero').value = propiedad.numero_propiedad || '';
      document.getElementById('edit-form-piso').value = propiedad.piso_propiedad || '';
      document.getElementById('edit-form-puerta').value = propiedad.puerta_propiedad || '';
      document.getElementById('edit-form-codigo-postal').value = propiedad.codigo_postal_propiedad || '';
      document.getElementById('edit-form-ciudad').value = propiedad.ciudad_propiedad || '';
      document.getElementById('edit-form-habitaciones').value = propiedad.habitaciones_propiedad || '';
      document.getElementById('edit-form-banos').value = propiedad.banos_propiedad || '';
      document.getElementById('edit-form-metros').value = propiedad.metros_cuadrados_propiedad || '';
      document.getElementById('edit-form-ascensor').checked = Boolean(Number(propiedad.ascensor_propiedad));
      document.getElementById('edit-form-amueblado').checked = Boolean(Number(propiedad.amueblado_propiedad));
      document.getElementById('edit-form-piscina').checked = Boolean(Number(propiedad.piscina_propiedad));
      document.getElementById('edit-form-terraza').checked = Boolean(Number(propiedad.terraza_propiedad));
      document.getElementById('edit-form-garaje').checked = Boolean(Number(propiedad.garaje_propiedad));
      document.getElementById('edit-form-aire-acondicionado').checked = Boolean(Number(propiedad.aire_acondicionado_propiedad));
      document.getElementById('edit-form-calefaccion').checked = Boolean(Number(propiedad.calefaccion_propiedad));
      document.getElementById('edit-form-trastero').checked = Boolean(Number(propiedad.trastero_propiedad));
      document.getElementById('edit-form-adicional').value = propiedad.adicional_propiedad || '';
      document.getElementById('edit-form-descripcion').value = propiedad.descripcion_propiedad || '';

      var seccionPrecio = document.getElementById('edit-seccion-precio');
      if (resultado.datos.permisos && !resultado.datos.permisos.puede_editar_precio) {
        seccionPrecio.style.display = 'none';
      } else {
        seccionPrecio.style.display = '';
        document.getElementById('edit-form-precio').value = propiedad.precio_propiedad || '';
      }
    })
    .catch(function (error) {
      cerrarModalEditar();
      if (window.swalError) {
        swalError('Error', error.message || 'No se pudo cargar la propiedad.');
      }
    });
}

function cerrarModalEditar() {
  var modal = document.getElementById('modal-editar-propiedad');
  if (modal) modal.classList.remove('is-open');
}

document.querySelectorAll('form[data-ajax-editar="true"]').forEach(function (form) {
  form.addEventListener('submit', function (evento) {
    evento.preventDefault();

    var boton = form.querySelector('button[type="submit"]');
    if (boton) {
      boton.disabled = true;
      boton.textContent = 'Guardando...';
    }

    var propiedadId = document.getElementById('edit-id-propiedad').value;
    var formData = new FormData(form);

    fetch('/gestor/propiedades/' + propiedadId + '/editar', {
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
        if (!resultado.ok || !resultado.datos.success) {
          throw new Error(resultado.datos.message || 'Error al guardar la propiedad.');
        }

        cerrarModalEditar();

        if (window.swalSuccess) {
          swalSuccess('Propiedad actualizada', resultado.datos.message || 'Cambios guardados correctamente.');
        }
      })
      .catch(function (error) {
        if (window.swalError) {
          swalError('Error', error.message || 'No se pudo guardar.');
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

document.onkeydown = function (evento) {
  if (evento.key === 'Escape') {
    cerrarModalEditar();
  }
};
