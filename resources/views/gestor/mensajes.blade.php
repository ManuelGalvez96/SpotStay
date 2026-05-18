@extends('layouts.gestor')
@section('titulo', 'Mensajes - Gestor SpotStay')

@section('css')
<link rel="stylesheet" href="{{ asset('css/gestor/mensajes.css') }}">
@endsection

@section('content')
<div class="hero-admin">
    <div class="hero-content">
        <h1>Mensajes</h1>
        <p>Conversaciones con arrendadores e inquilinos de tus propiedades asignadas</p>
    </div>
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
</div>

<section class="contenedor-chat" data-gestor-id="{{ $gestorId }}">
    <aside class="panel-conversaciones">
        <h2>Conversaciones</h2>
        <div class="lista-conversaciones" id="listaConversaciones">
            @forelse ($conversaciones as $conversacion)
                @php
                    $otro = $conversacion->participantes->firstWhere('id_usuario', '!=', $gestorId);
                    $ultimo = $conversacion->ultimoMensaje;
                    $activa = $conversacionActiva && $conversacionActiva->id_conversacion === $conversacion->id_conversacion;
                @endphp
                <button
                    class="item-conversacion {{ $activa ? 'activo' : '' }}"
                    data-conversacion-id="{{ $conversacion->id_conversacion }}"
                    data-propiedad-titulo="{{ $conversacion->propiedad->titulo_propiedad ?? 'Sin propiedad' }}"
                >
                    <strong>{{ $otro->nombre_usuario ?? 'Usuario' }}</strong>
                    <small>{{ $conversacion->propiedad->titulo_propiedad ?? 'Sin propiedad' }}</small>
                    <span>{{ $ultimo->cuerpo_mensaje ?? 'Sin mensajes todavía' }}</span>
                </button>
            @empty
                <p class="vacio">No hay conversaciones disponibles. Inicia un chat desde el detalle de una propiedad.</p>
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

        <form id="formularioMensaje" class="formulario-mensaje" @if(!$conversacionActiva) hidden @endif>
            <input type="hidden" id="idConversacionSeleccionada" value="{{ $conversacionActiva->id_conversacion ?? '' }}" />
            <textarea id="textoMensaje" rows="3" maxlength="2000" placeholder="Escribe tu mensaje..."></textarea>
            <button type="submit" class="btn-enviar">Enviar</button>
        </form>
    </main>
</section>

@if($conversacionActiva)
    <div id="datos-conversacion-activa"
         data-conversacion-id="{{ $conversacionActiva->id_conversacion }}"
         data-propiedad-titulo="{{ $conversacionActiva->propiedad->titulo_propiedad ?? 'Sin propiedad' }}">
    </div>
@endif
@endsection

@section('scripts')
<script src="{{ asset('js/gestor/mensajes.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const activa = document.getElementById('datos-conversacion-activa');
    if (activa) {
        const id = activa.getAttribute('data-conversacion-id');
        const titulo = activa.getAttribute('data-propiedad-titulo');
        if (id) {
            cargarConversacion(id, titulo);
        }
    }
});
</script>
@endsection
