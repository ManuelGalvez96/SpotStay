/* ════════════════════════════════════════ */
/* PROPIEDADES ADMIN — JS */
/* ════════════════════════════════════════ */

var csrfToken;
var paginaActual = 1;

/* ── window.onload ── */
window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    asignarEventosFiltros();
    asignarEventosTabla();
    asignarEventosModal();
    asignarEventosPaginacion();
};

/* ── Asignar eventos a filtros ── */
var asignarEventosFiltros = function() {
    var selectEstado = document.getElementById('selectEstado');
    var selectCiudad = document.getElementById('selectCiudad');
    var selectPrecio = document.getElementById('selectPrecio');
    var buscadorPropiedades = document.getElementById('buscadorPropiedades');

    selectEstado.onchange = function() {
        filtrarPropiedades();
    };

    selectCiudad.onchange = function() {
        filtrarPropiedades();
    };

    selectPrecio.onchange = function() {
        filtrarPropiedades();
    };

    buscadorPropiedades.onblur = function() {
        filtrarPropiedades();
    };

    buscadorPropiedades.onkeyup = function() {
        if (buscadorPropiedades.value.length === 0) {
            filtrarPropiedades();
        }
    };
};

/* ── Filtrar propiedades ── */
var filtrarPropiedades = function() {
    var estado = document.getElementById('selectEstado').value;
    var ciudad = document.getElementById('selectCiudad').value;
    var precio = document.getElementById('selectPrecio').value;
    var busqueda = document.getElementById('buscadorPropiedades').value.toLowerCase();

    var url = '/admin/propiedades/filtrar?estado=' + encodeURIComponent(estado) + 
              '&ciudad=' + encodeURIComponent(ciudad) + 
              '&precio=' + encodeURIComponent(precio) + 
              '&q=' + encodeURIComponent(busqueda);

    fetch(url)
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            actualizarTabla(data);
        })
        .catch(function(error) {
            console.error('Error al filtrar propiedades:', error);
        });
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
        
        var fila = '<tr data-id="' + prop.id + '">' +
            '<td>' +
                '<div class="propiedad-celda">' +
                    '<div class="thumb-propiedad" style="background: ' + prop.color + ';"></div>' +
                    '<div>' +
                        '<p class="propiedad-nombre">' + prop.direccion.split(',')[0] + '</p>' +
                        '<p class="propiedad-ciudad">' + prop.ciudad + ', ' + prop.cp + '</p>' +
                    '</div>' +
                '</div>' +
            '</td>' +
            '<td>' +
                '<div style="display: flex; align-items: center; gap: 8px;">' +
                    '<div class="avatar-tabla" style="background: ' + prop.color + '; width: 28px; height: 28px;">' + 
                        prop.arrendadorNombre.split(' ').map(function(n) { return n[0]; }).join('') +
                    '</div>' +
                    '<span style="font-size: 13px;">' + prop.arrendadorNombre + '</span>' +
                '</div>' +
            '</td>' +
            '<td><span class="badge-estado badge-' + prop.estado + '">' + 
                prop.estado.charAt(0).toUpperCase() + prop.estado.slice(1) + '</span></td>' +
            '<td><span class="precio-propiedad">' + prop.precio + '</span></td>' +
            '<td>' + prop.inquilinosActuales + ' / ' + prop.inquilinosTotales + '</td>' +
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
    fetch('/admin/propiedades/' + id)
        .then(function(response) {
            if (!response.ok) throw new Error('Error al cargar propiedad');
            return response.json();
        })
        .then(function(data) {
            var propiedad = data.propiedad;
            var alquileres = data.alquileres || [];

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

            var overlay = document.getElementById('modalOverlay');
            var modal = document.getElementById('modalPropiedad');
            overlay.classList.add('visible');
            modal.classList.add('visible');
        })
        .catch(function(error) {
            console.error('Error al cargar propiedad:', error);
            mostrarAlertaError('Error al cargar detalles de la propiedad');
        });
};

/* ── Cerrar modal ── */
var cerrarModal = function() {
    var overlay = document.getElementById('modalOverlay');
    var modal = document.getElementById('modalPropiedad');
    overlay.classList.remove('visible');
    modal.classList.remove('visible');
};

/* ── Asignar eventos al modal ── */
var asignarEventosModal = function() {
    var btnCerrarModal = document.getElementById('btnCerrarModal');
    var modalOverlay = document.getElementById('modalOverlay');
    var btnDesactivarPropiedad = document.getElementById('btnDesactivarPropiedad');
    var btnEditarPropiedad = document.getElementById('btnEditarPropiedad');
    var btnVerMapa = document.getElementById('btnVerMapa');
    var btnDescargarPDF = document.getElementById('btnDescargarPDF');

    btnCerrarModal.onclick = function() {
        cerrarModal();
    };

    modalOverlay.onclick = function() {
        cerrarModal();
    };

    btnDesactivarPropiedad.onclick = function() {
        var propiedadId = parseInt(document.getElementById('modalDireccion').getAttribute('data-propiedad-id') || '1');
        desactivarPropiedad(propiedadId);
    };

    btnEditarPropiedad.onclick = function() {
        console.log('Abrir modal editar propiedad');
    };

    btnVerMapa.onclick = function() {
        console.log('Abrir mapa');
    };

    btnDescargarPDF.onclick = function() {
        console.log('Descargar PDF del contrato');
    };
};

/* ── Desactivar propiedad ── */
var desactivarPropiedad = function(id) {
    var url = '/admin/propiedades/' + id + '/desactivar';
    var data = JSON.stringify({ _method: 'PUT' });

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        body: data
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            var row = document.querySelector('tr[data-id="' + id + '"]');
            if (row) {
                row.classList.add('fila-inactiva');
            }
            cerrarModal();
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
    });
};

/* ── Confirmar eliminar ── */
var confirmarEliminar = function(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta propiedad?')) {
        var url = '/admin/propiedades/' + id;

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                var row = document.querySelector('tr[data-id="' + id + '"]');
                if (row) {
                    row.parentNode.removeChild(row);
                }
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
    }
};

/* ── Asignar eventos a paginación ── */
var asignarEventosPaginacion = function() {
    var btnAnterior = document.getElementById('btnAnterior');
    var btnSiguiente = document.getElementById('btnSiguiente');
    var botonesPagina = document.querySelectorAll('.pag-numero');

    btnAnterior.onclick = function() {
        if (paginaActual > 1) {
            cambiarPagina(paginaActual - 1);
        }
    };

    btnSiguiente.onclick = function() {
        cambiarPagina(paginaActual + 1);
    };

    for (var i = 0; i < botonesPagina.length; i++) {
        botonesPagina[i].onclick = function() {
            var pagina = parseInt(this.getAttribute('data-pagina'));
            cambiarPagina(pagina);
        };
    }
};

/* ── Cambiar página ── */
var cambiarPagina = function(numero) {
    paginaActual = numero;

    var url = '/admin/propiedades?pagina=' + numero;

    fetch(url)
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            actualizarTabla(data);
            actualizarPaginacion(numero, data.totalPaginas || 3);
            var tabla = document.getElementById('tablaPropiedades');
            tabla.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
};

/* ── Actualizar paginación ── */
var actualizarPaginacion = function(paginaActiva, totalPaginas) {
    var botonesPagina = document.querySelectorAll('.pag-numero');
    for (var i = 0; i < botonesPagina.length; i++) {
        var pagina = parseInt(botonesPagina[i].getAttribute('data-pagina'));
        if (pagina === paginaActiva) {
            botonesPagina[i].classList.add('activo');
        } else {
            botonesPagina[i].classList.remove('activo');
        }
    }
};

/* ── Botón añadir propiedad ── */
document.getElementById('btnAniadirPropiedad').onclick = function() {
    console.log('Abrir modal nueva propiedad');
};

/* ── Botón exportar ── */
document.getElementById('btnExportar').onclick = function() {
    window.location.href = '/admin/propiedades/exportar';
};

/* ── Función editarPropiedad (placeholder) ── */
var editarPropiedad = function(id) {
    console.log('Editar propiedad ' + id);
};
