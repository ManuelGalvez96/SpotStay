/**
 * Dashboard Arrendador - JavaScript Interactivo
 * Incluye: Contadores animados, modales, interactividad general
 */

document.addEventListener('DOMContentLoaded', function () {
    iniciarContadores();
    iniciarInteractividad();
});

/**
 * Inicia los contadores animados
 */
function iniciarContadores() {
    const contadores = document.querySelectorAll('.counter');
    
    contadores.forEach(contador => {
        const valorFinal = parseInt(contador.dataset.value) || 0;
        const duracion = 1500; // milisegundos
        const pasos = 60;
        const incremento = valorFinal / pasos;
        let valorActual = 0;
        const tiempoInterval = duracion / pasos;

        const interval = setInterval(() => {
            valorActual += incremento;
            
            if (valorActual >= valorFinal) {
                valorActual = valorFinal;
                clearInterval(interval);
            }

            contador.textContent = formatearNumero(Math.round(valorActual));
        }, tiempoInterval);
    });

    // Contadores de porcentaje
    const contadoresPercent = document.querySelectorAll('.counter-percent');
    contadoresPercent.forEach(contador => {
        const valorFinal = parseInt(contador.dataset.value) || 0;
        const duracion = 1500;
        const pasos = 60;
        const incremento = valorFinal / pasos;
        let valorActual = 0;
        const tiempoInterval = duracion / pasos;

        const interval = setInterval(() => {
            valorActual += incremento;
            
            if (valorActual >= valorFinal) {
                valorActual = valorFinal;
                clearInterval(interval);
            }

            contador.textContent = Math.round(valorActual);
        }, tiempoInterval);
    });
}

/**
 * Formatea números con separadores de miles
 */
function formatearNumero(num) {
    return num.toLocaleString('es-ES', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

/**
 * Inicia la interactividad general
 */
function iniciarInteractividad() {
    // Event listeners para tarjetas clicables
    document.querySelectorAll('.info-card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = '';
            }, 10);
        });
    });

    document.querySelectorAll('.kpi-card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = '';
            }, 10);
        });
    });
}

/**
 * Abre un detalle/modal según el tipo
 * Los enlaces se obtienen del data-href de las tarjetas
 */
function abrirDetalle(tipo) {
    console.log('Abriendo detalle:', tipo);
    
    // Obtener el elemento que disparó el evento
    const elemento = event.currentTarget;
    const href = elemento.getAttribute('data-href') || elemento.querySelector('a')?.href;
    
    if (href) {
        window.location.href = href;
    } else {
        console.warn('No se encontró enlace para:', tipo);
    }
}

