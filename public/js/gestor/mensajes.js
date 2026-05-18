function obtenerGestorId() {
  var contenedor = document.querySelector('[data-gestor-id]');
  return contenedor ? contenedor.getAttribute('data-gestor-id') : '';
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
  if (!valor) return '';
  var fecha = new Date(valor);
  if (isNaN(fecha.getTime())) return valor;
  return fecha.toLocaleString('es-ES');
}

function escaparHtml(valor) {
  return String(valor)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function renderizarMensajes(mensajes) {
  var lista = document.getElementById('listaMensajes');
  if (!lista) return;

  if (!mensajes || mensajes.length === 0) {
    lista.innerHTML = '<p class="muted">Esta conversación todavía no tiene mensajes.</p>';
    return;
  }

  var contenidoHtml = '';
  mensajes.forEach(function (mensaje) {
    var clase = mensaje.es_mio ? 'burbuja gestor' : 'burbuja';
    var nombre = escaparHtml(mensaje.nombre_remitente || 'Usuario');
    var cuerpo = escaparHtml(mensaje.cuerpo_mensaje || '');
    var fecha = formatearFecha(mensaje.creado_mensaje);
    contenidoHtml += '<div class="' + clase + '">';
    contenidoHtml += '<strong>' + nombre + '</strong>';
    contenidoHtml += '<div>' + cuerpo + '</div>';
    contenidoHtml += '<small>' + fecha + '</small>';
    contenidoHtml += '</div>';
  });

  lista.innerHTML = contenidoHtml;
  lista.scrollTop = lista.scrollHeight;
}

function cargarConversacion(idConversacion, propiedadTitulo) {
  var gestorId = obtenerGestorId();
  var ruta = '/gestor/mensajes/' + idConversacion;

  fetch(ruta, {
    method: 'GET',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    credentials: 'same-origin'
  })
    .then(function (respuesta) {
      return respuesta.text().then(function (texto) {
        var datos = null;
        try { datos = JSON.parse(texto); } catch (e) {}
        return { ok: respuesta.ok, status: respuesta.status, datosRespuesta: datos, rawText: texto };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datosRespuesta || !resultado.datosRespuesta.success) {
        var mensaje = (resultado.datosRespuesta && resultado.datosRespuesta.message)
          ? resultado.datosRespuesta.message : 'No se pudo cargar la conversación.';
        throw new Error(mensaje);
      }

      var conversacion = resultado.datosRespuesta.conversacion;
      var titulo = document.getElementById('tituloHilo');
      var subtitulo = document.getElementById('subtituloHilo');
      var inputId = document.getElementById('idConversacionSeleccionada');
      var formulario = document.getElementById('formularioMensaje');

      if (titulo) {
        var nombre = conversacion.otro ? escaparHtml(conversacion.otro.nombre_usuario) : 'Conversación';
        var rol = conversacion.otro && conversacion.otro.rol ? conversacion.otro.rol : '';
        titulo.innerHTML = nombre + (rol ? ' <span class="rol-badge">' + rol + '</span>' : '');
      }
      if (subtitulo) {
        var email = conversacion.otro && conversacion.otro.email_usuario ? escaparHtml(conversacion.otro.email_usuario) : '';
        subtitulo.innerHTML = (propiedadTitulo ? escaparHtml(propiedadTitulo) : '') + (propiedadTitulo && email ? ' · ' : '') + email;
      }
      if (inputId) {
        inputId.value = conversacion.id_conversacion;
      }
      if (formulario) {
        formulario.hidden = false;
      }

      renderizarMensajes(conversacion.mensajes);
      marcarConversacionActiva(conversacion.id_conversacion);
    })
    .catch(function (err) {
      console.error('Error cargarConversacion:', err);
    });
}

function enviarMensajeConFetch(evento) {
  evento.preventDefault();

  var inputId = document.getElementById('idConversacionSeleccionada');
  var textarea = document.getElementById('textoMensaje');

  if (!inputId || !textarea || !inputId.value) return;

  var texto = textarea.value.trim();
  if (!texto) return;

  var ruta = '/gestor/mensajes/' + inputId.value;

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
      return respuesta.text().then(function (texto) {
        var datos = null;
        try { datos = JSON.parse(texto); } catch (e) {}
        return { ok: respuesta.ok, status: respuesta.status, datosRespuesta: datos, rawText: texto };
      });
    })
    .then(function (resultado) {
      if (!resultado.ok || !resultado.datosRespuesta || !resultado.datosRespuesta.success) {
        var mensaje = (resultado.datosRespuesta && resultado.datosRespuesta.message)
          ? resultado.datosRespuesta.message : 'No se pudo enviar el mensaje.';
        throw new Error(mensaje);
      }

      textarea.value = '';
      var item = document.querySelector('[data-conversacion-id="' + inputId.value + '"]');
      var propTitulo = item ? item.getAttribute('data-propiedad-titulo') : '';
      cargarConversacion(inputId.value, propTitulo);

      var item = document.querySelector('[data-conversacion-id="' + inputId.value + '"]');
      if (item) {
        var preview = item.querySelector('.conv-preview');
        var tiempo = item.querySelector('.conv-tiempo');
        if (preview) preview.textContent = texto;
        if (tiempo) tiempo.textContent = 'ahora mismo';
        var lista = document.getElementById('listaConversaciones');
        if (lista && item.parentNode === lista) {
          lista.insertBefore(item, lista.firstChild);
        }
      }
    })
    .catch(function (err) {
      console.error('Error enviarMensajeConFetch:', err);
    });
}

document.querySelectorAll('[data-conversacion-id]').forEach(function (boton) {
  boton.addEventListener('click', function () {
    var propiedadTitulo = boton.getAttribute('data-propiedad-titulo');
    cargarConversacion(boton.getAttribute('data-conversacion-id'), propiedadTitulo);
  });
});

var formularioMensaje = document.getElementById('formularioMensaje');
var textoMensaje = document.getElementById('textoMensaje');
if (formularioMensaje) {
  formularioMensaje.addEventListener('submit', enviarMensajeConFetch);
}
if (textoMensaje) {
  textoMensaje.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      formularioMensaje.requestSubmit();
    }
  });
}

var filtro = document.getElementById('filtroConversaciones');
if (filtro) {
  filtro.addEventListener('input', function () {
    var q = this.value.toLowerCase().trim();
    document.querySelectorAll('.item-conversacion').forEach(function (item) {
      var nombre = item.querySelector('.conv-nombre').textContent.toLowerCase();
      item.style.display = (!q || nombre.includes(q)) ? '' : 'none';
    });
  });
}
