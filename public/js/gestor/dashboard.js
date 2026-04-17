window.onload = function () {
    aplicarBarrasProgreso();
    iniciarReveals();
};

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
