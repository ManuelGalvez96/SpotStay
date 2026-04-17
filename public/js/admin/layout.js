var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

window.onload = function() {
    asignarEventosAdmin();
    asignarEventosNavIconos();
};

var asignarEventosAdmin = function() {
    var adminContainer = document.getElementById('adminContainer');
    var adminDropdown = document.getElementById('adminDropdown');
    var btnLogout = document.getElementById('btnLogout');
    
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
