(function () {
    var widget = document.getElementById('spotyChatbot');
    if (!widget) return;

    var toggleBtn = document.getElementById('spotyChatbotToggle');
    var mensajesContainer = document.getElementById('spotyChatbotMensajes');
    var input = document.getElementById('spotyChatbotInput');
    var enviarBtn = document.getElementById('spotyChatbotEnviar');
    var escribiendo = document.getElementById('spotyChatbotEscribiendo');

    var iniciarUrl = widget.dataset.iniciarUrl;
    var mensajeUrl = widget.dataset.mensajeUrl;
    var historialUrl = widget.dataset.historialUrl;
    var avatarUrl = widget.dataset.avatarUrl;

    var idSesion = null;
    var enviando = false;
    var inicializado = false;
    var csrfToken = function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    };

    function scrollAbajo() {
        mensajesContainer.scrollTop = mensajesContainer.scrollHeight;
    }

    function mostrarEscribiendo() {
        escribiendo.classList.add('visible');
        scrollAbajo();
    }

    function ocultarEscribiendo() {
        escribiendo.classList.remove('visible');
    }

    function agregarMensaje(texto, rol) {
        var div = document.createElement('div');
        div.className = 'spoty-chatbot-mensaje ' + (rol === 'usuario' ? 'spoty-chatbot-mensaje-usuario' : 'spoty-chatbot-mensaje-ia');

        var avatar = document.createElement('img');
        avatar.src = avatarUrl;
        avatar.alt = rol === 'usuario' ? 'Tú' : 'Spoty';
        avatar.className = 'spoty-chatbot-avatar-msg';

        var burbuja = document.createElement('div');
        burbuja.className = 'spoty-chatbot-burbuja';
        burbuja.textContent = texto;

        div.appendChild(avatar);
        div.appendChild(burbuja);
        mensajesContainer.appendChild(div);
        scrollAbajo();
    }

    function iniciarSesion() {
        var fd = new FormData();
        return fetch(iniciarUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            body: fd,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            idSesion = data.id_sesion_chatbot;
            try { localStorage.setItem('spoty_sesion_id', idSesion); } catch (e) {}
            inicializado = true;
        });
    }

    function cargarHistorial(sesionId) {
        return fetch(historialUrl + '?id_sesion_chatbot=' + sesionId, {
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        })
        .then(function (r) {
            if (!r.ok) throw new Error('Historial not found');
            return r.json();
        })
        .then(function (mensajes) {
            mensajes.forEach(function (m) {
                agregarMensaje(m.cuerpo_mensaje_chatbot, m.rol_mensaje_chatbot === 'usuario' ? 'usuario' : 'ia');
            });
        });
    }

    function restaurarSesion() {
        var saved;
        try { saved = localStorage.getItem('spoty_sesion_id'); } catch (e) {}
        if (saved) {
            idSesion = parseInt(saved, 10);
            return cargarHistorial(idSesion).then(function () {
                inicializado = true;
            }).catch(function () {
                idSesion = null;
                try { localStorage.removeItem('spoty_sesion_id'); } catch (e) {}
                return iniciarSesion();
            });
        }
        return iniciarSesion();
    }

    function enviarMensaje(texto) {
        if (enviando || !texto.trim()) return;

        if (!inicializado) {
            agregarMensaje('Espera un momento mientras conecto...', 'ia');
            return;
        }

        enviando = true;
        enviarBtn.disabled = true;
        input.disabled = true;

        agregarMensaje(texto.trim(), 'usuario');
        input.value = '';
        mostrarEscribiendo();

        var fd = new FormData();
        fd.append('mensaje', texto.trim());
        fd.append('id_sesion_chatbot', idSesion);

        fetch(mensajeUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: fd,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            ocultarEscribiendo();
            agregarMensaje(data.respuesta, 'ia');
        })
        .catch(function () {
            ocultarEscribiendo();
            agregarMensaje('Lo siento, hubo un error. Intentalo de nuevo.', 'ia');
        })
        .then(function () {
            enviando = false;
            enviarBtn.disabled = false;
            input.disabled = false;
            input.focus();
        });
    }

    function toggleChat() {
        widget.classList.toggle('abierto');
        if (widget.classList.contains('abierto') && !inicializado) {
            restaurarSesion();
        }
        if (widget.classList.contains('abierto')) {
            setTimeout(function () { input.focus(); }, 300);
        }
    }

    toggleBtn.addEventListener('click', toggleChat);

    enviarBtn.addEventListener('click', function () {
        enviarMensaje(input.value);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            enviarMensaje(input.value);
        }
    });
})();
