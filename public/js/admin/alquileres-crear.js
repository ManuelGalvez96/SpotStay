var inicializarFormularioAlquilerAdmin = function () {
    var selectPropiedad = document.getElementById('id_propiedad');
    var selectInquilino = document.getElementById('id_inquilino');
    var inputFechaInicio = document.getElementById('fecha_inicio');
    var inputPrecio = document.getElementById('precio');
    var form = document.querySelector('.form-grid');
    var errores = {
        propiedad: document.getElementById('errorPropiedadAlquiler'),
        inquilino: document.getElementById('errorInquilinoAlquiler'),
        fechaInicio: document.getElementById('errorFechaInicioAlquiler'),
        precio: document.getElementById('errorPrecioAlquiler')
    };

    if (!form || !selectPropiedad || !selectInquilino || !inputFechaInicio || !inputPrecio) {
        return;
    }

    function valorLimpio(elemento) {
        return elemento && typeof elemento.value === 'string' ? elemento.value.trim() : '';
    }

    function limpiarError(campo) {
        if (errores[campo]) {
            errores[campo].textContent = ' ';
        }
    }

    function mostrarError(campo, mensaje) {
        if (errores[campo]) {
            errores[campo].textContent = mensaje;
        }
    }

    function validarPropiedad() {
        if (!valorLimpio(selectPropiedad)) {
            mostrarError('propiedad', 'Selecciona una propiedad.');
            return false;
        }
        limpiarError('propiedad');
        return true;
    }

    function validarInquilino() {
        if (!valorLimpio(selectInquilino)) {
            mostrarError('inquilino', 'Selecciona un inquilino.');
            return false;
        }
        limpiarError('inquilino');
        return true;
    }

    function validarFechaInicio() {
        if (!valorLimpio(inputFechaInicio)) {
            mostrarError('fechaInicio', 'La fecha de inicio no puede estar vacía.');
            return false;
        }
        limpiarError('fechaInicio');
        return true;
    }

    function validarPrecio() {
        if (!valorLimpio(inputPrecio)) {
            mostrarError('precio', 'El precio mensual no puede estar vacío.');
            return false;
        }
        if (isNaN(inputPrecio.value) || parseFloat(inputPrecio.value) < 0) {
            mostrarError('precio', 'El precio mensual debe ser un número válido.');
            return false;
        }
        limpiarError('precio');
        return true;
    }

    function validarFormulario() {
        if (!validarPropiedad()) return false;
        if (!validarInquilino()) return false;
        if (!validarFechaInicio()) return false;
        if (!validarPrecio()) return false;
        return true;
    }

    selectPropiedad.onblur = validarPropiedad;
    selectPropiedad.onchange = function () {
        var opcion = selectPropiedad.options[selectPropiedad.selectedIndex];
        if (opcion) {
            var precio = opcion.getAttribute('data-precio');
            if (precio && !inputPrecio.value) {
                inputPrecio.value = parseFloat(precio).toFixed(2);
                limpiarError('precio');
            }
        }
        if (valorLimpio(selectPropiedad)) {
            limpiarError('propiedad');
        }
    };

    selectInquilino.onblur = validarInquilino;
    selectInquilino.onchange = function () {
        if (valorLimpio(selectInquilino)) {
            limpiarError('inquilino');
        }
    };

    inputFechaInicio.onblur = validarFechaInicio;
    inputFechaInicio.oninput = function () {
        if (valorLimpio(inputFechaInicio)) {
            limpiarError('fechaInicio');
        }
    };

    inputPrecio.onblur = validarPrecio;
    inputPrecio.oninput = function () {
        if (valorLimpio(inputPrecio) && !isNaN(inputPrecio.value)) {
            limpiarError('precio');
        }
    };

    form.onsubmit = function (evento) {
        if (!validarFormulario()) {
            evento.preventDefault();
            return false;
        }
        return true;
    };
};

inicializarFormularioAlquilerAdmin();
