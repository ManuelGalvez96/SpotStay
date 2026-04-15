/* ========================================
   GESTIÓN DE USUARIOS — SPOTYSTAY
   JavaScript Vanilla — Sin frameworks, sin async/await
   ======================================== */

/* ── Variables globales ── */
var csrfToken = null;
var paginaActual = 1;

/* ── Objeto con datos de usuarios hardcodeados ── */
var usuariosData = {
    1: { nombre: 'Carlos García', email: 'carlos.garcia@email.com', telefono: '+34 612 345 678', rol: 'arrendador', estado: 'Activo', registro: '12 ene 2025', propiedades: 3, acceso: 'hace 2h', alquileres: 5, suscripcion: 'Premium', avatar: '#B8CCE4', avatarText: 'CG' },
    2: { nombre: 'Laura Martínez', email: 'laura.martinez@email.com', telefono: '+34 623 456 789', rol: 'inquilino', estado: 'Activo', registro: '08 ene 2025', propiedades: 0, acceso: 'hace 1h', alquileres: 2, suscripcion: 'Estándar', avatar: '#A8D5BF', avatarText: 'LM' },
    3: { nombre: 'Sofía Rodríguez', email: 'sofia.rodriguez@email.com', telefono: '+34 634 567 890', rol: 'arrendador', estado: 'Inactivo', registro: '15 dic 2024', propiedades: 1, acceso: 'hace 5 días', alquileres: 1, suscripcion: 'Básico', avatar: '#F9E4A0', avatarText: 'SR' },
    4: { nombre: 'Pedro Molina', email: 'pedro.molina@email.com', telefono: '+34 645 678 901', rol: 'gestor', estado: 'Activo', registro: '20 dic 2024', propiedades: 0, acceso: 'hace 30min', alquileres: 0, suscripcion: 'Gestor', avatar: '#E8D5F0', avatarText: 'PM' },
    5: { nombre: 'Ana Torres', email: 'ana.torres@email.com', telefono: '+34 656 789 012', rol: 'miembro', estado: 'Activo', registro: '03 ene 2025', propiedades: 0, acceso: 'hace 3h', alquileres: 0, suscripcion: 'Estándar', avatar: '#FFD5CC', avatarText: 'AT' },
    6: { nombre: 'Miguel Fernández', email: 'miguel.fernandez@email.com', telefono: '+34 667 890 123', rol: 'admin', estado: 'Activo', registro: '01 ene 2025', propiedades: 0, acceso: 'hace 15min', alquileres: 0, suscripcion: 'Admin', avatar: '#CCE5FF', avatarText: 'MF' },
    7: { nombre: 'Elena Vargas', email: 'elena.vargas@email.com', telefono: '+34 678 901 234', rol: 'arrendador', estado: 'Activo', registro: '18 nov 2024', propiedades: 2, acceso: 'hace 1 día', alquileres: 3, suscripcion: 'Premium', avatar: '#D5F5E3', avatarText: 'EV' },
    8: { nombre: 'Javier Ruiz', email: 'javier.ruiz@email.com', telefono: '+34 689 012 345', rol: 'inquilino', estado: 'Inactivo', registro: '05 dic 2024', propiedades: 0, acceso: 'hace 1 mes', alquileres: 1, suscripcion: 'Básico', avatar: '#FAD7D7', avatarText: 'JR' },
    9: { nombre: 'Carmen López', email: 'carmen.lopez@email.com', telefono: '+34 690 123 456', rol: 'miembro', estado: 'Activo', registro: '10 ene 2025', propiedades: 0, acceso: 'hace 4h', alquileres: 0, suscripcion: 'Estándar', avatar: '#D7EAF9', avatarText: 'CL' },
    10: { nombre: 'Roberto Mora', email: 'roberto.mora@email.com', telefono: '+34 601 234 567', rol: 'arrendador', estado: 'Activo', registro: '22 oct 2024', propiedades: 5, acceso: 'ayer', alquileres: 8, suscripcion: 'Premium', avatar: '#FDE8C8', avatarText: 'RM' }
};

/* ── window.onload ── */
window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    
    asignarEventosFiltros();
    asignarEventosTabla();
    asignarEventosModal();
    asignarEventosPaginacion();
};

/* ================================================
   FUNCIÓN: asignarEventosFiltros
   Asigna eventos a los filtros y buscador
   ================================================ */
function asignarEventosFiltros() {
    var selectRol = document.getElementById('selectRol');
    var selectEstado = document.getElementById('selectEstado');
    var buscadorUsuarios = document.getElementById('buscadorUsuarios');
    
    if (selectRol) {
        selectRol.onchange = function() {
            filtrarUsuarios();
        };
    }
    
    if (selectEstado) {
        selectEstado.onchange = function() {
            filtrarUsuarios();
        };
    }
    
    if (buscadorUsuarios) {
        buscadorUsuarios.onblur = function() {
            filtrarUsuarios();
        };
        
        buscadorUsuarios.onkeyup = function() {
            if (this.value.length === 0) {
                filtrarUsuarios();
            }
        };
    }
}

/* ================================================
   FUNCIÓN: filtrarUsuarios
   Recoge valores de filtros y hace fetch
   ================================================ */
var filtrarUsuarios = function() {
    var selectRol = document.getElementById('selectRol');
    var selectEstado = document.getElementById('selectEstado');
    var buscadorUsuarios = document.getElementById('buscadorUsuarios');
    
    var rol = selectRol ? selectRol.value : '';
    var estado = selectEstado ? selectEstado.value : '';
    var busqueda = buscadorUsuarios ? buscadorUsuarios.value : '';
    
    var url = '/admin/usuarios/filtrar?rol=' + encodeURIComponent(rol) +
              '&estado=' + encodeURIComponent(estado) +
              '&q=' + encodeURIComponent(busqueda);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        actualizarTabla(data);
    })
    .catch(function(error) {
        console.error('Error en fetch filtrar:', error);
    });
};

/* ================================================
   FUNCIÓN: actualizarTabla
   Actualiza las filas de la tabla con nuevos datos
   ================================================ */
var actualizarTabla = function(data) {
    var tbody = document.getElementById('tbodyUsuarios');
    
    if (!tbody) {
        return;
    }
    
    // Limpiar tbody
    tbody.innerHTML = '';
    
    // Recorrer usuarios y crear filas
    if (data.usuarios && data.usuarios.length > 0) {
        for (var i = 0; i < data.usuarios.length; i++) {
            var usuario = data.usuarios[i];
            var activo = usuario.estado === 'activo' ? '1' : '0';
            var inactivaClass = activo === '0' ? 'class="fila-inactiva"' : '';
            
            var html = '<tr data-id="' + usuario.id + '" data-activo="' + activo + '" ' + inactivaClass + '>' +
                '<td>' +
                    '<div class="usuario-celda">' +
                        '<div class="avatar-tabla" style="background: ' + usuario.avatarColor + ';">' + usuario.avatarText + '</div>' +
                        '<div>' +
                            '<p class="usuario-nombre">' + usuario.nombre + '</p>' +
                            '<p class="usuario-email">' + usuario.email + '</p>' +
                        '</div>' +
                    '</div>' +
                '</td>' +
                '<td><span class="badge-rol badge-' + usuario.rol + '">' + usuario.rolLabel + '</span></td>' +
                '<td><span class="badge-estado badge-' + (usuario.estado === 'activo' ? 'activo' : 'inactivo') + '">' + (usuario.estado === 'activo' ? 'Activo' : 'Inactivo') + '</span></td>' +
                '<td>' + (usuario.propiedades > 0 ? usuario.propiedades : '—') + '</td>' +
                '<td>' + usuario.fechaRegistro + '</td>' +
                '<td>' +
                    '<div class="acciones-tabla">' +
                        '<button class="btn-accion btn-ver" data-id="' + usuario.id + '" title="Ver perfil">' +
                            '<i class="bi bi-eye"></i>' +
                        '</button>' +
                        '<button class="btn-accion btn-editar" data-id="' + usuario.id + '" title="Editar">' +
                            '<i class="bi bi-pencil"></i>' +
                        '</button>' +
                        '<div class="toggle-switch ' + (activo === '1' ? 'activo' : '') + '" data-id="' + usuario.id + '">' +
                            '<div class="toggle-circulo"></div>' +
                        '</div>' +
                    '</div>' +
                '</td>' +
            '</tr>';
            
            tbody.innerHTML += html;
        }
    }
    
    // Actualizar contador
    var contadorResultados = document.getElementById('contadorResultados');
    if (contadorResultados) {
        contadorResultados.textContent = data.total + ' usuarios encontrados';
    }
    
    // Reasignar eventos a los nuevos botones
    asignarEventosTabla();
};

/* ================================================
   FUNCIÓN: asignarEventosTabla
   Asigna eventos a botones y toggles de la tabla
   ================================================ */
function asignarEventosTabla() {
    var botonesVer = document.querySelectorAll('.btn-ver');
    var botonesEditar = document.querySelectorAll('.btn-editar');
    var toggles = document.querySelectorAll('.toggle-switch');
    
    // Asignar onclick a botones Ver
    for (var i = 0; i < botonesVer.length; i++) {
        var btnVer = botonesVer[i];
        btnVer.onclick = function(event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            abrirModal(id);
        };
    }
    
    // Asignar onclick a botones Editar
    for (var i = 0; i < botonesEditar.length; i++) {
        var btnEditar = botonesEditar[i];
        btnEditar.onclick = function(event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            editarUsuario(id);
        };
    }
    
    // Asignar onclick a toggles
    for (var i = 0; i < toggles.length; i++) {
        var toggle = toggles[i];
        toggle.onclick = function(event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            toggleEstado(id);
        };
    }
}

/* ================================================
   FUNCIÓN: abrirModal
   Abre el modal con los datos del usuario
   ================================================ */
var abrirModal = function(id) {
    var usuario = usuariosData[id];
    
    if (!usuario) {
        console.error('Usuario no encontrado:', id);
        return;
    }
    
    // Rellenar datos del modal
    document.getElementById('modalAvatar').innerHTML = usuario.avatarText;
    document.getElementById('modalAvatar').style.background = usuario.avatar;
    document.getElementById('modalNombre').textContent = usuario.nombre;
    document.getElementById('modalEmail').textContent = usuario.email;
    document.getElementById('modalTelefono').textContent = usuario.telefono;
    
    // Badge rol
    var badgeRol = document.getElementById('modalBadgeRol');
    var rolLabel = usuario.rol.charAt(0).toUpperCase() + usuario.rol.slice(1);
    badgeRol.textContent = rolLabel;
    badgeRol.className = 'badge-rol badge-' + usuario.rol;
    
    // Badge estado
    var badgeEstado = document.getElementById('modalBadgeEstado');
    badgeEstado.textContent = usuario.estado;
    badgeEstado.className = 'badge-estado badge-' + (usuario.estado === 'Activo' ? 'activo' : 'inactivo');
    
    // Datos
    document.getElementById('dataTelefono').textContent = usuario.telefono;
    document.getElementById('dataRegistro').textContent = usuario.registro;
    document.getElementById('dataPropiedades').textContent = usuario.propiedades > 0 ? usuario.propiedades : '—';
    document.getElementById('dataAcceso').textContent = usuario.acceso;
    document.getElementById('dataAlquileres').textContent = usuario.alquileres > 0 ? usuario.alquileres : '—';
    document.getElementById('dataSuscripcion').textContent = usuario.suscripcion;
    
    // Mostrar modal
    document.getElementById('modalOverlay').classList.add('visible');
    document.getElementById('modalPerfil').classList.add('visible');
    
    // Guardar ID actual para botones del modal
    document.getElementById('btnDesactivarUsuario').setAttribute('data-id', id);
};

/* ================================================
   FUNCIÓN: cerrarModal
   Cierra el modal de usuario
   ================================================ */
var cerrarModal = function() {
    document.getElementById('modalOverlay').classList.remove('visible');
    document.getElementById('modalPerfil').classList.remove('visible');
};

/* ================================================
   FUNCIÓN: toggleEstado
   Alterna el estado activo/inactivo de un usuario
   ================================================ */
var toggleEstado = function(id) {
    var url = '/admin/usuarios/' + id + '/toggle-estado';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            // Obtener la fila
            var tr = document.querySelector('tr[data-id="' + id + '"]');
            
            if (tr) {
                // Alternar clase activo del toggle
                var toggle = tr.querySelector('.toggle-switch');
                if (toggle) {
                    toggle.classList.toggle('activo');
                }
                
                // Actualizar data-activo
                var nuevoActivo = tr.getAttribute('data-activo') === '1' ? '0' : '1';
                tr.setAttribute('data-activo', nuevoActivo);
                
                // Actualizar badge de estado
                var badge = tr.querySelector('.badge-estado');
                if (badge) {
                    if (nuevoActivo === '1') {
                        badge.textContent = 'Activo';
                        badge.className = 'badge-estado badge-activo';
                    } else {
                        badge.textContent = 'Inactivo';
                        badge.className = 'badge-estado badge-inactivo';
                    }
                }
                
                // Alternar clase fila-inactiva
                tr.classList.toggle('fila-inactiva');
            }
        } else {
            console.error('Error al cambiar estado:', data.message);
        }
    })
    .catch(function(error) {
        console.error('Error en fetch toggle-estado:', error);
    });
};

/* ================================================
   FUNCIÓN: asignarEventosModal
   Asigna eventos a los botones del modal
   ================================================ */
function asignarEventosModal() {
    var btnCerrarModal = document.getElementById('btnCerrarModal');
    var modalOverlay = document.getElementById('modalOverlay');
    var btnDesactivarUsuario = document.getElementById('btnDesactivarUsuario');
    var btnEditarUsuario = document.getElementById('btnEditarUsuario');
    
    // Botón cerrar modal
    if (btnCerrarModal) {
        btnCerrarModal.onclick = function() {
            cerrarModal();
        };
    }
    
    // Cerrar al hacer click en el overlay
    if (modalOverlay) {
        modalOverlay.onclick = function(event) {
            if (event.target === this) {
                cerrarModal();
            }
        };
    }
    
    // Botón desactivar usuario
    if (btnDesactivarUsuario) {
        btnDesactivarUsuario.onclick = function() {
            var id = this.getAttribute('data-id');
            if (id) {
                toggleEstado(id);
                cerrarModal();
            }
        };
    }
    
    // Botón editar usuario
    if (btnEditarUsuario) {
        btnEditarUsuario.onclick = function() {
            var id = document.getElementById('btnDesactivarUsuario').getAttribute('data-id');
            editarUsuario(id);
        };
    }
}

/* ================================================
   FUNCIÓN: editarUsuario
   Placeholder para edición de usuario
   ================================================ */
var editarUsuario = function(id) {
    console.log('Abrir formulario de edición para usuario:', id);
    // Aquí iría la lógica para editar el usuario
    // O redirigir a una página de edición
};

/* ================================================
   FUNCIÓN: asignarEventosPaginacion
   Asigna eventos a los botones de paginación
   ================================================ */
function asignarEventosPaginacion() {
    var btnAnterior = document.getElementById('btnAnterior');
    var btnSiguiente = document.getElementById('btnSiguiente');
    var botonesNumero = document.querySelectorAll('.pag-numero');
    
    // Botón anterior
    if (btnAnterior) {
        btnAnterior.onclick = function(event) {
            event.preventDefault();
            if (paginaActual > 1) {
                cambiarPagina(paginaActual - 1);
            }
        };
    }
    
    // Botón siguiente
    if (btnSiguiente) {
        btnSiguiente.onclick = function(event) {
            event.preventDefault();
            cambiarPagina(paginaActual + 1);
        };
    }
    
    // Botones número de página
    for (var i = 0; i < botonesNumero.length; i++) {
        var btnNum = botonesNumero[i];
        btnNum.onclick = function(event) {
            event.preventDefault();
            var pagina = parseInt(this.getAttribute('data-pagina'));
            cambiarPagina(pagina);
        };
    }
}

/* ================================================
   FUNCIÓN: cambiarPagina
   Cambia la página y actualiza la tabla
   ================================================ */
var cambiarPagina = function(numeroPagina) {
    paginaActual = numeroPagina;
    
    var url = '/admin/usuarios?pagina=' + paginaActual;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        actualizarTabla(data);
        actualizarPaginacion(data.paginaActual, data.totalPaginas);
        
        // Hacer scroll al top de la tabla
        var tabla = document.getElementById('tablaUsuarios');
        if (tabla) {
            tabla.scrollIntoView({ behavior: 'smooth' });
        }
    })
    .catch(function(error) {
        console.error('Error en fetch cambiar página:', error);
    });
};

/* ================================================
   FUNCIÓN: actualizarPaginacion
   Actualiza los botones de paginación
   ================================================ */
var actualizarPaginacion = function(paginaActual, totalPaginas) {
    var botonesNumero = document.querySelectorAll('.pag-numero');
    
    for (var i = 0; i < botonesNumero.length; i++) {
        var btn = botonesNumero[i];
        var pagina = parseInt(btn.getAttribute('data-pagina'));
        
        if (pagina === paginaActual) {
            btn.classList.add('activo');
        } else {
            btn.classList.remove('activo');
        }
    }
};

/* ================================================
   EVENTOS BOTONES PRINCIPALES
   ================================================ */
var btnExportar = document.getElementById('btnExportar');
if (btnExportar) {
    btnExportar.onclick = function(event) {
        event.preventDefault();
        window.location.href = '/admin/usuarios/exportar';
    };
}

var btnNuevoUsuario = document.getElementById('btnNuevoUsuario');
if (btnNuevoUsuario) {
    btnNuevoUsuario.onclick = function(event) {
        event.preventDefault();
        console.log('Abrir formulario nuevo usuario');
        // Aquí iría la lógica para crear un nuevo usuario
    };
}
