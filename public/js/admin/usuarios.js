/* ========================================
   GESTIÓN DE USUARIOS — SPOTYSTAY
   JavaScript Vanilla — Sin frameworks, sin async/await
   ======================================== */

/* ── Variables globales ── */
var csrfToken = null;
var paginaActual = 1;

/* ── FUNCIONES DE VALIDACIÓN ── */

/* Validar formato de email */
var validarEmail = function(email) {
    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return email !== '' && regex.test(email);
};

/* Validar nombre (mínimo 3 caracteres) */
var validarNombre = function(nombre) {
    return nombre !== '' && nombre.length >= 3;
};

/* Validar teléfono (opcional, pero si está, mínimo 9 caracteres) */
var validarTelefono = function(telefono) {
    if (telefono === '') return true; // Opcional
    return telefono.length >= 9;
};

/* Validar contraseña (mínimo 6 caracteres) */
var validarPassword = function(password) {
    return password !== '' && password.length >= 6;
};

/* ── FUNCIONES DE SWEET ALERTS CON OSO ── */

/* SVG del oso idéntico al login con cartel dinámico */
var crearOsoExito = function() {
    return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <!-- Oso igual que login -->
        <circle class="yeti-part" cx="62" cy="52" r="14" />
        <circle class="yeti-part" cx="138" cy="52" r="14" />
        <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" />
        <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 128 Q100 133 108 128" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle class="hand hand-l" cx="48" cy="180" r="19" />
        <circle class="hand hand-r" cx="152" cy="180" r="19" />
        
        <!-- Cartel éxito sostenido por las manos -->
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#90EE90" stroke="#228B22" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#228B22">✓</text>
    </svg>
    `;
};

/* SVG del oso con cartel de error */
var crearOsoError = function() {
    return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <!-- Oso igual que login -->
        <circle class="yeti-part" cx="62" cy="52" r="14" />
        <circle class="yeti-part" cx="138" cy="52" r="14" />
        <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" />
        <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M92 135 Q100 128 108 135" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle class="hand hand-l" cx="48" cy="180" r="19" />
        <circle class="hand hand-r" cx="152" cy="180" r="19" />
        
        <!-- Cartel error sostenido por las manos -->
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FFB6C1" stroke="#DC143C" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#DC143C">✗</text>
    </svg>
    `;
};

/* SVG del oso con cartel de validación */
var crearOsoValidacion = function() {
    return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
        <!-- Oso igual que login -->
        <circle class="yeti-part" cx="62" cy="52" r="14" />
        <circle class="yeti-part" cx="138" cy="52" r="14" />
        <path class="yeti-part" d="M40,200 Q40,55 100,55 Q160,55 160,200 Z" />
        <path class="suit-jacket" d="M30,200 L170,200 L160,152 Q100,132 40,152 Z" />
        <path class="suit-shirt" d="M100,140 L120,168 L100,200 L80,168 Z" />
        <path class="suit-tie" d="M100,150 L110,168 L100,192 L90,168 Z" />
        <g id="face-group">
            <circle cx="82" cy="105" r="5" fill="#000" />
            <circle cx="118" cy="105" r="5" fill="#000" />
            <path d="M85 105 L115 105" stroke="#000" stroke-width="2.5" fill="none" stroke-linecap="round" />
        </g>
        <circle class="hand hand-l" cx="48" cy="180" r="19" />
        <circle class="hand hand-r" cx="152" cy="180" r="19" />
        
        <!-- Cartel validación sostenido por las manos -->
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FFE4B5" stroke="#FF8C00" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#FF8C00">!</text>
    </svg>
    `;
};

/* Alert de éxito con oso */
var mostrarAlertaExito = function(titulo, mensaje) {
    Swal.fire({
        title: titulo,
        html: mensaje,
        iconHtml: crearOsoExito(),
        customClass: {
            icon: 'oso-icon'
        },
        confirmButtonText: 'Ok',
        confirmButtonColor: '#035498'
    });
};

/* Alert de error con oso */
var mostrarAlertaError = function(titulo, mensaje) {
    Swal.fire({
        title: titulo,
        html: mensaje,
        iconHtml: crearOsoError(),
        customClass: {
            icon: 'oso-icon'
        },
        confirmButtonText: 'Ok',
        confirmButtonColor: '#d9534f'
    });
};

/* Alert de validación fallida */
var mostrarAlertaValidacion = function(mensaje) {
    Swal.fire({
        title: 'Validación',
        html: mensaje,
        iconHtml: crearOsoValidacion(),
        customClass: {
            icon: 'oso-icon'
        },
        confirmButtonText: 'Ok',
        confirmButtonColor: '#f0ad4e'
    });
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
            paginaActual = 1;
            filtrarUsuarios();
        };
    }
    
    if (selectEstado) {
        selectEstado.onchange = function() {
            paginaActual = 1;
            filtrarUsuarios();
        };
    }
    
    if (buscadorUsuarios) {
        // Filtrado en vivo: busca con cada keystroke para LIKE% matching
        buscadorUsuarios.onkeyup = function() {
            paginaActual = 1;
            filtrarUsuarios();
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
              '&q=' + encodeURIComponent(busqueda) +
              '&page=' + paginaActual;
    
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
        
        // Actualizar visualmente los botones de paginación
        if (data.currentPage && data.totalPages) {
            actualizarPaginacion(data.currentPage, data.totalPages);
        }
        
        // Re-vincular los eventos de paginación
        asignarEventosPaginacion();
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
            
            var rolLabel = usuario.rolLabel || 'Sin rol';
            var rolSlug = usuario.rol || 'usuario';
            var estadoLabel = usuario.estado === 'activo' ? 'Activo' : 'Inactivo';
            var estadoClass = activo === '1' ? 'activo' : 'inactivo';
            var propiedades = usuario.propiedades > 0 ? usuario.propiedades : '—';
            
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
                '<td><span class="badge-rol badge-' + rolSlug + '">' + rolLabel + '</span></td>' +
                '<td><span class="badge-estado badge-' + estadoClass + '">' + estadoLabel + '</span></td>' +
                '<td>' + propiedades + '</td>' +
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
    
    // Actualizar footer con información de paginación
    var tablaFooter = document.getElementById('tablaFooter');
    if (tablaFooter && data.from && data.to) {
        var footerText = 'Mostrando ' + data.from + '-' + data.to + ' de ' + data.total + ' usuarios';
        tablaFooter.innerHTML = '<span>' + footerText + '</span>';
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
   Abre el modal con los datos del usuario del backend
   ================================================ */
var abrirModal = function(id) {
    var url = '/admin/usuarios/' + id;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(usuario) {
        if (!usuario) {
            console.error('Usuario no encontrado');
            return;
        }
        
        // Generar avatar
        var nombre = usuario.nombre_usuario || 'Usuario';
        var partes = nombre.split(' ');
        var avatarText = partes[0].charAt(0).toUpperCase() + (partes[1] ? partes[1].charAt(0).toUpperCase() : '');
        
        // Rellenar datos del modal
        document.getElementById('modalAvatar').innerHTML = avatarText;
        document.getElementById('modalAvatar').style.background = '#B8CCE4';
        document.getElementById('modalNombre').textContent = nombre;
        document.getElementById('modalEmail').textContent = usuario.email_usuario || '';
        document.getElementById('modalTelefono').textContent = usuario.telefono_usuario || 'N/A';
        
        // Badge rol
        var badgeRol = document.getElementById('modalBadgeRol');
        var rolLabel = usuario.nombre_rol || 'Sin rol';
        badgeRol.textContent = rolLabel;
        badgeRol.className = 'badge-rol badge-usuario';
        
        // Badge estado
        var badgeEstado = document.getElementById('modalBadgeEstado');
        var estadoLabel = usuario.activo_usuario ? 'Activo' : 'Inactivo';
        badgeEstado.textContent = estadoLabel;
        badgeEstado.className = 'badge-estado badge-' + (usuario.activo_usuario ? 'activo' : 'inactivo');
        
        // Datos
        document.getElementById('dataTelefono').textContent = usuario.telefono_usuario || 'N/A';
        document.getElementById('dataRegistro').textContent = usuario.creado_usuario ? usuario.creado_usuario.substr(0, 10) : 'N/A';
        document.getElementById('dataPropiedades').textContent = '0';
        document.getElementById('dataAcceso').textContent = 'N/A';
        document.getElementById('dataAlquileres').textContent = '0';
        document.getElementById('dataSuscripcion').textContent = 'Estándar';
        
        // Mostrar modal
        document.getElementById('modalOverlay').classList.add('visible');
        document.getElementById('modalPerfil').classList.add('visible');
        
        // Guardar ID actual para botones del modal
        document.getElementById('btnDesactivarUsuario').setAttribute('data-id', usuario.id_usuario);
    })
    .catch(function(error) {
        console.error('Error en fetch abrirModal:', error);
        alert('Error al cargar datos del usuario');
    });
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
            /* Obtener la fila */
            var tr = document.querySelector('tr[data-id="' + id + '"]');
            
            if (tr) {
                /* Alternar clase activo del toggle */
                var toggle = tr.querySelector('.toggle-switch');
                if (toggle) {
                    toggle.classList.toggle('activo');
                }
                
                /* Actualizar data-activo */
                var nuevoActivo = tr.getAttribute('data-activo') === '1' ? '0' : '1';
                tr.setAttribute('data-activo', nuevoActivo);
                
                /* Actualizar badge de estado */
                var badge = tr.querySelector('.badge-estado');
                if (badge) {
                    if (nuevoActivo === '1') {
                        badge.textContent = 'Activo';
                        badge.className = 'badge-estado badge-activo';
                        mostrarAlertaExito('¡Éxito!', 'El usuario ha sido activado');
                    } else {
                        badge.textContent = 'Inactivo';
                        badge.className = 'badge-estado badge-inactivo';
                        mostrarAlertaExito('¡Éxito!', 'El usuario ha sido desactivado');
                    }
                }
                
                /* Alternar clase fila-inactiva */
                tr.classList.toggle('fila-inactiva');
            }
        } else {
            mostrarAlertaError('Error', data.message || 'No se pudo cambiar el estado del usuario');
        }
    })
    .catch(function(error) {
        console.error('Error en fetch toggle-estado:', error);
        mostrarAlertaError('Error', 'No se pudo cambiar el estado del usuario');
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
   Abre el modal de formulario para editar usuario
   ================================================ */
var editarUsuario = function(id) {
    var url = '/admin/usuarios/' + id;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(usuario) {
        if (!usuario) {
            mostrarAlertaError('Error', 'Usuario no encontrado');
            return;
        }
        
        /* Rellenar formulario con datos del usuario */
        document.getElementById('modalFormTitulo').textContent = 'Editar usuario';
        document.getElementById('inputNombre').value = usuario.nombre_usuario || '';
        document.getElementById('inputEmail').value = usuario.email_usuario || '';
        document.getElementById('inputTelefono').value = usuario.telefono_usuario || '';
        document.getElementById('selectRolForm').value = usuario.slug_rol || '';
        document.getElementById('inputPassword').value = '';
        document.getElementById('inputPassword').placeholder = 'Dejar vacío para no cambiar';
        
        /* Guardar ID del usuario en el formulario */
        document.getElementById('formUsuario').setAttribute('data-usuario-id', usuario.id_usuario);
        
        /* Mostrar modal de formulario */
        abrirModalFormUsuario();
        
        /* Cerrar modal de perfil */
        cerrarModal();
    })
    .catch(function(error) {
        console.error('Error en fetch editarUsuario:', error);
        mostrarAlertaError('Error', 'No se pudieron cargar los datos del usuario');
    });
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
    
    // Obtener los valores de filtros actuales
    var selectRol = document.getElementById('selectRol');
    var selectEstado = document.getElementById('selectEstado');
    var buscadorUsuarios = document.getElementById('buscadorUsuarios');
    
    var rol = selectRol ? selectRol.value : '';
    var estado = selectEstado ? selectEstado.value : '';
    var busqueda = buscadorUsuarios ? buscadorUsuarios.value : '';
    
    var url = '/admin/usuarios/filtrar?rol=' + encodeURIComponent(rol) +
              '&estado=' + encodeURIComponent(estado) +
              '&q=' + encodeURIComponent(busqueda) +
              '&page=' + numeroPagina;
    
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
        
        // Actualizar visualmente cuál botón de página está activo
        if (data.currentPage && data.totalPages) {
            actualizarPaginacion(data.currentPage, data.totalPages);
        }
        
        // Re-vincular los eventos de paginación después de actualizar
        asignarEventosPaginacion();
        
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
        // Limpiar formulario
        document.getElementById('formUsuario').reset();
        document.getElementById('formUsuario').removeAttribute('data-usuario-id');
        document.getElementById('modalFormTitulo').textContent = 'Nuevo usuario';
        document.getElementById('inputPassword').placeholder = 'Contraseña';
        
        // Mostrar modal de formulario
        abrirModalFormUsuario();
    };
}

/* ================================================
   FUNCIÓN: abrirModalFormUsuario
   Abre la modal de crear/editar usuario
   ================================================ */
var abrirModalFormUsuario = function() {
    document.getElementById('modalOverlayFormUsuario').classList.add('visible');
    document.getElementById('modalFormUsuario').classList.add('visible');
};

/* ================================================
   FUNCIÓN: cerrarModalFormUsuario
   Cierra la modal de crear/editar usuario
   ================================================ */
var cerrarModalFormUsuario = function() {
    document.getElementById('modalOverlayFormUsuario').classList.remove('visible');
    document.getElementById('modalFormUsuario').classList.remove('visible');
    document.getElementById('formUsuario').reset();
};

/* ================================================
   FUNCIÓN: guardarUsuario
   Guarda o actualiza un usuario con validaciones
   ================================================ */
var guardarUsuario = function() {
    var form = document.getElementById('formUsuario');
    var nombre = document.getElementById('inputNombre').value.trim();
    var email = document.getElementById('inputEmail').value.trim();
    var telefono = document.getElementById('inputTelefono').value.trim();
    var rol = document.getElementById('selectRolForm').value;
    var password = document.getElementById('inputPassword').value.trim();
    var usuarioId = form.getAttribute('data-usuario-id');
    
    /* Validaciones de nombre */
    if (!validarNombre(nombre)) {
        mostrarAlertaValidacion('El nombre es obligatorio y debe tener mínimo 3 caracteres');
        return;
    }
    
    /* Validaciones de email */
    if (!validarEmail(email)) {
        mostrarAlertaValidacion('Por favor introduce un correo electrónico válido');
        return;
    }
    
    /* Validaciones de teléfono */
    if (!validarTelefono(telefono)) {
        mostrarAlertaValidacion('El teléfono debe tener mínimo 9 caracteres');
        return;
    }
    
    /* Validaciones de rol */
    if (!rol || rol === '') {
        mostrarAlertaValidacion('Por favor selecciona un rol');
        return;
    }
    
    /* Si es crear, password es obligatorio */
    if (!usuarioId && !validarPassword(password)) {
        mostrarAlertaValidacion('La contraseña es obligatoria y debe tener mínimo 6 caracteres');
        return;
    }
    
    /* Si es editar y password tiene valor, validar que cumpla requisito mínimo */
    if (usuarioId && password !== '' && !validarPassword(password)) {
        mostrarAlertaValidacion('La contraseña debe tener mínimo 6 caracteres');
        return;
    }
    
    /* Determinar si es crear o editar */
    var url = usuarioId ? '/admin/usuarios/' + usuarioId + '/editar' : '/admin/usuarios/crear';
    
    var datos = {
        nombre: nombre,
        email: email,
        telefono: telefono,
        rol: rol
    };
    
    /* Solo incluir password si no está vacío */
    if (password && password.length > 0) {
        datos.password = password;
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(datos)
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            /* Cerrar modal */
            cerrarModalFormUsuario();
            
            /* Recargar tabla */
            filtrarUsuarios();
            
            /* Mostrar alerta de éxito */
            var mensaje = usuarioId ? 'El usuario ha sido actualizado correctamente' : 'El nuevo usuario ha sido creado correctamente';
            mostrarAlertaExito('¡Éxito!', mensaje);
        } else {
            var errorMsg = data.message || 'No se pudo guardar el usuario';
            if (data.errors) {
                errorMsg = '';
                for (var campo in data.errors) {
                    if (data.errors.hasOwnProperty(campo)) {
                        errorMsg += data.errors[campo][0] + '<br>';
                    }
                }
            }
            mostrarAlertaError('Error', errorMsg);
        }
    })
    .catch(function(error) {
        console.error('Error en fetch guardarUsuario:', error);
        mostrarAlertaError('Error', 'No se pudo guardar el usuario. Intenta de nuevo.');
    });
};

/* ================================================
   EVENTOS MODAL FORMULARIO USUARIO
   ================================================ */
var btnCerrarFormUsuario = document.getElementById('btnCerrarFormUsuario');
if (btnCerrarFormUsuario) {
    btnCerrarFormUsuario.onclick = function() {
        cerrarModalFormUsuario();
    };
}

var btnCancelarFormUsuario = document.getElementById('btnCancelarFormUsuario');
if (btnCancelarFormUsuario) {
    btnCancelarFormUsuario.onclick = function() {
        cerrarModalFormUsuario();
    };
}

var btnGuardarUsuario = document.getElementById('btnGuardarUsuario');
if (btnGuardarUsuario) {
    btnGuardarUsuario.onclick = function(event) {
        event.preventDefault();
        guardarUsuario();
    };
}

var modalOverlayFormUsuario = document.getElementById('modalOverlayFormUsuario');
if (modalOverlayFormUsuario) {
    modalOverlayFormUsuario.onclick = function(event) {
        if (event.target === this) {
            cerrarModalFormUsuario();
        }
    };
}
