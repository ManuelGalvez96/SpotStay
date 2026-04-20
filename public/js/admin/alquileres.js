/* ============================================
   ALQUILERES JS - SpotStay Admin Dashboard
   Logica de interaccion alquileres
   ============================================ */

var csrfToken = '';
var alquilerIdActual = null;
var pasoActual = 1;
var totalPasos = 4;
var datosNuevoAlquiler = {};
var paginaActual = 1;

window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    asignarEventosFiltros();
    asignarEventosTabla();
    asignarEventosModal();
    asignarEventosModalNuevo();
    asignarEventosPaginacion();
};

/* ── FILTROS ── */
var asignarEventosFiltros = function() {
    var selectEstado = document.getElementById('selectEstadoAlq');
    var selectPropiedad = document.getElementById('selectPropiedadAlq');
    var selectMes = document.getElementById('selectMesAlq');
    var buscador = document.getElementById('buscadorAlq');

    if (selectEstado) {
        selectEstado.onchange = function() {
            filtrarAlquileres();
        };
    }

    if (selectPropiedad) {
        selectPropiedad.onchange = function() {
            filtrarAlquileres();
        };
    }

    if (selectMes) {
        selectMes.onchange = function() {
            filtrarAlquileres();
        };
    }

    if (buscador) {
        buscador.onblur = function() {
            filtrarAlquileres();
        };
        buscador.onkeyup = function(e) {
            if (e.key === 'Enter') {
                filtrarAlquileres();
            }
        };
    }
};

var filtrarAlquileres = function() {
    var estado = document.getElementById('selectEstadoAlq') ? 
        document.getElementById('selectEstadoAlq').value : '';
    var propiedad = document.getElementById('selectPropiedadAlq') ? 
        document.getElementById('selectPropiedadAlq').value : '';
    var mes = document.getElementById('selectMesAlq') ? 
        document.getElementById('selectMesAlq').value : '';
    var q = document.getElementById('buscadorAlq') ? 
        document.getElementById('buscadorAlq').value : '';

    var url = '/admin/alquileres/filtrar?';
    var params = [];
    if (estado) params.push('estado=' + encodeURIComponent(estado));
    if (propiedad) params.push('propiedad=' + encodeURIComponent(propiedad));
    if (mes) params.push('mes=' + encodeURIComponent(mes));
    if (q) params.push('q=' + encodeURIComponent(q));
    
    if (params.length > 0) {
        url += params.join('&');
    }

    fetch(url).then(function(response) {
        return response.json();
    }).then(function(data) {
        var contador = document.getElementById('contadorAlquileres');
        if (contador) {
            contador.textContent = data.total;
        }
    }).catch(function(error) {
        console.error('Error filtrando:', error);
    });
};

/* ── TABLA ── */
var asignarEventosTabla = function() {
    var botonesVer = document.querySelectorAll('.btn-ver-alq');
    var botonesAprobar = document.querySelectorAll('.btn-aprobar-alq');
    var botonesRechazar = document.querySelectorAll('.btn-rechazar-alq');

    botonesVer.forEach(function(btn) {
        btn.onclick = function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-alquiler-id');
            abrirModal(id);
        };
    });

    botonesAprobar.forEach(function(btn) {
        btn.onclick = function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-alquiler-id');
            alquilerIdActual = id;
        };
    });

    botonesRechazar.forEach(function(btn) {
        btn.onclick = function(e) {
            e.preventDefault();
            var id = this.getAttribute('data-alquiler-id');
            alquilerIdActual = id;
        };
    });
};

/* ── MODAL DETALLE ── */
var asignarEventosModal = function() {
    var btnCerrar = document.getElementById('btnCerrarModal');
    var btnAprobarModal = document.getElementById('btnAprobarModal');
    var btnRechazarModal = document.getElementById('btnRechazarModal');
    var modalOverlay = document.getElementById('modalOverlay');

    if (btnCerrar) {
        btnCerrar.onclick = function() {
            cerrarModal();
        };
    }

    if (btnAprobarModal) {
        btnAprobarModal.onclick = function() {
            aprobarAlquiler(alquilerIdActual);
        };
    }

    if (btnRechazarModal) {
        btnRechazarModal.onclick = function() {
            rechazarAlquiler(alquilerIdActual);
        };
    }

    if (modalOverlay) {
        modalOverlay.onclick = function(e) {
            if (e.target === modalOverlay) {
                cerrarModal();
            }
        };
    }
};

var abrirModal = function(id) {
    alquilerIdActual = id;
    
    var url = '/admin/alquileres/' + id;
    fetch(url).then(function(response) {
        return response.json();
    }).then(function(data) {
        rellenarModal(data);
        rellenarTimeline(data.historial);
        var modal = document.getElementById('modalOverlay');
        if (modal) {
            modal.classList.add('visible');
        }
    }).catch(function(error) {
        console.error('Error obteniendo alquiler:', error);
    });
};

var rellenarModal = function(data) {
    var alquiler = data.alquiler;
    var contrato = data.contrato;
    var pago = data.pago;

    /* Partes implicadas */
    if (document.getElementById('avatarArrendador')) {
        var divAvatar = document.getElementById('avatarArrendador');
        divAvatar.textContent = alquiler.inicialesArr;
        divAvatar.style.background = alquiler.colorArr;
    }
    if (document.getElementById('nombreArrendador')) {
        document.getElementById('nombreArrendador').textContent = 
            alquiler.nombre_usuario_arrendador || '—';
    }
    if (document.getElementById('emailArrendador')) {
        document.getElementById('emailArrendador').textContent = 
            alquiler.email_arrendador || '—';
    }
    if (document.getElementById('telefonoArrendador')) {
        document.getElementById('telefonoArrendador').textContent = 
            alquiler.telefono_arrendador || '—';
    }

    if (document.getElementById('avatarInquilino')) {
        var divAvatar = document.getElementById('avatarInquilino');
        divAvatar.textContent = alquiler.inicialesInq;
        divAvatar.style.background = alquiler.colorInq;
    }
    if (document.getElementById('nombreInquilino')) {
        document.getElementById('nombreInquilino').textContent = 
            alquiler.nombre_usuario_inquilino || '—';
    }
    if (document.getElementById('emailInquilino')) {
        document.getElementById('emailInquilino').textContent = 
            alquiler.email_inquilino || '—';
    }
    if (document.getElementById('telefonoInquilino')) {
        document.getElementById('telefonoInquilino').textContent = 
            alquiler.telefono_inquilino || '—';
    }

    /* Propiedad */
    if (document.getElementById('thumbPropiedadModal')) {
        var img = document.getElementById('thumbPropiedadModal');
        img.src = alquiler.foto_propiedad || '/images/placeholder.jpg';
    }
    if (document.getElementById('nombrePropiedadModal')) {
        document.getElementById('nombrePropiedadModal').textContent = 
            alquiler.titulo_propiedad || '—';
    }
    if (document.getElementById('ciudadPropiedadModal')) {
        document.getElementById('ciudadPropiedadModal').textContent = 
            alquiler.ciudad_propiedad || '—';
    }
    if (document.getElementById('precioPropiedadModal')) {
        document.getElementById('precioPropiedadModal').textContent = 
            '€ ' + (alquiler.precio_propiedad ? parseFloat(alquiler.precio_propiedad).toFixed(2) : '0.00');
    }

    /* Fechas */
    if (document.getElementById('dataInicioAlq')) {
        document.getElementById('dataInicioAlq').textContent = 
            alquiler.fecha_inicio_alquiler || '—';
    }
    if (document.getElementById('dataFinAlq')) {
        document.getElementById('dataFinAlq').textContent = 
            alquiler.fecha_fin_alquiler ? alquiler.fecha_fin_alquiler : 'Sin especificar';
    }
    if (document.getElementById('dataDuracionAlq')) {
        document.getElementById('dataDuracionAlq').textContent = 
            alquiler.duracion_meses ? alquiler.duracion_meses + ' mes(es)' : '—';
    }
    if (document.getElementById('dataPrecioAlq')) {
        document.getElementById('dataPrecioAlq').textContent = 
            '€ ' + (alquiler.precio_propiedad ? parseFloat(alquiler.precio_propiedad).toFixed(2) : '0.00');
    }
    if (document.getElementById('dataFianzaAlq')) {
        var fianza = alquiler.precio_propiedad ? parseFloat(alquiler.precio_propiedad) * 2 : 0;
        document.getElementById('dataFianzaAlq').textContent = '€ ' + fianza.toFixed(2);
    }
    if (document.getElementById('dataTotalAnual')) {
        var meses = alquiler.duracion_meses ? parseInt(alquiler.duracion_meses) : 1;
        var total = alquiler.precio_propiedad ? parseFloat(alquiler.precio_propiedad) * meses : 0;
        document.getElementById('dataTotalAnual').textContent = '€ ' + total.toFixed(2);
    }

    /* Contrato */
    if (document.getElementById('firmaArrendador')) {
        var span = document.getElementById('firmaArrendador');
        if (contrato.firmado_arrendador) {
            span.innerHTML = '<span class="badge-estado badge-estado-activo">Firmado ✓</span>';
        } else {
            span.innerHTML = '<span class="badge-estado badge-estado-pendiente">Pendiente</span>';
        }
    }
    if (document.getElementById('firmaInquilino')) {
        var span = document.getElementById('firmaInquilino');
        if (contrato.firmado_inquilino) {
            span.innerHTML = '<span class="badge-estado badge-estado-activo">Firmado ✓</span>';
        } else {
            span.innerHTML = '<span class="badge-estado badge-estado-pendiente">Pendiente</span>';
        }
    }
    if (document.getElementById('estadoContrato')) {
        var span = document.getElementById('estadoContrato');
        var claseEstado = 'badge-estado-pendiente';
        if (contrato.estado_contrato === 'activo') {
            claseEstado = 'badge-estado-activo';
        } else if (contrato.estado_contrato === 'finalizado') {
            claseEstado = 'badge-estado-finalizado';
        }
        span.innerHTML = '<span class="badge-estado ' + claseEstado + '">' + contrato.estado_contrato.toUpperCase() + '</span>';
    }

    /* Pago */
    if (document.getElementById('estadoPago')) {
        var span = document.getElementById('estadoPago');
        var claseEstado = 'badge-estado-pendiente';
        if (pago.estado_pago === 'confirmado') {
            claseEstado = 'badge-estado-activo';
        }
        span.innerHTML = '<span class="badge-estado ' + claseEstado + '">' + pago.estado_pago.toUpperCase() + '</span>';
    }
    if (document.getElementById('importePago')) {
        document.getElementById('importePago').textContent = 
            '€ ' + (pago.importe_pago ? parseFloat(pago.importe_pago).toFixed(2) : '0.00');
    }
    if (document.getElementById('referenciaPago')) {
        document.getElementById('referenciaPago').textContent = 
            pago.referencia_pago || '—';
    }

    /* Notas */
    if (document.getElementById('modalNotasAlq')) {
        document.getElementById('modalNotasAlq').value = 
            alquiler.notas_alquiler || '';
    }

    /* Botones de acción */
    var btnAprobar = document.getElementById('btnAprobarModal');
    var btnRechazar = document.getElementById('btnRechazarModal');
    
    if (alquiler.estado_alquiler === 'pendiente') {
        if (btnAprobar) btnAprobar.style.display = 'flex';
        if (btnRechazar) btnRechazar.style.display = 'flex';
    } else {
        if (btnAprobar) btnAprobar.style.display = 'none';
        if (btnRechazar) btnRechazar.style.display = 'none';
    }
};

var rellenarTimeline = function(historial) {
    var timeline = document.getElementById('timelineAlquiler');
    if (!timeline) return;
    
    timeline.innerHTML = '';

    if (!historial || historial.length === 0) {
        timeline.innerHTML = '<p style="font-size: 12px; color: #9CA3AF; text-align: center; padding: 16px;">Sin eventos registrados</p>';
        return;
    }

    historial.forEach(function(evento) {
        var divEvento = document.createElement('div');
        divEvento.className = 'timeline-evento';

        var colorPunto = '#035498';
        if (evento.estado === 'rechazado') {
            colorPunto = '#EF4444';
        } else if (evento.estado === 'aprobado' || evento.estado === 'activo') {
            colorPunto = '#1AA068';
        }

        divEvento.innerHTML = 
            '<div class="timeline-punto-modal" style="background: ' + colorPunto + ';"></div>' +
            '<div class="timeline-evento-info">' +
            '  <p class="timeline-evento-texto">' + evento.comentario + '</p>' +
            '  <div class="timeline-evento-hora">' + evento.fecha + '</div>' +
            '</div>';

        timeline.appendChild(divEvento);
    });
};

var cerrarModal = function() {
    var modal = document.getElementById('modalOverlay');
    if (modal) {
        modal.classList.remove('visible');
    }
    alquilerIdActual = null;
};

var aprobarAlquiler = function(id) {
    if (!confirm('¿Aprobar este alquiler?')) return;

    var formData = new FormData();
    formData.append('_token', csrfToken);

    fetch('/admin/alquiler/' + id + '/aprobar', {
        method: 'POST',
        body: formData
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al aprobar: ' + (data.error || 'Unknown'));
        }
    }).catch(function(error) {
        console.error('Error:', error);
        alert('Error al aprobar el alquiler');
    });
};

var rechazarAlquiler = function(id) {
    if (!confirm('¿Rechazar este alquiler? Esta acción no se puede deshacer.')) return;

    var formData = new FormData();
    formData.append('_token', csrfToken);

    fetch('/admin/alquiler/' + id + '/rechazar', {
        method: 'POST',
        body: formData
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al rechazar: ' + (data.error || 'Unknown'));
        }
    }).catch(function(error) {
        console.error('Error:', error);
        alert('Error al rechazar el alquiler');
    });
};

/* ── MODAL NUEVO ALQUILER (WIZARD) ── */
var asignarEventosModalNuevo = function() {
    var btnNuevo = document.getElementById('btnNuevoAlquiler');
    var btnCerrar = document.getElementById('btnCerrarModalNuevo');
    var btnCancelar = document.getElementById('btnCancelarNuevo');
    var btnSiguiente = document.getElementById('btnPasoSiguiente');
    var btnAnterior = document.getElementById('btnPasoAnterior');
    var btnCrear = document.getElementById('btnCrearAlquiler');
    var selectPropiedad = document.getElementById('nuevoPropiedadId');
    var selectInquilino = document.getElementById('nuevoInquilinoId');
    var modalOverlay = document.getElementById('modalOverlayNuevo');

    if (btnNuevo) {
        btnNuevo.onclick = function() {
            abrirModalNuevo();
        };
    }

    if (btnCerrar) {
        btnCerrar.onclick = function() {
            cerrarModalNuevo();
        };
    }

    if (btnCancelar) {
        btnCancelar.onclick = function() {
            cerrarModalNuevo();
        };
    }

    if (btnSiguiente) {
        btnSiguiente.onclick = function() {
            if (validarPasoActual()) {
                irAPaso(pasoActual + 1);
            }
        };
    }

    if (btnAnterior) {
        btnAnterior.onclick = function() {
            if (pasoActual > 1) {
                irAPaso(pasoActual - 1);
            }
        };
    }

    if (btnCrear) {
        btnCrear.onclick = function() {
            if (validarPasoActual()) {
                crearAlquiler();
            }
        };
    }

    if (selectPropiedad) {
        selectPropiedad.onchange = function() {
            var idProp = this.value;
            var option = this.querySelector('option[value="' + idProp + '"]');
            var precioSugerido = option ? option.getAttribute('data-precio') : null;
            
            var divPreview = document.getElementById('propiedadSeleccionada');
            if (divPreview && idProp) {
                var nombreProp = option.textContent;
                divPreview.innerHTML = 
                    '<div style="font-weight: 600; font-size: 14px; color: #111827;">' + nombreProp + '</div>' +
                    (precioSugerido ? '<div style="font-size: 12px; color: #6B7280; margin-top: 4px;">€ ' + parseFloat(precioSugerido).toFixed(2) + '/mes</div>' : '');
                divPreview.style.display = 'block';
            } else if (divPreview) {
                divPreview.style.display = 'none';
            }

            if (precioSugerido) {
                datosNuevoAlquiler.precioSugerido = precioSugerido;
            }
        };
    }

    if (selectInquilino) {
        selectInquilino.onchange = function() {
            var idInq = this.value;
            var option = this.querySelector('option[value="' + idInq + '"]');
            var email = option ? option.getAttribute('data-email') : null;

            var divPreview = document.getElementById('inquilinoSeleccionado');
            if (divPreview && idInq) {
                var nombreInq = option.textContent;
                divPreview.innerHTML = 
                    '<div style="font-weight: 600; font-size: 14px; color: #111827;">' + nombreInq + '</div>' +
                    (email ? '<div style="font-size: 12px; color: #6B7280; margin-top: 4px;">' + email + '</div>' : '');
                divPreview.style.display = 'block';
            } else if (divPreview) {
                divPreview.style.display = 'none';
            }
        };
    }

    if (modalOverlay) {
        modalOverlay.onclick = function(e) {
            if (e.target === modalOverlay) {
                cerrarModalNuevo();
            }
        };
    }
};

var abrirModalNuevo = function() {
    pasoActual = 1;
    datosNuevoAlquiler = {};
    irAPaso(1);
    
    var modal = document.getElementById('modalOverlayNuevo');
    if (modal) {
        modal.classList.add('visible');
    }
};

var cerrarModalNuevo = function() {
    var modal = document.getElementById('modalOverlayNuevo');
    if (modal) {
        modal.classList.remove('visible');
    }
    pasoActual = 1;
    datosNuevoAlquiler = {};
};

var irAPaso = function(paso) {
    pasoActual = paso;

    /* Mostrar/ocultar contenido */
    for (var i = 1; i <= totalPasos; i++) {
        var div = document.getElementById('paso' + i);
        if (div) {
            div.style.display = (i === paso) ? 'block' : 'none';
        }
    }

    /* Actualizar indicador */
    var pasoItems = document.querySelectorAll('.paso-item');
    pasoItems.forEach(function(item, index) {
        var numPaso = index + 1;
        item.classList.remove('paso-activo', 'paso-completado');
        
        if (numPaso < paso) {
            item.classList.add('paso-completado');
        } else if (numPaso === paso) {
            item.classList.add('paso-activo');
        }
    });

    /* Actualizar label paso */
    var labelPaso = document.getElementById('labelPasoActual');
    if (labelPaso) {
        labelPaso.textContent = 'Paso ' + paso + ' de ' + totalPasos;
    }

    /* Mostrar/ocultar botones */
    var btnAnterior = document.getElementById('btnPasoAnterior');
    var btnSiguiente = document.getElementById('btnPasoSiguiente');
    var btnCrear = document.getElementById('btnCrearAlquiler');

    if (btnAnterior) {
        btnAnterior.style.display = (paso > 1) ? 'block' : 'none';
    }

    if (btnSiguiente) {
        btnSiguiente.style.display = (paso < totalPasos) ? 'block' : 'none';
    }

    if (btnCrear) {
        btnCrear.style.display = (paso === totalPasos) ? 'block' : 'none';
    }

    /* Rellenar resumen en paso 4 */
    if (paso === totalPasos) {
        rellenarResumen();
    }
};

var validarPasoActual = function() {
    if (pasoActual === 1) {
        var selectProp = document.getElementById('nuevoPropiedadId');
        if (!selectProp || !selectProp.value) {
            alert('Selecciona una propiedad');
            return false;
        }
        datosNuevoAlquiler.id_propiedad = selectProp.value;
        return true;
    }

    if (pasoActual === 2) {
        var selectInq = document.getElementById('nuevoInquilinoId');
        if (!selectInq || !selectInq.value) {
            alert('Selecciona un inquilino');
            return false;
        }
        datosNuevoAlquiler.id_inquilino = selectInq.value;
        return true;
    }

    if (pasoActual === 3) {
        var fechaInicio = document.getElementById('nuevoFechaInicio');
        var precio = document.getElementById('nuevoPrecio');

        if (!fechaInicio || !fechaInicio.value) {
            alert('Especifica la fecha de inicio');
            return false;
        }

        if (!precio || !precio.value || parseFloat(precio.value) <= 0) {
            alert('Especifica un precio válido');
            return false;
        }

        datosNuevoAlquiler.fecha_inicio = fechaInicio.value;
        datosNuevoAlquiler.fecha_fin = document.getElementById('nuevoFechaFin').value || null;
        datosNuevoAlquiler.precio = precio.value;
        return true;
    }

    return true;
};

var rellenarResumen = function() {
    var selectProp = document.getElementById('nuevoPropiedadId');
    var selectInq = document.getElementById('nuevoInquilinoId');
    var fechaInicio = document.getElementById('nuevoFechaInicio');
    var fechaFin = document.getElementById('nuevoFechaFin');
    var precio = document.getElementById('nuevoPrecio');

    var propText = selectProp ? selectProp.options[selectProp.selectedIndex].text : '—';
    var inqText = selectInq ? selectInq.options[selectInq.selectedIndex].text : '—';

    if (document.getElementById('resumenPropiedad')) {
        document.getElementById('resumenPropiedad').textContent = propText;
    }
    if (document.getElementById('resumenInquilino')) {
        document.getElementById('resumenInquilino').textContent = inqText;
    }
    if (document.getElementById('resumenInicio')) {
        document.getElementById('resumenInicio').textContent = fechaInicio && fechaInicio.value ? fechaInicio.value : '—';
    }
    if (document.getElementById('resumenFin')) {
        document.getElementById('resumenFin').textContent = fechaFin && fechaFin.value ? fechaFin.value : 'Sin especificar';
    }
    if (document.getElementById('resumenPrecio')) {
        document.getElementById('resumenPrecio').textContent = precio && precio.value ? 
            '€ ' + parseFloat(precio.value).toFixed(2) : '€ 0.00';
    }
    if (document.getElementById('resumenFianza')) {
        var fianza = precio && precio.value ? parseFloat(precio.value) * 2 : 0;
        document.getElementById('resumenFianza').textContent = '€ ' + fianza.toFixed(2);
    }
};

var crearAlquiler = function() {
    if (!datosNuevoAlquiler.id_propiedad || !datosNuevoAlquiler.id_inquilino || 
        !datosNuevoAlquiler.fecha_inicio || !datosNuevoAlquiler.precio) {
        alert('Rellena todos los datos requeridos');
        return;
    }

    var formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('id_propiedad', datosNuevoAlquiler.id_propiedad);
    formData.append('id_inquilino', datosNuevoAlquiler.id_inquilino);
    formData.append('fecha_inicio', datosNuevoAlquiler.fecha_inicio);
    if (datosNuevoAlquiler.fecha_fin) {
        formData.append('fecha_fin', datosNuevoAlquiler.fecha_fin);
    }
    formData.append('precio', datosNuevoAlquiler.precio);

    fetch('/admin/alquileres/crear', {
        method: 'POST',
        body: formData
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al crear: ' + (data.error || 'Unknown'));
        }
    }).catch(function(error) {
        console.error('Error:', error);
        alert('Error al crear el alquiler');
    });
};

/* ── PAGINACION ── */
var asignarEventosPaginacion = function() {
    var btnAnterior = document.getElementById('btnAnteriorAlq');
    var btnSiguiente = document.getElementById('btnSiguienteAlq');
    var btnsPaginas = document.querySelectorAll('[data-pagina]');

    if (btnAnterior) {
        btnAnterior.onclick = function() {
            if (paginaActual > 1) {
                cambiarPagina(paginaActual - 1);
            }
        };
    }

    if (btnSiguiente) {
        btnSiguiente.onclick = function() {
            cambiarPagina(paginaActual + 1);
        };
    }

    btnsPaginas.forEach(function(btn) {
        btn.onclick = function() {
            var pag = parseInt(this.getAttribute('data-pagina'));
            cambiarPagina(pag);
        };
    });
};

var cambiarPagina = function(numPagina) {
    var url = '/admin/alquileres?page=' + numPagina;
    var estado = document.getElementById('selectEstadoAlq') ? 
        document.getElementById('selectEstadoAlq').value : '';
    var propiedad = document.getElementById('selectPropiedadAlq') ? 
        document.getElementById('selectPropiedadAlq').value : '';
    var mes = document.getElementById('selectMesAlq') ? 
        document.getElementById('selectMesAlq').value : '';

    if (estado) url += '&estado=' + encodeURIComponent(estado);
    if (propiedad) url += '&propiedad=' + encodeURIComponent(propiedad);
    if (mes) url += '&mes=' + encodeURIComponent(mes);

    window.location.href = url;
};
