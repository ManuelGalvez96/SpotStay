/* ════════════════════════════════════════ */
/* PROPIEDADES ADMIN — JS */
/* ════════════════════════════════════════ */

var csrfToken;
var paginaActual = 1;
var totalPaginas = 1;
var propiedadActual = null;

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
    asignarEventosFiltros();
    asignarEventosTabla();
    asignarEventosModal();
    asignarEventosPaginacion();
    filtrarPropiedades(1);
};

/* ── Asignar eventos a filtros ── */
var asignarEventosFiltros = function() {
    var selectEstado = document.getElementById('selectEstado');
    var selectCiudad = document.getElementById('selectCiudad');
    var selectPrecio = document.getElementById('selectPrecio');
    var buscadorPropiedades = document.getElementById('buscadorPropiedades');

    selectEstado.onchange = function() {
        filtrarPropiedades(1);
    };

    selectCiudad.onchange = function() {
        filtrarPropiedades(1);
    };

    selectPrecio.onchange = function() {
        filtrarPropiedades(1);
    };

    buscadorPropiedades.oninput = function() {
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

    var params = new URLSearchParams({
        estado: estado,
        ciudad: ciudad,
        precio: precio,
        q: busqueda,
        page: paginaObjetivo
    });

    var url = '/admin/propiedades/filtrar?' + params.toString();

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
        
        var fila = '<tr data-id="' + prop.id + '">' +
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
            propiedadActual = {
                id: id,
                direccion: propiedad.direccion_propiedad,
                ciudad: propiedad.ciudad_propiedad,
                titulo: propiedad.titulo_propiedad
            };

            // Información general
            document.getElementById('modalDireccion').textContent = propiedad.direccion_propiedad + ', ' + propiedad.ciudad_propiedad;
            document.getElementById('dataPrecio').textContent = '$' + parseFloat(propiedad.precio_propiedad).toFixed(2) + '/mes';
            document.getElementById('dataCiudad').textContent = propiedad.ciudad_propiedad;
            document.getElementById('dataCP').textContent = propiedad.codigo_postal_propiedad;
            document.getElementById('dataDireccion').textContent = propiedad.direccion_propiedad;

            // Datos adicionales (si no existen, mostrar "N/A")
            document.getElementById('dataHabitaciones').textContent = '-';
            document.getElementById('dataBanos').textContent = '-';
            document.getElementById('dataTamano').textContent = '-';
            document.getElementById('dataPlanta').textContent = '-';
            document.getElementById('dataPublicada').textContent = new Date(propiedad.creado_propiedad).toLocaleDateString('es-ES');
            document.getElementById('dataActualizacion').textContent = new Date(propiedad.actualizado_propiedad).toLocaleDateString('es-ES');
            document.getElementById('dataVisitas').textContent = '-';
            document.getElementById('dataFavoritos').textContent = '-';

            // Gastos
            var gastos = propiedad.gastos_propiedad ? JSON.parse(propiedad.gastos_propiedad) : {};
            var agua = gastos.agua || 0;
            var luz = gastos.luz || 0;
            var gas = gastos.gas || 0;
            var comunidad = gastos.comunidad || 0;
            
            document.getElementById('dataAlquiler').textContent = '$' + parseFloat(propiedad.precio_propiedad).toFixed(2);
            document.getElementById('dataFianza').textContent = '$' + (parseFloat(propiedad.precio_propiedad) * 2).toFixed(2);
            document.getElementById('dataAgua').textContent = '$' + agua;
            document.getElementById('dataElectricidad').textContent = '$' + luz;
            document.getElementById('dataGas').textContent = '$' + gas;
            document.getElementById('dataComunidad').textContent = '$' + comunidad;
            var totalGastos = parseFloat(propiedad.precio_propiedad) + agua + luz + gas + comunidad;
            document.getElementById('dataTotalEstimado').textContent = 'Total estimado: $' + totalGastos.toFixed(2);

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

/* ── Botón añadir propiedad ── */
var btnAniadirPropiedad = document.getElementById('btnAniadirPropiedad');
if (btnAniadirPropiedad) {
    btnAniadirPropiedad.onclick = function() {
        window.location.href = '/admin/propiedades/nueva';
    };
}

/* ── Botón exportar ── */
var btnExportarPropiedades = document.getElementById('btnExportar');
if (btnExportarPropiedades) {
    btnExportarPropiedades.onclick = function() {
        window.location.href = '/admin/propiedades/exportar';
    };
}

/* ── Función editarPropiedad (placeholder) ── */
var editarPropiedad = function(id) {
    if (!id) {
        return;
    }
    window.location.href = '/admin/propiedades/' + id + '/editar';
};
