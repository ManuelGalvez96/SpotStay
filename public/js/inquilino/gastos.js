document.addEventListener('DOMContentLoaded', function () {
    inicializarGastos();
    comprobarAlertasSesionGastos();
});

function inicializarGastos() {
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.onclick = () => {
            const targetId = tab.getAttribute('data-tab');
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            const content = document.getElementById(targetId);
            if (content) content.classList.add('active');
        };
    });

    const btnPagarTodo = document.getElementById('btn-pagar-todo');
    if (btnPagarTodo) {
        btnPagarTodo.onclick = pagarTodo;
    }
}

function comprobarAlertasSesionGastos() {
    const data = document.getElementById('data-session');
    if (!data) return;
    const exito = data.getAttribute('data-exito');
    const error = data.getAttribute('data-error');
    if (exito) {
        mostrarAlertaExito('Pago exitoso', exito);
    }
    if (error) {
        mostrarAlertaError('Error en el pago', error);
    }
}

function pagarTodo() {
    let total = 0;
    const items = document.querySelectorAll('.gasto-item-row');
    items.forEach(item => {
        const importe = parseFloat(item.querySelector('.item-importe')?.textContent?.replace(/[^0-9,]/g, '').replace(',', '.') || '0');
        total += importe;
    });

    const propiedadId = document.getElementById('form-filtro-propiedad')?.querySelector('[name="propiedad_id"]')?.value || '';

    mostrarAlertaConfirmacion(
        'Pagar todo',
        'Se procesarán todos tus pagos pendientes en una sola transacción segura.' +
            '<br><br>Importe total: <strong>' + total.toFixed(2).replace('.', ',') + ' €</strong>',
        'Sí, pagar ahora',
        'Cancelar'
    ).then(resultado => {
        if (!resultado.isConfirmed) return;

        fetch('/inquilino/pagar-todo', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ propiedad_id: propiedadId || null })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.url) {
                window.location.href = data.url;
            } else {
                mostrarAlertaError('Error', data.message || 'No se pudo iniciar el pago.');
            }
        })
        .catch(err => {
            mostrarAlertaError('Error de conexión', err.message || 'Ocurrió un error al conectar con el servidor.');
        });
    });
}

function iniciarPago(tipo, id) {
    const btn = event?.currentTarget || event?.target;
    if (!btn) return;

    const originalText = btn.innerHTML;
    const row = btn.closest('.gasto-item-row');
    const concepto = row?.querySelector('.concepto')?.textContent || 'este concepto';
    const importe = row?.querySelector('.item-importe')?.textContent || '';

    mostrarAlertaConfirmacion(
        'Confirmar pago',
        'Vas a pagar <strong>' + concepto + '</strong>.<br><br>Importe: <strong>' + importe + '</strong>',
        'Sí, pagar ahora',
        'Cancelar'
    ).then(resultado => {
        if (!resultado.isConfirmed) return;

        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        btn.disabled = true;

        let url = '/inquilino/cuotas/' + id + '/pagar?tipo=' + tipo;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.url) {
                window.location.href = data.url;
            } else {
                mostrarAlertaError('Error', data.message || 'No se pudo iniciar el pago.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            mostrarAlertaError('Error de conexión', err.message || 'Ocurrió un error al conectar con el servidor.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}
