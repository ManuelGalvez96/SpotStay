@extends('layouts.arrendador')

@section('titulo', 'Mensajes - Arrendador')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/arrendador/mensajes.css') }}" />
@endsection

@section('content')
<div class="pagina" style="padding-top: 0;">
    <header class="cabecera" style="padding-top: 0; padding-bottom: 20px;">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Mensajes con inquilinos</h1>
            <p class="subtitulo">Consulta y responde conversaciones sin salir de esta pantalla.</p>
        </div>
    </header>

    <section class="contenedor-chat" data-arrendador-id="{{ $arrendadorId }}">
        <aside class="panel-conversaciones">
            <h2>Conversaciones</h2>
            <div class="lista-conversaciones" id="listaConversaciones">
                @forelse ($conversaciones as $conversacion)
                    <button
                        class="item-conversacion"
                        data-conversacion-id="{{ $conversacion->id_conversacion }}"
                        data-arrendador-id="{{ $arrendadorId }}"
                    >
                        <strong>{{ $conversacion->nombre_inquilino }}</strong>
                        <small>{{ $conversacion->email_inquilino }}</small>
                        <span>{{ $conversacion->resumen_ultimo_mensaje ?: 'Sin mensajes todavía' }}</span>
                    </button>
                @empty
                    <p class="vacio">No hay conversaciones disponibles por ahora.</p>
                @endforelse
            </div>
        </aside>

        <main class="panel-mensajes">
            <div class="cabecera-hilo">
                <h2 id="tituloHilo">Selecciona una conversación</h2>
                <p id="subtituloHilo" class="muted">El detalle aparecerá aquí.</p>
            </div>

            <div id="listaMensajes" class="lista-mensajes">
                <p class="muted">No has seleccionado ninguna conversación.</p>
            </div>

            <form id="formularioMensaje" class="formulario-mensaje" hidden>
                <input type="hidden" id="idConversacionSeleccionada" />
                <textarea id="textoMensaje" rows="3" maxlength="2000" placeholder="Escribe tu mensaje..."></textarea>
                <button type="submit" class="btn-enviar">Enviar</button>
            </form>
        </main>
    </section>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/arrendador/mensajes.js') }}"></script>
@endsection
