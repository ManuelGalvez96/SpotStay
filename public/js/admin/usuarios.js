/* ========================================
   GESTIÓN DE USUARIOS — SPOTYSTAY
   JavaScript Vanilla — Sin frameworks, sin async/await
   ======================================== */

/* ── Variables globales ── */
var csrfToken = null;
var paginaActual = 1;

// Flags para inicializar validación solo una vez
var _validacionFormularioInicializada = false;

// Estados de validación de campos
var _valNombre = false;
var _valEmail = false;
var _valTelefono = false; // ahora obligatorio por defecto
var _valRol = false;
var _valPassword = false; // depende de si es crear/editar
var errorNombreUsuario = null;
var errorEmailUsuario = null;
var errorTelefonoUsuario = null;
var errorRolUsuario = null;
var errorPasswordUsuario = null;
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
/* Validar teléfono: obligatorio. Debe empezar con +34, un espacio, y 9-11 dígitos */
var validarTelefono = function(telefono) {
    if (!telefono) return false;
    var regex = /^\+34\s\d{9,11}$/;
    return regex.test(telefono);
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
    if (window.mostrarAlertaAdminExito) {
        window.mostrarAlertaAdminExito(titulo, mensaje);
        return;
    }
    window.alert(mensaje);
};

/* Alert de error con oso */
var mostrarAlertaError = function(titulo, mensaje) {
    if (window.mostrarAlertaAdminError) {
        window.mostrarAlertaAdminError(titulo, mensaje);
        return;
    }
    window.alert(mensaje);
};

/* Alert de validación fallida */
var mostrarAlertaValidacion = function(mensaje) {
    if (window.mostrarAlertaAdminValidacion) {
        window.mostrarAlertaAdminValidacion(mensaje);
        return;
    }
    window.alert(mensaje);
};



/* ── Variables globales para modales Bootstrap ── */
var modalPerfil = null;
var modalFormUsuario = null;

/* ── window.onload ── */
window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    
    /* Inicializar instancias de Bootstrap Modal */
    modalPerfil = new bootstrap.Modal(document.getElementById('modalPerfil'));
    modalFormUsuario = new bootstrap.Modal(document.getElementById('modalFormUsuario'));
    
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
    // Flags para inicializar validación solo una vez
    var _validacionFormularioInicializada = false;

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
            var suscripcionLabel = usuario.suscripcionLabel || 'Sin suscripción';
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
                '<td>' + suscripcionLabel + '</td>' +
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
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
        }
        return response.json();
    })
    .then(function(usuario) {
        console.log('Usuario recibido:', usuario);
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
        badgeRol.className = 'badge bg-info';
        
        // Badge estado
        var badgeEstado = document.getElementById('modalBadgeEstado');
        var estadoLabel = usuario.activo_usuario ? 'Activo' : 'Inactivo';
        badgeEstado.textContent = estadoLabel;
        badgeEstado.className = 'badge ' + (usuario.activo_usuario ? 'bg-success' : 'bg-secondary');
        
        // Datos
        document.getElementById('dataTelefono').textContent = usuario.telefono_usuario || 'N/A';
        document.getElementById('dataRegistro').textContent = usuario.creado_usuario ? usuario.creado_usuario.substr(0, 10) : 'N/A';
        document.getElementById('dataPropiedades').textContent = usuario.total_propiedades || '0';
        document.getElementById('dataAcceso').textContent = 'N/A';
        document.getElementById('dataAlquileres').textContent = usuario.total_alquileres || '0';
        document.getElementById('dataSuscripcion').textContent = usuario.suscripcion || 'Sin suscripción';

        var bloqueCodigoGestor = document.getElementById('bloqueCodigoGestor');
        var dataCodigoGestor = document.getElementById('dataCodigoGestor');
        var esGestor = (usuario.slug_rol || '').toLowerCase() === 'gestor';

        if (bloqueCodigoGestor && dataCodigoGestor) {
            if (esGestor) {
                bloqueCodigoGestor.style.display = 'block';
                dataCodigoGestor.textContent = usuario.codigo_gestor || 'Sin código';
            } else {
                bloqueCodigoGestor.style.display = 'none';
                dataCodigoGestor.textContent = '—';
            }
        }
        
        // Rellenar sección de Propiedades del Usuario
        var listaPropiedades = document.getElementById('listaPropiedades');
        if (listaPropiedades) {
            listaPropiedades.innerHTML = '';
            
            if (usuario.propiedades && usuario.propiedades.length > 0) {
                usuario.propiedades.forEach(function(propiedad) {
                    var estadoBadgeClass = 'bg-success';
                    if (propiedad.estado_propiedad === 'disponible') {
                        estadoBadgeClass = 'bg-warning';
                    } else if (propiedad.estado_propiedad === 'mantenimiento') {
                        estadoBadgeClass = 'bg-danger';
                    }
                    
                    var item = document.createElement('div');
                    item.className = 'list-group-item';
                    item.innerHTML = '<p class="fw-bold mb-1">' + (propiedad.direccion_propiedad || 'Sin dirección') + '</p>' +
                                    '<span class="badge ' + estadoBadgeClass + '">' + (propiedad.estado_propiedad || 'Desconocido') + '</span>' +
                                    '<span class="fw-bold float-end">$' + (propiedad.precio_propiedad || '0') + '/mes</span>';
                    listaPropiedades.appendChild(item);
                });
            } else {
                var item = document.createElement('div');
                item.className = 'list-group-item';
                item.textContent = 'No tiene propiedades registradas';
                listaPropiedades.appendChild(item);

                    limpiarValidacionFormulario();
            }
        }
        
        // Mostrar modal Bootstrap
        modalPerfil.show();
        
        // Guardar ID actual para botones del modal
        document.getElementById('btnDesactivarUsuario').setAttribute('data-id', usuario.id_usuario);
    })
    .catch(function(error) {
        console.error('Error en fetch abrirModal:', error);
        limpiarValidacionFormulario();

        console.error('Error message:', error.message);
        mostrarAlertaError('Error', 'Error al cargar datos del usuario: ' + error.message);
    });
};

/* ================================================
   FUNCIÓN: cerrarModal
   Cierra el modal de usuario
   ================================================ */
var cerrarModal = function() {
    modalPerfil.hide();
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

            /* Actualizar KPIs */
            actualizarKpisUsuarios();
        } else {
            mostrarAlertaError('Error', data.message || 'No se pudo cambiar el estado del usuario ya que tiene contratos y/o propiedades asociadas');
        }
    })
    .catch(function(error) {
        console.error('Error en fetch toggle-estado:', error);
        mostrarAlertaError('Error', 'No se pudo cambiar el estado del usuario ya que tiene contratos y/o propiedades asociadas');
    });
};

/* ================================================
   FUNCIÓN: actualizarKpisUsuarios
   Actualiza los números de KPI dinámicamente
   ================================================ */
var actualizarKpisUsuarios = function() {
    fetch('/admin/usuarios/kpis')
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            /* Actualizar total usuarios */
            var elTotal = document.getElementById('kpiTotalUsuarios');
            if (elTotal) {
                elTotal.textContent = data.totalUsuarios;
            }

            /* Actualizar activos */
            var elActivos = document.getElementById('kpiActivos');
            if (elActivos) {
                elActivos.textContent = data.activos;
            }

            /* Actualizar inactivos */
            var elInactivos = document.getElementById('kpiInactivos');
            if (elInactivos) {
                elInactivos.textContent = data.inactivos;
            }

            /* Actualizar este mes */
            var elMes = document.getElementById('kpiEsteMes');
            if (elMes) {
                elMes.textContent = data.esteMes;
            }
        })
        .catch(function(error) {
            console.error('Error al actualizar KPIs:', error);
        });
};

/* ================================================
   FUNCIÓN: asignarEventosModal
   Asigna eventos a los botones del modal
   ================================================ */
function asignarEventosModal() {
    var btnDesactivarUsuario = document.getElementById('btnDesactivarUsuario');
    var btnEditarUsuario = document.getElementById('btnEditarUsuario');
    
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
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(function(response) {
        var contentType = response.headers.get('content-type') || '';

        if (!response.ok) {
            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
        }

        if (contentType.indexOf('application/json') === -1) {
            return response.text().then(function(texto) {
                if (texto.indexOf('Iniciar Sesión') !== -1 || texto.indexOf('Acceso denegado') !== -1) {
                    throw new Error('Tu sesión ha expirado o no tienes permisos para cargar este usuario');
                }
                throw new Error('La respuesta del servidor no es JSON válido');
            });
        }

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
        document.getElementById('selectSuscripcionForm').value = usuario.id_plan_fk || '';
        document.getElementById('inputPassword').value = '';
        document.getElementById('inputPassword').placeholder = 'Dejar vacío para no cambiar';
        
        /* Guardar ID del usuario en el formulario */
        document.getElementById('formUsuario').setAttribute('data-usuario-id', usuario.id_usuario);

        /* Mostrar modal de formulario */
        abrirModalFormUsuario();
        
        comprobarNombreUsuario();
        comprobarEmailUsuario();
        comprobarTelefonoUsuario();
        comprobarRolUsuario();
        comprobarPasswordUsuario();
        
        /* Cerrar modal de perfil */
        cerrarModal();
    })
    .catch(function(error) {
        console.error('Error en fetch editarUsuario:', error);
        mostrarAlertaError('Error', error.message || 'No se pudieron cargar los datos del usuario');
    });
};

/* ================================================
   FUNCIÓN: asignarEventosPaginacion
   Asigna eventos a los botones de paginación
        limpiarValidacionFormulario();
   ================================================ */
function asignarEventosPaginacion() {
    var botonesNumero = document.querySelectorAll('#paginas .page-link[data-pagina]');

    for (var i = 0; i < botonesNumero.length; i++) {
        var btnNum = botonesNumero[i];
        btnNum.onclick = function(event) {
            event.preventDefault();
            var pagina = parseInt(this.getAttribute('data-pagina'));
            if (!isNaN(pagina)) {
                cambiarPagina(pagina);
            }
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
   Regenera completamente los botones de paginación
   ================================================ */
var actualizarPaginacion = function(paginaActual, totalPaginas) {
    var paginasSpan = document.getElementById('paginas');
    
    if (!paginasSpan) {
        return;
    }
    
    paginasSpan.innerHTML = '';
    
    var crearItem = function(pagina, contenido, deshabilitado, activo) {
        var li = document.createElement('li');
        li.className = 'page-item' + (deshabilitado ? ' disabled' : '') + (activo ? ' active' : '');

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'page-link';
        if (pagina !== null && pagina !== undefined) {
            button.setAttribute('data-pagina', pagina);
        }
        button.innerHTML = contenido;
        li.appendChild(button);
        return li;
    };

    paginasSpan.appendChild(crearItem(paginaActual - 1, '<i class="bi bi-chevron-left"></i>', paginaActual <= 1, false));

    for (var i = 1; i <= totalPaginas; i++) {
        paginasSpan.appendChild(crearItem(i, String(i), false, i === paginaActual));
    }

    paginasSpan.appendChild(crearItem(paginaActual + 1, '<i class="bi bi-chevron-right"></i>', paginaActual >= totalPaginas, false));
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
   Abre la modal de crear/editar usuario (Bootstrap)
   ================================================ */
var abrirModalFormUsuario = function() {
    if (!modalFormUsuario) {
        console.error('Error: modalFormUsuario no está inicializado');
        return;
    }
    modalFormUsuario.show();

    // Inicializar validación al abrir modal (una sola vez)
    if (!_validacionFormularioInicializada) {
        inicializarValidacionFormulario();
        _validacionFormularioInicializada = true;
    }

    // Actualizar estado del botón según contenido actual del formulario
    actualizarEstadoFormulario();
};

/* ================================================
   FUNCIÓN: cerrarModalFormUsuario
   Cierra la modal de crear/editar usuario (Bootstrap)
   ================================================ */
var cerrarModalFormUsuario = function() {
    modalFormUsuario.hide();
    document.getElementById('formUsuario').reset();
    limpiarErroresYActualizarEstado();
};

/* ================================================
   VALIDACIÓN EN TIEMPO REAL PARA FORMULARIO USUARIO
   ================================================ */

// no usamos debounce aquí: usaremos eventos onblur/onchange/onclick para seguir estilo login

function marcarErrorElemento(el, mensaje) {
    if (!el) return;
    el.classList.add('is-invalid');
    var mensajeError = el.nextElementSibling;
    if (mensajeError && mensajeError.tagName === 'SMALL') {
        mensajeError.innerText = mensaje || '';
    }
}

function marcarValidoElemento(el) {
    if (!el) return;
    el.classList.remove('is-invalid');
    var mensajeError = el.nextElementSibling;
    if (mensajeError && mensajeError.tagName === 'SMALL') {
        mensajeError.innerText = ' ';
    }
}

function limpiarErroresYActualizarEstado() {
    var inputs = ['inputNombre', 'inputEmail', 'inputTelefono', 'selectRolForm', 'inputPassword'];
    inputs.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.classList.remove('is-invalid');
        }
    });
    if (errorNombreUsuario) errorNombreUsuario.textContent = ' ';
    if (errorEmailUsuario) errorEmailUsuario.textContent = ' ';
    if (errorTelefonoUsuario) errorTelefonoUsuario.textContent = ' ';
    if (errorRolUsuario) errorRolUsuario.textContent = ' ';
    if (errorPasswordUsuario) errorPasswordUsuario.textContent = ' ';
    // Reset flags conservativamente
    _valNombre = false; _valEmail = false; _valTelefono = true; _valRol = false; _valPassword = false;
    actualizarEstadoFormulario();
}

function limpiarValidacionFormulario() {
    limpiarErroresYActualizarEstado();
}

function limpiarTextoError(id) {
    var elemento = document.getElementById(id);
    if (elemento) {
        elemento.textContent = ' ';
    }
}

function mostrarTextoError(id, mensaje) {
    var elemento = document.getElementById(id);
    if (elemento) {
        elemento.textContent = mensaje;
    }
}

function comprobarNombreUsuario() {
    var input = document.getElementById('inputNombre');
    if (!input) return;

    var valor = input.value.trim();
    if (valor === '' || valor.length < 3) {
        marcarErrorElemento(input, 'El nombre es obligatorio y debe tener mínimo 3 caracteres');
        mostrarTextoError('errorNombreUsuario', 'El nombre es obligatorio y debe tener mínimo 3 caracteres');
        _valNombre = false;
    } else {
        marcarValidoElemento(input);
        limpiarTextoError('errorNombreUsuario');
        _valNombre = true;
    }

    actualizarEstadoFormulario();
}

function comprobarEmailUsuario() {
    var input = document.getElementById('inputEmail');
    if (!input) return;

    var valor = input.value.trim();
    if (valor === '') {
        marcarErrorElemento(input, 'El correo electrónico es obligatorio.');
        mostrarTextoError('errorEmailUsuario', 'El correo electrónico es obligatorio.');
        _valEmail = false;
    } else if (!validarEmail(valor)) {
        marcarErrorElemento(input, 'Introduce un correo válido.');
        mostrarTextoError('errorEmailUsuario', 'Introduce un correo válido.');
        _valEmail = false;
    } else {
        marcarValidoElemento(input);
        limpiarTextoError('errorEmailUsuario');
        _valEmail = true;
    }

    actualizarEstadoFormulario();
}

function comprobarTelefonoUsuario() {
    var input = document.getElementById('inputTelefono');
    if (!input) return;

    var valor = input.value.trim();
    if (valor === '') {
        marcarErrorElemento(input, 'El teléfono es obligatorio.');
        mostrarTextoError('errorTelefonoUsuario', 'El teléfono es obligatorio.');
        _valTelefono = false;
    } else if (!validarTelefono(valor)) {
        marcarErrorElemento(input, 'Formato: +34 612345678.');
        mostrarTextoError('errorTelefonoUsuario', 'Formato: +34 612345678. Debe empezar por +34, llevar un espacio y tener entre 9 y 11 dígitos.');
        _valTelefono = false;
    } else {
        marcarValidoElemento(input);
        limpiarTextoError('errorTelefonoUsuario');
        _valTelefono = true;
    }

    actualizarEstadoFormulario();
}

function comprobarRolUsuario() {
    var input = document.getElementById('selectRolForm');
    if (!input) return;

    var valor = input.value;
    if (valor === '') {
        marcarErrorElemento(input, 'Debes seleccionar un rol.');
        mostrarTextoError('errorRolUsuario', 'Debes seleccionar un rol.');
        _valRol = false;
    } else {
        marcarValidoElemento(input);
        limpiarTextoError('errorRolUsuario');
        _valRol = true;
    }

    actualizarEstadoFormulario();
}

function comprobarPasswordUsuario() {
    var input = document.getElementById('inputPassword');
    var form = document.getElementById('formUsuario');
    if (!input) return;

    var valor = input.value.trim();
    var esEdicion = !!(form && form.getAttribute('data-usuario-id'));

    if (valor === '' && esEdicion) {
        marcarValidoElemento(input);
        limpiarTextoError('errorPasswordUsuario');
        _valPassword = true;
    } else if (valor === '') {
        marcarErrorElemento(input, 'La contraseña es obligatoria.');
        mostrarTextoError('errorPasswordUsuario', 'La contraseña es obligatoria.');
        _valPassword = false;
    } else if (!validarPassword(valor)) {
        marcarErrorElemento(input, 'La contraseña debe tener al menos 6 caracteres.');
        mostrarTextoError('errorPasswordUsuario', 'La contraseña debe tener al menos 6 caracteres.');
        _valPassword = false;
    } else {
        marcarValidoElemento(input);
        limpiarTextoError('errorPasswordUsuario');
        _valPassword = true;
    }

    actualizarEstadoFormulario();
}

function actualizarEstadoFormulario() {
    var form = document.getElementById('formUsuario');
    if (!form) return;

    var usuarioId = form.getAttribute('data-usuario-id');

    // Si es edición, password puede estar vacío y sigue siendo válido
    if (usuarioId) {
        _valPassword = true; // hasta que el usuario escriba algo
        var pwd = document.getElementById('inputPassword').value || '';
        if (pwd !== '') {
            _valPassword = validarPassword(pwd);
        }
    } else {
        // Crear: password obligatorio
        var pwd2 = document.getElementById('inputPassword').value || '';
        _valPassword = validarPassword(pwd2);
    }

    // Comprobar el resto de flags ya actualizados por los listeners
    var formularioValido = _valNombre && _valEmail && _valTelefono && _valRol && _valPassword;

    var btn = document.getElementById('btnGuardarUsuario');
    if (btn) {
        btn.disabled = !formularioValido;
        if (btn.disabled) btn.classList.add('disabled'); else btn.classList.remove('disabled');
    }
}

function inicializarValidacionFormulario() {
    var nombre = document.getElementById('inputNombre');
    var email = document.getElementById('inputEmail');
    var telefono = document.getElementById('inputTelefono');
    var rol = document.getElementById('selectRolForm');
    var password = document.getElementById('inputPassword');

    errorNombreUsuario = document.getElementById('errorNombreUsuario');
    errorEmailUsuario = document.getElementById('errorEmailUsuario');
    errorTelefonoUsuario = document.getElementById('errorTelefonoUsuario');
    errorRolUsuario = document.getElementById('errorRolUsuario');
    errorPasswordUsuario = document.getElementById('errorPasswordUsuario');

    if (nombre) {
        nombre.oninput = function() {
            var valor = this.value.trim();
            if (valor.length >= 3) {
                marcarValidoElemento(this);
                limpiarTextoError('errorNombreUsuario');
                _valNombre = true;
            } else {
                _valNombre = false;
            }
            actualizarEstadoFormulario();
        };
        nombre.onblur = function() {
            var v = this.value.trim();
            _valNombre = validarNombre(v);
            if (!_valNombre) marcarErrorElemento(this, 'El nombre es obligatorio y debe tener mínimo 3 caracteres'); else marcarValidoElemento(this);
            actualizarEstadoFormulario();
        };
        // Validar también al hacer clic fuera del formulario (por consistencia)
        nombre.onclick = function() { /* noop para seguir estilo onclick disponible */ };
    }

    if (email) {
        email.oninput = function() {
            var valor = this.value.trim();
            if (validarEmail(valor)) {
                marcarValidoElemento(this);
                limpiarTextoError('errorEmailUsuario');
                _valEmail = true;
            } else {
                _valEmail = false;
            }
            actualizarEstadoFormulario();
        };
        email.onblur = function() {
            var v = this.value.trim();
            _valEmail = validarEmail(v);
            if (!_valEmail) marcarErrorElemento(this, 'Introduce un correo válido'); else marcarValidoElemento(this);
            actualizarEstadoFormulario();
        };
    }

    if (telefono) {
        telefono.oninput = function() {
            var valor = this.value.trim();
            if (validarTelefono(valor)) {
                marcarValidoElemento(this);
                limpiarTextoError('errorTelefonoUsuario');
                _valTelefono = true;
            } else {
                _valTelefono = false;
            }
            actualizarEstadoFormulario();
        };
        telefono.onblur = function() {
            var v = this.value.trim();
            _valTelefono = validarTelefono(v);
            if (!_valTelefono) marcarErrorElemento(this, 'El teléfono es obligatorio y debe comenzar con +34 seguido de un espacio y entre 9 y 11 dígitos (ej: +34 612345678)'); else marcarValidoElemento(this);
            actualizarEstadoFormulario();
        };
    }

    if (rol) {
        rol.oninput = function() {
            var v = this.value;
            _valRol = !!v && v !== '';
            if (_valRol) {
                marcarValidoElemento(this);
                limpiarTextoError('errorRolUsuario');
            }
            actualizarEstadoFormulario();
        };
        rol.onchange = function() {
            var v = this.value;
            _valRol = !!v && v !== '';
            if (_valRol) {
                marcarValidoElemento(this);
                limpiarTextoError('errorRolUsuario');
            }
            actualizarEstadoFormulario();
        };
    }

    if (password) {
        password.oninput = function() {
            var v = this.value || '';
            var form = document.getElementById('formUsuario');
            var usuarioId = form ? form.getAttribute('data-usuario-id') : null;
            if (usuarioId && v === '') {
                limpiarTextoError('errorPasswordUsuario');
                _valPassword = true;
            } else if (validarPassword(v)) {
                marcarValidoElemento(this);
                limpiarTextoError('errorPasswordUsuario');
                _valPassword = true;
            } else {
                _valPassword = false;
            }
            actualizarEstadoFormulario();
        };
        password.onblur = function() {
            var v = this.value || '';
            var form = document.getElementById('formUsuario');
            var usuarioId = form ? form.getAttribute('data-usuario-id') : null;
            if (usuarioId) {
                // edición: solo validar si hay contenido
                if (v === '') {
                    _valPassword = true;
                    marcarValidoElemento(this);
                } else {
                    _valPassword = validarPassword(v);
                    if (!_valPassword) marcarErrorElemento(this, 'La contraseña debe tener mínimo 6 caracteres'); else marcarValidoElemento(this);
                }
            } else {
                // crear: obligatorio
                _valPassword = validarPassword(v);
                if (!_valPassword) marcarErrorElemento(this, 'La contraseña es obligatoria y debe tener mínimo 6 caracteres'); else marcarValidoElemento(this);
            }
            actualizarEstadoFormulario();
        };
    }

    // Ejecutar primera vez para ajustar botón según valores iniciales
    actualizarEstadoFormulario();
}

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
    var suscripcion = document.getElementById('selectSuscripcionForm').value;
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
    
    /* Validaciones de teléfono (obligatorio, formato +34 9-11 dígitos) */
    if (!validarTelefono(telefono)) {
        mostrarAlertaValidacion('El teléfono es obligatorio y debe comenzar con +34 seguido de un espacio y entre 9 y 11 dígitos (ej: +34 612345678)');
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
        rol: rol,
        suscripcion_plan: suscripcion
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
            
            /* Actualizar KPIs */
            actualizarKpisUsuarios();
            
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
