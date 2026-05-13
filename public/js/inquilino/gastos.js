/**
 * Lógica para la página de Gastos y Pagos del Inquilino
 * Maneja pestañas, navegación y llamadas a Stripe.
 */

document.addEventListener('DOMContentLoaded', function() {
    inicializarGastos();
});

function inicializarGastos() {
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    // Gestión de Pestañas (Tabs)
    tabs.forEach(tab => {
        tab.onclick = () => {
            const targetId = tab.getAttribute('data-tab');

            // 1. Quitar estado activo de todas las pestañas y contenidos
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            // 2. Activar la pestaña clicada
            tab.classList.add('active');
            
            // 3. Mostrar el contenido correspondiente
            const content = document.getElementById(targetId);
            if (content) {
                content.classList.add('active');
            }
        };
    });

    // Acción de Pagar Todo (Placeholder para integración futura)
    const btnPagarTodo = document.getElementById('btn-pagar-todo');
    if (btnPagarTodo) {
        btnPagarTodo.onclick = () => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Pagar todo?',
                    text: "Se procesarán todos tus pagos pendientes en una sola transacción segura.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#0f4c81',
                    cancelButtonColor: '#5c6b7a',
                    confirmButtonText: 'Sí, proceder',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('Procesando', 'Estamos preparando tu pasarela de pago...', 'success');
                    }
                });
            } else {
                alert('Esta funcionalidad procesará todos tus pagos pendientes en una sola transacción. (En desarrollo)');
            }
        };
    }
}

/**
 * Inicia el proceso de pago individual con Stripe
 * @param {string} tipo - 'alquiler', 'gasto' o 'incidencia'
 * @param {number} id - ID del registro a pagar
 */
function iniciarPago(tipo, id) {
    const btn = event.currentTarget || event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    btn.disabled = true;

    // Construcción de la URL según el tipo de concepto
    let url = `/inquilino/cuotas/${id}/pagar?tipo=${tipo}`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.url) {
            window.location.href = data.url;
        } else {
            throw new Error(data.message || 'No se pudo iniciar el pago.');
        }
    })
    .catch(err => {
        console.error(err);
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', err.message || 'Ocurrió un error al conectar con Stripe.', 'error');
        } else {
            alert('Error: ' + err.message);
        }
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
