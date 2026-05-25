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

function iniciarValidacionFormularioPropiedad() {
  var formulario = document.querySelector('form[data-ajax-form="true"]');

  if (!formulario) {
    return;
  }

  var botonEnviar = formulario.querySelector('button[type="submit"]');
  var camposObligatorios = [
    document.getElementById('form-titulo'),
    document.getElementById('form-tipo'),
    document.getElementById('form-estado'),
    document.getElementById('form-calle'),
    document.getElementById('form-numero'),
    document.getElementById('form-codigo-postal'),
    document.getElementById('form-ciudad'),
    document.getElementById('form-precio')
  ].filter(function (campo) {
    return Boolean(campo);
  });

  formulario.noValidate = true;

  function obtenerContenedorError(campo) {
    if (!campo) {
      return null;
    }

    var contenedor = campo.nextElementSibling;
    if (contenedor && contenedor.classList && contenedor.classList.contains('campo-error')) {
      return contenedor;
    }

    contenedor = document.createElement('div');
    contenedor.className = 'campo-error';
    contenedor.setAttribute('aria-live', 'polite');
    contenedor.hidden = true;
    campo.insertAdjacentElement('afterend', contenedor);
    return contenedor;
  }

  function esCampoValido(campo) {
    if (!campo) {
      return true;
    }

    var valor = typeof campo.value === 'string' ? campo.value.trim() : '';

    return !(campo.required && valor === '');
  }

  function pintarErrorCampo(campo) {
    if (!campo) {
      return true;
    }

    var mensajeError = esCampoValido(campo) ? '' : 'Este campo es obligatorio.';

    var contenedorError = obtenerContenedorError(campo);

    if (contenedorError) {
      contenedorError.textContent = mensajeError;
      contenedorError.hidden = mensajeError === '';
    }

    campo.setAttribute('aria-invalid', mensajeError ? 'true' : 'false');
    campo.classList.toggle('campo-invalido', mensajeError !== '');

    return mensajeError === '';
  }

  function actualizarEstadoBoton() {
    var formularioValido = true;

    camposObligatorios.forEach(function (campo) {
      if (!esCampoValido(campo)) {
        formularioValido = false;
      }
    });

    if (botonEnviar) {
      botonEnviar.disabled = !formularioValido;
    }

    return formularioValido;
  }

  camposObligatorios.forEach(function (campo) {
    campo.oninput = function () {
      if (campo.dataset.tocado === 'true') {
        pintarErrorCampo(campo);
      }

      actualizarEstadoBoton();
    };

    campo.onchange = function () {
      if (campo.dataset.tocado === 'true') {
        pintarErrorCampo(campo);
      }

      actualizarEstadoBoton();
    };

    campo.onblur = function () {
      campo.dataset.tocado = 'true';
      pintarErrorCampo(campo);
      actualizarEstadoBoton();
    };
  });

  formulario.onsubmit = function (evento) {
    var formularioValido = true;

    camposObligatorios.forEach(function (campo) {
      campo.dataset.tocado = 'true';
      if (!pintarErrorCampo(campo)) {
        formularioValido = false;
      }
    });

    actualizarEstadoBoton();

    if (!formularioValido) {
      evento.preventDefault();

      mostrarMensaje('Completa los campos obligatorios antes de guardar la propiedad.', true);
      return false;
    }

    return true;
  };

  window.actualizarEstadoValidacionFormularioPropiedad = actualizarEstadoBoton;
}

function actualizarFilaPropiedad(datosPropiedad) {
  if (!datosPropiedad || !datosPropiedad.id_propiedad) {
    return;
  }

  var botonEditar = document.querySelector('[data-propiedad-id="' + datosPropiedad.id_propiedad + '"][onclick*="fetchEditData"]');
  if (!botonEditar) {
    return;
  }

  var fila = botonEditar.closest('tr');
  if (!fila) {
    return;
  }

  var titulo = fila.querySelector('.property-title');
  var metas = fila.querySelectorAll('.property-meta');
  var estadoPropiedad = fila.querySelector('td:nth-child(2) .badge');

  if (titulo) {
    titulo.textContent = datosPropiedad.titulo_propiedad || titulo.textContent;
  }

  if (metas.length > 0) {
    metas[0].textContent = (datosPropiedad.direccion_propiedad || '') + (datosPropiedad.ciudad_propiedad ? ', ' + datosPropiedad.ciudad_propiedad : '') + (datosPropiedad.codigo_postal_propiedad ? ' ' + datosPropiedad.codigo_postal_propiedad : '');
  }

  if (metas.length > 1 && datosPropiedad.precio_propiedad !== undefined && datosPropiedad.precio_propiedad !== null) {
    metas[1].textContent = parseFloat(datosPropiedad.precio_propiedad).toFixed(2).replace('.', ',') + ' €/mes';
  }

  if (estadoPropiedad && datosPropiedad.estado_propiedad) {
    estadoPropiedad.className = 'badge badge-' + datosPropiedad.estado_propiedad;
    estadoPropiedad.textContent = datosPropiedad.estado_propiedad.charAt(0).toUpperCase() + datosPropiedad.estado_propiedad.slice(1);
  }
}

function fetchEditData(propiedadId) {
  var ruta = rutaDatosEdicionPropiedad.replace(':id', propiedadId);

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
        throw new Error(extraerMensajeError(resultado.datos));
      }

      abrirModalFormulario(null, resultado.datos.propiedad);
    })
    .catch(function (error) {
      if (window.swalError) {
        swalError('No se pudo cargar la edición', error.message || 'Error al cargar la propiedad.');
      } else {
        mostrarMensaje(error.message || 'Error al cargar la propiedad.', true);
      }
    });
}

function enviarFormularioConFetch(formulario) {
  formulario.onsubmit = function (evento) {
    evento.preventDefault();

    if (!actualizarEstadoBoton()) {
      mostrarMensaje('Completa los campos obligatorios antes de guardar.', true);
      return;
    }

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

        var mensajeExito = resultado.datosRespuesta.message || 'Cambio aplicado correctamente.';
        var esEdicionPropiedad = Boolean(formulario.querySelector('#form-id-propiedad') && formulario.querySelector('#form-id-propiedad').value);

        if (esEdicionPropiedad && resultado.datosRespuesta.propiedad) {
          cargarTablaPropiedades();
        }

        cerrarModalFormulario();

        if (window.swalSuccess) {
          swalSuccess('Guardado correcto', mensajeExito).then(function () {
            if (!esEdicionPropiedad) {
              window.location.reload();
            }
          });
        } else {
          mostrarMensaje(mensajeExito, false);
          if (!esEdicionPropiedad) {
            window.location.reload();
          }
        }
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
    document.getElementById('form-piscina').checked = false;
    document.getElementById('form-terraza').checked = false;
    document.getElementById('form-garaje').checked = false;
    document.getElementById('form-aire-acondicionado').checked = false;
    document.getElementById('form-calefaccion').checked = false;
    document.getElementById('form-trastero').checked = false;
    document.getElementById('form-adicional').value = '';
    document.getElementById('form-precio').value = '';
    document.getElementById('form-descripcion').value = '';
    document.getElementById('btn-submit-formulario').textContent = 'Crear propiedad';
    formulario.querySelectorAll('.campo-error').forEach(function (error) {
      error.textContent = '';
      error.hidden = true;
    });
    formulario.querySelectorAll('.campo-invalido').forEach(function (campoInvalido) {
      campoInvalido.classList.remove('campo-invalido');
      campoInvalido.setAttribute('aria-invalid', 'false');
    });
    formulario.querySelectorAll('[data-tocado]').forEach(function (campoTocado) {
      campoTocado.removeAttribute('data-tocado');
    });
    
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
      document.getElementById('form-piscina').checked = Boolean(Number(datosPropiedad.piscina_propiedad));
      document.getElementById('form-terraza').checked = Boolean(Number(datosPropiedad.terraza_propiedad));
      document.getElementById('form-garaje').checked = Boolean(Number(datosPropiedad.garaje_propiedad));
      document.getElementById('form-aire-acondicionado').checked = Boolean(Number(datosPropiedad.aire_acondicionado_propiedad));
      document.getElementById('form-calefaccion').checked = Boolean(Number(datosPropiedad.calefaccion_propiedad));
      document.getElementById('form-trastero').checked = Boolean(Number(datosPropiedad.trastero_propiedad));
      document.getElementById('form-adicional').value = datosPropiedad.adicional_propiedad || '';
      document.getElementById('form-precio').value = datosPropiedad.precio_propiedad || '';
      document.getElementById('form-descripcion').value = datosPropiedad.descripcion_propiedad || '';
    }

    if (typeof window.actualizarEstadoValidacionFormularioPropiedad === 'function') {
      window.actualizarEstadoValidacionFormularioPropiedad();
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
        btnGuardar.dataset.codigoGestor = '';
        btnGuardar.disabled = !tieneGestor;
      }

      var btnDesasignar = document.getElementById('btnDesasignarGestor');
      if (btnDesasignar) {
        btnDesasignar.dataset.propiedadId = propiedadId;
        btnDesasignar.style.display = tieneGestor ? '' : 'none';
      }

      // Inicializar selector de gestores
      inicializarSelectorGestores(propiedadId, data.id_gestor);
      })
      .catch(() => {
      mostrarMensaje('No se pudieron cargar los permisos del gestor.', true);
      });

  modal.hidden = false;
}

// Inicializar selector de gestores
function inicializarSelectorGestores(propiedadId, gestorIdActual) {
  const selectorBox = document.querySelector('.gestor-selector-box');
  if (!selectorBox) return;

  // Remover dropdown anterior si existe
  const dropdownAnterior = document.getElementById('gestor-selector-dropdown');
  if (dropdownAnterior) {
    dropdownAnterior.remove();
  }

  // Agregar click handler al selector
  selectorBox.style.cursor = 'pointer';
  selectorBox.onclick = function(e) {
    e.stopPropagation();
    mostrarSelectorGestores(propiedadId);
  };
}

// Mostrar dropdown de gestores
function mostrarSelectorGestores(propiedadId) {
  // Remover dropdown anterior si existe
  const dropdownAnterior = document.getElementById('gestor-selector-dropdown');
  if (dropdownAnterior) {
    dropdownAnterior.remove();
    return; // Si ya estaba abierto, cerrarlo
  }

  // Obtener lista de gestores disponibles
  fetch('/arrendador/gestores/disponibles')
    .then(response => response.json())
    .then(gestores => {
      crearDropdownGestores(gestores, propiedadId);
    })
    .catch(error => {
      console.error('Error al cargar gestores:', error);
      mostrarMensaje('No se pudieron cargar los gestores disponibles.', true);
    });
}

// Crear dropdown con lista de gestores
function crearDropdownGestores(gestores, propiedadId) {
  const selectorBox = document.querySelector('.gestor-selector-box');
  if (!selectorBox) return;

  // Crear contenedor del dropdown
  const dropdown = document.createElement('div');
  dropdown.id = 'gestor-selector-dropdown';
  dropdown.className = 'gestor-dropdown';
  
  const contenido = document.createElement('div');
  contenido.className = 'gestor-dropdown-content';
  
  gestores.forEach(gestor => {
    const inicial = gestor.nombre_usuario && gestor.nombre_usuario.length > 0 
      ? gestor.nombre_usuario.trim().charAt(0).toUpperCase() 
      : '-';
    
    const item = document.createElement('div');
    item.className = 'gestor-dropdown-item';
    item.dataset.gestorId = gestor.id_usuario;
    item.dataset.gestorNombre = gestor.nombre_usuario;
    item.dataset.gestorEmail = gestor.email_usuario;
    item.dataset.propiedadId = propiedadId;
    
    item.innerHTML = `
      <div class="gestor-dropdown-avatar">${inicial}</div>
      <div class="gestor-dropdown-info">
        <div class="gestor-dropdown-nombre">${gestor.nombre_usuario}</div>
        <div class="gestor-dropdown-email">${gestor.email_usuario}</div>
      </div>
    `;
    
    item.addEventListener('click', function() {
      solicitarCodigoGestor(
        parseInt(this.dataset.gestorId),
        this.dataset.gestorNombre,
        this.dataset.gestorEmail,
        parseInt(this.dataset.propiedadId)
      );
    });
    
    contenido.appendChild(item);
  });
  
  dropdown.appendChild(contenido);
  
  // Insertar dropdown después del selector
  selectorBox.parentElement.insertBefore(dropdown, selectorBox.nextSibling);
  
  // Cerrar dropdown al hacer click fuera
  setTimeout(() => {
    document.addEventListener('click', cerrarDropdownGestores);
  }, 0);
}

// Cerrar dropdown de gestores
function cerrarDropdownGestores(evento) {
  if (evento && (evento.target.closest('.gestor-selector-box') || evento.target.closest('.gestor-dropdown'))) {
    return; // No cerrar si se hace click en el selector o dropdown
  }
  
  const dropdown = document.getElementById('gestor-selector-dropdown');
  if (dropdown) {
    dropdown.remove();
    document.removeEventListener('click', cerrarDropdownGestores);
  }
}

function solicitarCodigoGestor(gestorId, nombreGestor, emailGestor, propiedadId) {
  if (!window.Swal) {
    var codigoManual = prompt('Introduce el código del gestor para asignarlo:');
    if (codigoManual === null) {
      return;
    }

    codigoManual = codigoManual.trim();
    if (!codigoManual) {
      return;
    }

    seleccionarGestor(gestorId, nombreGestor, emailGestor, propiedadId, codigoManual);
    return;
  }

  Swal.fire({
    title: 'Código del gestor',
    text: 'Introduce el código para poder asignar este gestor a la propiedad.',
    input: 'password',
    inputPlaceholder: 'Código del gestor',
    showCancelButton: true,
    confirmButtonText: 'Validar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#035498',
    reverseButtons: true,
    inputValidator: function (valor) {
      if (!valor || !valor.trim()) {
        return 'Debes introducir el código del gestor.';
      }
      return null;
    }
  }).then(function (resultado) {
    if (!resultado.isConfirmed) {
      return;
    }

    seleccionarGestor(gestorId, nombreGestor, emailGestor, propiedadId, resultado.value.trim());
  });
}

// Seleccionar gestor y actualizar modal
function seleccionarGestor(gestorId, nombreGestor, emailGestor, propiedadId, codigoGestor) {
  // Actualizar display del selector
  const inicialGestor = nombreGestor && nombreGestor.length > 0 
    ? nombreGestor.trim().charAt(0).toUpperCase() 
    : '-';
  
  document.getElementById('nombre_gestor').textContent = nombreGestor;
  document.getElementById('email_gestor').textContent = emailGestor;
  document.getElementById('gestor_avatar_inicial').textContent = inicialGestor;
  
  // Actualizar ID del gestor en el botón de guardado
  const btnGuardar = document.getElementById('btnGuardarPermisosGestor');
  if (btnGuardar) {
    btnGuardar.dataset.gestorId = gestorId;
    btnGuardar.dataset.codigoGestor = codigoGestor || '';
  }
  
  // Habilitar checkboxes de permisos
  document.getElementById('permiso-chat').disabled = false;
  document.getElementById('permiso-editar').disabled = false;
  document.getElementById('permiso-gastos').disabled = false;
  document.getElementById('permiso-incidencias').disabled = false;
  
  var btnVerPerfil = document.getElementById('btnVerPerfilGestor');
  if (btnVerPerfil) {
    btnVerPerfil.disabled = false;
  }
  
  btnGuardar.disabled = false;
  
  // Cerrar dropdown
  cerrarDropdownGestores();
  
  mostrarMensaje('Gestor seleccionado: ' + nombreGestor + '. Pulsa guardar para confirmar la asignación.', false);
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
    var codigoGestor = btn.dataset.codigoGestor || '';

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
      incidencias: document.getElementById('permiso-incidencias').checked,
      codigo_gestor: codigoGestor
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
      btn.dataset.codigoGestor = '';
      cerrarModalGestor();
      cargarTablaPropiedades();
      if (window.swalSuccess) {
        swalSuccess('Permisos actualizados', data.message || 'Los permisos del gestor se han guardado correctamente.');
        } else {
          mostrarMensaje(data.message || 'Permisos actualizados correctamente.', false);
        }
    } else if (data.requiere_desasignar) {
      mostrarMensaje(data.message || 'El código del gestor no es correcto.', true);
      ejecutarDesasignar(propiedadId);
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

function desasignarGestorPropiedad() {
  var btn = document.getElementById('btnDesasignarGestor');
  if (!btn) return;
  var propiedadId = btn.dataset.propiedadId;
  if (!propiedadId) return;

  if (!window.Swal) {
    var confirmar = confirm('¿Estás seguro de que quieres desasignar el gestor de esta propiedad?');
    if (!confirmar) return;
    ejecutarDesasignar(propiedadId);
    return;
  }

  Swal.fire({
    title: '\u00BFDesasignar gestor?',
    text: '\u00BFEst\u00E1s seguro de que quieres desasignar el gestor de esta propiedad?',
    iconHtml: crearOsoError(),
    customClass: { icon: 'oso-icon' },
    showCancelButton: true,
    confirmButtonText: 'S\u00ED, desasignar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#d9534f',
    reverseButtons: true
  }).then(function (result) {
    if (result.isConfirmed) {
      ejecutarDesasignar(propiedadId);
    }
  });
}

function ejecutarDesasignar(propiedadId) {
  fetch('/arrendador/propiedades/' + propiedadId + '/gestor/desasignar', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': obtenerTokenCsrf(),
      'Accept': 'application/json'
    },
    credentials: 'same-origin'
  })
  .then(function (response) { return response.json(); })
  .then(function (data) {
    if (data.success) {
      cerrarModalGestor();
      cargarTablaPropiedades();
      if (window.swalSuccess) {
        swalSuccess('Gestor desasignado', data.message || 'Gestor desasignado correctamente.');
      } else {
        mostrarMensaje(data.message || 'Gestor desasignado correctamente.', false);
      }
    } else {
      throw new Error(data.message || 'Error al desasignar gestor.');
    }
  })
  .catch(function (error) {
    console.error(error);
    if (window.swalError) {
      swalError('Error', error.message || 'No se pudo desasignar el gestor.');
    } else {
      mostrarMensaje(error.message || 'No se pudo desasignar el gestor.', true);
    }
  });
}


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
    construirHtmlCaracteristicas(propiedad) +
    (propiedad.descripcion_propiedad ? (
      '<div style="margin-bottom: 20px;">' +
        '<p style="color: #999; font-size: 12px; text-transform: uppercase; margin-bottom: 8px;">Descripción</p>' +
        '<p style="font-size: 14px; line-height: 1.6; color: #555;">' + propiedad.descripcion_propiedad + '</p>' +
      '</div>'
    ) : '') +
    htmlAlquiler;
}

function construirHtmlCaracteristicas(p) {
  var etiquetas = [];

  if (p.habitaciones_propiedad) {
    etiquetas.push('<span style="background:#f0f0f0;padding:4px 10px;border-radius:999px;font-size:13px;"><strong>' + p.habitaciones_propiedad + '</strong> hab.</span>');
  }
  if (p.banos_propiedad) {
    etiquetas.push('<span style="background:#f0f0f0;padding:4px 10px;border-radius:999px;font-size:13px;"><strong>' + p.banos_propiedad + '</strong> ba&ntilde;os</span>');
  }
  if (p.metros_cuadrados_propiedad) {
    etiquetas.push('<span style="background:#f0f0f0;padding:4px 10px;border-radius:999px;font-size:13px;"><strong>' + p.metros_cuadrados_propiedad + '</strong> m&sup2;</span>');
  }

  var extras = [];
  if (Number(p.ascensor_propiedad)) extras.push('Ascensor');
  if (Number(p.amueblado_propiedad)) extras.push('Amueblado');
  if (Number(p.piscina_propiedad)) extras.push('Piscina');
  if (Number(p.terraza_propiedad)) extras.push('Terraza');
  if (Number(p.garaje_propiedad)) extras.push('Garaje');
  if (Number(p.aire_acondicionado_propiedad)) extras.push('Aire acondicionado');
  if (Number(p.calefaccion_propiedad)) extras.push('Calefacción');
  if (Number(p.trastero_propiedad)) extras.push('Trastero');

  extras.forEach(function(e) {
    etiquetas.push('<span style="background:#e8f4e8;padding:4px 10px;border-radius:999px;font-size:13px;">' + e + '</span>');
  });

  if (etiquetas.length === 0 && !p.adicional_propiedad) {
    return '';
  }

  var html = '<div style="margin-bottom:20px;">' +
    '<p style="color:#999;font-size:12px;text-transform:uppercase;margin-bottom:8px;">Características</p>' +
    '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;">' +
    etiquetas.join('') +
    '</div>';

  if (p.adicional_propiedad) {
    html += '<p style="font-size:14px;color:#555;margin-top:4px;"><em>' + p.adicional_propiedad + '</em></p>';
  }

  html += '</div>';
  return html;
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


var paginaActualPropiedades = 1;

function cargarTablaPropiedades(pagina) {
  pagina = pagina || paginaActualPropiedades;
  var contenedor = document.querySelector('.properties-table tbody');
  var paginationWrap = document.querySelector('.pagination-wrap');
  if (!contenedor) return;

  contenedor.innerHTML = '<tr><td colspan="6" class="text-center"><div class="spinner">Cargando...</div></td></tr>';

  fetch('/arrendador/propiedades/datos?page=' + pagina, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    credentials: 'same-origin'
  })
  .then(function (r) { return r.json(); })
  .then(function (res) {
    var datos = res.propiedades;
    var totales = res.totales;
    var arrendadorId = res.arrendadorId;
    var filas = '';

    if (datos.data && datos.data.length > 0) {
      datos.data.forEach(function (p) {
        var estadoPago = 'al-dia';
        var labelPago = 'Al d\u00EDa';
        var atrasadas = Number(p.cuotas_atrasadas || 0);
        var pendientes = Number(p.cuotas_pendientes || 0);
        if (atrasadas > 0) { estadoPago = 'atrasado'; labelPago = 'Atrasado'; }
        else if (pendientes > 0) { estadoPago = 'pendiente'; labelPago = 'Pendiente'; }

        var precio = parseFloat(p.precio_propiedad || 0).toFixed(2).replace('.', ',');
        var gestorNombre = p.nombre_gestor || 'Sin gestor asignado';
        var incidencias = p.total_incidencias || 'Sin incidencias';

        filas += '<tr>' +
          '<td><div class="property-info">' +
            '<p class="property-title">' + escaparHtml(p.titulo_propiedad || '') + '</p>' +
            '<p class="property-meta">' + escaparHtml(p.direccion_propiedad || '') + ', ' + escaparHtml(p.ciudad_propiedad || '') + ' ' + escaparHtml(p.codigo_postal_propiedad || '') + '</p>' +
            '<p class="property-meta">' + precio + ' \u20AC/mes</p>' +
          '</div></td>' +
          '<td><span class="badge badge-' + p.estado_propiedad + '">' + (p.estado_propiedad ? p.estado_propiedad.charAt(0).toUpperCase() + p.estado_propiedad.slice(1) : '') + '</span></td>' +
          '<td>' + incidencias + '</td>' +
          '<td><span class="badge badge-' + estadoPago + '">' + labelPago + '</span></td>' +
          '<td>' +
            '<span class="gestor-nombre">' + escaparHtml(gestorNombre) + '</span>' +
            '<button type="button" class="btn-gear" aria-label="Configurar gestor" data-propiedad-id="' + p.id_propiedad + '" onclick="abrirModalGestor(this.dataset.propiedadId)">' +
              '<i class="bi bi-gear"></i>' +
            '</button>' +
          '</td>' +
          '<td><div class="table-actions">' +
            '<button class="action-link" type="button" data-propiedad-id="' + p.id_propiedad + '" onclick="fetchEditData(this.dataset.propiedadId)">Editar</button>' +
            '<button class="action-link" type="button" data-propiedad-id="' + p.id_propiedad + '" data-arrendador-id="' + arrendadorId + '" onclick="abrirModalPropiedad(this.dataset.propiedadId, this.dataset.arrendadorId)">Previsualizar</button>' +
          '</div></td>' +
        '</tr>';
      });
    } else {
      filas = '<tr><td colspan="6" class="text-center">A\u00FAn no tienes propiedades creadas.</td></tr>';
    }

    contenedor.innerHTML = filas;

    paginaActualPropiedades = datos.current_page || 1;

    // Actualizar stats
    if (totales) {
      var statSpans = document.querySelectorAll('.stats-grid .stat-card span');
      if (statSpans.length >= 4) {
        statSpans[0].textContent = totales.totalPropiedades || 0;
        statSpans[1].textContent = totales.publicadas || 0;
        statSpans[2].textContent = totales.alquiladas || 0;
        statSpans[3].textContent = totales.inactivas || 0;
      }
    }

    // Paginación
    if (paginationWrap) {
      var htmlPag = '';
      if (datos.last_page && datos.last_page > 1) {
        htmlPag += '<ul class="pagination">';
        if (datos.prev_page_url) {
          htmlPag += '<li class="page-item"><a class="page-link" href="#" onclick="cargarTablaPropiedades(' + (datos.current_page - 1) + ');return false;">&laquo;</a></li>';
        }
        for (var i = 1; i <= datos.last_page; i++) {
          htmlPag += '<li class="page-item' + (i === datos.current_page ? ' active' : '') + '"><a class="page-link" href="#" onclick="cargarTablaPropiedades(' + i + ');return false;">' + i + '</a></li>';
        }
        if (datos.next_page_url) {
          htmlPag += '<li class="page-item"><a class="page-link" href="#" onclick="cargarTablaPropiedades(' + (datos.current_page + 1) + ');return false;">&raquo;</a></li>';
        }
        htmlPag += '</ul>';
      }
      paginationWrap.innerHTML = htmlPag;
    }
  })
  .catch(function () {
    contenedor.innerHTML = '<tr><td colspan="6" class="text-center">Error al cargar las propiedades.</td></tr>';
  });
}

function escaparHtml(texto) {
  var div = document.createElement('div');
  div.appendChild(document.createTextNode(texto));
  return div.innerHTML;
}

document.querySelectorAll('form[data-ajax-form="true"]').forEach(enviarFormularioConFetch);
iniciarValidacionImagenes();
iniciarValidacionFormularioPropiedad();
cargarTablaPropiedades();

document.onkeydown = function (evento) {
  if (evento.key === 'Escape') {
    cerrarModalPropiedad();
    cerrarModalFormulario();
  }
};
