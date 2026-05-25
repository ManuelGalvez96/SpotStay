<link rel="stylesheet" href="{{ asset('css/asesoria-chatbot.css') }}">

<div class="spoty-chatbot" id="spotyChatbot"
     data-iniciar-url="{{ $chatbotIniciarUrl }}"
     data-mensaje-url="{{ $chatbotMensajeUrl }}"
     data-historial-url="{{ $chatbotHistorialUrl }}"
     data-avatar-url="{{ asset('img/spoty.png') }}"
     data-usuario-avatar-url="{{ $chatbotUsuarioAvatar ?? '' }}"
     data-usuario-nombre="{{ $chatbotUsuarioNombre ?? '' }}">
    <button class="spoty-chatbot-toggle" id="spotyChatbotToggle" aria-label="Abrir chat con Spoty">
        <img src="{{ asset('img/spoty.png') }}" alt="Spoty" class="spoty-chatbot-toggle-img">
        <span class="spoty-chatbot-toggle-close">✕</span>
    </button>
    <div class="spoty-chatbot-panel" id="spotyChatbotPanel">
        <div class="spoty-chatbot-header">
            <div class="spoty-chatbot-header-left">
                <img src="{{ asset('img/spoty.png') }}" alt="Spoty" class="spoty-chatbot-avatar">
                <div>
                    <h3>Spoty</h3>
                    <p>Tu asistente legal IA</p>
                </div>
            </div>
            <button class="spoty-chatbot-btn-cerrar" id="spotyChatbotCerrar" type="button" aria-label="Cerrar chat">
                <i class="bi bi-x" aria-hidden="true"></i>
            </button>
        </div>
        <div class="spoty-chatbot-mensajes" id="spotyChatbotMensajes">
            <div class="spoty-chatbot-mensaje spoty-chatbot-mensaje-ia">
                <img src="{{ asset('img/spoty.png') }}" alt="Spoty" class="spoty-chatbot-avatar-msg">
                <div class="spoty-chatbot-burbuja">
                    ¡Hola! Soy Spoty, tu asistente legal de SpotStay. Pregúntame lo que quieras sobre alquiler, vivienda, contratos y normativa española.
                </div>
            </div>
        </div>
        <div class="spoty-chatbot-input-area">
            <div class="spoty-chatbot-escribiendo" id="spotyChatbotEscribiendo">
                <img src="{{ asset('img/spoty.png') }}" alt="Spoty" class="spoty-chatbot-avatar-msg">
                <div class="spoty-chatbot-burbuja spoty-chatbot-escribiendo-burbuja">
                    <span class="spoty-dot"></span>
                    <span class="spoty-dot"></span>
                    <span class="spoty-dot"></span>
                </div>
            </div>
            <div class="spoty-chatbot-input-row">
                <input type="text" class="spoty-chatbot-input" id="spotyChatbotInput" placeholder="Escribe tu consulta legal..." maxlength="2000">
                <button class="spoty-chatbot-enviar" id="spotyChatbotEnviar" aria-label="Enviar mensaje">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/asesoria-chatbot.js') }}"></script>
