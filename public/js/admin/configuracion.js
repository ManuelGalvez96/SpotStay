var inicializarValidacionNotificacionesAdmin = function () {
    var form = document.getElementById('form-notificacion-admin');

    if (!form) {
        return;
    }

    var campos = {
        destino: form.querySelector('[name="destino"]'),
        rolDestino: form.querySelector('[name="rol_destino"]'),
        usuarioDestino: form.querySelector('[name="usuario_destino"]'),
        titulo: form.querySelector('[name="titulo_notificacion"]'),
        mensaje: form.querySelector('[name="mensaje_notificacion"]')
    };

    var botonEnviar = form.querySelector('button[type="submit"]');
    var errores = {
        destino: document.getElementById('errorDestinoNotificacion'),
        rol: document.getElementById('errorRolNotificacion'),
        usuario: document.getElementById('errorUsuarioNotificacion'),
        titulo: document.getElementById('errorTituloNotificacion'),
        mensaje: document.getElementById('errorMensajeNotificacion')
    };

    function valorLimpio(elemento) {
        return elemento && typeof elemento.value === 'string' ? elemento.value.trim() : '';
    }

    function mostrarError(campo, mensaje) {
        if (errores[campo]) {
            errores[campo].textContent = mensaje;
        }
    }

    function limpiarError(campo) {
        if (errores[campo]) {
            errores[campo].textContent = ' ';
        }
    }

    function validarDestino() {
        var destino = valorLimpio(campos.destino);

        if (!destino) {
            return false;
        }

        if (destino === 'rol' && !valorLimpio(campos.rolDestino)) {
            return false;
        }

        if (destino === 'usuario' && !valorLimpio(campos.usuarioDestino)) {
            return false;
        }

        return true;
    }

    function validarCamposVacios() {
        var titulo = valorLimpio(campos.titulo);
        var mensaje = valorLimpio(campos.mensaje);

        return Boolean(valorLimpio(campos.destino) && titulo && mensaje && validarDestino());
    }

    function actualizarBoton() {
        botonEnviar.disabled = !validarCamposVacios();
    }

    campos.destino.onblur = function () {
        var destino = valorLimpio(campos.destino);
        if (!destino) {
            mostrarError('destino', 'Selecciona el destino de la notificación.');
            limpiarError('rol');
            limpiarError('usuario');
        } else {
            limpiarError('destino');
            if (destino !== 'rol') {
                limpiarError('rol');
            }
            if (destino !== 'usuario') {
                limpiarError('usuario');
            }
        }
        actualizarBoton();
    };

    campos.destino.onchange = function () {
        campos.destino.onblur();
    };

    campos.destino.oninput = function () {
        if (valorLimpio(campos.destino)) {
            limpiarError('destino');
        }
        actualizarBoton();
    };

    campos.rolDestino.onblur = function () {
        if (valorLimpio(campos.destino) === 'rol' && !valorLimpio(campos.rolDestino)) {
            mostrarError('rol', 'Selecciona un rol de destino.');
        } else {
            limpiarError('rol');
        }
        actualizarBoton();
    };

    campos.rolDestino.oninput = function () {
        if (valorLimpio(campos.rolDestino)) {
            limpiarError('rol');
        }
        actualizarBoton();
    };

    campos.usuarioDestino.onblur = function () {
        if (valorLimpio(campos.destino) === 'usuario' && !valorLimpio(campos.usuarioDestino)) {
            mostrarError('usuario', 'Selecciona un usuario concreto.');
        } else {
            limpiarError('usuario');
        }
        actualizarBoton();
    };

    campos.usuarioDestino.oninput = function () {
        if (valorLimpio(campos.usuarioDestino)) {
            limpiarError('usuario');
        }
        actualizarBoton();
    };

    campos.titulo.onblur = function () {
        if (!valorLimpio(campos.titulo)) {
            mostrarError('titulo', 'El título no puede estar vacío.');
        } else {
            limpiarError('titulo');
        }
        actualizarBoton();
    };

    campos.titulo.oninput = function () {
        if (valorLimpio(campos.titulo)) {
            limpiarError('titulo');
        }
        actualizarBoton();
    };

    campos.mensaje.onblur = function () {
        if (!valorLimpio(campos.mensaje)) {
            mostrarError('mensaje', 'El mensaje no puede estar vacío.');
        } else {
            limpiarError('mensaje');
        }
        actualizarBoton();
    };

    campos.mensaje.oninput = function () {
        if (valorLimpio(campos.mensaje)) {
            limpiarError('mensaje');
        }
        actualizarBoton();
    };

    form.onsubmit = function (evento) {
        var valido = validarCamposVacios();
        if (!valido) {
            evento.preventDefault();

            if (!valorLimpio(campos.destino)) {
                mostrarError('destino', 'Selecciona el destino de la notificación.');
                return false;
            }

            if (valorLimpio(campos.destino) === 'rol' && !valorLimpio(campos.rolDestino)) {
                mostrarError('rol', 'Selecciona un rol de destino.');
                return false;
            }

            if (valorLimpio(campos.destino) === 'usuario' && !valorLimpio(campos.usuarioDestino)) {
                mostrarError('usuario', 'Selecciona un usuario concreto.');
                return false;
            }

            if (!valorLimpio(campos.titulo)) {
                mostrarError('titulo', 'El título no puede estar vacío.');
                return false;
            }

            if (!valorLimpio(campos.mensaje)) {
                mostrarError('mensaje', 'El mensaje no puede estar vacío.');
                return false;
            }
        }
        return true;
    };

    actualizarBoton();
};

inicializarValidacionNotificacionesAdmin();
