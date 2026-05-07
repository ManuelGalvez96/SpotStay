function obtenerTokenCsrf() {
  var etiquetaCsrf = document.querySelector('meta[name="csrf-token"]');
  return etiquetaCsrf ? etiquetaCsrf.getAttribute('content') : '';
}

function mostrarMensaje(texto, esError) {
  var mensajeActual = document.querySelector('.fetch-toast');
  if (mensajeActual) {
    mensajeActual.remove();
  }

  var aviso = document.createElement('div');
  aviso.className = 'fetch-toast ' + (esError ? 'fetch-toast-error' : 'fetch-toast-success');
  aviso.textContent = texto;
  document.body.appendChild(aviso);

  setTimeout(function () {
    aviso.classList.add('is-visible');
  }, 10);

  setTimeout(function () {
    aviso.classList.remove('is-visible');
    setTimeout(function () {
      if (aviso.parentNode) {
        aviso.parentNode.removeChild(aviso);
      }
    }, 220);
  }, 2200);
}

function extraerMensajeError(datosRespuesta) {
  if (!datosRespuesta) {
    return 'Error al procesar la solicitud.';
  }

  if (datosRespuesta.message) {
    return datosRespuesta.message;
  }

  if (datosRespuesta.errors) {
    var campos = Object.keys(datosRespuesta.errors);
    if (campos.length > 0 && datosRespuesta.errors[campos[0]] && datosRespuesta.errors[campos[0]].length > 0) {
      return datosRespuesta.errors[campos[0]][0];
    }
  }

  return 'Error al procesar la solicitud.';
}

function actualizarEstadoEnTarjeta(formulario, nuevoEstado) {
  var tarjeta = formulario.closest('.property-card');
  if (!tarjeta) {
    return;
  }

  var insignia = tarjeta.querySelector('.badge');
  if (insignia) {
    insignia.className = 'badge badge-' + nuevoEstado;
    insignia.textContent = nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1);
  }

  var boton = formulario.querySelector('[data-state-button="true"]');
  if (boton) {
    boton.textContent = nuevoEstado === 'publicada' ? 'Inactivar' : 'Publicar';
  }
}

function enviarFormularioConFetch(formulario) {
  formulario.onsubmit = function (evento) {
    evento.preventDefault();

    var botonEnviar = formulario.querySelector('button[type="submit"]');
    var textoOriginal = botonEnviar ? botonEnviar.textContent : '';

    if (botonEnviar) {
      botonEnviar.disabled = true;
      botonEnviar.textContent = 'Guardando...';
    }

    var datosFormulario = new FormData(formulario);

    // Caso especial: formulario de propiedades con archivos acumulados
    if (formulario._archivosAcumulados && formulario._archivosAcumulados.length > 0) {
      formulario._archivosAcumulados.forEach(function(obj) {
        datosFormulario.append('imagenes_propiedad[]', obj.archivo);
      });
    }

    fetch(formulario.action, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': obtenerTokenCsrf(),
        'Accept': 'application/json'
      },
      body: datosFormulario,
      credentials: 'same-origin'
    })
      .then(function (respuesta) {
        return respuesta.json().catch(function () {
          return {};
        }).then(function (datosRespuesta) {
          return { ok: respuesta.ok, datosRespuesta: datosRespuesta };
        });
      })
      .then(function (resultado) {
        if (!resultado.ok || !resultado.datosRespuesta.success) {
          throw new Error(extraerMensajeError(resultado.datosRespuesta));
        }
 
        mostrarMensaje(resultado.datosRespuesta.message || 'Cambio aplicado correctamente.', false);

        if (formulario.dataset.ajaxStateForm === 'true' && resultado.datosRespuesta.estado) {
          actualizarEstadoEnTarjeta(formulario, resultado.datosRespuesta.estado);
        }

        window.location.reload();
      })
      .catch(function (error) {
        mostrarMensaje(error.message || 'Error al procesar la solicitud.', true);
      })
      .finally(function () {
        if (botonEnviar) {
          botonEnviar.disabled = false;
          botonEnviar.textContent = textoOriginal;
        }
      });
  };
}

function iniciarValidacionImagenes() {
  var inputImagenes = document.getElementById('imagenes-propiedad');
  var formulario = document.querySelector('form[data-ajax-form="true"]');
  var contenedorPrevia = document.getElementById('contenedor-previa-imagenes');
  var listaPrevia = document.getElementById('lista-previa-imagenes');

  if (!inputImagenes || !formulario || !contenedorPrevia || !listaPrevia) {
    return;
  }

  var botonEnviar = formulario.querySelector('button[type="submit"]');
  var archivosAcumulados = [];
  formulario._archivosAcumulados = archivosAcumulados;

  function actualizarPrevia() {
    listaPrevia.innerHTML = '';
    
    if (archivosAcumulados.length > 0) {
      contenedorPrevia.style.display = 'block';

      archivosAcumulados.forEach(function (archivoObj, indice) {
        var lector = new FileReader();

        lector.onload = function (evento) {
          var divFoto = document.createElement('div');
          divFoto.style.cssText = 'border: 2px solid #ccc; border-radius: 8px; padding: 8px; position: relative; cursor: pointer; transition: border-color 0.2s;';
          divFoto.dataset.indice = indice;

          var img = document.createElement('img');
          img.src = evento.target.result;
          img.style.cssText = 'width: 100%; height: 100px; object-fit: cover; border-radius: 4px; display: block;';

          var checkboxDiv = document.createElement('div');
          checkboxDiv.style.cssText = 'margin-top: 6px; display: flex; align-items: center; justify-content: space-between;';

          var checkboxLabel = document.createElement('div');
          checkboxLabel.style.cssText = 'display: flex; align-items: center; gap: 6px;';

          var checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.dataset.indice = indice;
          checkbox.style.cssText = 'cursor: pointer;';

          var label = document.createElement('label');
          label.textContent = 'Ancla';
          label.style.cssText = 'cursor: pointer; font-size: 12px; margin: 0;';

          checkbox.onchange = function () {
            if (checkbox.checked) {
              var todosChequeados = document.querySelectorAll('input[type="checkbox"][data-indice]');
              todosChequeados.forEach(function (ch) {
                if (ch.dataset.indice !== checkbox.dataset.indice) {
                  ch.checked = false;
                  var divPadre = ch.closest('div[data-indice]');
                  if (divPadre) {
                    divPadre.style.borderColor = '#ccc';
                    divPadre.style.backgroundColor = 'transparent';
                  }
                }
              });
              divFoto.style.borderColor = '#4CAF50';
              divFoto.style.backgroundColor = '#f1f8f4';
              document.getElementById('imagen-principal-indice').value = indice;
            } else {
              divFoto.style.borderColor = '#ccc';
              divFoto.style.backgroundColor = 'transparent';
              document.getElementById('imagen-principal-indice').value = '-1';
            }
          };

          checkboxLabel.appendChild(checkbox);
          checkboxLabel.appendChild(label);

          var botonEliminar = document.createElement('button');
          botonEliminar.type = 'button';
          botonEliminar.textContent = '✕';
          botonEliminar.style.cssText = 'background: #ff6b6b; color: white; border: none; border-radius: 4px; padding: 2px 6px; cursor: pointer; font-size: 12px; font-weight: bold;';
          
          botonEliminar.onclick = function (e) {
            e.preventDefault();
            archivosAcumulados.splice(indice, 1);
            actualizarPrevia();
          };

          checkboxDiv.appendChild(checkboxLabel);
          checkboxDiv.appendChild(botonEliminar);
          divFoto.appendChild(img);
          divFoto.appendChild(checkboxDiv);

          divFoto.onmouseover = function () {
            if (!checkbox.checked) {
              divFoto.style.borderColor = '#999';
            }
          };
          divFoto.onmouseout = function () {
            if (!checkbox.checked) {
              divFoto.style.borderColor = '#ccc';
            }
          };

          listaPrevia.appendChild(divFoto);
        };

        lector.readAsDataURL(archivoObj.archivo);
      });
    } else {
      contenedorPrevia.style.display = 'none';
    }
  }

  inputImagenes.onchange = function () {
    var nuevosArchivos = inputImagenes.files ? Array.from(inputImagenes.files) : [];
    
    if ((archivosAcumulados.length + nuevosArchivos.length) > 10) {
      mostrarMensaje('No puedes superar 10 imágenes en total.', true);
      inputImagenes.value = '';
      return;
    }

    nuevosArchivos.forEach(function (archivo) {
      archivosAcumulados.push({
        archivo: archivo,
        nombre: archivo.name
      });
    });

    inputImagenes.value = '';
    actualizarPrevia();

    if (botonEnviar) {
      botonEnviar.disabled = false;
    }
  };
}

function abrirModalPropiedad(propiedadId, arrendadorId) {
  var modal = document.getElementById('modal-propiedad');
  var modalBody = document.getElementById('modal-body');

  if (!modal || !modalBody) {
    return;
  }

  modal.style.display = 'flex';
  modalBody.innerHTML = '<div class="spinner">Cargando...</div>';

  fetch('/arrendador/propiedades/' + propiedadId + '?arrendador_id=' + arrendadorId)
    .then(function (respuesta) {
      return respuesta.json();
    })
    .then(function (datos) {
      if (datos.propiedad) {
        modalBody.innerHTML = construirContenidoModal(datos);
      } else {
        modalBody.innerHTML = '<p class="error">No se pudo cargar la propiedad.</p>';
      }
    })
    .catch(function (error) {
      console.error(error);
      modalBody.innerHTML = '<p class="error">Error al cargar los datos.</p>';
    });
}

function cerrarModalPropiedad() {
  var modal = document.getElementById('modal-propiedad');

  if (modal) {
    modal.style.display = 'none';
  }
}

function construirContenidoModal(datos) {
  var propiedad = datos.propiedad;
  var alquiler = datos.alquiler_activo;
  var fotos = datos.fotos || [];
  var htmlFotos = '';
  var htmlMiniaturas = '';
  var htmlAlquiler = '';
  var claseEstado = 'badge-' + propiedad.estado_propiedad;
  var textoEstado = propiedad.estado_propiedad.charAt(0).toUpperCase() + propiedad.estado_propiedad.slice(1);

  if (fotos.length > 0) {
    htmlFotos = '<img src="/storage/' + fotos[0].ruta_foto + '" alt="' + propiedad.titulo_propiedad + '" style="width: 100%; height: 300px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;" />';
  } else {
    htmlFotos = '<div style="width: 100%; height: 300px; background: #e0e0e0; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; color: #999;">Sin imágenes</div>';
  }

  if (fotos.length > 1) {
    htmlMiniaturas = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 8px; margin-bottom: 20px;">';
    fotos.forEach(function (foto) {
      htmlMiniaturas += '<img src="/storage/' + foto.ruta_foto + '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; cursor: pointer;" onclick="cambiarFotoModal(this.src)" />';
    });
    htmlMiniaturas += '</div>';
  }

  if (alquiler) {
    htmlAlquiler = '<div style="background: #f0f7ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0066cc; margin-top: 20px;">' +
      '<h3 style="margin-bottom: 10px; color: #0066cc; font-size: 14px; text-transform: uppercase;">Alquiler Activo</h3>' +
      '<p><strong>Inquilino:</strong> ' + alquiler.nombre_inquilino + '</p>' +
      '<p><strong>Email:</strong> ' + alquiler.email_inquilino + '</p>' +
      '<p><strong>Inicio:</strong> ' + new Date(alquiler.fecha_inicio_alquiler).toLocaleDateString('es-ES') + '</p>' +
      '<p><strong>Precio:</strong> ' + (alquiler.precio_alquiler ? (parseFloat(alquiler.precio_alquiler).toFixed(2) + ' €') : 'N/A') + '</p>' +
    '</div>';
  }

  return '' +
    htmlFotos +
    htmlMiniaturas +
    '<h3 style="margin-bottom: 10px;">' + propiedad.titulo_propiedad + '</h3>' +
    '<p style="color: #666; margin-bottom: 15px; font-size: 14px;">' + propiedad.direccion_propiedad + ', ' + propiedad.ciudad_propiedad + ' · ' + propiedad.codigo_postal_propiedad + '</p>' +
    '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">' +
      '<div>' +
        '<p style="color: #999; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Estado</p>' +
        '<span class="badge ' + claseEstado + '">' + textoEstado + '</span>' +
      '</div>' +
      '<div>' +
        '<p style="color: #999; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Precio</p>' +
        '<p style="font-weight: 500; font-size: 16px;">' + parseFloat(propiedad.precio_propiedad).toFixed(2).replace('.', ',') + ' €/mes</p>' +
      '</div>' +
    '</div>' +
    (propiedad.descripcion_propiedad ? (
      '<div style="margin-bottom: 20px;">' +
        '<p style="color: #999; font-size: 12px; text-transform: uppercase; margin-bottom: 8px;">Descripción</p>' +
        '<p style="font-size: 14px; line-height: 1.6; color: #555;">' + propiedad.descripcion_propiedad + '</p>' +
      '</div>'
    ) : '') +
    htmlAlquiler;
}

function cambiarFotoModal(src) {
  var modal = document.getElementById('modal-body');
  var fotos = modal ? modal.querySelectorAll('img') : [];

  fotos.forEach(function (foto) {
    if (foto.style.width === '60px') {
      foto.style.border = foto.src === src ? '2px solid #0066cc' : 'none';
    }
  });

  var fotoPrincipal = modal ? modal.querySelector('img[style*="height: 300px"]') : null;
  if (fotoPrincipal) {
    fotoPrincipal.src = src;
  }
}


document.querySelectorAll('form[data-ajax-form="true"]').forEach(enviarFormularioConFetch);
document.querySelectorAll('form[data-ajax-state-form="true"]').forEach(enviarFormularioConFetch);
iniciarValidacionImagenes();

document.onkeydown = function (evento) {
  if (evento.key === 'Escape') {
    cerrarModalPropiedad();
  }
};
