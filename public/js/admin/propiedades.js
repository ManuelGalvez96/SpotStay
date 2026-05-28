/* ════════════════════════════════════════ */
/* PROPIEDADES ADMIN — JS */
/* ════════════════════════════════════════ */

var csrfToken;
var paginaActual = 1;
var totalPaginas = 1;
var propiedadActual = null;

var modalFormPropiedad = null;
var editandoPropiedadId = null;

// Referencias al formulario del modal
var formPropiedad, camposForm, erroresForm;
var _valTitulo = false, _valCalle = false, _valNumero = false, _valCiudad = false;
var _valCodigoPostal = false, _valPrecio = false, _valEstado = false, _valEmail = false;

var esEstadoAlquilada = function(estado) {
    return String(estado || '').trim().toLowerCase() === 'alquilada';
};

var actualizarEstadoBotonDesactivar = function(estado) {
    var btnDesactivarPropiedad = document.getElementById('btnDesactivarPropiedad');
    var modalPropiedad = document.getElementById('modalPropiedad');
    if (!btnDesactivarPropiedad) {
        return;
    }

    var estadoNormalizado = String(estado || '').trim().toLowerCase();
    if (modalPropiedad) {
        modalPropiedad.setAttribute('data-estado-propiedad', estadoNormalizado);
    }
    btnDesactivarPropiedad.setAttribute('data-estado-propiedad', estadoNormalizado);

    if (esEstadoAlquilada(estadoNormalizado)) {
        btnDesactivarPropiedad.disabled = true;
        btnDesactivarPropiedad.classList.add('is-disabled');
        btnDesactivarPropiedad.setAttribute('aria-disabled', 'true');
        btnDesactivarPropiedad.title = 'No se puede desactivar una propiedad alquilada';
        btnDesactivarPropiedad.textContent = 'No disponible (alquilada)';
    } else {
        btnDesactivarPropiedad.disabled = false;
        btnDesactivarPropiedad.classList.remove('is-disabled');
        btnDesactivarPropiedad.setAttribute('aria-disabled', 'false');
        btnDesactivarPropiedad.title = '';
        btnDesactivarPropiedad.textContent = 'Desactivar propiedad';
    }
};

var actualizarBotonMapaGeneral = function() {
    var btnVerMapaGeneral = document.getElementById('btnVerMapaGeneral');
    if (!btnVerMapaGeneral) {
        return;
    }

    if (propiedadActual && propiedadActual.direccion) {
        btnVerMapaGeneral.disabled = false;
        btnVerMapaGeneral.title = 'Abrir la ubicación de la última propiedad vista';
        return;
    }

    btnVerMapaGeneral.disabled = true;
    btnVerMapaGeneral.title = 'Abre primero una propiedad para ver su ubicación';
};

/* ── window.onload ── */
window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;

    // Inicializar modal del formulario
    var modalFormEl = document.getElementById('modalFormPropiedad');
    if (modalFormEl && typeof bootstrap !== 'undefined') {
        modalFormPropiedad = new bootstrap.Modal(modalFormEl);
        // Limpiar errores al cerrar el modal
        modalFormEl.addEventListener('hidden.bs.modal', function() {
            limpiarErroresFormPropiedad();
        });
    }

    // Inicializar referencias del formulario
    formPropiedad = document.getElementById('formPropiedad');
    camposForm = {
        titulo: document.getElementById('inputTitulo'),
        calle: document.getElementById('inputCalle'),
        numero: document.getElementById('inputNumero'),
        ciudad: document.getElementById('inputCiudad'),
        codigoPostal: document.getElementById('inputCodigoPostal'),
        precio: document.getElementById('inputPrecio'),
        estado: document.getElementById('inputEstado'),
        emailArrendador: document.getElementById('inputEmailArrendador')
    };
    erroresForm = {
        titulo: document.getElementById('errorTituloPropiedad'),
        calle: document.getElementById('errorCallePropiedad'),
        numero: document.getElementById('errorNumeroPropiedad'),
        ciudad: document.getElementById('errorCiudadPropiedad'),
        codigoPostal: document.getElementById('errorCodigoPostalPropiedad'),
        precio: document.getElementById('errorPrecioPropiedad'),
        estado: document.getElementById('errorEstadoPropiedad'),
        emailArrendador: document.getElementById('errorEmailArrendadorPropiedad')
    };

    asignarEventosFiltros();
    asignarEventosTabla();
    asignarEventosModal();
    asignarEventosPaginacion();
    asignarEventosFormPropiedad();
    filtrarPropiedades(1);
};

/* ── Asignar eventos a filtros ── */
var asignarEventosFiltros = function() {
    var selectEstado = document.getElementById('selectEstado');
    var selectCiudad = document.getElementById('selectCiudad');
    var selectPrecio = document.getElementById('selectPrecio');
    var buscadorPropiedades = document.getElementById('buscadorPropiedades');

    if (!selectEstado) {
        console.error('selectEstado no encontrado');
        return;
    }

    selectEstado.onchange = function(e) {
        console.log('Cambio en estado:', this.value);
        filtrarPropiedades(1);
    };

    selectCiudad.onchange = function(e) {
        console.log('Cambio en ciudad:', this.value);
        filtrarPropiedades(1);
    };

    selectPrecio.onchange = function(e) {
        console.log('Cambio en precio:', this.value);
        filtrarPropiedades(1);
    };

    buscadorPropiedades.oninput = function(e) {
        console.log('Cambio en búsqueda:', this.value);
        filtrarPropiedades(1);
    };
};

/* ── Filtrar propiedades ── */
var filtrarPropiedades = function(pagina) {
    var estado = document.getElementById('selectEstado').value;
    var ciudad = document.getElementById('selectCiudad').value;
    var precio = document.getElementById('selectPrecio').value;
    var busqueda = document.getElementById('buscadorPropiedades').value.toLowerCase();
    var paginaObjetivo = pagina || 1;

    console.log('Filtrando con parámetros:', {
        estado: estado,
        ciudad: ciudad,
        precio: precio,
        busqueda: busqueda,
        pagina: paginaObjetivo
    });

    var params = new URLSearchParams({
        estado: estado,
        ciudad: ciudad,
        precio: precio,
        q: busqueda,
        page: paginaObjetivo
    });

    var url = '/admin/propiedades/filtrar?' + params.toString();
    console.log('URL de filtrado:', url);

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(function(response) {
            if (!response.ok) {
                throw new Error(' Error HTTP ' + response.status);
            }
            return response.json(); 
        })
        .then(function(data) {
            actualizarTabla(data);
            actualizarPaginacion(data.currentPage || 1, data.totalPages || 1);
            actualizarResumenTabla(data.from, data.to, data.total);
        })
        .catch(function(error) {
            console.error('Error al filtrar propiedades:', error);
            var tbody = document.getElementById('tbodyPropiedades');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#EF4444;">No se pudieron cargar los resultados.</td></tr>';
            }
        });
};

var actualizarResumenTabla = function(from, to, total) {
    var footer = document.querySelector('.tabla-footer span');
    if (footer) {
        var inicio = from || 0;
        var fin = to || 0;
        var totalRegistros = total || 0;
        footer.textContent = 'Mostrando ' + inicio + '-' + fin + ' de ' + totalRegistros + ' propiedades';
    }
};

/* ── Actualizar tabla ── */
var actualizarTabla = function(data) {
    var tbody = document.getElementById('tbodyPropiedades');
    tbody.innerHTML = '';

    var propiedades = data.propiedades || [];
    var contador = document.getElementById('contadorPropiedades');
    contador.textContent = data.total + ' propiedades encontradas';

    var filas = '';
    for (var i = 0; i < propiedades.length; i++) {
        var prop = propiedades[i];
        var direccion = prop.direccion || '';
        var ciudad = prop.ciudad || '';
        var cp = prop.cp || '';
        var arrendadorNombre = prop.arrendadorNombre || 'Sin arrendador';
        var estado = prop.estado || 'borrador';
        var precio = prop.precio || '$0.00/mes';
        var color = prop.color || '#B8CCE4';
        var inquilinosActuales = typeof prop.inquilinosActuales === 'number' ? prop.inquilinosActuales : 0;
        var inquilinosTotales = typeof prop.inquilinosTotales === 'number' ? prop.inquilinosTotales : 1;

        var primeraLineaDireccion = direccion.indexOf(',') >= 0 ? direccion.split(',')[0] : (direccion || 'Sin direccion');
        var iniciales = arrendadorNombre.split(' ').map(function(n) { return n[0] || ''; }).join('').substring(0, 2).toUpperCase();
        
        var fila = '<tr data-id="' + prop.id + '" data-estado="' + estado + '">' +
            '<td>' +
                '<div class="propiedad-celda">' +
                    '<div class="thumb-propiedad" style="background: ' + color + ';"></div>' +
                    '<div>' +
                        '<p class="propiedad-nombre">' + primeraLineaDireccion + '</p>' +
                        '<p class="propiedad-ciudad">' + ciudad + ', ' + cp + '</p>' +
                    '</div>' +
                '</div>' +
            '</td>' +
            '<td>' +
                '<div style="display: flex; align-items: center; gap: 8px;">' +
                    '<div class="avatar-tabla" style="background: ' + color + '; width: 28px; height: 28px;">' + 
                        iniciales +
                    '</div>' +
                    '<span style="font-size: 13px;">' + arrendadorNombre + '</span>' +
                '</div>' +
            '</td>' +
            '<td><span class="badge-estado badge-' + estado + '">' + 
                estado.charAt(0).toUpperCase() + estado.slice(1) + '</span></td>' +
            '<td><span class="precio-propiedad">' + precio + '</span></td>' +
            '<td>' + inquilinosActuales + ' / ' + inquilinosTotales + '</td>' +
            '<td>' +
                '<div class="acciones-tabla">' +
                    '<button class="btn-accion btn-ver" data-id="' + prop.id + '" title="Ver detalle">' +
                        '<i class="bi bi-eye"></i>' +
                    '</button>' +
                    '<button class="btn-accion btn-editar" data-id="' + prop.id + '" title="Editar">' +
                        '<i class="bi bi-pencil"></i>' +
                    '</button>' +
                    '<button class="btn-accion btn-eliminar" data-id="' + prop.id + '" title="Eliminar">' +
                        '<i class="bi bi-trash"></i>' +
                    '</button>' +
                '</div>' +
            '</td>' +
            '</tr>';

        filas += fila;
    }

    if (filas.length === 0) {
        filas = '<tr><td colspan="6" style="text-align:center; color:#6B7280;">No se encontraron propiedades.</td></tr>';
    }

    tbody.innerHTML = filas;
    asignarEventosTabla();
};

/* ── Asignar eventos a tabla ── */
var asignarEventosTabla = function() {
    var botonesVer = document.querySelectorAll('.btn-ver');
    var botonesEditar = document.querySelectorAll('.btn-editar');
    var botonesEliminar = document.querySelectorAll('.btn-eliminar');

    for (var i = 0; i < botonesVer.length; i++) {
        botonesVer[i].onclick = function(e) {
            var id = parseInt(this.getAttribute('data-id'));
            abrirModal(id);
        };
    }

    for (var i = 0; i < botonesEditar.length; i++) {
        botonesEditar[i].onclick = function(e) {
            var id = parseInt(this.getAttribute('data-id'));
            editarPropiedad(id);
        };
    }

    for (var i = 0; i < botonesEliminar.length; i++) {
        botonesEliminar[i].onclick = function(e) {
            var id = parseInt(this.getAttribute('data-id'));
            confirmarEliminar(id);
        };
    }

    // Procesar botones según estado (mostrar publicar en borrador, ocultar editar/eliminar en alquilada)
    procesarBotonesSegunEstado();
};

/* ── Abrir modal ── */
var abrirModal = function(id) {
    actualizarEstadoBotonDesactivar('alquilada');

    fetch('/admin/propiedades/' + id)
        .then(function(response) {
            if (!response.ok) throw new Error('Error al cargar propiedad');
            return response.json();
        })
        .then(function(data) {
            var propiedad = data.propiedad;
            var alquileres = data.alquileres || [];
            var fotos = data.fotos || [];
            propiedadActual = {
                id: id,
                direccion: propiedad.direccion_propiedad,
                ciudad: propiedad.ciudad_propiedad,
                titulo: propiedad.titulo_propiedad
            };

            // Portada interactiva
            var modalImagenPropiedad = document.getElementById('modalImagenPropiedad');
            if (modalImagenPropiedad) {
                var fotoPrincipal = fotos.find(function(f) { return f.es_principal_foto; }) || fotos[0];
                if (fotoPrincipal) {
                    modalImagenPropiedad.style.backgroundImage = "url('/img/" + fotoPrincipal.ruta_foto + "')";
                    modalImagenPropiedad.style.backgroundSize = 'cover';
                    modalImagenPropiedad.style.backgroundPosition = 'center';
                } else {
                    modalImagenPropiedad.style.background = 'linear-gradient(135deg, #8AAAC4, #B8CCE4)';
                }
            }

            // Rellenar galería de imágenes
            var seccionGaleriaModal = document.getElementById('seccionGaleriaModal');
            var galeriaModal = document.getElementById('galeriaModal');
            if (seccionGaleriaModal && galeriaModal) {
                galeriaModal.innerHTML = '';
                if (fotos.length > 0) {
                    seccionGaleriaModal.style.display = 'block';
                    fotos.forEach(function(foto, idx) {
                        var imgEl = document.createElement('img');
                        imgEl.src = '/img/' + foto.ruta_foto;
                        imgEl.style.cssText = 'height: 100px; width: 140px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 2px solid transparent; transition: all 0.2s;';
                        imgEl.title = 'Haz clic para previsualizar esta imagen';
                        
                        // Marcar la foto activa en el borde
                        if (foto.es_principal_foto || (!fotos.some(function(f) { return f.es_principal_foto; }) && idx === 0)) {
                            imgEl.style.borderColor = '#007BFF';
                        }

                        imgEl.onclick = function() {
                            modalImagenPropiedad.style.backgroundImage = "url('/img/" + foto.ruta_foto + "')";
                            galeriaModal.querySelectorAll('img').forEach(function(img) {
                                img.style.borderColor = 'transparent';
                            });
                            imgEl.style.borderColor = '#007BFF';
                        };
                        galeriaModal.appendChild(imgEl);
                    });
                } else {
                    seccionGaleriaModal.style.display = 'none';
                }
            }

            // Información general
            document.getElementById('modalDireccion').textContent = propiedad.direccion_propiedad + ', ' + propiedad.ciudad_propiedad;
            document.getElementById('dataPrecio').textContent = '$' + parseFloat(propiedad.precio_propiedad).toFixed(2) + '/mes';
            document.getElementById('dataCiudad').textContent = propiedad.ciudad_propiedad;
            document.getElementById('dataCP').textContent = propiedad.codigo_postal_propiedad;
            document.getElementById('dataDireccion').textContent = propiedad.direccion_propiedad;
            document.getElementById('dataLatitud').textContent = (propiedad.latitud_propiedad !== null && propiedad.latitud_propiedad !== undefined && propiedad.latitud_propiedad !== '') ? propiedad.latitud_propiedad : '-';
            document.getElementById('dataLongitud').textContent = (propiedad.longitud_propiedad !== null && propiedad.longitud_propiedad !== undefined && propiedad.longitud_propiedad !== '') ? propiedad.longitud_propiedad : '-';

            // Datos adicionales (si no existen, mostrar "N/A")
            document.getElementById('dataTipo').textContent = propiedad.tipo_propiedad || '-';
            document.getElementById('dataHabitaciones').textContent = propiedad.habitaciones_propiedad || '-';
            document.getElementById('dataBanos').textContent = propiedad.banos_propiedad || '-';
            document.getElementById('dataTamano').textContent = propiedad.metros_cuadrados_propiedad ? propiedad.metros_cuadrados_propiedad + ' m²' : '-';
            document.getElementById('dataPlanta').textContent = propiedad.piso_propiedad || '-';
            document.getElementById('dataPuerta').textContent = propiedad.puerta_propiedad || '-';
            document.getElementById('dataPublicada').textContent = new Date(propiedad.creado_propiedad).toLocaleDateString('es-ES');
            document.getElementById('dataActualizacion').textContent = new Date(propiedad.actualizado_propiedad).toLocaleDateString('es-ES');
            document.getElementById('dataVisitas').textContent = '-';
            document.getElementById('dataFavoritos').textContent = '-';

            // Extras
            var extras = [
                { key: 'amueblado_propiedad', label: 'Amueblado' },
                { key: 'piscina_propiedad', label: 'Piscina' },
                { key: 'terraza_propiedad', label: 'Terraza' },
                { key: 'garaje_propiedad', label: 'Garaje' },
                { key: 'ascensor_propiedad', label: 'Ascensor' },
                { key: 'aire_acondicionado_propiedad', label: 'Aire acondicionado' },
                { key: 'calefaccion_propiedad', label: 'Calefacción' },
                { key: 'trastero_propiedad', label: 'Trastero' }
            ];

            var extrasHTML = '<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;">';
            for (var k = 0; k < extras.length; k++) {
                var extra = extras[k];
                var tiene = propiedad[extra.key] ? '✓' : '✗';
                var clase = propiedad[extra.key] ? 'extra-activado' : 'extra-desactivado';
                extrasHTML += '<div class="' + clase + '"><strong>' + tiene + '</strong> ' + extra.label + '</div>';
            }
            if (propiedad.adicional_propiedad) {
                extrasHTML += '<div style="grid-column: 1 / -1; padding: 8px 12px; background: #f0f0f0; border-radius: 6px;"><strong>Otros:</strong> ' + propiedad.adicional_propiedad + '</div>';
            }
            extrasHTML += '</div>';
            var extrasModalEl = document.getElementById('extrasModal');
            if (extrasModalEl) {
                extrasModalEl.innerHTML = extrasHTML;
            }

            // Nota: Secciones de PRECIOS Y GASTOS eliminadas del modal

            // Arrendador
            var avatarArrendador = document.getElementById('avatarArrendador');
            avatarArrendador.style.background = '#B8CCE4';
            avatarArrendador.textContent = propiedad.nombre_arrendador.split(' ').map(function(n) { return n[0]; }).join('');
            
            document.getElementById('nombreArrendador').textContent = propiedad.nombre_arrendador;
            document.getElementById('emailArrendador').textContent = propiedad.email_arrendador;
            document.getElementById('telefonoArrendador').textContent = '-';
            document.getElementById('linkPerfilArrendador').href = '#perfil';

            // Gestor
            var avatarGestor = document.getElementById('avatarGestor');
            avatarGestor.style.background = '#A8D5BF';
            var nombreGestor = propiedad.nombre_gestor || 'Sin asignar';
            avatarGestor.textContent = nombreGestor.split(' ').map(function(n) { return n[0]; }).join('');
            document.getElementById('nombreGestor').textContent = nombreGestor;

            // Inquilinos (desde alquileres activos)
            document.getElementById('labelInquilinos').textContent = 'INQUILINOS ACTUALES (' + alquileres.length + ')';

            var listaInquilinos = document.getElementById('listaInquilinos');
            listaInquilinos.innerHTML = '';
            for (var i = 0; i < alquileres.length; i++) {
                var alquiler = alquileres[i];
                var itemHTML = '<div class="inquilino-item">' +
                    '<div class="avatar-tabla" style="background: #D7EAF9;">' + alquiler.nombre_usuario.split(' ').map(function(n) { return n[0]; }).join('') + '</div>' +
                    '<div>' +
                        '<p style="font-weight: 600; font-size: 13px; margin: 0;">' + alquiler.nombre_usuario + '</p>' +
                        '<p style="font-size: 12px; color: #6B7280; margin: 0;">Estado: Activo</p>' +
                    '</div>' +
                    '<span class="badge-estado badge-activo" style="margin-left: auto;">Activo</span>' +
                    '</div>';
                listaInquilinos.innerHTML += itemHTML;
            }

            var badgeEstado = document.getElementById('modalBadgeEstado');
            badgeEstado.className = 'badge-estado badge-' + propiedad.estado_propiedad;
            badgeEstado.textContent = propiedad.estado_propiedad.charAt(0).toUpperCase() + propiedad.estado_propiedad.slice(1);

            document.getElementById('modalDireccion').setAttribute('data-propiedad-id', String(id));

            actualizarEstadoBotonDesactivar(propiedad.estado_propiedad);
            actualizarBotonMapaGeneral();

            // Mostrar modal Bootstrap
            var modalEl = document.getElementById('modalPropiedad');
            if (typeof bootstrap !== 'undefined' && modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        })
        .catch(function(error) {
            console.error('Error al cargar propiedad:', error);
            mostrarAlertaError('Error al cargar detalles de la propiedad');
        });
};

/* ── Cerrar modal ── */
var cerrarModal = function() {
    var modalEl = document.getElementById('modalPropiedad');
    if (typeof bootstrap !== 'undefined' && modalEl) {
        bootstrap.Modal.getOrCreateInstance(modalEl).hide();
    }
};

/* ── Asignar eventos al modal ── */
var asignarEventosModal = function() {
    var btnCerrarModal = document.getElementById('btnCerrarModal');
    var modalOverlay = document.getElementById('modalOverlay');
    var btnDesactivarPropiedad = document.getElementById('btnDesactivarPropiedad');
    var btnEditarPropiedad = document.getElementById('btnEditarPropiedad');
    var btnVerMapaGeneral = document.getElementById('btnVerMapaGeneral');
    var btnDescargarPDF = document.getElementById('btnDescargarPDF');

    if (btnCerrarModal) {
        btnCerrarModal.onclick = function() {
            cerrarModal();
        };
    }

    if (modalOverlay) {
        modalOverlay.onclick = function() {
            cerrarModal();
        };
    }

    btnDesactivarPropiedad.onclick = function() {
        var propiedadId = parseInt(document.getElementById('modalDireccion').getAttribute('data-propiedad-id') || '1');
        var modalPropiedad = document.getElementById('modalPropiedad');
        var estadoBoton = btnDesactivarPropiedad.getAttribute('data-estado-propiedad') || '';
        var estadoModal = modalPropiedad ? (modalPropiedad.getAttribute('data-estado-propiedad') || '') : '';

        if (btnDesactivarPropiedad.disabled || esEstadoAlquilada(estadoBoton) || esEstadoAlquilada(estadoModal)) {
            if (window.mostrarAlertaAdminValidacion) {
                window.mostrarAlertaAdminValidacion('No puedes desactivar una propiedad alquilada.');
            } else {
                alert('No puedes desactivar una propiedad alquilada.');
            }
            return;
        }
        desactivarPropiedad(propiedadId);
    };

    btnEditarPropiedad.onclick = function() {
        var propiedadId = parseInt(document.getElementById('modalDireccion').getAttribute('data-propiedad-id') || '0');
        if (propiedadId > 0) {
            editarPropiedad(propiedadId);
        }
    };

    if (btnVerMapaGeneral) {
        btnVerMapaGeneral.onclick = function() {
            if (!propiedadActual || !propiedadActual.direccion) {
                return;
            }

            var consulta = propiedadActual.direccion + ', ' + (propiedadActual.ciudad || '');
            var urlMapa = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(consulta);
            window.open(urlMapa, '_blank', 'noopener');
        };
    }

    btnDescargarPDF.onclick = function() {
        if (!propiedadActual || !propiedadActual.id) {
            if (window.mostrarAlertaAdminError) {
                window.mostrarAlertaAdminError('PDF no disponible', 'Abre una propiedad primero.');
            } else {
                alert('Abre una propiedad primero.');
            }
            return;
        }

        window.open('/admin/propiedades/' + propiedadActual.id + '/descargar-pdf', '_blank', 'noopener');
    };
};

/* ── Desactivar propiedad ── */
var desactivarPropiedad = function(id) {
    var url = '/admin/propiedades/' + id + '/desactivar';
    var formData = new FormData();
    formData.append('_token', csrfToken);

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        return response.text().then(function(texto) {
            var data = null;
            try {
                data = JSON.parse(texto);
            } catch (e) {
                data = null;
            }

            return {
                ok: response.ok,
                status: response.status,
                data: data
            };
        });
    })
    .then(function(resultado) {
        if (resultado.ok && resultado.data && resultado.data.success) {
            var row = document.querySelector('tr[data-id="' + id + '"]');
            if (row) {
                row.classList.add('fila-inactiva');

                var badgeTabla = row.querySelector('.badge-estado');
                if (badgeTabla) {
                    badgeTabla.className = 'badge-estado badge-inactiva';
                    badgeTabla.textContent = 'Inactiva';
                }
            }

            var badgeModal = document.getElementById('modalBadgeEstado');
            if (badgeModal) {
                badgeModal.className = 'badge-estado badge-inactiva';
                badgeModal.textContent = 'Inactiva';
            }

            actualizarEstadoBotonDesactivar('inactiva');

            cerrarModal();
            return;
        }

        var mensajeError = (resultado.data && resultado.data.message)
                ? resultado.data.message
                : 'La propiedad no se pudo desactivar (' + resultado.status + ').';

        if (window.mostrarAlertaAdminValidacion) {
            window.mostrarAlertaAdminValidacion(mensajeError);
        } else {
            alert(mensajeError);
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        if (window.mostrarAlertaAdminError) {
            window.mostrarAlertaAdminError('Error', 'No se pudo desactivar la propiedad.');
        } else {
            alert('No se pudo desactivar la propiedad.');
        }
    });
};

/* ── Procesar botones según estado de propiedad ── */
var procesarBotonesSegunEstado = function() {
    var filas = document.querySelectorAll('#tbodyPropiedades tr');
    
    for (var i = 0; i < filas.length; i++) {
        var fila = filas[i];
        var estado = fila.getAttribute('data-estado') || '';
        var accionesDiv = fila.querySelector('.acciones-tabla');
        
        if (!accionesDiv) continue;
        
        var btnEditar = accionesDiv.querySelector('.btn-editar');
        var btnEliminar = accionesDiv.querySelector('.btn-eliminar');
        var btnPublicar = accionesDiv.querySelector('.btn-publicar');
        
        // Si estado es alquilada: eliminar botones editar y eliminar
        if (estado.toLowerCase() === 'alquilada') {
            if (btnEditar) btnEditar.remove();
            if (btnEliminar) btnEliminar.remove();
        }
        
        // Si estado es borrador: crear botón publicar
        if (estado.toLowerCase() === 'borrador') {
            if (!btnPublicar) {  // Evitar duplicados
                var propiedadId = parseInt(fila.getAttribute('data-id'));
                var btnPublicarHTML = '<button class="btn-accion btn-publicar" data-id="' + propiedadId + '" title="Publicar propiedad">' +
                    '<i class="bi bi-cloud-upload"></i>' +
                    '</button>';
                
                // Insertar antes del botón editar (si existe) o antes de eliminar
                if (btnEditar) {
                    btnEditar.insertAdjacentHTML('beforebegin', btnPublicarHTML);
                } else if (btnEliminar) {
                    btnEliminar.insertAdjacentHTML('beforebegin', btnPublicarHTML);
                } else {
                    accionesDiv.insertAdjacentHTML('beforeend', btnPublicarHTML);
                }
            }
        }
    }
    
    // Asignar eventos a botones publicar recién creados
    var botonesPublicar = document.querySelectorAll('.btn-publicar');
    for (var j = 0; j < botonesPublicar.length; j++) {
        botonesPublicar[j].onclick = function(e) {
            e.preventDefault();
            var id = parseInt(this.getAttribute('data-id'));
            publicarPropiedad(id);
        };
    }
};

/* ── Publicar propiedad ── */
var publicarPropiedad = function(id) {
    var url = '/admin/propiedades/' + id + '/publicar';
    var formData = new FormData();
    formData.append('_token', csrfToken);

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        return response.text().then(function(texto) {
            var data = null;
            try {
                data = JSON.parse(texto);
            } catch (e) {
                data = null;
            }

            return {
                ok: response.ok,
                status: response.status,
                data: data
            };
        });
    })
    .then(function(resultado) {
        if (resultado.ok && resultado.data && resultado.data.success) {
            // Actualizar fila en tabla
            var row = document.querySelector('tr[data-id="' + id + '"]');
            if (row) {
                // Cambiar data-estado
                row.setAttribute('data-estado', 'publicada');
                
                // Actualizar badge
                var badgeTabla = row.querySelector('.badge-estado');
                if (badgeTabla) {
                    badgeTabla.className = 'badge-estado badge-publicada';
                    badgeTabla.textContent = 'Publicada';
                }
                
                // Eliminar botón publicar
                var accionesDiv = row.querySelector('.acciones-tabla');
                if (accionesDiv) {
                    var btnPublicar = accionesDiv.querySelector('.btn-publicar');
                    if (btnPublicar) {
                        btnPublicar.remove();
                    }
                }
            }

            // Mostrar alerta de éxito
            if (window.mostrarAlertaAdminExito) {
                window.mostrarAlertaAdminExito('Propiedad publicada', 'La propiedad está ahora disponible para alquiler.');
            }
            return;
        }

        var mensajeError = (resultado.data && resultado.data.message)
            ? resultado.data.message
            : 'La propiedad no se pudo publicar (' + resultado.status + ')';

        if (window.mostrarAlertaAdminError) {
            window.mostrarAlertaAdminError('Error al publicar', mensajeError);
        } else {
            alert('Error: ' + mensajeError);
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        if (window.mostrarAlertaAdminError) {
            window.mostrarAlertaAdminError('Error', 'No se pudo publicar la propiedad.');
        } else {
            alert('Error: No se pudo publicar la propiedad.');
        }
    });
};

/* ── Confirmar eliminar ── */
var confirmarEliminar = function(id) {
    var ejecutarEliminacion = function() {
        var url = '/admin/propiedades/' + id;

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(function(response) {
            return response.text().then(function(texto) {
                var data = null;
                try {
                    data = JSON.parse(texto);
                } catch (e) {
                    console.error('Respuesta del servidor (no JSON):', texto);
                    data = { success: false, message: 'Error del servidor: ' + texto.substring(0, 100) };
                }
                return { ok: response.ok, status: response.status, data: data };
            });
        })
        .then(function(resultado) {
            if (resultado.ok && resultado.data && resultado.data.success) {
                var row = document.querySelector('tr[data-id="' + id + '"]');
                if (row) {
                    row.parentNode.removeChild(row);
                }

                if (window.mostrarAlertaAdminExito) {
                    window.mostrarAlertaAdminExito('Eliminada', 'La propiedad se eliminó correctamente.');
                }
                return;
            }

            var mensaje = (resultado.data && resultado.data.message)
                ? resultado.data.message
                : 'Error ' + resultado.status + ' al eliminar.';

            console.error('Error al eliminar:', resultado);
            if (window.mostrarAlertaAdminError) {
                window.mostrarAlertaAdminError('No se pudo eliminar', mensaje);
            }
        })
        .catch(function(error) {
            console.error('Error de red al eliminar:', error);
            if (window.mostrarAlertaAdminError) {
                window.mostrarAlertaAdminError('Error', 'Ocurrió un error al eliminar la propiedad.');
            }
        });
    };

    if (window.confirmarAdmin) {
        window.confirmarAdmin('Eliminar propiedad', '¿Eliminar propiedad? Esta acción no se puede deshacer.')
            .then(function(confirmado) {
                if (!confirmado) return;
                ejecutarEliminacion();
            });
    } else {
        if (confirm('¿Eliminar propiedad? Esta acción no se puede deshacer.')) {
            ejecutarEliminacion();
        }
    }
};

/* ── Asignar eventos a paginación ── */
var asignarEventosPaginacion = function() {
    var botonesPagina = document.querySelectorAll('.page-link[data-pagina]');

    for (var i = 0; i < botonesPagina.length; i++) {
        botonesPagina[i].onclick = function() {
            var pagina = parseInt(this.getAttribute('data-pagina'));
            cambiarPagina(pagina);
        };
    }
};

/* ── Cambiar página ── */
var cambiarPagina = function(numero) {
    if (numero < 1 || numero > totalPaginas) {
        return;
    }

    paginaActual = numero;
    filtrarPropiedades(numero);

    var tabla = document.getElementById('tablaPropiedades');
    if (tabla) {
        tabla.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
};

/* ── Actualizar paginación ── */
var actualizarPaginacion = function(paginaActiva, totalPaginasNuevas) {
    paginaActual = paginaActiva;
    totalPaginas = totalPaginasNuevas;

    var contenedor = document.getElementById('paginas');
    if (!contenedor) {
        return;
    }
    var html = '';
    html += '<li class="page-item' + (paginaActiva <= 1 ? ' disabled' : '') + '"><button type="button" class="page-link" data-pagina="' + Math.max(1, paginaActiva - 1) + '"><i class="bi bi-chevron-left"></i></button></li>';
    for (var i = 1; i <= totalPaginasNuevas; i++) {
        var activo = i === paginaActiva ? ' active' : '';
        html += '<li class="page-item' + activo + '"><button type="button" class="page-link" data-pagina="' + i + '">' + i + '</button></li>';
    }

    html += '<li class="page-item' + (paginaActiva >= totalPaginasNuevas ? ' disabled' : '') + '"><button type="button" class="page-link" data-pagina="' + Math.min(totalPaginasNuevas, paginaActiva + 1) + '"><i class="bi bi-chevron-right"></i></button></li>';

    contenedor.innerHTML = html;

    var botonesPagina = contenedor.querySelectorAll('.page-link[data-pagina]');
    for (var j = 0; j < botonesPagina.length; j++) {
        botonesPagina[j].onclick = function() {
            var pagina = parseInt(this.getAttribute('data-pagina'));
            cambiarPagina(pagina);
        };
    }
};

/* ════════════════════════════════════════ */
/* MODAL CREAR / EDITAR PROPIEDAD        */
/* ════════════════════════════════════════ */

var asignarEventosFormPropiedad = function() {
    var btnAniadir = document.getElementById('btnAniadirPropiedad');
    if (btnAniadir) {
        btnAniadir.onclick = function() {
            abrirModalCrearPropiedad();
        };
    }

    var btnGuardar = document.getElementById('btnGuardarPropiedad');
    if (btnGuardar) {
        btnGuardar.onclick = function() {
            guardarPropiedad();
        };
    }

    // Activar validación en tiempo real
    activarValidacionCamposPropiedad();
};

/* ── Abrir modal en modo crear ── */
var abrirModalCrearPropiedad = function() {
    editandoPropiedadId = null;
    formPropiedad.reset();
    formPropiedad.removeAttribute('data-propiedad-id');
    document.getElementById('modalFormTitulo').textContent = 'Nueva propiedad';
    document.getElementById('btnGuardarPropiedad').querySelector('span').textContent = 'Guardar propiedad';

    // Ocultar sección de fotos existentes
    document.getElementById('seccionFotosExistentes').style.display = 'none';
    document.getElementById('contenedorFotosExistentes').innerHTML = '';
    document.getElementById('inputEliminarFotos').value = '';

    // Limpiar errores
    limpiarErroresFormPropiedad();

    // Cambiar placeholder del email
    camposForm.emailArrendador.placeholder = 'arrendador@example.com';

    if (modalFormPropiedad) {
        modalFormPropiedad.show();
    }
};

/* ── Abrir modal en modo editar ── */
var abrirModalEditarPropiedad = function(id) {
    editandoPropiedadId = id;

    fetch('/admin/propiedades/' + id + '/editar', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        if (!response.ok) throw new Error('Error al cargar propiedad');
        return response.json();
    })
    .then(function(data) {
        var propiedad = data.propiedad;
        var fotos = data.fotos || [];

        document.getElementById('modalFormTitulo').textContent = 'Editar propiedad';
        document.getElementById('btnGuardarPropiedad').querySelector('span').textContent = 'Guardar cambios';
        formPropiedad.setAttribute('data-propiedad-id', id);

        // Poblar campos
        camposForm.titulo.value = propiedad.titulo_propiedad || '';
        camposForm.calle.value = propiedad.calle_propiedad || '';
        camposForm.numero.value = propiedad.numero_propiedad || '';
        document.getElementById('inputPiso').value = propiedad.piso_propiedad || '';
        document.getElementById('inputPuerta').value = propiedad.puerta_propiedad || '';
        camposForm.ciudad.value = propiedad.ciudad_propiedad || '';
        camposForm.codigoPostal.value = propiedad.codigo_postal_propiedad || '';
        camposForm.precio.value = propiedad.precio_propiedad || '';
        document.getElementById('inputTipo').value = propiedad.tipo_propiedad || '';
        document.getElementById('inputHabitaciones').value = propiedad.habitaciones_propiedad || '';
        document.getElementById('inputMetros').value = propiedad.metros_cuadrados_propiedad || '';
        document.getElementById('inputBanos').value = propiedad.banos_propiedad || '';
        camposForm.estado.value = propiedad.estado_propiedad || 'publicada';
        camposForm.emailArrendador.value = propiedad.email_arrendador || '';
        document.getElementById('inputDescripcion').value = propiedad.descripcion_propiedad || '';
        document.getElementById('inputAdicional').value = propiedad.adicional_propiedad || '';
        camposForm.emailArrendador.placeholder = 'arrendador@example.com';

        // Extras checkboxes
        var extras = ['amueblado','piscina','terraza','garaje','ascensor','aire_acondicionado','calefaccion','trastero'];
        for (var e = 0; e < extras.length; e++) {
            var columna = extras[e] + '_propiedad';
            var checkbox = formPropiedad.querySelector('input[name="extras[]"][value="' + extras[e] + '"]');
            if (checkbox) {
                checkbox.checked = propiedad[columna] == 1 || propiedad[columna] === true;
            }
        }

        // Fotos existentes
        mostrarFotosExistentes(fotos);

        // Limpiar errores
        limpiarErroresFormPropiedad();

        // Cerrar el modal de detalle si está abierto
        cerrarModal();

        if (modalFormPropiedad) {
            modalFormPropiedad.show();
        }
    })
    .catch(function(error) {
        console.error('Error al cargar propiedad para editar:', error);
        if (window.mostrarAlertaAdminError) {
            window.mostrarAlertaAdminError('Error', 'No se pudieron cargar los datos de la propiedad.');
        }
    });
};

/* ── Mostrar fotos existentes en edición ── */
var fotosEliminadas = [];

var mostrarFotosExistentes = function(fotos) {
    var seccion = document.getElementById('seccionFotosExistentes');
    var contenedor = document.getElementById('contenedorFotosExistentes');
    var inputEliminar = document.getElementById('inputEliminarFotos');

    fotosEliminadas = [];

    if (!fotos || fotos.length === 0) {
        seccion.style.display = 'none';
        return;
    }

    seccion.style.display = 'block';
    contenedor.innerHTML = '';

    for (var i = 0; i < fotos.length; i++) {
        var foto = fotos[i];
        var card = document.createElement('div');
        card.className = 'foto-card-admin';
        card.id = 'foto-card-' + foto.id_foto;
        card.style.cssText = 'border: 2px solid #ccc; border-radius: 8px; padding: 8px; position: relative; transition: all 0.2s;';

        var img = document.createElement('img');
        img.src = '/img/' + foto.ruta_foto;
        img.style.cssText = 'width: 100%; height: 90px; object-fit: cover; border-radius: 4px; display: block;';
        card.appendChild(img);

        var btnWrap = document.createElement('div');
        btnWrap.style.cssText = 'margin-top: 6px; display: flex; align-items: center; justify-content: center;';

        var btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.textContent = 'Eliminar';
        btnEliminar.style.cssText = 'background: #ff6b6b; color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 11px; font-weight: bold; width: 100%;';
        btnEliminar.onclick = function(idFoto) {
            return function() {
                fotosEliminadas.push(idFoto);
                inputEliminar.value = fotosEliminadas.join(',');

                var cardEl = document.getElementById('foto-card-' + idFoto);
                cardEl.style.opacity = '0.4';
                cardEl.style.borderColor = '#ff6b6b';
                var btns = cardEl.querySelectorAll('button');
                btns[0].style.display = 'none';
                btns[1].style.display = 'block';
            };
        }(foto.id_foto);
        btnWrap.appendChild(btnEliminar);

        var btnRestaurar = document.createElement('button');
        btnRestaurar.type = 'button';
        btnRestaurar.textContent = 'Restaurar';
        btnRestaurar.style.cssText = 'background: #4CAF50; color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 11px; font-weight: bold; display: none; width: 100%;';
        btnRestaurar.onclick = function(idFoto) {
            return function() {
                fotosEliminadas = fotosEliminadas.filter(function(pid) { return pid !== idFoto; });
                inputEliminar.value = fotosEliminadas.join(',');

                var cardEl = document.getElementById('foto-card-' + idFoto);
                cardEl.style.opacity = '1';
                cardEl.style.borderColor = '#ccc';
                var btns = cardEl.querySelectorAll('button');
                btns[1].style.display = 'none';
                btns[0].style.display = 'block';
            };
        }(foto.id_foto);
        btnWrap.appendChild(btnRestaurar);

        card.appendChild(btnWrap);
        contenedor.appendChild(card);
    }
};

/* ── Función editarPropiedad (desde tabla o modal detalle) ── */
var editarPropiedad = function(id) {
    if (!id) return;
    abrirModalEditarPropiedad(id);
};

/* ════════════════════════════════════════ */
/* VALIDACIÓN DEL FORMULARIO              */
/* ════════════════════════════════════════ */

function valorLimpio(el) {
    return el && typeof el.value === 'string' ? el.value.trim() : '';
}

function textoError(el, msg) {
    if (el) el.textContent = msg;
}

function limpiarErrorForm(campo) {
    textoError(erroresForm[campo], '');
}

function limpiarErroresFormPropiedad() {
    for (var k in erroresForm) {
        textoError(erroresForm[k], '');
    }
}

function validarEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validarTituloPropiedad() {
    var v = valorLimpio(camposForm.titulo);
    if (!v) { textoError(erroresForm.titulo, 'El título es obligatorio.'); _valTitulo = false; return false; }
    if (v.length < 3) { textoError(erroresForm.titulo, 'Mínimo 3 caracteres.'); _valTitulo = false; return false; }
    limpiarErrorForm('titulo'); _valTitulo = true; return true;
}

function validarCallePropiedad() {
    var v = valorLimpio(camposForm.calle);
    if (!v) { textoError(erroresForm.calle, 'La calle es obligatoria.'); _valCalle = false; return false; }
    if (v.length < 3) { textoError(erroresForm.calle, 'Mínimo 3 caracteres.'); _valCalle = false; return false; }
    limpiarErrorForm('calle'); _valCalle = true; return true;
}

function validarNumeroPropiedad() {
    var v = valorLimpio(camposForm.numero);
    if (!v) { textoError(erroresForm.numero, 'El número es obligatorio.'); _valNumero = false; return false; }
    limpiarErrorForm('numero'); _valNumero = true; return true;
}

function validarCiudadPropiedad() {
    var v = valorLimpio(camposForm.ciudad);
    if (!v) { textoError(erroresForm.ciudad, 'La ciudad es obligatoria.'); _valCiudad = false; return false; }
    if (v.length < 2) { textoError(erroresForm.ciudad, 'Mínimo 2 caracteres.'); _valCiudad = false; return false; }
    limpiarErrorForm('ciudad'); _valCiudad = true; return true;
}

function validarCodigoPostalPropiedad() {
    var v = valorLimpio(camposForm.codigoPostal);
    if (!v) { textoError(erroresForm.codigoPostal, 'El código postal es obligatorio.'); _valCodigoPostal = false; return false; }
    if (!/^\d{5}$/.test(v)) { textoError(erroresForm.codigoPostal, 'Debe tener 5 dígitos.'); _valCodigoPostal = false; return false; }
    limpiarErrorForm('codigoPostal'); _valCodigoPostal = true; return true;
}

function validarPrecioPropiedad() {
    var v = valorLimpio(camposForm.precio);
    if (!v) { textoError(erroresForm.precio, 'El precio es obligatorio.'); _valPrecio = false; return false; }
    if (isNaN(v) || parseFloat(v) < 0) { textoError(erroresForm.precio, 'Debe ser un número >= 0.'); _valPrecio = false; return false; }
    limpiarErrorForm('precio'); _valPrecio = true; return true;
}

function validarEstadoPropiedad() {
    var v = valorLimpio(camposForm.estado);
    if (!v) { textoError(erroresForm.estado, 'El estado es obligatorio.'); _valEstado = false; return false; }
    limpiarErrorForm('estado'); _valEstado = true; return true;
}

function validarEmailArrendadorPropiedad() {
    var v = valorLimpio(camposForm.emailArrendador);
    if (!v) { textoError(erroresForm.emailArrendador, 'El email es obligatorio.'); _valEmail = false; return false; }
    if (!validarEmail(v)) { textoError(erroresForm.emailArrendador, 'Email no válido.'); _valEmail = false; return false; }
    limpiarErrorForm('emailArrendador'); _valEmail = true; return true;
}

function validarFormularioPropiedad() {
    var ok = true;
    if (!validarTituloPropiedad()) ok = false;
    if (!validarCallePropiedad()) ok = false;
    if (!validarNumeroPropiedad()) ok = false;
    if (!validarCiudadPropiedad()) ok = false;
    if (!validarCodigoPostalPropiedad()) ok = false;
    if (!validarPrecioPropiedad()) ok = false;
    if (!validarEmailArrendadorPropiedad()) ok = false;
    if (!validarEstadoPropiedad()) ok = false;
    return ok;
}

function activarValidacionCamposPropiedad() {
    if (!camposForm.titulo) return;

    function activar(campo, fn, cond) {
        if (!campo) return;
        campo.onblur = fn;
        campo.oninput = function() {
            if (cond()) fn();
        };
    }

    activar(camposForm.titulo, validarTituloPropiedad, function() { return valorLimpio(camposForm.titulo).length >= 3; });
    activar(camposForm.calle, validarCallePropiedad, function() { return valorLimpio(camposForm.calle).length >= 3; });
    activar(camposForm.numero, validarNumeroPropiedad, function() { return valorLimpio(camposForm.numero).length > 0; });
    activar(camposForm.ciudad, validarCiudadPropiedad, function() { return valorLimpio(camposForm.ciudad).length >= 2; });
    activar(camposForm.codigoPostal, validarCodigoPostalPropiedad, function() { return /^\d{5}$/.test(valorLimpio(camposForm.codigoPostal)); });
    activar(camposForm.precio, validarPrecioPropiedad, function() { return valorLimpio(camposForm.precio).length > 0; });
    activar(camposForm.estado, validarEstadoPropiedad, function() { return valorLimpio(camposForm.estado).length > 0; });
    activar(camposForm.emailArrendador, validarEmailArrendadorPropiedad, function() { return validarEmail(valorLimpio(camposForm.emailArrendador)); });
}

/* ════════════════════════════════════════ */
/* GUARDAR PROPIEDAD (CREAR / ACTUALIZAR) */
/* ════════════════════════════════════════ */

function mostrarErroresServidorPropiedad(errores) {
    var mapa = {
        titulo: 'titulo',
        calle: 'calle',
        numero: 'numero',
        ciudad: 'ciudad',
        codigo_postal: 'codigoPostal',
        precio: 'precio',
        estado: 'estado',
        arrendador_email: 'emailArrendador'
    };
    for (var campo in errores) {
        if (mapa[campo] && errores[campo] && errores[campo].length) {
            textoError(erroresForm[mapa[campo]], errores[campo][0]);
        }
    }
}

var guardarPropiedad = function() {
    if (!validarFormularioPropiedad()) return;

    var esEdicion = editandoPropiedadId !== null;
    var url = esEdicion ? '/admin/propiedades/' + editandoPropiedadId + '/editar' : '/admin/propiedades/crear';
    var formData = new FormData(formPropiedad);
    formData.append('_token', csrfToken);

    var btnGuardar = document.getElementById('btnGuardarPropiedad');
    var textoOriginal = btnGuardar ? btnGuardar.innerHTML : '';
    if (btnGuardar) {
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    }

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(function(response) {
        if (response.status === 422) {
            return response.json().then(function(data) {
                mostrarErroresServidorPropiedad(data.errors || {});
                throw new Error('Revisa los campos marcados.');
            });
        }
        if (!response.ok) {
            return response.json().then(function(data) {
                throw new Error(data.message || 'Error en la solicitud');
            }).catch(function(err) {
                if (err.message === 'Revisa los campos marcados.') throw err;
                throw new Error('Error en la solicitud');
            });
        }
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            // Cerrar modal
            if (modalFormPropiedad) modalFormPropiedad.hide();

            // Refrescar tabla
            filtrarPropiedades(1);

            // Mostrar alerta
            var titulo = esEdicion ? 'Propiedad actualizada' : 'Propiedad creada';
            var mensaje = esEdicion ? 'Los cambios se guardaron correctamente.' : 'La propiedad se creó correctamente.';
            if (window.mostrarAlertaAdminExito) {
                window.mostrarAlertaAdminExito(titulo, mensaje);
            }
        }
    })
    .catch(function(error) {
        if (error && error.message === 'Revisa los campos marcados.') return;
        var msg = error && error.message ? error.message : 'Error al procesar el formulario.';
        if (window.mostrarAlertaAdminError) {
            window.mostrarAlertaAdminError('Error', msg);
        }
    })
    .finally(function() {
        if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = textoOriginal;
        }
    });
};

/* ── Botón exportar ── */
var btnExportarPropiedades = document.getElementById('btnExportar');
if (btnExportarPropiedades) {
    btnExportarPropiedades.onclick = function() {
        window.location.href = '/admin/propiedades/exportar';
    };
}
