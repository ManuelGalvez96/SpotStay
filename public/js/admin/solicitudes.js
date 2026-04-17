/* ===== SOLICITUDES JAVASCRIPT ===== */

var csrfToken;
var solicitudIdActual;

window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    asignarEventosFiltros();
    asignarEventosTabla();
    asignarEventosModal();
    asignarEventosPaginacion();
};

var asignarEventosFiltros = function() {
    var buscador = document.getElementById('buscadorSolicitudes');
    var selectEstado = document.getElementById('selectEstadoSol');
    var selectCiudad = document.getElementById('selectCiudadSol');

    if (buscador) {
        buscador.onkeyup = function() {
            if (this.value.length === 0 || this.value.length >= 3) {
                filtrarSolicitudes();
            }
        };
    }

    if (selectEstado) {
        selectEstado.onchange = function() {
            filtrarSolicitudes();
        };
    }

    if (selectCiudad) {
        selectCiudad.onchange = function() {
            filtrarSolicitudes();
        };
    }
};

var asignarEventosTabla = function() {
    var botonesAprobar = document.querySelectorAll('.btn-aprobar-sol');
    var i;
    for (i = 0; i < botonesAprobar.length; i++) {
        botonesAprobar[i].onclick = function(evento) {
            evento.preventDefault();
            var id = this.getAttribute('data-id');
            aprobarSolicitud(id);
        };
    }

    var botonesRechazar = document.querySelectorAll('.btn-rechazar-sol');
    for (i = 0; i < botonesRechazar.length; i++) {
        botonesRechazar[i].onclick = function(evento) {
            evento.preventDefault();
            var id = this.getAttribute('data-id');
            abrirModalRechazar(id);
        };
    }

    var botonesVer = document.querySelectorAll('.btn-ver-sol');
    for (i = 0; i < botonesVer.length; i++) {
        botonesVer[i].onclick = function(evento) {
            evento.preventDefault();
            var id = this.getAttribute('data-id');
            abrirModal(id);
        };
    }
};

var asignarEventosPaginacion = function() {
    var botonesPage = document.querySelectorAll('#paginacionSolicitudes .btn-paginacion');
    var i;
    for (i = 0; i < botonesPage.length; i++) {
        botonesPage[i].onclick = function(evento) {
            evento.preventDefault();
            var page = this.getAttribute('data-page');
            if (page) {
                cambiarPaginaSol(page);
            }
        };
    }
};

var filtrarSolicitudes = function() {
    var estado = document.getElementById('selectEstadoSol') ? document.getElementById('selectEstadoSol').value : '';
    var ciudad = document.getElementById('selectCiudadSol') ? document.getElementById('selectCiudadSol').value : '';
    var q = document.getElementById('buscadorSolicitudes') ? document.getElementById('buscadorSolicitudes').value : '';
    
    var url = '/admin/solicitudes/filtrar?estado=' + encodeURIComponent(estado) +
              '&ciudad=' + encodeURIComponent(ciudad) +
              '&q=' + encodeURIComponent(q);
    
    fetch(url)
        .then(function(respuesta) {
            return respuesta.json();
        })
        .then(function(datos) {
            actualizarTabla(datos);
            actualizarPaginacion(datos);
            actualizarContador(datos.total);
        })
        .catch(function(error) {
            console.log('Error al filtrar: ', error);
        });
};

var actualizarTabla = function(datos) {
    var tablaBody = document.getElementById('tablaSolicitudes');
    if (!tablaBody) return;

    tablaBody.innerHTML = '';

    if (datos.data && datos.data.length > 0) {
        var i;
        for (i = 0; i < datos.data.length; i++) {
            var solicitud = datos.data[i];
            var partes = solicitud.nombre_usuario.split(' ');
            var iniciales = (partes[0] ? partes[0].charAt(0) : '') + (partes[1] ? partes[1].charAt(0) : '');
            var colores = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#FFD5CC', '#D7EAF9', '#EDE7F6', '#D5F5E3', '#FAD7D7'];
            var color = colores[solicitud.id_solicitud_arrendador % 8];
            var datos_prop = JSON.parse(solicitud.datos_solicitud_arrendador || '{}');
            var fecha = new Date(solicitud.creado_solicitud_arrendador).toLocaleDateString('es-ES');

            var fila = document.createElement('tr');
            fila.className = 'fila-solicitud';
            fila.setAttribute('data-id', solicitud.id_solicitud_arrendador);

            var htmlFila = '<td><div class="usuario-celda">' +
                '<div class="avatar-tabla" style="background:' + color + '">' + iniciales.toUpperCase() + '</div>' +
                '<div class="usuario-info-tabla">' +
                '<span class="usuario-nombre-tabla">' + solicitud.nombre_usuario + '</span>' +
                '<span class="usuario-email-tabla">' + solicitud.email_usuario + '</span>' +
                '</div></div></td>' +
                '<td>' + (datos_prop.ciudad || '—') + '</td>' +
                '<td>' + (datos_prop.direccion || '—') + '</td>' +
                '<td>' + fecha + '</td>' +
                '<td><span class="badge-estado badge-pendiente">Pendiente</span></td>' +
                '<td><div class="acciones-tabla">' +
                '<button class="btn-icono btn-ver-sol" data-id="' + solicitud.id_solicitud_arrendador + '" title="Ver detalles"><i class="bi bi-eye"></i></button>' +
                '<button class="btn-icono btn-aprobar-sol" data-id="' + solicitud.id_solicitud_arrendador + '" title="Aprobar"><i class="bi bi-check-circle"></i></button>' +
                '<button class="btn-icono btn-rechazar-sol" data-id="' + solicitud.id_solicitud_arrendador + '" title="Rechazar"><i class="bi bi-x-circle"></i></button>' +
                '</div></td>';

            fila.innerHTML = htmlFila;
            tablaBody.appendChild(fila);
        }
    } else {
        var fila = document.createElement('tr');
        fila.innerHTML = '<td colspan="6" class="sin-resultados">No hay solicitudes que coincidan con los filtros</td>';
        tablaBody.appendChild(fila);
    }

    asignarEventosTabla();
};

var actualizarPaginacion = function(datos) {
    var paginacion = document.getElementById('paginacionSolicitudes');
    if (!paginacion) return;

    paginacion.innerHTML = '';

    var botIzq = document.createElement('span');
    botIzq.className = 'btn-paginacion ' + (datos.current_page === 1 ? 'deshabilitado' : '');
    botIzq.innerHTML = '<i class="bi bi-chevron-left"></i>';
    if (datos.current_page > 1) {
        botIzq.onclick = function() { cambiarPaginaSol(datos.current_page - 1); };
    }
    paginacion.appendChild(botIzq);

    var j;
    for (j = 1; j <= datos.last_page; j++) {
        var bot = document.createElement('button');
        bot.className = 'btn-paginacion ' + (j === datos.current_page ? 'activo' : '');
        bot.textContent = j;
        bot.setAttribute('data-page', j);
        paginacion.appendChild(bot);
    }

    var botDer = document.createElement('button');
    botDer.className = 'btn-paginacion ' + (datos.current_page === datos.last_page ? 'deshabilitado' : '');
    botDer.innerHTML = '<i class="bi bi-chevron-right"></i>';
    if (datos.current_page < datos.last_page) {
        botDer.onclick = function() { cambiarPaginaSol(datos.current_page + 1); };
    }
    paginacion.appendChild(botDer);

    asignarEventosPaginacion();
};

var actualizarContador = function(total) {
    var el = document.querySelector('.texto-pendientes');
    if (el) {
        el.textContent = total + ' pendientes de revisión';
    }
};

var cambiarPaginaSol = function(pagina) {
    var estado = document.getElementById('selectEstadoSol') ? document.getElementById('selectEstadoSol').value : '';
    var ciudad = document.getElementById('selectCiudadSol') ? document.getElementById('selectCiudadSol').value : '';
    var q = document.getElementById('buscadorSolicitudes') ? document.getElementById('buscadorSolicitudes').value : '';

    var url = '/admin/solicitudes?pagina=' + pagina +
              '&estado=' + encodeURIComponent(estado) +
              '&ciudad=' + encodeURIComponent(ciudad) +
              '&q=' + encodeURIComponent(q);

    fetch(url)
        .then(function(respuesta) {
            return respuesta.json();
        })
        .then(function(datos) {
            actualizarTabla(datos);
            actualizarPaginacion(datos);
            window.scrollTo(0, 0);
        })
        .catch(function(error) {
            console.log('Error al cambiar página: ', error);
        });
};

var abrirModal = function(id) {
    solicitudIdActual = id;
    fetch('/admin/solicitudes/' + id)
        .then(function(respuesta) {
            return respuesta.json();
        })
        .then(function(datos) {
            rellenarModal(datos);
            document.getElementById('modalOverlay').classList.add('activo');
            document.getElementById('modalSolicitud').classList.add('activo');
            document.getElementById('btnAprobarModal').style.display = 'block';
            document.getElementById('btnRechazarModal').style.display = 'block';
            document.getElementById('modalNotas').value = '';
        })
        .catch(function(error) {
            console.log('Error al abrir modal: ', error);
        });
};

var abrirModalRechazar = function(id) {
    solicitudIdActual = id;
    document.getElementById('modalNotas').value = '';
    document.getElementById('modalOverlay').classList.add('activo');
    document.getElementById('modalSolicitud').classList.add('activo');
    document.getElementById('btnAprobarModal').style.display = 'none';
    document.getElementById('btnRechazarModal').style.display = 'block';
};

var rellenarModal = function(datos) {
    var partes = datos.nombre_usuario.split(' ');
    var iniciales = (partes[0] ? partes[0].charAt(0) : '') + (partes[1] ? partes[1].charAt(0) : '');
    var colores = ['#B8CCE4', '#A8D5BF', '#F9E4A0', '#FFD5CC', '#D7EAF9', '#EDE7F6', '#D5F5E3', '#FAD7D7'];
    var color = colores[datos.id_solicitud_arrendador % 8];

    document.getElementById('modalAvatar').style.background = color;
    document.getElementById('modalAvatar').textContent = iniciales.toUpperCase();
    document.getElementById('modalNombre').textContent = datos.nombre_usuario;
    document.getElementById('modalEmail').textContent = datos.email_usuario;
    document.getElementById('modalCiudad').innerHTML = '<i class="bi bi-geo-alt"></i>' + (JSON.parse(datos.datos_solicitud_arrendador || '{}').ciudad || 'No disponible');

    var datosPropiedad = JSON.parse(datos.datos_solicitud_arrendador || '{}');
    var gridElement = document.getElementById('modalDatosPropiedad');
    if (gridElement) {
        gridElement.innerHTML = '';

        var propiedades = [
            { label: 'Dirección', valor: datosPropiedad.direccion || '—' },
            { label: 'Tipo', valor: datosPropiedad.tipo || '—' },
            { label: 'Precio', valor: '$' + (datosPropiedad.precio_estimado || '0') + '/mes' },
            { label: 'Habitaciones', valor: datosPropiedad.habitaciones || '—' },
            { label: 'Baños', valor: datosPropiedad.banos || '—' },
            { label: 'Tamaño', valor: (datosPropiedad.tamano || '0') + ' m²' }
        ];

        var k;
        for (k = 0; k < propiedades.length; k++) {
            var div = document.createElement('div');
            div.innerHTML = '<label>' + propiedades[k].label + '</label><span>' + propiedades[k].valor + '</span>';
            gridElement.appendChild(div);
        }
    }
};

var cerrarModal = function() {
    document.getElementById('modalOverlay').classList.remove('activo');
    document.getElementById('modalSolicitud').classList.remove('activo');
    solicitudIdActual = null;
};

var asignarEventosModal = function() {
    var btnCerrar = document.getElementById('btnCerrarModal');
    var overlay = document.getElementById('modalOverlay');
    var btnAprobar = document.getElementById('btnAprobarModal');
    var btnRechazar = document.getElementById('btnRechazarModal');

    if (btnCerrar) {
        btnCerrar.onclick = function() {
            cerrarModal();
        };
    }

    if (overlay) {
        overlay.onclick = function(evento) {
            if (evento.target === overlay) {
                cerrarModal();
            }
        };
    }

    if (btnAprobar) {
        btnAprobar.onclick = function() {
            aprobarSolicitud(solicitudIdActual);
        };
    }

    if (btnRechazar) {
        btnRechazar.onclick = function() {
            var notas = document.getElementById('modalNotas').value;
            rechazarSolicitud(solicitudIdActual, notas);
        };
    }
};

var aprobarSolicitud = function(id) {
    if (!id) {
        alert('Error: ID de solicitud no disponible');
        return;
    }

    fetch('/admin/solicitudes/' + id + '/aprobar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
        .then(function(respuesta) {
            return respuesta.json();
        })
        .then(function(datos) {
            if (datos.success) {
                cerrarModal();
                location.reload();
            } else {
                alert('Error al aprobar: ' + (datos.error || 'Error desconocido'));
            }
        })
        .catch(function(error) {
            console.log('Error en aprobar: ', error);
            alert('Error al procesar la solicitud');
        });
};

var rechazarSolicitud = function(id, notas) {
    if (!id) {
        alert('Error: ID de solicitud no disponible');
        return;
    }

    fetch('/admin/solicitudes/' + id + '/rechazar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ notas: notas })
    })
        .then(function(respuesta) {
            return respuesta.json();
        })
        .then(function(datos) {
            if (datos.success) {
                cerrarModal();
                location.reload();
            } else {
                alert('Error al rechazar: ' + (datos.error || 'Error desconocido'));
            }
        })
        .catch(function(error) {
            console.log('Error en rechazar: ', error);
            alert('Error al procesar la solicitud');
        });
};

