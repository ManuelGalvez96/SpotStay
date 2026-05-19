// Ejecutar inmediatamente: la plantilla carga los scripts al final del body
const el = document.getElementById('planes-messages');
if (el) {
    const success = el.getAttribute('data-success') || '';
    const error = el.getAttribute('data-error') || '';

    if (success && success.trim().length) {
        if (window.swalSuccess) {
            window.swalSuccess('Plan creado/actualizado', success);
        } else if (window.Swal) {
            Swal.fire('Plan creado/actualizado', success, 'success');
        }
    }

    if (error && error.trim().length) {
        if (window.swalError) {
            window.swalError('Revisa los datos', error);
        } else if (window.Swal) {
            Swal.fire('Revisa los datos', error, 'error');
        }
    }
}

// Manejo de confirmación para eliminar planes (sin addEventListener)
var deleteButtons = document.querySelectorAll('.btn-eliminar-plan');
if (deleteButtons && deleteButtons.length) {
    Array.prototype.forEach.call(deleteButtons, function(btn) {
        btn.onclick = function () {
            var planId = btn.getAttribute('data-plan-id');
            var form = document.getElementById('form-eliminar-' + planId);
            var card = btn.closest('.plan-card');
            var planName = '';
            if (card) {
                var h3 = card.querySelector('h3');
                if (h3) planName = h3.textContent.trim();
            }

            if (window.Swal) {
                Swal.fire({
                    title: 'Eliminar plan',
                    html: '¿Eliminar el plan <strong>' + (planName || '') + '</strong>? Esta acción no se puede deshacer.',
                    iconHtml: (window.crearOsoPregunta ? window.crearOsoPregunta() : (window.crearOsoError ? window.crearOsoError() : undefined)),
                    customClass: { icon: 'oso-icon' },
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d9534f'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        if (form) form.submit();
                    }
                });
            } else {
                // Fallback simple confirm
                if (confirm('¿Eliminar el plan ' + (planName || '') + '?')) {
                    if (form) form.submit();
                }
            }
        };
    });
}
