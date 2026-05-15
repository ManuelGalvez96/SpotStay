<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - Arrendador</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-pi9qg5Dvprt5r+gZsxslCbWUUcc2/djiCCwYinnBJlcgkYR5LAWaxkulGLmQ40SP" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/arrendador/mensajes.css') }}" />
</head>
<body>
<x-arrendador.topbar :arrendadorId="$arrendadorId" :avatarInicial="$avatarInicial" />
<div class="pagina">
    <header class="cabecera">
        <div>
            <p class="etiqueta">Arrendador</p>
            <h1>Mensajes con inquilinos</h1>
            <p class="subtitulo">Consulta y responde conversaciones sin salir de esta pantalla.</p>
        </div>
        <div class="acciones-cabecera">
            <div class="avatar">{{ $avatarInicial }}</div>
            <a class="btn-volver" href="{{ route('arrendador.dashboard', ['arrendador_id' => $arrendadorId]) }}">Volver al dashboard</a>
            <a class="btn-volver" href="{{ route('logout') }}">Cerrar sesion</a>
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

<script src="{{ asset('js/arrendador/mensajes.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55RPKM/DDL/M2PgkxjQlro0Pnd8NF" crossorigin="anonymous"></script>
<script src="{{ asset('js/admin/layout.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/shared/swal-oso.js') }}"></script>
</body>
</html>
