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
      contenedorPrevia.hidden = false;

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
      contenedorPrevia.hidden = true;
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

  modal.hidden = false;
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
    modal.hidden = true;
    modal.style.display = '';
  }
}

function abrirModalFormulario(arrendadorId, datosPropiedad) {
  var modal = document.getElementById('modal-formulario');
  
  if (!modal) {
    return;
  }

  // Limpiar el formulario
  var formulario = modal.querySelector('form');
  if (formulario) {
    formulario.reset();
    document.getElementById('form-id-propiedad').value = '';
    document.getElementById('form-titulo').value = '';
    document.getElementById('form-tipo').value = '';
    document.getElementById('form-estado').value = 'borrador';
    document.getElementById('form-calle').value = '';
    document.getElementById('form-numero').value = '';
    document.getElementById('form-piso').value = '';
    document.getElementById('form-puerta').value = '';
    document.getElementById('form-codigo-postal').value = '';
    document.getElementById('form-ciudad').value = '';
    document.getElementById('form-habitaciones').value = '';
    document.getElementById('form-banos').value = '';
    document.getElementById('form-metros').value = '';
    document.getElementById('form-ascensor').checked = false;
    document.getElementById('form-amueblado').checked = false;
    document.getElementById('form-precio').value = '';
    document.getElementById('form-descripcion').value = '';
    document.getElementById('btn-submit-formulario').textContent = 'Crear propiedad';
    
    // Limpiar archivos acumulados
    if (formulario._archivosAcumulados) {
      formulario._archivosAcumulados = [];
    }
    
    // Ocultar vista previa de imágenes
    var contenedorPrevia = document.getElementById('contenedor-previa-imagenes');
    if (contenedorPrevia) {
      contenedorPrevia.hidden = true;
    }

    if (datosPropiedad) {
      document.getElementById('modal-formulario-titulo').textContent = 'Editar propiedad';
      document.getElementById('btn-submit-formulario').textContent = 'Guardar cambios';
      document.getElementById('form-id-propiedad').value = datosPropiedad.id_propiedad || '';
      document.getElementById('form-titulo').value = datosPropiedad.titulo_propiedad || '';
      document.getElementById('form-tipo').value = datosPropiedad.tipo_propiedad || '';
      document.getElementById('form-estado').value = datosPropiedad.estado_propiedad || 'borrador';
      document.getElementById('form-calle').value = datosPropiedad.calle_propiedad || '';
      document.getElementById('form-numero').value = datosPropiedad.numero_propiedad || '';
      document.getElementById('form-piso').value = datosPropiedad.piso_propiedad || '';
      document.getElementById('form-puerta').value = datosPropiedad.puerta_propiedad || '';
      document.getElementById('form-codigo-postal').value = datosPropiedad.codigo_postal_propiedad || '';
      document.getElementById('form-ciudad').value = datosPropiedad.ciudad_propiedad || '';
      document.getElementById('form-habitaciones').value = datosPropiedad.habitaciones_propiedad || '';
      document.getElementById('form-banos').value = datosPropiedad.banos_propiedad || '';
      document.getElementById('form-metros').value = datosPropiedad.metros_cuadrados_propiedad || '';
      document.getElementById('form-ascensor').checked = Boolean(Number(datosPropiedad.ascensor_propiedad));
      document.getElementById('form-amueblado').checked = Boolean(Number(datosPropiedad.amueblado_propiedad));
      document.getElementById('form-precio').value = datosPropiedad.precio_propiedad || '';
      document.getElementById('form-descripcion').value = datosPropiedad.descripcion_propiedad || '';
    }
  }

  if (!datosPropiedad) {
    document.getElementById('modal-formulario-titulo').textContent = 'Nueva propiedad';
  }
  modal.hidden = false;
}

function cerrarModalFormulario() {
  var modal = document.getElementById('modal-formulario');

  if (modal) {
    modal.hidden = true;
  }
}


// MODAL GESTOR
function actualizarResumenPropiedadEnModal(propiedadId) {
  var tituloElemento = document.getElementById('modal_propiedad_titulo');
  var direccionElemento = document.getElementById('modal_propiedad_direccion');
  var precioElemento = document.getElementById('modal_propiedad_precio');
  var estadoElemento = document.getElementById('modal_propiedad_estado');

  if (!tituloElemento || !direccionElemento || !precioElemento || !estadoElemento) {
    return;
  }

  tituloElemento.textContent = 'Propiedad #' + propiedadId;
  direccionElemento.textContent = 'Datos no disponibles';
  precioElemento.textContent = 'Precio no disponible';
  estadoElemento.textContent = 'Estado no disponible';

  var botonOrigen = document.querySelector('.btn-gear[data-propiedad-id="' + propiedadId + '"]');
  if (!botonOrigen) {
    return;
  }

  var fila = botonOrigen.closest('tr');
  if (!fila) {
    return;
  }

  var titulo = fila.querySelector('.property-title');
  var metas = fila.querySelectorAll('.property-meta');
  var insigniaEstado = fila.querySelector('td:nth-child(2) .badge');

  if (titulo && titulo.textContent.trim() !== '') {
    tituloElemento.textContent = titulo.textContent.trim();
  }

  if (metas.length > 0 && metas[0].textContent.trim() !== '') {
    direccionElemento.textContent = metas[0].textContent.trim();
  }

  if (metas.length > 1 && metas[1].textContent.trim() !== '') {
    precioElemento.textContent = metas[1].textContent.trim();
  }

  if (insigniaEstado && insigniaEstado.textContent.trim() !== '') {
    estadoElemento.textContent = 'Estado: ' + insigniaEstado.textContent.trim();
  }
}

function abrirModalGestor(propiedadId) {
  const modal = document.getElementById('modal-gestor-config');
  const ruta = rutaPermisosGestor.replace(':propiedad', propiedadId);
  actualizarResumenPropiedadEnModal(propiedadId);
  //FETCH para obtener datos del gestor y los permisos
  fetch(ruta)
    .then(response => response.json())
    .then(data => {
      //DATOS DE EJEMPLO QUE DEVUELVE: 
      // {id_gestor: 51, nombre_gestor: 'Carlos Garcia', email_gestor: 'carlos@spotstay.com', incidencias: 1, gastos: 1, …}
      
      //Gestor
      var nombreGestor = data.nombre_gestor || 'Sin gestor asignado';
      var emailGestor = data.email_gestor || 'Sin email disponible';
      var tieneGestor = Boolean(data.id_gestor);
      var inicialGestor = nombreGestor && nombreGestor.length > 0 ? nombreGestor.trim().charAt(0).toUpperCase() : '-';

      document.getElementById('nombre_gestor').textContent = nombreGestor;
      document.getElementById('email_gestor').textContent = emailGestor;
      document.getElementById('nombre_gestor_subtitulo').textContent = tieneGestor ? nombreGestor : 'el gestor';
      document.getElementById('gestor_avatar_inicial').textContent = inicialGestor;
      //Permisos
      document.getElementById('permiso-chat').checked = Boolean(data.chat);
      document.getElementById('permiso-editar').checked = Boolean(data.editar_propiedad);
      document.getElementById('permiso-gastos').checked = Boolean(data.gastos);
      document.getElementById('permiso-incidencias').checked = Boolean(data.incidencias);

      document.getElementById('permiso-chat').disabled = !tieneGestor;
      document.getElementById('permiso-editar').disabled = !tieneGestor;
      document.getElementById('permiso-gastos').disabled = !tieneGestor;
      document.getElementById('permiso-incidencias').disabled = !tieneGestor;

      var btnVerPerfil = document.getElementById('btnVerPerfilGestor');
      if (btnVerPerfil) {
        btnVerPerfil.disabled = !tieneGestor;
      }

      //Guardar IDs para el submit
      var btnGuardar = document.getElementById('btnGuardarPermisosGestor');
      if (btnGuardar) {
        btnGuardar.dataset.propiedadId = propiedadId;
        btnGuardar.dataset.gestorId = data.id_gestor || '';
        btnGuardar.disabled = !tieneGestor;
      }

    })
    .catch(() => {
      mostrarMensaje('No se pudieron cargar los permisos del gestor.', true);
    });

  modal.hidden = false;
}

document.addEventListener('click', function (evento) {
  var objetivo = evento.target;
  if (objetivo && objetivo.id === 'btnGuardarPermisosGestor') {
    evento.preventDefault();
    guardarPermisos();
  }
});

//Guardar permisos del gestor
function guardarPermisos() {
    var btn = document.getElementById('btnGuardarPermisosGestor');
    if (!btn) {
      if (window.swalError) {
        swalError('Error', 'No se encontró el botón de guardado de permisos.');
      } else {
        mostrarMensaje('No se encontró el botón de guardado de permisos.', true);
      }
      return;
    }
    var propiedadId = btn.dataset.propiedadId;
    var gestorId = btn.dataset.gestorId;

    if (!propiedadId) {
      if (window.swalError) {
        swalError('Error', 'No se encontró la propiedad a actualizar.');
      } else {
        mostrarMensaje('No se encontró la propiedad a actualizar.', true);
      }
      return;
    }

    var permisos = {
      chat: document.getElementById('permiso-chat').checked,
      editar_propiedad: document.getElementById('permiso-editar').checked,
      gastos: document.getElementById('permiso-gastos').checked,
      incidencias: document.getElementById('permiso-incidencias').checked
    };
    // Incluir gestorId en la carga útil para que el controlador lo reciba
    permisos.gestor_id = gestorId;

    fetch('/arrendador/propiedades/' + propiedadId + '/gestor/permisos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': obtenerTokenCsrf(),
        'Accept': 'application/json'
      },
      body: JSON.stringify(permisos),
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        cerrarModalGestor();
        if (window.swalSuccess) {
          swalSuccess('Permisos actualizados', data.message || 'Los permisos del gestor se han guardado correctamente.');
        } else {
          mostrarMensaje(data.message || 'Permisos actualizados correctamente.', false);
        }
      } else {
        throw new Error(data.message || 'Error al actualizar permisos.');
      }
    })
    .catch(error => {
      console.error(error);
      if (window.swalError) {
        swalError('No se pudo guardar', error.message || 'Error al actualizar permisos.');
      } else {
        mostrarMensaje(error.message || 'Error al actualizar permisos.', true);
      }
    });
};


function cerrarModalGestor() {
  var modal = document.getElementById('modal-gestor-config');
  if (modal) modal.hidden = true;
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
    cerrarModalFormulario();
  }
};
