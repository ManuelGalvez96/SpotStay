/* ════════════════════════════════════════ */
/* PROPIEDADES ADMIN — JS */
/* ════════════════════════════════════════ */

var csrfToken;
var paginaActual = 1;

/* ── Datos hardcodeados de las 10 propiedades ── */
var dataPropiedades = {
    1: {
        id: 1,
        direccion: 'Calle Mayor 14',
        ciudad: 'Madrid',
        cp: '28001',
        estado: 'alquilada',
        precio: '$1.200/mes',
        habitaciones: '3',
        banos: '1',
        tamano: '75 m²',
        planta: '2ª',
        publicada: '15 ene 2025',
        actualizacion: '10 abr 2025',
        visitas: '47',
        favoritos: '12',
        alquilerBase: '$1.200/mes',
        fianza: '$2.400',
        agua: '$30/mes',
        electricidad: '$50/mes',
        gas: '$25/mes',
        comunidad: '$40/mes',
        total: '$1.345/mes',
        arrendadorNombre: 'Carlos García',
        arrendadorEmail: 'carlos.garcia@email.com',
        arrendadorTelefono: '+34 612 345 678',
        arrendadorColor: '#B8CCE4',
        inquilinosActuales: 2,
        inquilinosTotales: 3,
        inquilinos: [
            { nombre: 'Laura Martínez', email: 'laura@email.com', desde: 'enero 2025', color: '#A8D5BF', iniciales: 'LM' },
            { nombre: 'Pedro Molina', email: 'pedro@email.com', desde: 'febrero 2025', color: '#D7EAF9', iniciales: 'PM' }
        ],
        imagen: null
    },
    2: {
        id: 2,
        direccion: 'Gran Vía 22',
        ciudad: 'Madrid',
        cp: '28013',
        estado: 'publicada',
        precio: '$980/mes',
        habitaciones: '2',
        banos: '1',
        tamano: '55 m²',
        planta: '1ª',
        publicada: '22 feb 2025',
        actualizacion: '5 abr 2025',
        visitas: '31',
        favoritos: '8',
        alquilerBase: '$980/mes',
        fianza: '$1.960',
        agua: '$25/mes',
        electricidad: '$40/mes',
        gas: '$20/mes',
        comunidad: '$35/mes',
        total: '$1.100/mes',
        arrendadorNombre: 'Ana Torres',
        arrendadorEmail: 'ana.torres@email.com',
        arrendadorTelefono: '+34 623 456 789',
        arrendadorColor: '#A8D5BF',
        inquilinosActuales: 0,
        inquilinosTotales: 2,
        inquilinos: [],
        imagen: null
    },
    3: {
        id: 3,
        direccion: 'Av. Diagonal 88',
        ciudad: 'Barcelona',
        cp: '08008',
        estado: 'alquilada',
        precio: '$1.500/mes',
        habitaciones: '3',
        banos: '2',
        tamano: '85 m²',
        planta: '3ª',
        publicada: '10 dic 2024',
        actualizacion: '8 abr 2025',
        visitas: '62',
        favoritos: '18',
        alquilerBase: '$1.500/mes',
        fianza: '$3.000',
        agua: '$35/mes',
        electricidad: '$60/mes',
        gas: '$30/mes',
        comunidad: '$50/mes',
        total: '$1.675/mes',
        arrendadorNombre: 'Elena Vargas',
        arrendadorEmail: 'elena.vargas@email.com',
        arrendadorTelefono: '+34 634 567 890',
        arrendadorColor: '#F9E4A0',
        inquilinosActuales: 1,
        inquilinosTotales: 1,
        inquilinos: [
            { nombre: 'Juan Sáenz', email: 'juan@email.com', desde: 'marzo 2025', color: '#D7EAF9', iniciales: 'JS' }
        ],
        imagen: null
    },
    4: {
        id: 4,
        direccion: 'Paseo de Gracia 5',
        ciudad: 'Barcelona',
        cp: '08007',
        estado: 'publicada',
        precio: '$2.200/mes',
        habitaciones: '4',
        banos: '2',
        tamano: '120 m²',
        planta: '4ª',
        publicada: '5 mar 2025',
        actualizacion: '1 abr 2025',
        visitas: '89',
        favoritos: '25',
        alquilerBase: '$2.200/mes',
        fianza: '$4.400',
        agua: '$40/mes',
        electricidad: '$75/mes',
        gas: '$35/mes',
        comunidad: '$60/mes',
        total: '$2.410/mes',
        arrendadorNombre: 'Roberto Mora',
        arrendadorEmail: 'roberto.mora@email.com',
        arrendadorTelefono: '+34 645 678 901',
        arrendadorColor: '#FFD5CC',
        inquilinosActuales: 0,
        inquilinosTotales: 4,
        inquilinos: [],
        imagen: null
    },
    5: {
        id: 5,
        direccion: 'Calle Serrano 47',
        ciudad: 'Madrid',
        cp: '28001',
        estado: 'alquilada',
        precio: '$1.800/mes',
        habitaciones: '3',
        banos: '1',
        tamano: '80 m²',
        planta: '5ª',
        publicada: '18 ene 2025',
        actualizacion: '12 abr 2025',
        visitas: '54',
        favoritos: '15',
        alquilerBase: '$1.800/mes',
        fianza: '$3.600',
        agua: '$32/mes',
        electricidad: '$55/mes',
        gas: '$28/mes',
        comunidad: '$45/mes',
        total: '$1.960/mes',
        arrendadorNombre: 'Carlos García',
        arrendadorEmail: 'carlos.garcia@email.com',
        arrendadorTelefono: '+34 612 345 678',
        arrendadorColor: '#D7EAF9',
        inquilinosActuales: 1,
        inquilinosTotales: 1,
        inquilinos: [
            { nombre: 'Sofía López', email: 'sofia@email.com', desde: 'febrero 2025', color: '#FFD5CC', iniciales: 'SL' }
        ],
        imagen: null
    },
    6: {
        id: 6,
        direccion: 'Calle Colón 8',
        ciudad: 'Valencia',
        cp: '46004',
        estado: 'borrador',
        precio: '$750/mes',
        habitaciones: '2',
        banos: '1',
        tamano: '50 m²',
        planta: 'P',
        publicada: '—',
        actualizacion: '9 abr 2025',
        visitas: '0',
        favoritos: '0',
        alquilerBase: '$750/mes',
        fianza: '$1.500',
        agua: '$20/mes',
        electricidad: '$35/mes',
        gas: '$15/mes',
        comunidad: '$25/mes',
        total: '$845/mes',
        arrendadorNombre: 'Isabel Sanz',
        arrendadorEmail: 'isabel.sanz@email.com',
        arrendadorTelefono: '+34 656 789 012',
        arrendadorColor: '#EDE7F6',
        inquilinosActuales: 0,
        inquilinosTotales: 0,
        inquilinos: [],
        imagen: null
    },
    7: {
        id: 7,
        direccion: 'Alameda de Hércules 3',
        ciudad: 'Sevilla',
        cp: '41002',
        estado: 'publicada',
        precio: '$650/mes',
        habitaciones: '2',
        banos: '1',
        tamano: '60 m²',
        planta: '2ª',
        publicada: '1 mar 2025',
        actualizacion: '6 abr 2025',
        visitas: '28',
        favoritos: '6',
        alquilerBase: '$650/mes',
        fianza: '$1.300',
        agua: '$22/mes',
        electricidad: '$38/mes',
        gas: '$18/mes',
        comunidad: '$30/mes',
        total: '$758/mes',
        arrendadorNombre: 'Diego Guerrero',
        arrendadorEmail: 'diego.guerrero@email.com',
        arrendadorTelefono: '+34 667 890 123',
        arrendadorColor: '#D5F5E3',
        inquilinosActuales: 0,
        inquilinosTotales: 2,
        inquilinos: [],
        imagen: null
    },
    8: {
        id: 8,
        direccion: 'Gran Vía 45',
        ciudad: 'Bilbao',
        cp: '48001',
        estado: 'inactiva',
        precio: '$900/mes',
        habitaciones: '2',
        banos: '1',
        tamano: '65 m²',
        planta: '3ª',
        publicada: '20 nov 2024',
        actualizacion: '2 feb 2025',
        visitas: '92',
        favoritos: '21',
        alquilerBase: '$900/mes',
        fianza: '$1.800',
        agua: '$26/mes',
        electricidad: '$42/mes',
        gas: '$22/mes',
        comunidad: '$33/mes',
        total: '$1.023/mes',
        arrendadorNombre: 'Miguel Fdez.',
        arrendadorEmail: 'miguel.fdez@email.com',
        arrendadorTelefono: '+34 678 901 234',
        arrendadorColor: '#FAD7D7',
        inquilinosActuales: 0,
        inquilinosTotales: 0,
        inquilinos: [],
        imagen: null
    },
    9: {
        id: 9,
        direccion: 'Calle Pelai 12',
        ciudad: 'Barcelona',
        cp: '08001',
        estado: 'alquilada',
        precio: '$1.100/mes',
        habitaciones: '2',
        banos: '1',
        tamano: '70 m²',
        planta: '1ª',
        publicada: '8 ene 2025',
        actualizacion: '7 abr 2025',
        visitas: '71',
        favoritos: '19',
        alquilerBase: '$1.100/mes',
        fianza: '$2.200',
        agua: '$28/mes',
        electricidad: '$48/mes',
        gas: '$24/mes',
        comunidad: '$38/mes',
        total: '$1.238/mes',
        arrendadorNombre: 'Elena Vargas',
        arrendadorEmail: 'elena.vargas@email.com',
        arrendadorTelefono: '+34 634 567 890',
        arrendadorColor: '#CCE5FF',
        inquilinosActuales: 2,
        inquilinosTotales: 2,
        inquilinos: [
            { nombre: 'Patricia Ruiz', email: 'patricia@email.com', desde: 'enero 2025', color: '#A8D5BF', iniciales: 'PR' },
            { nombre: 'Marcos Gómez', email: 'marcos@email.com', desde: 'enero 2025', color: '#F9E4A0', iniciales: 'MG' }
        ],
        imagen: null
    },
    10: {
        id: 10,
        direccion: 'Calle Larios 7',
        ciudad: 'Málaga',
        cp: '29005',
        estado: 'publicada',
        precio: '$820/mes',
        habitaciones: '2',
        banos: '1',
        tamano: '62 m²',
        planta: '2ª',
        publicada: '14 feb 2025',
        actualizacion: '3 abr 2025',
        visitas: '43',
        favoritos: '11',
        alquilerBase: '$820/mes',
        fianza: '$1.640',
        agua: '$24/mes',
        electricidad: '$42/mes',
        gas: '$20/mes',
        comunidad: '$32/mes',
        total: '$938/mes',
        arrendadorNombre: 'Roberto Mora',
        arrendadorEmail: 'roberto.mora@email.com',
        arrendadorTelefono: '+34 645 678 901',
        arrendadorColor: '#FDE8C8',
        inquilinosActuales: 0,
        inquilinosTotales: 3,
        inquilinos: [],
        imagen: null
    }
};

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
    var propiedad = dataPropiedades[id];
    if (!propiedad) return;

    document.getElementById('modalDireccion').textContent = propiedad.direccion + ', ' + propiedad.ciudad;
    document.getElementById('dataPrecio').textContent = propiedad.precio;
    document.getElementById('dataCiudad').textContent = propiedad.ciudad;
    document.getElementById('dataCP').textContent = propiedad.cp;
    document.getElementById('dataDireccion').textContent = propiedad.direccion;
    document.getElementById('dataHabitaciones').textContent = propiedad.habitaciones;
    document.getElementById('dataBanos').textContent = propiedad.banos;
    document.getElementById('dataTamano').textContent = propiedad.tamano;
    document.getElementById('dataPlanta').textContent = propiedad.planta;
    document.getElementById('dataPublicada').textContent = propiedad.publicada;
    document.getElementById('dataActualizacion').textContent = propiedad.actualizacion;
    document.getElementById('dataVisitas').textContent = propiedad.visitas;
    document.getElementById('dataFavoritos').textContent = propiedad.favoritos;

    document.getElementById('dataAlquiler').textContent = propiedad.alquilerBase;
    document.getElementById('dataFianza').textContent = propiedad.fianza;
    document.getElementById('dataAgua').textContent = propiedad.agua;
    document.getElementById('dataElectricidad').textContent = propiedad.electricidad;
    document.getElementById('dataGas').textContent = propiedad.gas;
    document.getElementById('dataComunidad').textContent = propiedad.comunidad;
    document.getElementById('dataTotalEstimado').textContent = 'Total estimado: ' + propiedad.total;

    var avatarArrendador = document.getElementById('avatarArrendador');
    avatarArrendador.style.background = propiedad.arrendadorColor;
    avatarArrendador.textContent = propiedad.arrendadorNombre.split(' ').map(function(n) { return n[0]; }).join('');
    
    document.getElementById('nombreArrendador').textContent = propiedad.arrendadorNombre;
    document.getElementById('emailArrendador').textContent = propiedad.arrendadorEmail;
    document.getElementById('telefonoArrendador').textContent = propiedad.arrendadorTelefono;
    document.getElementById('linkPerfilArrendador').href = '#perfil';

    var avatarGestor = document.getElementById('avatarGestor');
    avatarGestor.style.background = propiedad.arrendadorColor;
    avatarGestor.textContent = propiedad.arrendadorNombre.split(' ').map(function(n) { return n[0]; }).join('');
    document.getElementById('nombreGestor').textContent = propiedad.arrendadorNombre;

    document.getElementById('labelInquilinos').textContent = 'INQUILINOS ACTUALES (' + propiedad.inquilinosActuales + '/' + propiedad.inquilinosTotales + ')';

    var listaInquilinos = document.getElementById('listaInquilinos');
    listaInquilinos.innerHTML = '';
    for (var i = 0; i < propiedad.inquilinos.length; i++) {
        var inquilino = propiedad.inquilinos[i];
        var itemHTML = '<div class="inquilino-item">' +
            '<div class="avatar-tabla" style="background: ' + inquilino.color + ';">' + inquilino.iniciales + '</div>' +
            '<div>' +
                '<p style="font-weight: 600; font-size: 13px; margin: 0;">' + inquilino.nombre + '</p>' +
                '<p style="font-size: 12px; color: #6B7280; margin: 0;">' + inquilino.email + '</p>' +
                '<p style="font-size: 12px; color: #6B7280; margin: 0;">Desde: ' + inquilino.desde + '</p>' +
            '</div>' +
            '<span class="badge-estado badge-activo" style="margin-left: auto;">Activo</span>' +
            '</div>';
        listaInquilinos.innerHTML += itemHTML;
    }

    var badgeEstado = document.getElementById('modalBadgeEstado');
    badgeEstado.className = 'badge-estado badge-' + propiedad.estado;
    badgeEstado.textContent = propiedad.estado.charAt(0).toUpperCase() + propiedad.estado.slice(1);

    var overlay = document.getElementById('modalOverlay');
    var modal = document.getElementById('modalPropiedad');
    overlay.classList.add('visible');
    modal.classList.add('visible');
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
