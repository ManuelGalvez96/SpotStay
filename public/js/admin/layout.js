var csrfMeta = document.querySelector('meta[name="csrf-token"]');
var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

var crearOsoAdminExito = function() {
    return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
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
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#D1FAE5" stroke="#1AA068" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#1AA068">✓</text>
    </svg>
    `;
};

var crearOsoAdminError = function() {
    return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
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
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FEE2E2" stroke="#EF4444" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#EF4444">✗</text>
    </svg>
    `;
};

var crearOsoAdminValidacion = function() {
    return `
    <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px;">
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
        <rect x="55" y="130" width="90" height="45" rx="5" fill="#FEF3C7" stroke="#D97706" stroke-width="2.5"/>
        <text x="100" y="160" font-size="32" font-weight="bold" text-anchor="middle" fill="#D97706">!</text>
    </svg>
    `;
};

var mostrarAlertaAdmin = function(tipo, titulo, mensaje) {
    var modalElement = document.getElementById('modalAlertaAdmin');
    var tituloElement = document.getElementById('modalAlertaTituloAdmin');
    var mensajeElement = document.getElementById('modalAlertaMensajeAdmin');
    var iconoElement = document.getElementById('modalAlertaIconoAdmin');
    var botonElement = document.getElementById('modalAlertaBotonAdmin');

    if (!modalElement || !tituloElement || !mensajeElement || !iconoElement || !botonElement || typeof bootstrap === 'undefined') {
        window.alert(mensaje || titulo || 'Aviso');
        return;
    }

    tituloElement.textContent = titulo || 'Aviso';
    mensajeElement.textContent = mensaje || '';

    var claseBoton = 'btn-primary';
    var icono = crearOsoAdminExito();

    if (tipo === 'error') {
        claseBoton = 'btn-danger';
        icono = crearOsoAdminError();
    } else if (tipo === 'validacion') {
        claseBoton = 'btn-warning';
        icono = crearOsoAdminValidacion();
    }

    botonElement.className = 'btn ' + claseBoton;
    iconoElement.innerHTML = icono;

    bootstrap.Modal.getOrCreateInstance(modalElement).show();
};

window.mostrarAlertaAdminExito = function(titulo, mensaje) {
    mostrarAlertaAdmin('exito', titulo, mensaje);
};

window.mostrarAlertaAdminError = function(titulo, mensaje) {
    mostrarAlertaAdmin('error', titulo, mensaje);
};

window.mostrarAlertaAdminValidacion = function(mensaje) {
    mostrarAlertaAdmin('validacion', 'Validación', mensaje);
};

// Confirmación global que devuelve una Promise<boolean>
window.confirmarAdmin = function(titulo, mensaje) {
    return new Promise(function(resolve) {
        var modalElement = document.getElementById('modalConfirmAdmin');
        var tituloElement = document.getElementById('modalConfirmTituloAdmin');
        var mensajeElement = document.getElementById('modalConfirmMensajeAdmin');
        var botonConfirmar = document.getElementById('modalConfirmBotonConfirmarAdmin');

        if (!modalElement || !botonConfirmar || typeof bootstrap === 'undefined') {
            var ok = window.confirm(mensaje || titulo || 'Confirmar?');
            resolve(Boolean(ok));
            return;
        }

        tituloElement.textContent = titulo || 'Confirmar';
        mensajeElement.textContent = mensaje || '';

        var bsModal = bootstrap.Modal.getOrCreateInstance(modalElement);

        var limpiar = function() {
            botonConfirmar.removeEventListener('click', confirmarHandler);
            modalElement.removeEventListener('hidden.bs.modal', hiddenHandler);
        };

        var confirmarHandler = function() {
            limpiar();
            bsModal.hide();
            resolve(true);
        };

        var hiddenHandler = function() {
            limpiar();
            resolve(false);
        };

        botonConfirmar.addEventListener('click', confirmarHandler);
        modalElement.addEventListener('hidden.bs.modal', hiddenHandler);
        bsModal.show();
    });
};

window.onload = function() {
    asignarEventosAdmin();
    asignarEventosNavIconos();
};

var asignarEventosAdmin = function() {
    var adminContainer = document.getElementById('adminContainer');
    var adminDropdown = document.getElementById('adminDropdown');
    var btnLogout = document.getElementById('btnLogout');
    var botonesNav = document.querySelectorAll('.btn-nav-icon');

    for (var i = 0; i < botonesNav.length; i++) {
        botonesNav[i].onclick = function() {
            var ruta = this.getAttribute('data-ruta');
            if (ruta && ruta.trim() !== '') {
                window.location.href = ruta;
            }
        };
    }
    
    if (!adminContainer || !adminDropdown) return;
    
    adminContainer.onclick = function(e) {
        e.stopPropagation();
        if (adminDropdown.classList.contains('visible')) {
            adminDropdown.classList.remove('visible');
        } else {
            adminDropdown.classList.add('visible');
        }
    };
    
    if (btnLogout) {
        btnLogout.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            hacerLogout();
        };
    }
    
    document.onclick = function(e) {
        if (adminContainer && adminDropdown) {
            if (!adminContainer.contains(e.target)) {
                adminDropdown.classList.remove('visible');
            }
        }
    };
};

// Ejecutar también al cargar el script por si window.onload ya pasó
asignarEventosAdmin();
asignarEventosNavIconos();

var hacerLogout = function() {
    fetch('/logout', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        window.location.href = '/';
    })
    .catch(function(error) {
        window.location.href = '/logout';
    });
};

/* ================================================
   FUNCIÓN: asignarEventosNavIconos
   Asigna .onclick a iconos de navegación (funciona en todas las vistas)
   ================================================ */
function asignarEventosNavIconos() {
    var botonesNav = document.querySelectorAll('.btn-nav-icon');
    
    for (var i = 0; i < botonesNav.length; i++) {
        var btnNav = botonesNav[i];
        btnNav.onclick = function(event) {
            event.preventDefault();
            var ruta = this.getAttribute('data-ruta');
            if (ruta) {
                window.location.href = ruta;
            }
        };
    }
}

function inicializarMenuPerfilYNav() {
    var botonPerfil = document.getElementById('boton-perfil');
    var submenu = document.getElementById('submenu-perfil');

    if (botonPerfil && submenu) {
        botonPerfil.onclick = function (e) {
            e.stopPropagation();
            submenu.classList.toggle('activo');
        };

        submenu.onclick = function (e) {
            e.stopPropagation();
        };
        
        var originalDocClick = document.onclick;
        document.onclick = function (e) {
            if (originalDocClick) originalDocClick(e);
            submenu.classList.remove('activo');
        };
    }

    // Auto-centrar navegación horizontal activa al cargar la página
    var contenedor = document.querySelector('.contenedor-nav');
    var enlaceActivo = document.querySelector('.enlace-nav.activo');

    if (contenedor && enlaceActivo) {
        var offsetLeft = enlaceActivo.offsetLeft;
        var anchoContenedor = contenedor.clientWidth;
        var anchoEnlace = enlaceActivo.clientWidth;

        var scrollPos = offsetLeft - (anchoContenedor / 2) + (anchoEnlace / 2);
        contenedor.scrollTo({
            left: scrollPos,
            behavior: 'smooth'
        });
    }
}

// Inicializar al cargar el script
inicializarMenuPerfilYNav();
