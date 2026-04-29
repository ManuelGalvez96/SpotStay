document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.form-gasto');
    if (!form) return;

    const category = form.querySelector('select[name="categoria_gasto"]');
    const concepto = form.querySelector('input[name="concepto_gasto"]');
    const importe = form.querySelector('input[name="importe_estimado"]');
    const fechaInicio = form.querySelector('input[name="fecha_inicio_gasto"]');
    const fechaFin = form.querySelector('input[name="fecha_fin_gasto"]');

    function clearErrors() {
        const existing = document.querySelectorAll('.mensaje-error-js');
        existing.forEach(e => e.remove());
    }

    function showError(message) {
        clearErrors();
        const div = document.createElement('div');
        div.className = 'mensaje-estado mensaje-error mensaje-error-js';
        div.style.marginBottom = '12px';
        div.textContent = message;
        form.parentNode.insertBefore(div, form);
        div.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    form.addEventListener('submit', function (e) {
        clearErrors();
        const errors = [];

        if (!category || !category.value) {
            errors.push('Selecciona una categoría.');
        }

        if (concepto) {
            const txt = concepto.value.trim();
            if (txt.length > 200) {
                errors.push('El concepto no puede tener más de 200 caracteres.');
            }
        }

        if (!importe) {
            errors.push('El campo importe no está disponible.');
        } else {
            // aceptamos coma o punto
            const raw = (importe.value || '').toString().trim().replace(',', '.');
            const val = parseFloat(raw);
            if (Number.isNaN(val) || val < 0.01) {
                errors.push('Introduce un importe válido (>= 0.01).');
            }
        }

        if (!fechaInicio || !fechaInicio.value) {
            errors.push('Selecciona la fecha inicio.');
        }
        if (!fechaFin || !fechaFin.value) {
            errors.push('Selecciona la fecha fin.');
        }

        if (fechaInicio && fechaFin && fechaInicio.value && fechaFin.value) {
            const fi = new Date(fechaInicio.value + 'T00:00:00');
            const ff = new Date(fechaFin.value + 'T00:00:00');
            if (ff < fi) {
                errors.push('La fecha fin no puede ser anterior a la fecha inicio.');
            }
        }

        if (errors.length) {
            e.preventDefault();
            showError(errors.join(' '));
            return false;
        }

        // evitar envíos dobles
        const submits = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submits.forEach(s => s.disabled = true);
    });
});