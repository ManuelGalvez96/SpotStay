@extends('layouts.miembro')

@section('title', 'Inicio')
@section('body-class', 'pagina-mensajes')
@section('content')
    <section class="mensajes-layout">
        <aside class="mensajes-lista panel-filtros-miembro">
            <h2 class="titulo-filtros">Conversaciones</h2>
            <button class="boton-conversaciones-cerrar" id="boton-cerrar-conversaciones" type="button">
                <i class="bi bi-x" aria-hidden="true"></i>
                Cerrar
            </button>
            <a class="detalle-volver" href="{{ url('/miembro/inicio') }}" aria-label="Volver">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </a>

            @if ($conversaciones->isEmpty())
                <p class="descripcion-filtros">Aun no tienes conversaciones.</p>
            @else
                @foreach ($conversaciones as $conversacion)
                    @php
                        $otroUsuario = $conversacion->participantes->firstWhere('id_usuario', '!=', auth()->id());
                        $ultimo = $conversacion->ultimoMensaje;
                        $activa = $conversacionActiva && $conversacionActiva->id_conversacion === $conversacion->id_conversacion;
                    @endphp

                    <a href="{{ route('miembro.mensajes.show', ['id' => $conversacion->id_conversacion]) }}" class="mensajes-item {{ $activa ? 'activo' : '' }}">
                        <p class="mensajes-item-titulo">{{ $otroUsuario->nombre_usuario ?? 'Sin participante' }}</p>
                        <p class="mensajes-item-sub">{{ $conversacion->propiedad->titulo_propiedad ?? 'Sin propiedad' }}</p>
                        <p class="mensajes-item-preview">{{ $ultimo->cuerpo_mensaje ?? 'Sin mensajes' }}</p>
                    </a>
                @endforeach
            @endif
        </aside>

        <section class="mensajes-panel listado-propiedades">
            @php
                $otroUsuario = null;
                if ($conversacionActiva) {
                    $otroUsuario = $conversacionActiva->participantes->firstWhere('id_usuario', '!=', auth()->id());
                }
            @endphp

            <div class="mensajes-barra-mobile">
                <button class="boton-conversaciones-toggle" id="boton-abrir-conversaciones" type="button">
                    <i class="bi bi-list" aria-hidden="true"></i>
                    Conversaciones
                </button>
                <span class="mensajes-chat-actual">{{ optional($otroUsuario)->nombre_usuario ?? 'Selecciona una conversacion' }}</span>
            </div>

            @if (!$conversacionActiva)
                <div class="estado-vacio">
                    <p>Selecciona una conversación para empezar a chatear.</p>
                </div>
            @else
                <header class="mensajes-cabecera">
                    <h2 class="titulo-listado">{{ $otroUsuario->nombre_usuario ?? 'Chat' }}</h2>
                    <span class="contador-propiedades">{{ $conversacionActiva->propiedad->titulo_propiedad ?? 'Sin propiedad' }}</span>
                </header>

                <div
                    id="mensajes-datos"
                    data-conversacion-id="{{ $conversacionActiva->id_conversacion }}"
                    data-url-mensajes="{{ route('miembro.mensajes.mensajes', ['id' => $conversacionActiva->id_conversacion]) }}"
                    data-url-enviar="{{ route('miembro.mensajes.enviar', ['id' => $conversacionActiva->id_conversacion]) }}"
                ></div>

                <div class="mensajes-mensajes" id="mensajes-mensajes">
                    @forelse ($mensajes as $mensaje)
                        @php
                            $esMio = (int) $mensaje->id_remitente_fk === (int) auth()->id();
                        @endphp

                        <div class="mensajes-burbuja {{ $esMio ? 'mio' : 'otro' }}">
                            <p class="mensajes-burbuja-texto">{{ $mensaje->cuerpo_mensaje }}</p>
                            <span class="mensajes-burbuja-fecha">{{ optional($mensaje->creado_mensaje)->format('d/m/Y H:i') }}</span>
                        </div>
                    @empty
                        <div class="estado-vacio">
                            <p>Aun no hay mensajes en esta conversación.</p>
                        </div>
                    @endforelse
                </div>

                <form id="mensajes-formulario" action="{{ route('miembro.mensajes.enviar', ['id' => $conversacionActiva->id_conversacion]) }}" method="POST" class="mensajes-formulario filtros-miembro">
                    @csrf
                    <div class="grupo-filtro">
                        <label class="etiqueta-filtro" for="mensaje">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" class="campo-filtro" rows="3" required>{{ old('mensaje') }}</textarea>
                    </div>
                    <button type="submit" class="boton-aplicar">Enviar</button>
                </form>
            @endif
        </section>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/miembro/mensajes.js') }}"></script>
@endsection