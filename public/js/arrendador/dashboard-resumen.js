// Dashboard Resumen - Interactividad

document.addEventListener('DOMContentLoaded', function() {
    inicializarClicksResumen();
});

function inicializarClicksResumen() {
    // Clics en stat-box
    const statBoxes = document.querySelectorAll('.stat-box[data-tipo]');
    statBoxes.forEach(box => {
        box.addEventListener('click', abrirModalResumen);
    });

    // Clics en status-card (propiedades por estado)
    const statusCards = document.querySelectorAll('.status-card[data-estado]');
    statusCards.forEach(card => {
        card.addEventListener('click', abrirModalEstado);
    });

    // Cerrar modales
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            cerrarModal();
        }
        if (e.target.classList.contains('btn-cerrar')) {
            cerrarModal();
        }
    });

    // Tecla ESC para cerrar modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModal();
        }
    });
}

function abrirModalResumen(e) {
    const tipo = this.dataset.tipo;
    const contenidoModal = generarContenidoResumen(tipo);

    if (!contenidoModal) return;

    mostrarModal(contenidoModal);
}

function abrirModalEstado(e) {
    const estado = this.dataset.estado;
    const cantidad = this.dataset.cantidad;
    
    const contenido = `
        <h3>Propiedades en estado: <strong>${estado}</strong></h3>
        <p>Total: <strong>${cantidad}</strong></p>
        <p style="margin-top: 15px; color: #999; font-size: 14px;">
            Puedes gestionar estas propiedades desde la sección "Mis propiedades" en tu dashboard.
        </p>
    `;

    mostrarModal(contenido, `Propiedades ${estado}`);
}

function generarContenidoResumen(tipo) {
    const elementoStats = document.querySelector(`.stat-box[data-tipo="${tipo}"]`);
    if (!elementoStats) return null;

    let contenido = '';
    let titulo = '';

    switch(tipo) {
        case 'propiedades-activas':
            titulo = 'Propiedades Activas';
            contenido = generarDetailPropiedadesActivas();
            break;
        case 'inquilinos-activos':
            titulo = 'Inquilinos Activos';
            contenido = generarDetailInquilinos();
            break;
        case 'ingresos-mes':
            titulo = 'Ingresos Este Mes';
            contenido = generarDetailIngresosMes();
            break;
        case 'solicitudes-pendientes':
            titulo = 'Solicitudes Pendientes';
            contenido = generarDetailSolicitudes();
            break;
        case 'ingresos-totales':
            titulo = 'Ingresos Totales';
            contenido = generarDetailIngresosTotales();
            break;
        case 'total-propiedades':
            titulo = 'Total de Propiedades';
            contenido = generarDetailTotalPropiedades();
            break;
        case 'tasa-ocupacion':
            titulo = 'Tasa de Ocupación';
            contenido = generarDetailTasaOcupacion();
            break;
        case 'pagos-pendientes':
            titulo = 'Pagos Pendientes';
            contenido = generarDetailPagosPendientes();
            break;
        case 'incidencias-abiertas':
            titulo = 'Incidencias Abiertas';
            contenido = generarDetailIncidencias();
            break;
    }

    mostrarModal(contenido, titulo);
}

function generarDetailPropiedadesActivas() {
    return `
        <div class="info-grupo">
            <label>Propiedades Publicadas o Alquiladas</label>
            <p>Estas son las propiedades que están disponibles para alquilar o ya están alquiladas.</p>
        </div>
        <div class="info-grupo">
            <label>Acciones</label>
            <p>
                <a href="/admin/propiedades" style="color: #007bff; text-decoration: none; font-weight: 600;">
                    → Ver todas las propiedades
                </a>
            </p>
        </div>
    `;
}

function generarDetailInquilinos() {
    return `
        <div class="info-grupo">
            <label>Inquilinos con Alquiler Activo</label>
            <p>Número de inquilinos que tienen un alquiler en estado activo en tus propiedades.</p>
        </div>
        <div class="info-grupo">
            <label>Detalles</label>
            <p>Estos inquilinos están pagando alquiler mensual en tus propiedades.</p>
        </div>
        <div class="info-grupo">
            <label>Acciones</label>
            <p>
                <a href="/admin/inquilinos" style="color: #007bff; text-decoration: none; font-weight: 600;">
                    → Ver información de inquilinos
                </a>
            </p>
        </div>
    `;
}

function generarDetailIngresosMes() {
    const mesActual = new Date().toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
    return `
        <div class="info-grupo">
            <label>Período</label>
            <p><strong>${mesActual}</strong></p>
        </div>
        <div class="info-grupo">
            <label>Concepto</label>
            <p>Suma de los precios mensuales de todos los alquileres activos durante este mes.</p>
        </div>
        <div class="info-grupo">
            <label>Cálculo</label>
            <p>Se calcula automáticamente basado en el precio de cada propiedad y la fecha de aprobación del alquiler.</p>
        </div>
    `;
}

function generarDetailSolicitudes() {
    return `
        <div class="info-grupo">
            <label>Estado</label>
            <p>Solicitudes de alquiler que aún no han sido aprobadas o rechazadas.</p>
        </div>
        <div class="info-grupo">
            <label>Acción requerida</label>
            <p>Necesitas revisar y decidir sobre estas solicitudes lo antes posible.</p>
        </div>
        <div class="info-grupo">
            <label>Acciones</label>
            <p>
                <a href="/admin/solicitudes" style="color: #007bff; text-decoration: none; font-weight: 600;">
                    → Ver todas las solicitudes pendientes
                </a>
            </p>
        </div>
    `;
}

function generarDetailIngresosTotales() {
    return `
        <div class="info-grupo">
            <label>Ingresos Acumulados</label>
            <p>Suma total de todos los alquileres activos en tus propiedades.</p>
        </div>
        <div class="info-grupo">
            <label>Período</label>
            <p>Estos son todos los ingresos mensuales recurrentes que recibes actualmente.</p>
        </div>
        <div class="info-grupo">
            <label>Nota</label>
            <p>Este monto se calcula basado en los alquileres en estado "activo".</p>
        </div>
    `;
}

function generarDetailTotalPropiedades() {
    return `
        <div class="info-grupo">
            <label>Desglose</label>
            <p>Incluye todas tus propiedades en cualquier estado: borrador, publicada, alquilada e inactiva.</p>
        </div>
        <div class="info-grupo">
            <label>Gestión</label>
            <p>Accede a la sección de propiedades para crear, editar o eliminar propiedades.</p>
        </div>
        <div class="info-grupo">
            <label>Acciones</label>
            <p>
                <a href="/admin/propiedades" style="color: #007bff; text-decoration: none; font-weight: 600;">
                    → Gestionar propiedades
                </a>
            </p>
        </div>
    `;
}

function generarDetailTasaOcupacion() {
    return `
        <div class="info-grupo">
            <label>Cálculo</label>
            <p>Porcentaje de propiedades alquiladas respecto al total de propiedades.</p>
        </div>
        <div class="info-grupo">
            <label>Fórmula</label>
            <p><strong>(Propiedades Alquiladas / Total Propiedades) × 100</strong></p>
        </div>
        <div class="info-grupo">
            <label>Objetivo</label>
            <p>Mientras mayor sea este porcentaje, mejor es tu ocupación de mercado.</p>
        </div>
    `;
}

function generarDetailPagosPendientes() {
    return `
        <div class="info-grupo">
            <label>Descripción</label>
            <p>Monto total de pagos (alquileres o gastos) que aún no han sido confirmados.</p>
        </div>
        <div class="info-grupo">
            <label>Estados Incluidos</label>
            <p>Solo se cuentan los pagos en estado "pendiente".</p>
        </div>
        <div class="info-grupo">
            <label>Acciones</label>
            <p>
                <a href="/admin/pagos" style="color: #007bff; text-decoration: none; font-weight: 600;">
                    → Ver gestión de pagos
                </a>
            </p>
        </div>
    `;
}

function generarDetailIncidencias() {
    return `
        <div class="info-grupo">
            <label>Descripción</label>
            <p>Número de incidencias o problemas reportados en tus propiedades que aún están abiertos.</p>
        </div>
        <div class="info-grupo">
            <label>Estados Incluidos</label>
            <p>Solo se cuentan las incidencias en estado "abierta".</p>
        </div>
        <div class="info-grupo">
            <label>Acciones</label>
            <p>
                <a href="/admin/incidencias" style="color: #007bff; text-decoration: none; font-weight: 600;">
                    → Gestionar incidencias
                </a>
            </p>
        </div>
    `;
}

function mostrarModal(contenido, titulo = 'Información') {
    let overlay = document.getElementById('modalOverlayResumen');
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'modalOverlayResumen';
        overlay.className = 'modal-overlay';
        document.body.appendChild(overlay);
    }

    overlay.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>${titulo}</h2>
                <button class="btn-cerrar">×</button>
            </div>
            <div class="modal-body">
                ${contenido}
            </div>
        </div>
    `;

    overlay.classList.add('activo');

    // Reattach event listeners
    overlay.querySelector('.btn-cerrar').addEventListener('click', cerrarModal);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            cerrarModal();
        }
    });
}

function cerrarModal() {
    const overlay = document.getElementById('modalOverlayResumen');
    if (overlay) {
        overlay.classList.remove('activo');
    }
}
