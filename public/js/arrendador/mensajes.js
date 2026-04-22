function obtenerArrendadorId() {
  var contenedor = document.querySelector('[data-arrendador-id]');
  return contenedor ? contenedor.getAttribute('data-arrendador-id') : '';
}

function obtenerTokenCsrf() {
  var etiquetaCsrf = document.querySelector('meta[name="csrf-token"]');
  return etiquetaCsrf ? etiquetaCsrf.getAttribute('content') : '';
}

function marcarConversacionActiva(idConversacion) {
  document.querySelectorAll('[data-conversacion-id]').forEach(function (elemento) {
    if (elemento.getAttribute('data-conversacion-id') === String(idConversacion)) {
      elemento.classList.add('activo');
    } else {
      elemento.classList.remove('activo');
    }
  });
}

function formatearFecha(valor) {
  if (!valor) {
    return '';
  }

  var fecha = new Date(valor);
  if (isNaN(fecha.getTime())) {
    return valor;
  }

  return fecha.toLocaleString('es-ES');
}

function renderizarMensajes(mensajes, arrendadorId) {
  var lista = document.getElementById('listaMensajes');
  if (!lista) {
    return;
  }

  if (!mensajes || mensajes.length === 0) {
    lista.innerHTML = '<p class="muted">Esta conversación todavía no tiene mensajes.</p>';
    return;
  }

  var contenidoHtml = '';
  mensajes.forEach(function (mensaje) {
    var clase = String(mensaje.id_remitente) === String(arrendadorId) ? 'burbuja arrendador' : 'burbuja';
    contenidoHtml += '<div class="' + clase + '">';
    contenidoHtml += '<strong>' + (mensaje.nombre_remitente || 'Usuario') + '</strong>';
    contenidoHtml += '<div>' + (mensaje.cuerpo_mensaje || '') + '</div>';
    contenidoHtml += '<small>' + formatearFecha(mensaje.creado_mensaje) + '</small>';
    contenidoHtml += '</div>';
  });

  lista.innerHTML = contenidoHtml;
  lista.scrollTop = lista.scrollHeight;
}

function cargarConversacion(idConversacion) {
  var arrendadorId = obtenerArrendadorId();
  var ruta = '/arrendador/mensajes/' + idConversacion + '?arrendador_id=' + encodeURIComponent(arrendadorId);

  fetch(ruta, {
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
        throw new Error(resultado.datosRespuesta.message || 'No se pudo cargar la conversación.');
      }

      var conversacion = resultado.datosRespuesta.conversacion;
      var titulo = document.getElementById('tituloHilo');
      var subtitulo = document.getElementById('subtituloHilo');
      var inputId = document.getElementById('idConversacionSeleccionada');
      var formulario = document.getElementById('formularioMensaje');

      if (titulo) {
        titulo.textContent = conversacion.inquilino ? conversacion.inquilino.nombre_usuario : 'Conversación';
      }
      if (subtitulo) {
        subtitulo.textContent = conversacion.inquilino
          ? (conversacion.inquilino.email_usuario || 'Sin email')
          : 'Sin datos del inquilino';
      }
      if (inputId) {
        inputId.value = conversacion.id_conversacion;
      }
      if (formulario) {
        formulario.hidden = false;
      }

      renderizarMensajes(conversacion.mensajes, arrendadorId);
      marcarConversacionActiva(conversacion.id_conversacion);
    })
    .catch(function () {
      alert('No se pudo cargar la conversación.');
    });
}

function enviarMensajeConFetch(evento) {
  evento.preventDefault();

  var inputId = document.getElementById('idConversacionSeleccionada');
  var textarea = document.getElementById('textoMensaje');
  var arrendadorId = obtenerArrendadorId();

  if (!inputId || !textarea || !inputId.value) {
    return;
  }

  var texto = textarea.value.trim();
  if (!texto) {
    return;
  }

  var ruta = '/arrendador/mensajes/' + inputId.value + '/enviar?arrendador_id=' + encodeURIComponent(arrendadorId);

  fetch(ruta, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': obtenerTokenCsrf(),
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    },
    credentials: 'same-origin',
    body: JSON.stringify({ texto: texto })
  })
    .then(function (respuesta) {
      return respuesta.json().then(function (datosRespuesta) {
        return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datosRespuesta.success) {
        throw new Error(resultado.datosRespuesta.message || 'No se pudo enviar el mensaje.');
      }

      textarea.value = '';
      cargarConversacion(inputId.value);
    })
    .catch(function () {
      alert('No se pudo enviar el mensaje.');
    });
}

document.querySelectorAll('[data-conversacion-id]').forEach(function (boton) {
  boton.addEventListener('click', function () {
    cargarConversacion(boton.getAttribute('data-conversacion-id'));
  });
});

var formularioMensaje = document.getElementById('formularioMensaje');
if (formularioMensaje) {
  formularioMensaje.addEventListener('submit', enviarMensajeConFetch);
}

var primeraConversacion = document.querySelector('[data-conversacion-id]');
if (primeraConversacion) {
  cargarConversacion(primeraConversacion.getAttribute('data-conversacion-id'));
}
