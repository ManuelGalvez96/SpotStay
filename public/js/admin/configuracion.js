var inicializarValidacionNotificacionesAdmin = function () {
    var form = document.getElementById('form-notificacion-admin');

    if (!form) {
        return;
    }

    var destino = document.getElementById('destinoRolNotificacion');
    var alcance = document.getElementById('alcanceDestinoNotificacion');
    var usuario = document.getElementById('usuarioDestinoNotificacion');
    var titulo = form.querySelector('[name="titulo_notificacion"]');
    var mensaje = form.querySelector('[name="mensaje_notificacion"]');
    var botonEnviar = form.querySelector('button[type="submit"]');

    var bloqueAlcance = document.getElementById('bloqueAlcanceNotificacion');
    var bloqueUsuario = document.getElementById('bloqueUsuarioNotificacion');

    var errorDestino = document.getElementById('errorDestinoNotificacion');
    var errorAlcance = document.getElementById('errorAlcanceNotificacion');
    var errorUsuario = document.getElementById('errorUsuarioNotificacion');
    var errorTitulo = document.getElementById('errorTituloNotificacion');
    var errorMensaje = document.getElementById('errorMensajeNotificacion');

    var usuariosOriginales = [];
    var i;

    function limpiarTexto(valor) {
        return valor ? String(valor).trim() : '';
    }

    function ponerError(elemento, texto) {
        if (elemento) {
            elemento.textContent = texto;
        }
    }

    function quitarError(elemento) {
        if (elemento) {
            elemento.textContent = '';
        }
    }

    function guardarUsuariosOriginales() {
        if (!usuario) {
            return;
        }

        usuariosOriginales = [];

        for (i = 0; i < usuario.options.length; i++) {
            usuariosOriginales.push({
                value: usuario.options[i].value,
                text: usuario.options[i].textContent,
                roles: usuario.options[i].getAttribute('data-roles') || ''
            });
        }
    }

    function mostrarCamposSegunDestino() {
        var valorDestino = limpiarTexto(destino ? destino.value : '');
        var valorAlcance = limpiarTexto(alcance ? alcance.value : '');

        if (bloqueAlcance) {
            bloqueAlcance.classList.toggle('d-none', valorDestino === '');
        }

        if (bloqueUsuario) {
            bloqueUsuario.classList.toggle('d-none', !(valorDestino && valorAlcance === 'usuario'));
        }

        if (valorDestino === '') {
            if (alcance) {
                alcance.value = '';
            }
            if (usuario) {
                usuario.value = '';
            }
        }

        if (valorAlcance !== 'usuario' && usuario) {
            usuario.value = '';
        }
    }

    function rellenarUsuarios() {
        if (!usuario) {
            return;
        }

        var valorDestino = limpiarTexto(destino ? destino.value : '');
        var valorAlcance = limpiarTexto(alcance ? alcance.value : '');

        usuario.innerHTML = '<option value="">Selecciona un usuario</option>';

        if (!valorDestino || valorAlcance !== 'usuario') {
            return;
        }

        for (i = 0; i < usuariosOriginales.length; i++) {
            if (!usuariosOriginales[i].value) {
                continue;
            }

            if (valorDestino === 'todos' || usuariosOriginales[i].roles.indexOf(valorDestino) !== -1) {
                var opcion = document.createElement('option');
                opcion.value = usuariosOriginales[i].value;
                opcion.textContent = usuariosOriginales[i].text;
                opcion.setAttribute('data-roles', usuariosOriginales[i].roles);
                usuario.appendChild(opcion);
            }
        }
    }

    function formularioValido() {
        var valorDestino = limpiarTexto(destino ? destino.value : '');
        var valorAlcance = limpiarTexto(alcance ? alcance.value : '');
        var valorUsuario = limpiarTexto(usuario ? usuario.value : '');
        var valorTitulo = limpiarTexto(titulo ? titulo.value : '');
        var valorMensaje = limpiarTexto(mensaje ? mensaje.value : '');

        if (!valorDestino || !valorAlcance || !valorTitulo || !valorMensaje) {
            return false;
        }

        if (valorAlcance === 'usuario' && !valorUsuario) {
            return false;
        }

        return true;
    }

    function actualizarBoton() {
        if (botonEnviar) {
            botonEnviar.disabled = !formularioValido();
        }
    }

    if (destino) {
        destino.onchange = function () {
            quitarError(errorDestino);
            mostrarCamposSegunDestino();
            rellenarUsuarios();
            actualizarBoton();
        };

        destino.onblur = function () {
            if (!limpiarTexto(destino.value)) {
                ponerError(errorDestino, 'Selecciona el rol de destino.');
            } else {
                quitarError(errorDestino);
            }
            mostrarCamposSegunDestino();
            rellenarUsuarios();
            actualizarBoton();
        };
    }

    if (alcance) {
        alcance.onchange = function () {
            quitarError(errorAlcance);
            mostrarCamposSegunDestino();
            rellenarUsuarios();
            actualizarBoton();
        };

        alcance.onblur = function () {
            if (limpiarTexto(destino.value) && !limpiarTexto(alcance.value)) {
                ponerError(errorAlcance, 'Selecciona el alcance del envío.');
            } else {
                quitarError(errorAlcance);
            }
            mostrarCamposSegunDestino();
            rellenarUsuarios();
            actualizarBoton();
        };
    }

    if (usuario) {
        usuario.onchange = function () {
            quitarError(errorUsuario);
            actualizarBoton();
        };

        usuario.onblur = function () {
            if (limpiarTexto(alcance.value) === 'usuario' && !limpiarTexto(usuario.value)) {
                ponerError(errorUsuario, 'Selecciona un usuario concreto.');
            } else {
                quitarError(errorUsuario);
            }
            actualizarBoton();
        };
    }

    if (titulo) {
        titulo.oninput = function () {
            if (limpiarTexto(titulo.value)) {
                quitarError(errorTitulo);
            }
            actualizarBoton();
        };

        titulo.onblur = function () {
            if (!limpiarTexto(titulo.value)) {
                ponerError(errorTitulo, 'El título no puede estar vacío.');
            } else {
                quitarError(errorTitulo);
            }
            actualizarBoton();
        };
    }

    if (mensaje) {
        mensaje.oninput = function () {
            if (limpiarTexto(mensaje.value)) {
                quitarError(errorMensaje);
            }
            actualizarBoton();
        };

        mensaje.onblur = function () {
            if (!limpiarTexto(mensaje.value)) {
                ponerError(errorMensaje, 'El mensaje no puede estar vacío.');
            } else {
                quitarError(errorMensaje);
            }
            actualizarBoton();
        };
    }

    form.onsubmit = function (evento) {
        if (!formularioValido()) {
            evento.preventDefault();

            if (!limpiarTexto(destino.value)) {
                ponerError(errorDestino, 'Selecciona el rol de destino.');
            }

            if (!limpiarTexto(alcance.value)) {
                ponerError(errorAlcance, 'Selecciona el alcance del envío.');
            }

            if (limpiarTexto(alcance.value) === 'usuario' && !limpiarTexto(usuario.value)) {
                ponerError(errorUsuario, 'Selecciona un usuario concreto.');
            }

            if (!limpiarTexto(titulo.value)) {
                ponerError(errorTitulo, 'El título no puede estar vacío.');
            }

            if (!limpiarTexto(mensaje.value)) {
                ponerError(errorMensaje, 'El mensaje no puede estar vacío.');
            }

            return false;
        }

        return true;
    };

    guardarUsuariosOriginales();
    mostrarCamposSegunDestino();
    rellenarUsuarios();
    actualizarBoton();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarValidacionNotificacionesAdmin);
} else {
    inicializarValidacionNotificacionesAdmin();
}
