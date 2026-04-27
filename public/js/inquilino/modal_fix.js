/**
 * Fix para modales Bootstrap en la página de detalles de propiedades
 * Asegura que los modales sean completamente interactuables
 */

document.addEventListener('DOMContentLoaded', function () {
    const modalReportar = document.getElementById('modalReportar');
    
    if (modalReportar) {
        // Asegurar que el modal tiene los estilos correctos
        modalReportar.style.pointerEvents = 'auto';
        
        // Cuando el modal se muestra
        modalReportar.addEventListener('show.bs.modal', function () {
            console.log('Modal abierto');
            // Asegurar que el backdrop permite interacción
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.style.pointerEvents = 'auto';
            }
        });

        // Cuando el modal se cierra
        modalReportar.addEventListener('hide.bs.modal', function () {
            console.log('Modal cerrado');
        });

        // Asegurar que todos los inputs dentro del modal sean interactuables
        const inputs = modalReportar.querySelectorAll('input, select, textarea, button');
        inputs.forEach(input => {
            input.style.pointerEvents = 'auto';
        });
    }
});
