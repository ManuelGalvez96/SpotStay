window.onload = function () {
    var selectPropiedad = document.getElementById('id_propiedad');
    var inputPrecio = document.getElementById('precio');

    if (!selectPropiedad || !inputPrecio) {
        return;
    }

    selectPropiedad.onchange = function () {
        var opcion = selectPropiedad.options[selectPropiedad.selectedIndex];
        if (!opcion) {
            return;
        }

        var precio = opcion.getAttribute('data-precio');
        if (precio && !inputPrecio.value) {
            inputPrecio.value = parseFloat(precio).toFixed(2);
        }
    };
};
