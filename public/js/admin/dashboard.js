/* ========================================
   DASHBOARD ADMIN — SPOTYSTAY
   JavaScript Vanilla — Sin frameworks, sin async/await
   ======================================== */

/* ── Variables globales ── */
var csrfToken = null;

/* ── window.onload ── */
window.onload = function() {
    csrfToken = document.querySelector('meta[name=csrf-token]').content;
    
    iniciarDonut();
    asignarEventosBotones();
    asignarEventoBuscador();
    asignarEventosNavIconos();
};

/* ================================================
   FUNCIÓN: iniciarDonut
   Inicializa el gráfico Chart.js tipo doughnut
   ================================================ */
function iniciarDonut() {
    var canvasElement = document.getElementById('donutChart');
    
    if (!canvasElement) {
        return;
    }
    
    // Fetch stats from API
    fetch('/admin/dashboard/stats')
        .then(function(response) {
            if (!response.ok) throw new Error('Error al cargar estadísticas');
            return response.json();
        })
        .then(function(result) {
            var chartData = result.data || [0, 0, 0, 0];
            iniciarDonutConDatos(chartData);
        })
        .catch(function(error) {
            console.error('Error al cargar estadísticas del dashboard:', error);
            // Usar datos por defecto si falla la carga
            iniciarDonutConDatos([0, 0, 0, 0]);
        });
}

function iniciarDonutConDatos(chartData) {
    var canvasElement = document.getElementById('donutChart');
    var ctx = canvasElement.getContext('2d');
    
    var donutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Inquilinos', 'Arrendadores', 'Miembros', 'Gestores'],
            datasets: [{
                data: chartData,
                backgroundColor: ['#1AA068', '#035498', '#94A3B8', '#CBD5E1'],
                borderColor: '#FFFFFF',
                borderWidth: 2
            }]
        },
        options: {
            cutout: '72%',
            responsive: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 10,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    borderColor: '#FFFFFF',
                    borderWidth: 1
                }
            },
            animation: false
        }
    });
}

/* ================================================
   FUNCIÓN: asignarEventosBotones
   Asigna .onclick a botones Aprobar y Rechazar
   ================================================ */
function asignarEventosBotones() {
    var botonesAprobar = document.querySelectorAll('.btn-aprobar');
    var botonesRechazar = document.querySelectorAll('.btn-rechazar');
    
    // Asignar onclick a botones Aprobar
    for (var i = 0; i < botonesAprobar.length; i++) {
        var btnAprobar = botonesAprobar[i];
        btnAprobar.onclick = function(event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            aprobarAlquiler(id);
        };
    }
    
    // Asignar onclick a botones Rechazar
    for (var i = 0; i < botonesRechazar.length; i++) {
        var btnRechazar = botonesRechazar[i];
        btnRechazar.onclick = function(event) {
            event.preventDefault();
            var id = this.getAttribute('data-id');
            rechazarAlquiler(id);
        };
    }
}

/* ================================================
   FUNCIÓN: aprobarAlquiler
   Hace fetch POST para aprobar un alquiler
   ================================================ */
function aprobarAlquiler(id) {
    var url = '/admin/alquiler/' + id + '/aprobar';
    
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
            // Actualizar badge a "Activo"
            var tr = document.querySelector('tr[data-id="' + id + '"]');
            if (tr) {
                var badgeElement = tr.querySelector('.badge-estado');
                if (badgeElement) {
                    badgeElement.textContent = 'Activo';
                    badgeElement.className = 'badge-estado badge-activo';
                }
                
                // Ocultar botones de acción
                var accionesDiv = tr.querySelector('.acciones-tabla');
                if (accionesDiv) {
                    accionesDiv.innerHTML = '<span class="sin-accion">—</span>';
                }
            }
        } else {
            console.error('Error al aprobar alquiler:', data.message);
        }
    })
    .catch(function(error) {
        console.error('Error en fetch:', error);
    });
}

/* ================================================
   FUNCIÓN: rechazarAlquiler
   Hace fetch POST para rechazar un alquiler
   ================================================ */
function rechazarAlquiler(id) {
    var url = '/admin/alquiler/' + id + '/rechazar';
    
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
            // Actualizar badge a "Rechazado"
            var tr = document.querySelector('tr[data-id="' + id + '"]');
            if (tr) {
                var badgeElement = tr.querySelector('.badge-estado');
                if (badgeElement) {
                    badgeElement.textContent = 'Rechazado';
                    badgeElement.className = 'badge-estado badge-rechazado';
                }
                
                // Ocultar botones de acción
                var accionesDiv = tr.querySelector('.acciones-tabla');
                if (accionesDiv) {
                    accionesDiv.innerHTML = '<span class="sin-accion">—</span>';
                }
            }
        } else {
            console.error('Error al rechazar alquiler:', data.message);
        }
    })
    .catch(function(error) {
        console.error('Error en fetch:', error);
    });
}

/* ================================================
   FUNCIÓN: asignarEventoBuscador
   Asigna eventos onblur y onkeyup al buscador
   ================================================ */
function asignarEventoBuscador() {
    var buscador = document.getElementById('buscadorTabla');
    
    if (!buscador) {
        return;
    }
    
    // Evento onblur
    buscador.onblur = function() {
        filtrarTabla(this.value);
    };
    
    // Evento onkeyup
    buscador.onkeyup = function() {
        if (this.value.length === 0) {
            filtrarTabla('');
        }
    };
}

/* ================================================
   FUNCIÓN: filtrarTabla
   Filtra las filas de la tabla según el término
   ================================================ */
function filtrarTabla(termino) {
    var tbody = document.getElementById('tbodyAlquileres');
    
    if (!tbody) {
        return;
    }
    
    var filas = tbody.querySelectorAll('tr');
    
    if (termino === '' || termino.length === 0) {
        // Mostrar todas las filas
        for (var i = 0; i < filas.length; i++) {
            filas[i].style.display = 'table-row';
        }
        return;
    }
    
    var terminoLower = termino.toLowerCase();
    
    // Recorrer filas y ocultar/mostrar según coincidencia
    for (var i = 0; i < filas.length; i++) {
        var fila = filas[i];
        var celdas = fila.querySelectorAll('td');
        var coincide = false;
        
        // Buscar en propiedad (1ª columna) e inquilino (2ª columna)
        for (var j = 0; j < celdas.length; j++) {
            if (j === 0 || j === 1) { // Propiedad e Inquilino
                var texto = celdas[j].textContent.toLowerCase();
                if (texto.indexOf(terminoLower) !== -1) {
                    coincide = true;
                    break;
                }
            }
        }
        
        if (coincide) {
            fila.style.display = 'table-row';
        } else {
            fila.style.display = 'none';
        }
    }
}

/* ================================================
   FUNCIÓN: asignarEventosNavIconos
   Asigna .onclick a iconos de navegación
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
