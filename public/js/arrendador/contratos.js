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
