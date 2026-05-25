/* ========================================
   GESTIÓN DE SOLICITUDES — SPOTYSTAY
   JavaScript Vanilla
   ======================================== */

var csrfToken = '';
var solicitudIdActual = null;
var tipoSolicitudActual = 'arrendador';
var modalSolicitud = null;
var paginaActualSol = 1;
var temporizadorBusqueda = null;

var mostrarAlertaExito = function (titulo, mensaje) {
    if (window.mostrarAlertaAdminExito) {
        window.mostrarAlertaAdminExito(titulo, mensaje);
        return;
    }

    window.alert(mensaje);
};

var mostrarAlertaError = function (titulo, mensaje) {
    if (window.mostrarAlertaAdminError) {
        window.mostrarAlertaAdminError(titulo, mensaje);
        return;
    }

    window.alert(mensaje);
};

var escaparHtml = function (texto) {
    return String(texto || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

var inicializarSolicitudesAdmin = function () {
    try {
        var metaCsrf = document.querySelector('meta[name=csrf-token]');
        if (metaCsrf) {
            csrfToken = metaCsrf.content || '';
        }

        var modalElement = document.getElementById('modalSolicitud');
        if (modalElement && typeof bootstrap !== 'undefined') {
            modalSolicitud = new bootstrap.Modal(modalElement);
        }

        asignarEventosFiltros();
        asignarEventosModal();
        asignarEventosTablaDelegados();
        filtrarSolicitudes();
        actualizarKpisSolicitudes();
    } catch (error) {
        console.error('Error inicializando solicitudes:', error);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarSolicitudesAdmin);
} else {
    inicializarSolicitudesAdmin();
}

var asignarEventosFiltros = function () {
    var formFiltros = document.getElementById('formFiltrosSolicitudes');
    var buscador = document.getElementById('buscadorSolicitudes');
    var selectRango = document.getElementById('selectRangoSol');
    var selectTipo = document.getElementById('selectTipoSol');
    var selectEstado = document.getElementById('selectEstadoSol');
    var selectCiudad = document.getElementById('selectCiudadSol');

    var enviarFormulario = function () {
        if (!formFiltros) {
            return;
        }

        paginaActualSol = 1;
        filtrarSolicitudes();
        actualizarKpisSolicitudes();
    };

    if (formFiltros) {
        formFiltros.onsubmit = function (evento) {
            evento.preventDefault();
            paginaActualSol = 1;
            filtrarSolicitudes();
            actualizarKpisSolicitudes();
        };
    }

    if (buscador) {
        buscador.oninput = function () {
            clearTimeout(temporizadorBusqueda);
            temporizadorBusqueda = setTimeout(function () {
                enviarFormulario();
            }, 250);
        };
    }

    if (selectRango) {
        selectRango.onchange = enviarFormulario;
    }

    if (selectTipo) {
        selectTipo.onchange = enviarFormulario;
    }

    if (selectEstado) {
        selectEstado.onchange = enviarFormulario;
    }

    if (selectCiudad) {
        selectCiudad.onchange = enviarFormulario;
    }
};

var asignarEventosTabla = function () {
    var botonesVer = document.querySelectorAll('.btn-ver-sol');
    var botonesAprobar = document.querySelectorAll('.btn-aprobar-sol');
    var botonesRechazar = document.querySelectorAll('.btn-rechazar-sol');
    var i;

    for (i = 0; i < botonesVer.length; i++) {
        botonesVer[i].onclick = function (evento) {
            evento.preventDefault();
            abrirModal(this.getAttribute('data-id'), this.getAttribute('data-tipo'));
        };
    }

    for (i = 0; i < botonesAprobar.length; i++) {
        botonesAprobar[i].onclick = function (evento) {
            evento.preventDefault();
            abrirModal(this.getAttribute('data-id'), this.getAttribute('data-tipo'));
        };
    }

    for (i = 0; i < botonesRechazar.length; i++) {
        botonesRechazar[i].onclick = function (evento) {
            evento.preventDefault();
            abrirModal(this.getAttribute('data-id'), this.getAttribute('data-tipo'));
        };
    }
};

var asignarEventosTablaDelegados = function () {
    var tablaBody = document.getElementById('tablaSolicitudes');

    if (!tablaBody) {
        return;
    }

    tablaBody.onclick = function (evento) {
        var boton = evento.target.closest('.btn-ver-sol, .btn-aprobar-sol, .btn-rechazar-sol');

        if (!boton) {
            return;
        }

        evento.preventDefault();
        abrirModal(boton.getAttribute('data-id'), boton.getAttribute('data-tipo'));
    };
};

var asignarEventosPaginacion = function () {
    var botonesPage = document.querySelectorAll('#paginacionSolicitudes .page-link[data-page]');
    var i;

    for (i = 0; i < botonesPage.length; i++) {
        botonesPage[i].onclick = function (evento) {
            evento.preventDefault();
            var page = parseInt(this.getAttribute('data-page'), 10);

            if (page && page > 0) {
                cambiarPaginaSol(page);
            }
        };
    }
};

var filtrarSolicitudes = function () {
    var selectRango = document.getElementById('selectRangoSol');
    var selectTipo = document.getElementById('selectTipoSol');
    var selectEstado = document.getElementById('selectEstadoSol');
    var selectCiudad = document.getElementById('selectCiudadSol');
    var buscador = document.getElementById('buscadorSolicitudes');

    var rango = selectRango ? selectRango.value : 'mes';
    var tipo = selectTipo ? selectTipo.value : 'all';
    var estado = selectEstado ? selectEstado.value : '';
    var ciudad = selectCiudad ? selectCiudad.value : '';
    var q = buscador ? buscador.value : '';

    var url = '/admin/solicitudes/filtrar?rango=' + encodeURIComponent(rango) +
        '&tipo=' + encodeURIComponent(tipo) +
        '&estado=' + encodeURIComponent(estado) +
        '&ciudad=' + encodeURIComponent(ciudad) +
        '&q=' + encodeURIComponent(q) +
        '&page=' + encodeURIComponent(paginaActualSol);

    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
        .then(function (respuesta) {
            return respuesta.json();
        })
        .then(function (datos) {
            actualizarTabla(datos);
            actualizarPaginacionUI(datos);
            asignarEventosPaginacion();
        })
        .catch(function (error) {
            console.error('Error al filtrar solicitudes:', error);
        });
};

var actualizarTabla = function (datos) {
    var tablaBody = document.getElementById('tablaSolicitudes');

    if (!tablaBody) {
        return;
    }
    var listaSolicitudes = datos && datos.data ? datos.data : [];

    tablaBody.innerHTML = '';

    if (listaSolicitudes.length > 0) {
        for (var i = 0; i < listaSolicitudes.length; i++) {
            var solicitud = listaSolicitudes[i];
            var partes = String(solicitud.nombre_usuario || '').split(' ');
            var iniciales = ((partes[0] || '').charAt(0) + (partes[1] || '').charAt(0)).toUpperCase();
            var colores = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#FFD5CC', '#D7EAF9', '#EDE7F6', '#D5F5E3', '#FAD7D7'];
            var color = colores[(solicitud.id_solicitud || 0) % colores.length];
            var fecha = solicitud.creado_solicitud ? new Date(solicitud.creado_solicitud).toLocaleDateString('es-ES') : '—';
            var tipoLabel = solicitud.tipo_label || 'Solicitud';
            var esGestor = solicitud.tipo_solicitud === 'gestor';
            var detallePrincipal = esGestor
                ? (solicitud.experiencia_solicitud || solicitud.descripcion_solicitud || '—')
                : (solicitud.tipo_arrendador_solicitud || solicitud.descripcion_solicitud || '—');
            var detalleSecundario = esGestor
                ? (solicitud.descripcion_solicitud || '—')
                : (solicitud.direccion_fiscal_solicitud || '—');

            var pagoHtml = esGestor
                ? '<span class="badge-estado badge-activo" style="background: rgba(52, 152, 219, 0.1); color: #3498db; border: 1px solid #3498db;">No aplica</span>'
                : (solicitud.stripe_status === 'active'
                    ? '<span class="badge-estado badge-activo" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71;">Pagado</span>'
                    : '<span class="badge-estado badge-pendiente" style="background: rgba(243, 156, 18, 0.1); color: #f39c12; border: 1px solid #f39c12;">Pendiente</span>');

            var fila = document.createElement('tr');
            fila.className = 'fila-solicitud';
            fila.setAttribute('data-id', solicitud.id_solicitud);
            fila.setAttribute('data-tipo', solicitud.tipo_solicitud);

            fila.innerHTML =
                '<td data-label="SOLICITANTE"><div class="usuario-celda"><div class="avatar-tabla" style="background:' + color + '">' + escaparHtml(iniciales || '?') + '</div><div class="usuario-info-tabla"><span class="usuario-nombre-tabla">' + escaparHtml(solicitud.nombre_usuario) + '</span><span class="usuario-email-tabla">' + escaparHtml(solicitud.email_usuario) + '</span></div></div></td>' +
                '<td data-label="TIPO" class="col-tablet-hide"><span class="badge-estado badge-activo" style="background: rgba(52, 152, 219, 0.1); color: #3498db; border: 1px solid #3498db;">' + escaparHtml(tipoLabel) + '</span></td>' +
                '<td data-label="DETALLE" class="col-mobile-hide"><div class="usuario-info-tabla"><span class="usuario-nombre-tabla">' + escaparHtml(detallePrincipal) + '</span><span class="usuario-email-tabla">' + escaparHtml(detalleSecundario) + '</span></div></td>' +
                '<td data-label="FECHA" class="col-tablet-hide">' + escaparHtml(fecha) + '</td>' +
                '<td data-label="ESTADO"><span class="badge-estado badge-pendiente">' + escaparHtml(solicitud.estado_solicitud ? solicitud.estado_solicitud.charAt(0).toUpperCase() + solicitud.estado_solicitud.slice(1) : 'Pendiente') + '</span></td>' +
                '<td data-label="PAGO">' + pagoHtml + '</td>' +
                '<td data-label="ACCIONES"><div class="acciones-tabla"><button type="button" class="btn-icono btn-ver-sol" data-id="' + solicitud.id_solicitud + '" data-tipo="' + escaparHtml(solicitud.tipo_solicitud) + '" title="Ver detalles"><i class="bi bi-eye"></i></button><button type="button" class="btn-icono btn-aprobar-sol" data-id="' + solicitud.id_solicitud + '" data-tipo="' + escaparHtml(solicitud.tipo_solicitud) + '" title="Aprobar"><i class="bi bi-check-circle"></i></button><button type="button" class="btn-icono btn-rechazar-sol" data-id="' + solicitud.id_solicitud + '" data-tipo="' + escaparHtml(solicitud.tipo_solicitud) + '" title="Rechazar"><i class="bi bi-x-circle"></i></button></div></td>';

            tablaBody.appendChild(fila);
        }
    } else {
        var filaVacia = document.createElement('tr');
        filaVacia.innerHTML = '<td colspan="7" class="sin-resultados">No hay solicitudes que coincidan con los filtros</td>';
        tablaBody.appendChild(filaVacia);
    }

    var infoPaginacion = document.getElementById('contadorResultados');
    if (infoPaginacion && datos && typeof datos.from !== 'undefined' && typeof datos.to !== 'undefined') {
        infoPaginacion.textContent = 'Mostrando ' + datos.from + '-' + datos.to + ' de ' + datos.total + ' solicitudes';
    }

    asignarEventosTabla();
    asignarEventosTablaDelegados();
    asignarEventosPaginacion();
};

var actualizarPaginacionUI = function (datos) {
    var paginacion = document.getElementById('paginacionSolicitudes');
    if (!paginacion) {
        return;
    }

    var info = datos || {};
    var paginaActual = parseInt(info.current_page || 1, 10);
    var ultimaPagina = parseInt(info.last_page || 1, 10);
    var html = '<ul class="pagination pagination-sm mb-0">';

    var crearItem = function (page, contenido, deshabilitado, activo) {
        var claseItem = 'page-item';

        if (deshabilitado) {
            claseItem += ' disabled';
        }

        if (activo) {
            claseItem += ' active';
        }

        return '<li class="' + claseItem + '"><button type="button" class="page-link"' + (page ? ' data-page="' + page + '"' : '') + '>' + contenido + '</button></li>';
    };

    html += crearItem(paginaActual - 1, '<i class="bi bi-chevron-left"></i>', paginaActual <= 1, false);

    for (var j = 1; j <= ultimaPagina; j++) {
        html += crearItem(j, String(j), false, j === paginaActual);
    }

    html += crearItem(paginaActual + 1, '<i class="bi bi-chevron-right"></i>', paginaActual >= ultimaPagina, false);
    html += '</ul>';

    paginacion.innerHTML = html;
    asignarEventosPaginacion();
};

var cambiarPaginaSol = function (page) {
    paginaActualSol = page;
    filtrarSolicitudes();
    actualizarKpisSolicitudes();
};

var actualizarKpisSolicitudes = function () {
    var selectRango = document.getElementById('selectRangoSol');
    var selectTipo = document.getElementById('selectTipoSol');
    var selectEstado = document.getElementById('selectEstadoSol');
    var selectCiudad = document.getElementById('selectCiudadSol');
    var buscador = document.getElementById('buscadorSolicitudes');

    var rango = selectRango ? selectRango.value : 'mes';
    var tipo = selectTipo ? selectTipo.value : 'all';
    var estado = selectEstado ? selectEstado.value : '';
    var ciudad = selectCiudad ? selectCiudad.value : '';
    var q = buscador ? buscador.value : '';

    var url = '/admin/solicitudes/kpis?rango=' + encodeURIComponent(rango) +
        '&tipo=' + encodeURIComponent(tipo) +
        '&estado=' + encodeURIComponent(estado) +
        '&ciudad=' + encodeURIComponent(ciudad) +
        '&q=' + encodeURIComponent(q);

    fetch(url, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            var elPendientes = document.getElementById('kpiPendientesSolicitudes');
            var elAprobadas = document.getElementById('kpiAprobadasSolicitudes');
            var elRechazadas = document.getElementById('kpiRechazadasSolicitudes');
            var elTotal = document.getElementById('kpiTotalSolicitudes');
            var badgeAprobadas = document.getElementById('badgeAprobadasDetalles');
            var badgeRechazadas = document.getElementById('badgeRechazadasDetalles');
            var txtPendientes = document.querySelector('.texto-pendientes');

            if (elPendientes) {
                elPendientes.textContent = data.pendientes;
            }
            if (elAprobadas) {
                elAprobadas.textContent = data.aprobadas;
            }
            if (elRechazadas) {
                elRechazadas.textContent = data.rechazadas;
            }
            if (elTotal) {
                elTotal.textContent = data.total;
            }
            if (badgeAprobadas) {
                badgeAprobadas.textContent = data.aprobadas;
            }
            if (badgeRechazadas) {
                badgeRechazadas.textContent = data.rechazadas;
            }
            if (txtPendientes) {
                txtPendientes.textContent = data.total + ' resultados filtrados';
            }
        })
        .catch(function (error) {
            console.error('Error al actualizar KPIs:', error);
        });
};

var abrirModal = function (id, tipo) {
    solicitudIdActual = id;
    tipoSolicitudActual = tipo || 'arrendador';

    var url = '/admin/solicitudes/' + encodeURIComponent(id) + '?tipo=' + encodeURIComponent(tipoSolicitudActual);

    fetch(url)
        .then(function (respuesta) {
            return respuesta.json();
        })
        .then(function (datos) {
            rellenarModal(datos);

            var estado = datos.estado_solicitud || 'pendiente';
            var btnAprobar = document.getElementById('btnAprobarModal');
            var btnRechazar = document.getElementById('btnRechazarModal');

            if (btnAprobar) {
                btnAprobar.style.display = estado === 'aprobada' ? 'none' : 'block';
            }

            if (btnRechazar) {
                btnRechazar.style.display = estado === 'rechazada' ? 'none' : 'block';
            }

            var notas = document.getElementById('modalNotas');
            if (notas) {
                notas.value = '';
            }

            if (modalSolicitud) {
                modalSolicitud.show();
            }
        })
        .catch(function (error) {
            console.error('Error al abrir modal:', error);
            mostrarAlertaError('Error', 'No se pudo cargar la solicitud');
        });
};

    if (typeof window !== 'undefined') {
        window.abrirModal = abrirModal;
    }

var rellenarModal = function (datos) {
    var partes = String(datos.nombre_usuario || '').split(' ');
    var iniciales = ((partes[0] || '').charAt(0) + (partes[1] || '').charAt(0)).toUpperCase();
    var colores = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#FFD5CC', '#D7EAF9', '#EDE7F6', '#D5F5E3', '#FAD7D7'];
    var color = colores[(datos.id_solicitud || 0) % colores.length];
    var esGestor = datos.tipo_solicitud === 'gestor';
    var tipoLabel = datos.tipo_label || (esGestor ? 'Gestor' : 'Arrendador');

    var modalAvatar = document.getElementById('modalAvatar');
    var modalNombre = document.getElementById('modalNombre');
    var modalEmail = document.getElementById('modalEmail');
    var modalTipoSolicitud = document.getElementById('modalTipoSolicitud');
    var modalTelefono = document.getElementById('modalTelefono');
    var modalEstadoTexto = document.getElementById('modalEstadoTexto');
    var modalBadgeEstado = document.getElementById('modalBadgeEstado');
    var bloqueArrendador = document.getElementById('bloque-arrendador-solicitud');
    var bloqueGestor = document.getElementById('bloque-gestor-solicitud');

    if (modalAvatar) {
        modalAvatar.style.background = color;
        modalAvatar.textContent = iniciales || 'S';
    }

    if (modalNombre) {
        modalNombre.textContent = datos.nombre_usuario || '—';
    }

    if (modalEmail) {
        modalEmail.textContent = datos.email_usuario || '—';
    }

    if (modalTipoSolicitud) {
        modalTipoSolicitud.innerHTML = '<i class="bi bi-briefcase"></i> ' + tipoLabel;
    }

    if (modalTelefono) {
        modalTelefono.textContent = datos.telefono_contacto || datos.telefono_usuario || '—';
    }

    if (modalEstadoTexto) {
        modalEstadoTexto.textContent = datos.estado_solicitud ? datos.estado_solicitud.charAt(0).toUpperCase() + datos.estado_solicitud.slice(1) : 'Pendiente';
    }

    if (modalBadgeEstado) {
        var estado = datos.estado_solicitud || 'pendiente';
        modalBadgeEstado.textContent = estado.charAt(0).toUpperCase() + estado.slice(1);
        modalBadgeEstado.className = 'badge';
        if (estado === 'aprobada') {
            modalBadgeEstado.classList.add('bg-success');
        } else if (estado === 'rechazada') {
            modalBadgeEstado.classList.add('bg-danger');
        } else {
            modalBadgeEstado.classList.add('bg-warning');
        }
    }

    if (bloqueArrendador) {
        bloqueArrendador.style.display = esGestor ? 'none' : 'block';
    }

    if (bloqueGestor) {
        bloqueGestor.style.display = esGestor ? 'block' : 'none';
    }

    var elementoFechaNacimiento = document.getElementById('modalFechaNacimiento');
    var elementoTipoDocumento = document.getElementById('modalTipoDocumento');
    var elementoNumeroDocumento = document.getElementById('modalNumeroDocumento');
    var elementoIban = document.getElementById('modalIban');
    var elementoTitularCuenta = document.getElementById('modalTitularCuenta');
    var elementoNif = document.getElementById('modalNif');
    var elementoDireccionFiscal = document.getElementById('modalDireccionFiscal');
    var elementoTipoArrendador = document.getElementById('modalTipoArrendador');
    var elementoNumPropiedades = document.getElementById('modalNumPropiedades');
    var elementoEsPropietario = document.getElementById('modalEsPropietario');
    var elementoDescripcion = document.getElementById('modalDescripcion');
    var elementoDescripcionGestor = document.getElementById('modalDescripcionGestor');
    var elementoExperienciaGestor = document.getElementById('modalExperienciaGestor');
    var elementoAceptaTerminos = document.getElementById('modalAceptaTerminos');
    var elementoAceptaVeracidad = document.getElementById('modalAceptaVeracidad');

    if (elementoFechaNacimiento) {
        var fechaNac = datos.fecha_nacimiento_solicitud || '—';
        if (fechaNac !== '—') {
            fechaNac = new Date(fechaNac).toLocaleDateString('es-ES');
        }
        elementoFechaNacimiento.textContent = fechaNac;
    }

    if (elementoTipoDocumento) {
        elementoTipoDocumento.textContent = datos.tipo_documento_solicitud || '—';
    }

    if (elementoNumeroDocumento) {
        elementoNumeroDocumento.textContent = datos.numero_documento_solicitud || '—';
    }

    if (elementoIban) {
        elementoIban.textContent = datos.iban_solicitud || '—';
    }

    if (elementoTitularCuenta) {
        elementoTitularCuenta.textContent = datos.titular_cuenta_solicitud || '—';
    }

    if (elementoNif) {
        elementoNif.textContent = datos.nif_solicitud || '—';
    }

    if (elementoDireccionFiscal) {
        elementoDireccionFiscal.textContent = datos.direccion_fiscal_solicitud || '—';
    }

    if (elementoTipoArrendador) {
        elementoTipoArrendador.textContent = datos.tipo_arrendador_solicitud || '—';
    }

    if (elementoNumPropiedades) {
        elementoNumPropiedades.textContent = datos.num_propiedades_previstas_solicitud || '—';
    }

    if (elementoEsPropietario) {
        elementoEsPropietario.textContent = datos.es_propietario_solicitud ? 'Sí' : 'No';
    }

    if (elementoDescripcion) {
        elementoDescripcion.textContent = datos.descripcion_solicitud || '—';
    }

    if (elementoDescripcionGestor) {
        elementoDescripcionGestor.textContent = datos.descripcion_solicitud || '—';
    }

    if (elementoExperienciaGestor) {
        elementoExperienciaGestor.textContent = datos.experiencia_solicitud || '—';
    }

    if (elementoAceptaTerminos) {
        elementoAceptaTerminos.textContent = datos.acepta_terminos_solicitud ? 'Sí' : 'No';
    }

    if (elementoAceptaVeracidad) {
        elementoAceptaVeracidad.textContent = datos.acepta_veracidad_solicitud ? 'Sí' : 'No';
    }
};

var asignarEventosModal = function () {
    var btnAprobar = document.getElementById('btnAprobarModal');
    var btnRechazar = document.getElementById('btnRechazarModal');

    if (btnAprobar) {
        btnAprobar.onclick = function () {
            aprobarSolicitud(solicitudIdActual, tipoSolicitudActual);
        };
    }

    if (btnRechazar) {
        btnRechazar.onclick = function () {
            var notas = document.getElementById('modalNotas');
            rechazarSolicitud(solicitudIdActual, tipoSolicitudActual, notas ? notas.value : '');
        };
    }
};

var aprobarSolicitud = function (id, tipo) {
    if (!id) {
        mostrarAlertaError('Error', 'ID de solicitud no disponible');
        return;
    }

    fetch('/admin/solicitudes/' + encodeURIComponent(id) + '/aprobar?tipo=' + encodeURIComponent(tipo || 'arrendador'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
        .then(function (respuesta) {
            return respuesta.json();
        })
        .then(function (datos) {
            if (datos.success) {
                if (modalSolicitud) {
                    modalSolicitud.hide();
                }
                mostrarAlertaExito('¡Éxito!', datos.message || 'Solicitud aprobada correctamente');
                paginaActualSol = 1;
                filtrarSolicitudes();
                actualizarKpisSolicitudes();
            } else {
                mostrarAlertaError('Error', datos.error || datos.message || 'Error desconocido al aprobar');
            }
        })
        .catch(function (error) {
            console.error('Error en aprobar:', error);
            mostrarAlertaError('Error', 'Error al procesar la solicitud');
        });
};

var rechazarSolicitud = function (id, tipo, notas) {
    if (!id) {
        mostrarAlertaError('Error', 'ID de solicitud no disponible');
        return;
    }

    fetch('/admin/solicitudes/' + encodeURIComponent(id) + '/rechazar?tipo=' + encodeURIComponent(tipo || 'arrendador'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ notas: notas })
    })
        .then(function (respuesta) {
            return respuesta.json();
        })
        .then(function (datos) {
            if (datos.success) {
                if (modalSolicitud) {
                    modalSolicitud.hide();
                }
                mostrarAlertaExito('¡Rechazada!', datos.message || 'Solicitud rechazada correctamente');
                paginaActualSol = 1;
                filtrarSolicitudes();
                actualizarKpisSolicitudes();
            } else {
                mostrarAlertaError('Error', datos.error || datos.message || 'Error desconocido al rechazar');
            }
        })
        .catch(function (error) {
            console.error('Error en rechazar:', error);
            mostrarAlertaError('Error', 'Error al procesar la solicitud');
        });
};
