/* =========================================================
   SECCIÓN 1: UTILIDADES Y MODALES
   Funciones para CSRF, toasts y creación dinámica de modales.
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

function crearModal(titulo, contenido) {
  var modal = document.createElement('div');
  modal.className = 'modal-overlay';

  var contenedor = document.createElement('div');
  contenedor.className = 'modal-contenedor';

  var encabezado = document.createElement('div');
  encabezado.className = 'modal-encabezado';

  var h2 = document.createElement('h2');
  h2.textContent = titulo;
  encabezado.appendChild(h2);

  var botonCerrar = document.createElement('button');
  botonCerrar.className = 'modal-cerrar';
  botonCerrar.textContent = '✕';
  botonCerrar.onclick = function () { modal.remove(); };
  encabezado.appendChild(botonCerrar);

  var cuerpo = document.createElement('div');
  cuerpo.className = 'modal-cuerpo';
  cuerpo.innerHTML = contenido;

  contenedor.appendChild(encabezado);
  contenedor.appendChild(cuerpo);
  modal.appendChild(contenedor);
  modal.onclick = function (e) {
    if (e.target === modal) modal.remove();
  };

  return { modal: modal, cuerpo: cuerpo, contenedor: contenedor };
}

/* =========================================================
   SECCIÓN 2: ACTUALIZACIÓN DE UI (TABLA)
   Modifica los botones y estado de la fila según el estado.
   ========================================================= */

function actualizarFila(id, estado) {
  var estadoNodo = document.getElementById('estado-' + id);
  var accionesNodo = document.querySelector('[data-acciones="' + id + '"]');

  if (estadoNodo) {
    estadoNodo.textContent = estado.charAt(0).toUpperCase() + estado.slice(1);
    estadoNodo.className = 'estado estado-' + estado;
  }

  if (accionesNodo) {
    if (estado === 'activo') {
      accionesNodo.innerHTML =
        '<button class="btn-ver" data-ver="' + id + '">Ver</button>';
    } else if (estado === 'pendiente') {
      accionesNodo.innerHTML =
        '<button class="btn-ver" data-ver="' + id + '">Ver</button>' +
        '<button class="btn-editar" data-editar="' + id + '">Editar</button>' +
        '<button class="btn-icono btn-aprobar-sol" data-id="' + id + '" title="Aprobar"><i class="bi bi-check-circle"></i></button>' +
        '<button class="btn-icono btn-rechazar-sol" data-id="' + id + '" title="Rechazar"><i class="bi bi-x-circle"></i></button>';
    } else {
      accionesNodo.innerHTML =
        '<button class="btn-ver" data-ver="' + id + '">Ver</button>' +
        '<button class="btn-editar" data-editar="' + id + '">Editar</button>';
    }
    accionesNodo.setAttribute('data-estado', estado);
    agregarEventosAcciones();
  }
}

/* =========================================================
   SECCIÓN 3: VER DETALLES (MODAL)
   Obtiene los datos de la solicitud y los muestra en un modal.
   ========================================================= */

function verSolicitud(id, arrendadorId) {
  fetch('/arrendador/solicitudes/' + id + '/ver?arrendador_id=' + encodeURIComponent(arrendadorId || ''), {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    credentials: 'same-origin'
  })
    .then(function (respuesta) {
      return respuesta.json().then(function (datosRespuesta) {
        return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datosRespuesta.success) {
        throw new Error(resultado.datosRespuesta.message || 'No se pudo obtener los datos.');
      }

      var datos = resultado.datosRespuesta.data;
      var contenido =
        '<div class="detalles-solicitud">' +
        '  <p><strong>Propiedad:</strong> ' + datos.titulo_propiedad + '</p>' +
        '  <p><strong>Dirección:</strong> ' + datos.direccion_propiedad + '</p>' +
        '  <p><strong>Inquilino:</strong> ' + datos.nombre_inquilino + '</p>' +
        '  <p><strong>Email:</strong> ' + datos.email_inquilino + '</p>' +
        '  <p><strong>Teléfono:</strong> ' + (datos.telefono_inquilino || 'No disponible') + '</p>' +
        '  <p><strong>Estado:</strong> ' + datos.estado_solicitud_alquiler + '</p>' +
        '  <p><strong>Fecha Inicio:</strong> ' + datos.fecha_inicio_solicitud_alquiler + '</p>' +
        '  <p><strong>Monto Mensual:</strong> €' + (parseFloat(datos.precio_alquiler) || 0).toFixed(2) + '</p>' +
        '</div>';

      var miModal = crearModal('Detalles de la Solicitud', contenido);
      document.body.appendChild(miModal.modal);
    })
    .catch(function (error) {
      mostrarToast(error.message || 'Error al obtener los datos.');
    });
}

/* =========================================================
   SECCIÓN 4: EDITAR SOLICITUD (MODAL)
   Carga el formulario de edición en un modal.
   ========================================================= */

function editarSolicitud(id, arrendadorId) {
  fetch('/arrendador/solicitudes/' + id + '/ver?arrendador_id=' + encodeURIComponent(arrendadorId || ''), {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    credentials: 'same-origin'
  })
    .then(function (respuesta) {
      return respuesta.json().then(function (datosRespuesta) {
        return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datosRespuesta.success) {
        throw new Error(resultado.datosRespuesta.message || 'No se pudo obtener los datos.');
      }

      var datos = resultado.datosRespuesta.data;
      var contenido =
        '<form id="formulario-editar-' + id + '" class="formulario-editar">' +
        '  <div class="campo-formulario">' +
        '    <label>Propiedad:</label>' +
        '    <input type="text" value="' + datos.titulo_propiedad + '" disabled>' +
        '  </div>' +
        '  <div class="campo-formulario">' +
        '    <label>Inquilino:</label>' +
        '    <input type="text" value="' + datos.nombre_inquilino + '" disabled>' +
        '  </div>' +
        '  <div class="campo-formulario">' +
        '    <label for="fecha-inicio-' + id + '">Fecha Inicio:</label>' +
        '    <input type="date" id="fecha-inicio-' + id + '" name="fecha_inicio_solicitud_alquiler" value="' + datos.fecha_inicio_solicitud_alquiler + '" required>' +
        '  </div>' +
        '</form>';

      var miModal = crearModal('Editar Solicitud', contenido);

      var pieModal = document.createElement('div');
      pieModal.className = 'modal-pie';

      var botonGuardar = document.createElement('button');
      botonGuardar.className = 'btn-guardar';
      botonGuardar.textContent = 'Guardar Cambios';
      botonGuardar.onclick = function () {
        guardarEdicion(id, arrendadorId, miModal.modal);
      };

      var botonCancelar = document.createElement('button');
      botonCancelar.className = 'btn-cancelar';
      botonCancelar.textContent = 'Cancelar';
      botonCancelar.onclick = function () {
        miModal.modal.remove();
      };

      pieModal.appendChild(botonGuardar);
      pieModal.appendChild(botonCancelar);
      miModal.contenedor.appendChild(pieModal);

      document.body.appendChild(miModal.modal);
    })
    .catch(function (error) {
      mostrarToast(error.message || 'Error al obtener los datos.');
    });
}

/* =========================================================
  SECCIÓN 5: GUARDAR (API)
  Funciones asíncronas para actualizar solicitudes.
  ========================================================= */

function guardarEdicion(id, arrendadorId, modal) {
  var formulario = document.getElementById('formulario-editar-' + id);
  if (!formulario) return;

  var datosFormulario = new FormData(formulario);
  datosFormulario.append('arrendador_id', arrendadorId || '');

  fetch('/arrendador/solicitudes/' + id + '/actualizar', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': obtenerTokenCsrf(),
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    body: datosFormulario,
    credentials: 'same-origin'
  })
    .then(function (respuesta) {
      return respuesta.json().then(function (datosRespuesta) {
        return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datosRespuesta.success) {
        throw new Error(resultado.datosRespuesta.message || 'No se pudo guardar los cambios.');
      }

      modal.remove();
      mostrarToast('Solicitud actualizada correctamente.');
    })
    .catch(function (error) {
      mostrarToast(error.message || 'Error al guardar los cambios.');
    });
}

function aprobarSolicitud(id, arrendadorId) {
  Swal.fire({
    title: '¿Aceptar solicitud?',
    text: 'Se creara el alquiler y se asignara el rol de inquilino si procede.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#0f9f6e',
    cancelButtonColor: '#757575',
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar'
  }).then(function (result) {
    if (!result.isConfirmed) {
      return;
    }

    fetch('/arrendador/solicitudes/' + id + '/aprobar', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': obtenerTokenCsrf(),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: new URLSearchParams({ arrendador_id: arrendadorId || '' }),
      credentials: 'same-origin'
    })
      .then(function (respuesta) {
        return respuesta.json().then(function (datosRespuesta) {
          return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
        });
      })
      .then(function (resultado) {
        if (!resultado.ok || !resultado.datosRespuesta.success) {
          throw new Error(resultado.datosRespuesta.message || 'No se pudo aprobar la solicitud.');
        }

        actualizarFila(id, 'activo');
        mostrarToast('Solicitud aprobada correctamente.');
      })
      .catch(function (error) {
        mostrarToast(error.message || 'Error al aprobar la solicitud.');
      });
  });
}

function rechazarSolicitud(id, arrendadorId) {
  Swal.fire({
    title: '¿Rechazar solicitud?',
    text: 'Esta accion no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#f97316',
    cancelButtonColor: '#757575',
    confirmButtonText: 'Rechazar',
    cancelButtonText: 'Cancelar'
  }).then(function (result) {
    if (!result.isConfirmed) {
      return;
    }

    fetch('/arrendador/solicitudes/' + id + '/rechazar', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': obtenerTokenCsrf(),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: new URLSearchParams({ arrendador_id: arrendadorId || '' }),
      credentials: 'same-origin'
    })
      .then(function (respuesta) {
        return respuesta.json().then(function (datosRespuesta) {
          return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
        });
      })
      .then(function (resultado) {
        if (!resultado.ok || !resultado.datosRespuesta.success) {
          throw new Error(resultado.datosRespuesta.message || 'No se pudo rechazar la solicitud.');
        }

        actualizarFila(id, 'rechazado');
        mostrarToast('Solicitud rechazada correctamente.');
      })
      .catch(function (error) {
        mostrarToast(error.message || 'Error al rechazar la solicitud.');
      });
  });
}

/* =========================================================
  SECCIÓN 6: EVENT LISTERS (ACCIÓN)
  Asigna los clics a los botones de la tabla (Ver, Editar, Aceptar, Rechazar).
  ========================================================= */

function agregarEventosAcciones() {
  document.querySelectorAll('[data-ver]').forEach(function (boton) {
    boton.onclick = function (e) {
      e.preventDefault();
      var accionesDiv = boton.closest('[data-acciones]');
      var arrendadorId = accionesDiv ? accionesDiv.getAttribute('data-arrendador') : '';
      verSolicitud(boton.getAttribute('data-ver'), arrendadorId);
    };
  });

  document.querySelectorAll('[data-editar]').forEach(function (boton) {
    boton.onclick = function (e) {
      e.preventDefault();
      var accionesDiv = boton.closest('[data-acciones]');
      var arrendadorId = accionesDiv ? accionesDiv.getAttribute('data-arrendador') : '';
      editarSolicitud(boton.getAttribute('data-editar'), arrendadorId);
    };
  });

  document.querySelectorAll('[data-aprobar], .btn-aprobar-sol').forEach(function (boton) {
    boton.onclick = function (e) {
      e.preventDefault();
      var accionesDiv = boton.closest('[data-acciones]');
      var arrendadorId = accionesDiv ? accionesDiv.getAttribute('data-arrendador') : '';
      var solicitudId = boton.getAttribute('data-aprobar') || boton.getAttribute('data-id');
      aprobarSolicitud(solicitudId, arrendadorId);
    };
  });

  document.querySelectorAll('[data-rechazar], .btn-rechazar-sol').forEach(function (boton) {
    boton.onclick = function (e) {
      e.preventDefault();
      var accionesDiv = boton.closest('[data-acciones]');
      var arrendadorId = accionesDiv ? accionesDiv.getAttribute('data-arrendador') : '';
      var solicitudId = boton.getAttribute('data-rechazar') || boton.getAttribute('data-id');
      rechazarSolicitud(solicitudId, arrendadorId);
    };
  });
}

agregarEventosAcciones();

