document.addEventListener('DOMContentLoaded', function () {
    iniciarValidacionConfiguracionCuenta();
});

function iniciarValidacionConfiguracionCuenta() {
    var formulario = document.getElementById('form-configuracion-cuenta');
    var botonEnviar = document.getElementById('boton-guardar-cambios');
    var emailOriginal = formulario ? (formulario.dataset.emailOriginal || '').trim() : '';
    var telefonoOriginal = formulario ? (formulario.dataset.telefonoOriginal || '').trim() : '';
    var dniOriginal = formulario ? (formulario.dataset.dniOriginal || '').trim().toUpperCase() : '';

    var entradaNombre = document.getElementById('nombre_usuario');
    var entradaEmail = document.getElementById('email_usuario');
    var entradaTelefono = document.getElementById('telefono_usuario');
    var entradaDni = document.getElementById('dni_usuario');
    var selectorTipoDoc = document.getElementById('tipo_documento_selector');
    var entradaTipoArrendador = document.getElementById('tipo_arrendador_usuario');
    var entradaDireccion = document.getElementById('direccion_fiscal_usuario');
    var entradaCif = document.getElementById('cif_usuario');
    var contenedorCif = document.getElementById('contenedor_cif_usuario');
    var entradaFechaNacimiento = document.getElementById('fecha_nacimiento_usuario');
    var entradaAvatar = document.getElementById('avatar_usuario');
    var entradaContrasenaNueva = document.getElementById('contrasena_usuario');
    var entradaContrasenaConfirmacion = document.getElementById('contrasena_usuario_confirmation');

    var errorNombre = document.getElementById('error-nombre-usuario');
    var errorEmail = document.getElementById('error-email-usuario');
    var errorTelefono = document.getElementById('error-telefono-usuario');
    var errorDni = document.getElementById('error-dni-usuario');
    var errorDireccion = document.getElementById('error-direccion-fiscal-usuario');
    var errorCif = document.getElementById('error-cif-usuario');
    var errorFechaNacimiento = document.getElementById('error-fecha-nacimiento-usuario');
    var errorAvatar = document.getElementById('error-avatar-usuario');
    var errorContrasenaNueva = document.getElementById('error-contrasena-usuario');
    var errorContrasenaConfirmacion = document.getElementById('error-contrasena-usuario-confirmation');

    var estadoNombre = false;
    var estadoEmail = false;
    var estadoTelefono = true;
    var estadoDni = true;
    var estadoDireccion = true;
    var estadoCif = true;
    var estadoFechaNacimiento = true;
    var estadoAvatar = true;
    var estadoContrasenaNueva = true;
    var estadoContrasenaConfirmacion = true;

    var temporizadorEmail = null;
    var temporizadorTelefono = null;
    var temporizadorDni = null;
    var controladorEmail = null;
    var controladorTelefono = null;
    var controladorDni = null;

    if (!formulario || !botonEnviar || !entradaNombre || !entradaEmail) {
        return;
    }

    if (entradaDni && selectorTipoDoc && entradaDni.value.trim() !== '') {
        var valorInicial = entradaDni.value.trim().toUpperCase();
        if (/^[XYZ]/.test(valorInicial)) {
            selectorTipoDoc.value = 'NIE';
        } else {
            selectorTipoDoc.value = 'DNI';
        }
    }

    function actualizarVisibilidadCif() {
        if (entradaTipoArrendador && contenedorCif) {
            if (entradaTipoArrendador.value === 'empresa') {
                contenedorCif.style.display = 'block';
            } else {
                contenedorCif.style.display = 'none';
                if (entradaCif) {
                    entradaCif.value = '';
                    limpiarError(errorCif);
                    estadoCif = true;
                }
            }
            comprobarBoton();
        }
    }

    if (entradaTipoArrendador) {
        entradaTipoArrendador.addEventListener('change', function() {
            actualizarVisibilidadCif();
            if (entradaCif && entradaTipoArrendador.value === 'empresa') {
                validarCif(true);
            }
        });
        actualizarVisibilidadCif();
    }

    if (selectorTipoDoc) {
        selectorTipoDoc.addEventListener('change', function() {
            if (entradaDni.value.trim() !== '') {
                validarDni();
            }
        });
    }

    botonEnviar.disabled = true;

    function comprobarBoton() {
        var formularioValido = estadoNombre && estadoEmail && estadoTelefono && estadoDni && estadoDireccion && estadoCif && estadoFechaNacimiento && estadoAvatar && estadoContrasenaNueva && estadoContrasenaConfirmacion;

        botonEnviar.disabled = !formularioValido;
    }

    function limpiarError(elementoError) {
        if (elementoError) {
            elementoError.textContent = '';
        }
    }

    function establecerError(elementoError, mensaje) {
        if (elementoError) {
            elementoError.textContent = mensaje;
        }
    }

    function validarNombre() {
        var valor = entradaNombre.value.trim();

        if (valor === '') {
            establecerError(errorNombre, 'El nombre es obligatorio.');
            estadoNombre = false;
        } else if (valor.length < 3) {
            establecerError(errorNombre, 'El nombre debe tener al menos 3 caracteres.');
            estadoNombre = false;
        } else {
            limpiarError(errorNombre);
            estadoNombre = true;
        }

        comprobarBoton();
    }

    async function validarEmail() {
        var valor = entradaEmail.value.trim();
        var patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (valor === '') {
            establecerError(errorEmail, 'El correo electrónico es obligatorio.');
            estadoEmail = false;
            comprobarBoton();
            return;
        }

        if (!patron.test(valor)) {
            establecerError(errorEmail, 'Introduce un correo electrónico válido.');
            estadoEmail = false;
            comprobarBoton();
            return;
        }

        if (valor.toLowerCase() === emailOriginal.toLowerCase()) {
            limpiarError(errorEmail);
            estadoEmail = true;
            comprobarBoton();
            return;
        }

        if (controladorEmail) {
            controladorEmail.abort();
        }

        controladorEmail = new AbortController();

        try {
            var parametros = new URLSearchParams({ email: valor });
            var respuesta = await fetch('/admin/usuarios/check-email?' + parametros.toString(), {
                method: 'GET',
                signal: controladorEmail.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!respuesta.ok) {
                throw new Error('No se pudo comprobar el correo.');
            }

            var datos = await respuesta.json();

            if (datos && datos.disponible) {
                limpiarError(errorEmail);
                estadoEmail = true;
            } else {
                establecerError(errorEmail, 'Este correo electrónico ya está en uso.');
                estadoEmail = false;
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            establecerError(errorEmail, 'No se pudo verificar el correo electrónico.');
            estadoEmail = false;
        }

        comprobarBoton();
    }

    async function validarTelefono() {
        var valor = entradaTelefono ? entradaTelefono.value.trim() : '';
        var patron = /^\+\d{1,4} \d{6,11}$/;

        if (valor === '') {
            limpiarError(errorTelefono);
            estadoTelefono = true;
            comprobarBoton();
            return;
        }

        if (valor === telefonoOriginal) {
            limpiarError(errorTelefono);
            estadoTelefono = true;
            comprobarBoton();
            return;
        }

        if (!patron.test(valor)) {
            establecerError(errorTelefono, 'Formato: +34 600123456');
            estadoTelefono = false;
            comprobarBoton();
            return;
        }

        if (controladorTelefono) {
            controladorTelefono.abort();
        }

        controladorTelefono = new AbortController();

        try {
            var parametrosTelefono = new URLSearchParams({ telefono: valor });
            var respuestaTelefono = await fetch('/admin/usuarios/check-telefono?' + parametrosTelefono.toString(), {
                method: 'GET',
                signal: controladorTelefono.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!respuestaTelefono.ok) {
                throw new Error('No se pudo comprobar el teléfono.');
            }

            var datosTelefono = await respuestaTelefono.json();

            if (datosTelefono && datosTelefono.disponible) {
                limpiarError(errorTelefono);
                estadoTelefono = true;
            } else {
                establecerError(errorTelefono, 'Este teléfono ya está en uso.');
                estadoTelefono = false;
            }
        } catch (errorTelefonoFetch) {
            if (errorTelefonoFetch.name === 'AbortError') {
                return;
            }

            establecerError(errorTelefono, 'No se pudo verificar el teléfono.');
            estadoTelefono = false;
        }

        comprobarBoton();
    }

    async function validarDni() {
        var valor = entradaDni ? entradaDni.value.trim().toUpperCase() : '';

        if (valor === '') {
            limpiarError(errorDni);
            estadoDni = true;
            comprobarBoton();
            return;
        }

        if (valor === dniOriginal) {
            limpiarError(errorDni);
            estadoDni = true;
            comprobarBoton();
            return;
        }

        var patronDni = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/;
        var patronNie = /^[XYZ][0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKE]$/;
        var tipoSeleccionado = selectorTipoDoc ? selectorTipoDoc.value : 'DNI';

        var esValido = false;
        var mensajeError = 'Formato de documento inválido.';
        var letrasValidas = 'TRWAGMYFPDXBNJZSQVHLCKE';

        if (tipoSeleccionado === 'DNI') {
            if (patronDni.test(valor)) {
                var numeroDni = parseInt(valor.substring(0, 8), 10);
                var letraDni = valor.charAt(8);
                var letraEsperada = letrasValidas.charAt(numeroDni % 23);
                
                if (letraDni === letraEsperada) {
                    esValido = true;
                } else {
                    mensajeError = 'La letra del DNI no es correcta.';
                }
            } else {
                mensajeError = 'Introduce un DNI válido (8 números y 1 letra).';
            }
        } else if (tipoSeleccionado === 'NIE') {
            if (patronNie.test(valor)) {
                var primeraLetra = valor.charAt(0);
                var numeroNieStr = valor.substring(1, 8);
                var prefijo = '0';
                
                if (primeraLetra === 'Y') prefijo = '1';
                else if (primeraLetra === 'Z') prefijo = '2';
                
                var numeroCompleto = parseInt(prefijo + numeroNieStr, 10);
                var letraNie = valor.charAt(8);
                var letraEsperadaNie = letrasValidas.charAt(numeroCompleto % 23);
                
                if (letraNie === letraEsperadaNie) {
                    esValido = true;
                } else {
                    mensajeError = 'La letra del NIE no es correcta.';
                }
            } else {
                mensajeError = 'Introduce un NIE válido (Letra, 7 números, Letra).';
            }
        }

        if (!esValido) {
            establecerError(errorDni, mensajeError);
            estadoDni = false;
            comprobarBoton();
            return;
        }

        if (controladorDni) {
            controladorDni.abort();
        }

        controladorDni = new AbortController();

        try {
            var parametrosDni = new URLSearchParams({ dni: valor });
            var respuestaDni = await fetch('/admin/usuarios/check-dni?' + parametrosDni.toString(), {
                method: 'GET',
                signal: controladorDni.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!respuestaDni.ok) {
                throw new Error('No se pudo comprobar el documento.');
            }

            var datosDni = await respuestaDni.json();

            if (datosDni && datosDni.disponible) {
                limpiarError(errorDni);
                estadoDni = true;
            } else {
                establecerError(errorDni, 'Este documento ya está registrado.');
                estadoDni = false;
            }
        } catch (errorDniFetch) {
            if (errorDniFetch.name === 'AbortError') {
                return;
            }

            establecerError(errorDni, 'No se pudo verificar el documento.');
            estadoDni = false;
        }

        comprobarBoton();
    }

    function validarDireccion() {
        var valor = entradaDireccion ? entradaDireccion.value.trim() : '';

        if (valor.length > 255) {
            establecerError(errorDireccion, 'La dirección fiscal no puede superar 255 caracteres.');
            estadoDireccion = false;
        } else {
            limpiarError(errorDireccion);
            estadoDireccion = true;
        }

        comprobarBoton();
    }

    function validarCif(ignorarVacio = false) {
        if (!entradaCif || !contenedorCif || contenedorCif.style.display === 'none') {
            estadoCif = true;
            comprobarBoton();
            return;
        }

        var valor = entradaCif.value.trim().toUpperCase();
        var patronCif = /^[A-JUV][0-9]{7}[0-9A-J]$/;

        if (valor === '') {
            if (ignorarVacio === true) {
                limpiarError(errorCif);
            } else {
                establecerError(errorCif, 'El NIF de la empresa es obligatorio.');
            }
            estadoCif = false;
        } else if (!patronCif.test(valor)) {
            establecerError(errorCif, 'Introduce un NIF válido (Letra, 7 números, número/letra).');
            estadoCif = false;
        } else {
            limpiarError(errorCif);
            estadoCif = true;
        }

        comprobarBoton();
    }

    function validarFechaNacimiento() {
        if (!entradaFechaNacimiento) {
            estadoFechaNacimiento = true;
            comprobarBoton();
            return;
        }

        var valor = entradaFechaNacimiento.value.trim();

        if (valor === '') {
            limpiarError(errorFechaNacimiento);
            estadoFechaNacimiento = true;
        } else {
            var fecha = new Date(valor);
            var hoy = new Date();
            var minima = new Date();

            minima.setFullYear(hoy.getFullYear() - 18);

            if (isNaN(fecha.getTime()) || fecha > minima) {
                establecerError(errorFechaNacimiento, 'Debes ser mayor de edad.');
                estadoFechaNacimiento = false;
            } else {
                limpiarError(errorFechaNacimiento);
                estadoFechaNacimiento = true;
            }
        }

        comprobarBoton();
    }

    function validarAvatar() {
        if (!entradaAvatar || !entradaAvatar.files || entradaAvatar.files.length === 0) {
            limpiarError(errorAvatar);
            estadoAvatar = true;
            comprobarBoton();
            return;
        }

        var archivo = entradaAvatar.files[0];
        var extensionesPermitidas = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        var tamanoMaximo = 2 * 1024 * 1024;

        if (extensionesPermitidas.indexOf(archivo.type) === -1) {
            establecerError(errorAvatar, 'La imagen debe ser JPEG, PNG, GIF o WebP.');
            estadoAvatar = false;
        } else if (archivo.size > tamanoMaximo) {
            establecerError(errorAvatar, 'La imagen no puede superar los 2MB.');
            estadoAvatar = false;
        } else {
            limpiarError(errorAvatar);
            estadoAvatar = true;
        }

        comprobarBoton();
    }

    function validarContrasena() {
        var valorNueva = entradaContrasenaNueva ? entradaContrasenaNueva.value.trim() : '';
        var valorConfirmacion = entradaContrasenaConfirmacion ? entradaContrasenaConfirmacion.value.trim() : '';

        if (valorNueva === '' && valorConfirmacion === '') {
            limpiarError(errorContrasenaNueva);
            limpiarError(errorContrasenaConfirmacion);
            estadoContrasenaNueva = true;
            estadoContrasenaConfirmacion = true;
            comprobarBoton();
            return;
        }

        if (valorNueva === '') {
            limpiarError(errorContrasenaNueva);
            limpiarError(errorContrasenaConfirmacion);
            estadoContrasenaNueva = true;
            estadoContrasenaConfirmacion = true;
            comprobarBoton();
            return;
        }

        if (valorConfirmacion === '') {
            establecerError(errorContrasenaNueva, '');
            establecerError(errorContrasenaConfirmacion, 'Confirma la nueva contraseña.');
            estadoContrasenaNueva = valorNueva.length >= 8 && /[0-9]/.test(valorNueva);
            estadoContrasenaConfirmacion = false;
            comprobarBoton();
            return;
        }

        if (valorNueva.length < 8) {
            establecerError(errorContrasenaNueva, 'La nueva contraseña debe tener al menos 8 caracteres.');
            estadoContrasenaNueva = false;
        } else if (!/[0-9]/.test(valorNueva)) {
            establecerError(errorContrasenaNueva, 'La nueva contraseña debe contener al menos un número.');
            estadoContrasenaNueva = false;
        } else {
            limpiarError(errorContrasenaNueva);
            estadoContrasenaNueva = true;
        }

        if (valorNueva === '' || valorConfirmacion === '') {
            establecerError(errorContrasenaConfirmacion, 'Confirma la nueva contraseña.');
            estadoContrasenaConfirmacion = false;
        } else if (valorNueva !== valorConfirmacion) {
            establecerError(errorContrasenaConfirmacion, 'Las contraseñas no coinciden.');
            estadoContrasenaConfirmacion = false;
        } else {
            limpiarError(errorContrasenaConfirmacion);
            estadoContrasenaConfirmacion = true;
        }

        comprobarBoton();
    }

    function programarValidacion(fn, temporizadorRef) {
        return function () {
            clearTimeout(temporizadorRef.valor);
            temporizadorRef.valor = setTimeout(fn, 250);
        };
    }

    var temporizadorEmailRef = { valor: null };
    var temporizadorTelefonoRef = { valor: null };
    var temporizadorDniRef = { valor: null };

    entradaNombre.addEventListener('input', validarNombre);
    entradaNombre.addEventListener('blur', validarNombre);

    entradaEmail.addEventListener('input', function () {
        clearTimeout(temporizadorEmailRef.valor);
        temporizadorEmailRef.valor = setTimeout(validarEmail, 250);
    });
    entradaEmail.addEventListener('blur', validarEmail);

    if (entradaTelefono) {
        entradaTelefono.addEventListener('input', function () {
            clearTimeout(temporizadorTelefonoRef.valor);
            temporizadorTelefonoRef.valor = setTimeout(validarTelefono, 250);
        });
        entradaTelefono.addEventListener('blur', validarTelefono);
    }

    if (entradaDni) {
        entradaDni.addEventListener('input', function () {
            clearTimeout(temporizadorDniRef.valor);
            temporizadorDniRef.valor = setTimeout(validarDni, 250);
        });
        entradaDni.addEventListener('blur', validarDni);
    }

    if (entradaDireccion) {
        entradaDireccion.addEventListener('input', validarDireccion);
        entradaDireccion.addEventListener('blur', validarDireccion);
    }

    if (entradaCif) {
        entradaCif.addEventListener('input', function() { validarCif(false); });
        entradaCif.addEventListener('blur', function() { validarCif(false); });
    }

    if (entradaFechaNacimiento) {
        entradaFechaNacimiento.addEventListener('change', validarFechaNacimiento);
        entradaFechaNacimiento.addEventListener('blur', validarFechaNacimiento);
    }

    if (entradaAvatar) {
        entradaAvatar.addEventListener('change', validarAvatar);
    }

    if (entradaContrasenaNueva) {
        entradaContrasenaNueva.addEventListener('input', validarContrasena);
        entradaContrasenaNueva.addEventListener('blur', validarContrasena);
    }

    if (entradaContrasenaConfirmacion) {
        entradaContrasenaConfirmacion.addEventListener('input', validarContrasena);
        entradaContrasenaConfirmacion.addEventListener('blur', validarContrasena);
    }

    formulario.addEventListener('submit', function (evento) {
        validarNombre();
        validarEmail();
        validarTelefono();
        validarDni();
        validarDireccion();
        validarCif(false);
        validarFechaNacimiento();
        validarAvatar();
        validarContrasena();

        if (botonEnviar.disabled) {
            evento.preventDefault();
        }
    });

    validarNombre();
    validarEmail();
    validarTelefono();
    validarDni();
    validarDireccion();
    validarCif(true);
    validarFechaNacimiento();
    validarAvatar();
    validarContrasena();
}
