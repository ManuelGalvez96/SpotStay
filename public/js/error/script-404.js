/**
 * Lógica de interactividad para Error 404
 * Sin utilizar addEventListener
 */

// Referencias globales
const faceGroup = document.getElementById('face-group');
const eyeGroup = document.getElementById('eye-group');
const yetiSvg = document.getElementById('yeti-svg');
const errorContent = document.querySelector('.error-content');

// Variable de control para pausar el seguimiento
let isFrozen = false;

// Si el cursor entra en el recuadro de texto, pausamos el movimiento y reseteamos la posición
if (errorContent) {
    errorContent.onmouseenter = function () {
        isFrozen = true;
        // Reseteamos la cara a la posición inicial (mirando al frente)
        if (faceGroup) {
            faceGroup.style.transform = 'translate(0px, 0px)';
        }
    };
    errorContent.onmouseleave = function () {
        isFrozen = false;
    };
}

// 1. SEGUIMIENTO DEL CURSOR GLOBAL
// Usamos la propiedad onmousemove del objeto window para rastrear toda la pantalla
window.onmousemove = function (e) {
    if (!yetiSvg || !faceGroup || isFrozen) return;

    // Obtenemos la posición actual de la mascota
    const rect = yetiSvg.getBoundingClientRect();

    // Punto central de la mascota
    const yetiCenterX = rect.left + rect.width / 2;
    const yetiCenterY = rect.top + rect.height / 2;

    // Distancia entre cursor y centro
    const deltaX = e.clientX - yetiCenterX;
    const deltaY = e.clientY - yetiCenterY;

    // Cálculo de movimiento limitado para que la cara no se salga del cuerpo
    const faceMoveX = Math.min(Math.max(deltaX * 0.02, -10), 10);
    const faceMoveY = Math.min(Math.max(deltaY * 0.02, -6), 6);

    // Aplicar transformación a la cara
    faceGroup.style.transform = `translate(${faceMoveX}px, ${faceMoveY}px)`;
};

// 2. LÓGICA DE PARPADEO ALEATORIO
// Función autoejecutada para el ciclo de parpadeo
function triggerBlink() {
    if (!eyeGroup) return;

    // Activar parpadeo (definido en CSS)
    eyeGroup.classList.add('blink');

    // Duración del parpadeo: 150ms
    setTimeout(function () {
        eyeGroup.classList.remove('blink');
    }, 150);

    // Siguiente parpadeo aleatorio entre 2.5 y 7 segundos
    const nextBlink = Math.random() * (7000 - 2500) + 2500;
    setTimeout(triggerBlink, nextBlink);
}

// Iniciar el ciclo
triggerBlink();