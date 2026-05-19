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
