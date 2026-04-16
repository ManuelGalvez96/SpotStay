window.onload = function () {
    asignarFiltrosTabla();
    aplicarBarrasProgreso();
    iniciarReveals();
};

function asignarFiltrosTabla() {
    var buscador = document.getElementById('busquedaIncidencias');
    var filtroEstado = document.getElementById('filtroEstado');

    if (!buscador || !filtroEstado) {
        return;
    }

    buscador.addEventListener('input', filtrarTablaIncidencias);
    filtroEstado.addEventListener('change', filtrarTablaIncidencias);
}

function filtrarTablaIncidencias() {
    var termino = (document.getElementById('busquedaIncidencias').value || '').toLowerCase().trim();
    var estado = document.getElementById('filtroEstado').value;
    var filas = document.querySelectorAll('#tablaIncidenciasRecientes tr');

    for (var i = 0; i < filas.length; i++) {
        var fila = filas[i];
        var textoFila = (fila.textContent || '').toLowerCase();
        var estadoFila = fila.getAttribute('data-estado');

        if (!estadoFila) {
            continue;
        }

        var coincideTexto = termino === '' || textoFila.indexOf(termino) !== -1;
        var coincideEstado = estado === 'todos' || estadoFila === estado;

        if (estado === 'esperando') {
            var prioridadFila = fila.getAttribute('data-prioridad');
            coincideEstado = prioridadFila === 'alta';
        }

        fila.style.display = coincideTexto && coincideEstado ? 'table-row' : 'none';
    }
}

function iniciarReveals() {
    var bloques = document.querySelectorAll('.reveal');

    for (var i = 0; i < bloques.length; i++) {
        (function (indice) {
            setTimeout(function () {
                bloques[indice].classList.add('show');
            }, 70 * indice);
        })(i);
    }
}

function aplicarBarrasProgreso() {
    var barras = document.querySelectorAll('.progress-fill');

    for (var i = 0; i < barras.length; i++) {
        var valor = parseInt(barras[i].getAttribute('data-value'), 10);

        if (isNaN(valor)) {
            valor = 0;
        }

        if (valor < 0) {
            valor = 0;
        }

        if (valor > 100) {
            valor = 100;
        }

        barras[i].style.width = valor + '%';
    }
}
